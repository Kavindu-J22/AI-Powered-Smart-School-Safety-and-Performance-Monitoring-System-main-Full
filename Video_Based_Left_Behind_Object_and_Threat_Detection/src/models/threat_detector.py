"""
Threat Detection Model - Pose-Based Approach

Detects threatening behavior using YOLOv8-pose keypoints + optical flow.
No external datasets or heavy frameworks required — works on CPU.

How it works
------------
1. YOLOv8n-pose detects people and their 17 COCO keypoints per frame.
2. Four heuristic scores are computed:
   - Proximity   : bounding-box overlap between any two people
   - Arm-raise   : wrists elevated above shoulder level (punching pose)
   - Motion      : dense optical flow magnitude in person regions
   - Fall        : unusually wide bounding box (person knocked down)
3. Scores are blended and smoothed over a short history window.
4. If smoothed confidence >= threshold -> threat alert.
"""

import cv2
import numpy as np
from typing import List, Dict, Optional
from collections import deque
import logging

logger = logging.getLogger(__name__)

# COCO-Pose keypoint indices
KP_LEFT_SHOULDER  = 5
KP_RIGHT_SHOULDER = 6
KP_LEFT_WRIST     = 9
KP_RIGHT_WRIST    = 10


class ThreatDetector:
    """
    Detects threatening behavior (fighting, aggression, falls) using
    YOLOv8-pose keypoints and dense optical flow — no retraining needed.
    """

    def __init__(
        self,
        model_path: Optional[str] = None,   # unused, kept for API compat
        model_type: str = "pose",
        confidence_threshold: float = 0.40,
        clip_length: int = 8,
        device: str = None,
    ):
        import torch
        self.device = device or ("cuda" if torch.cuda.is_available() else "cpu")
        self.confidence_threshold = confidence_threshold
        self.history_len = max(clip_length, 4)
        self.model_type = "pose"

        # Threat class labels (returned in API response)
        self.threat_classes = ["fighting", "pushing", "aggressive_behavior", "fall_detected"]
        self.normal_class   = "normal"

        # Score history for temporal smoothing
        self._score_history: deque = deque(maxlen=self.history_len)
        # Previous grayscale frame for optical flow
        self._prev_gray: Optional[np.ndarray] = None
        # Frame buffer for add_frame() API compatibility
        self.frame_buffer: deque = deque(maxlen=clip_length)
        # Track previous person count so we can flush stale history when
        # the scene drops from ≥2 people to <2 (avoids lingering false alerts)
        self._prev_person_count: int = 0

        self.pose_model = self._load_pose_model()

    # -----------------------------------------------------------------------
    # Model loading
    # -----------------------------------------------------------------------

    def _load_pose_model(self):
        try:
            from ultralytics import YOLO
            model = YOLO("yolov8n-pose.pt")   # ~6 MB, auto-downloads
            model.to(self.device)
            logger.info(f"ThreatDetector: YOLOv8n-pose loaded on {self.device}")
            return model
        except Exception as exc:
            logger.error(f"ThreatDetector: Could not load pose model — {exc}")
            return None
    
    # -----------------------------------------------------------------------
    # Helper: extract persons from pose results
    # -----------------------------------------------------------------------

    def _extract_persons(self, results) -> List[Dict]:
        """Return list of {'bbox': [x1,y1,x2,y2], 'keypoints': ndarray(17,3)}."""
        persons = []
        if results is None:
            return persons
        for r in results:
            if r.boxes is None:
                continue
            boxes    = r.boxes.xyxy.cpu().numpy()
            kps      = r.keypoints.xy.cpu().numpy()   if r.keypoints is not None else None
            kp_confs = r.keypoints.conf.cpu().numpy() if (r.keypoints is not None and r.keypoints.conf is not None) else None
            for i, box in enumerate(boxes):
                kp_xy = kps[i]      if kps      is not None else np.zeros((17, 2))
                kp_c  = kp_confs[i] if kp_confs is not None else np.zeros(17)
                kp_full = np.concatenate([kp_xy, kp_c[:, None]], axis=1)   # (17, 3)
                persons.append({'bbox': box.tolist(), 'keypoints': kp_full})
        return persons

    # -----------------------------------------------------------------------
    # Heuristic scorers — each returns float in [0, 1]
    # -----------------------------------------------------------------------

    def _score_proximity(self, persons: List[Dict]) -> float:
        """
        High score only when people are physically very close (touching / overlapping).

        Uses center-to-center distance normalised by the average person width.
        The denominator was reduced from 3.0 to 1.5 so that normal classroom
        standing distance (1.5+ person-widths apart) produces a score of 0.0.
        Only people who are within arm's reach score meaningfully.

        Score mapping (D = distance / avg_width):
          D <= 0.0  → 1.0  (overlapping bboxes — physical contact)
          D  = 0.5  → 0.67 (very close — arm's reach, likely contact)
          D  = 1.0  → 0.33 (close — suspicious proximity)
          D >= 1.5  → 0.0  (normal classroom/conversation distance — no score)
        """
        if len(persons) < 2:
            return 0.0
        max_score = 0.0
        for i in range(len(persons)):
            for j in range(i + 1, len(persons)):
                b1, b2 = persons[i]['bbox'], persons[j]['bbox']
                # Center points
                cx1 = (b1[0] + b1[2]) / 2.0;  cy1 = (b1[1] + b1[3]) / 2.0
                cx2 = (b2[0] + b2[2]) / 2.0;  cy2 = (b2[1] + b2[3]) / 2.0
                # Average person width as normalisation reference
                w1 = max(b1[2] - b1[0], 1.0)
                w2 = max(b2[2] - b2[0], 1.0)
                avg_w = (w1 + w2) / 2.0
                # Euclidean distance in "person-widths"
                dist = ((cx2 - cx1) ** 2 + (cy2 - cy1) ** 2) ** 0.5
                norm_dist = dist / avg_w
                # Raised denominator from 3.0 → 1.5: normal conversation distance (1.5 widths)
                # now scores 0.0 instead of 0.5, eliminating false positives from
                # students/teacher standing near each other in normal classroom settings.
                score = max(0.0, 1.0 - norm_dist / 1.5)
                max_score = max(max_score, score)
        return max_score

    def _score_arm_raise(self, persons: List[Dict]) -> float:
        """
        Score high for aggressive arm poses — punching, pushing, or swinging.

        Two signals are combined per arm:
          1. Vertical raise  : wrist is above the shoulder (uppercut / overhead blow).
          2. Lateral extend  : wrist is far outside the body mid-line (side-swing / push).
        The two signals are max-combined so either alone triggers the score.
        """
        if not persons:
            return 0.0
        scores = []
        for p in persons:
            kp = p['keypoints']
            score, count = 0.0, 0
            # Hip midpoint x as a rough body-center reference (keypoints 11,12)
            KP_LEFT_HIP, KP_RIGHT_HIP = 11, 12
            body_cx = None
            if kp[KP_LEFT_HIP, 2] > 0.2 and kp[KP_RIGHT_HIP, 2] > 0.2:
                body_cx = (kp[KP_LEFT_HIP, 0] + kp[KP_RIGHT_HIP, 0]) / 2.0

            for sh_idx, wr_idx in [(KP_LEFT_SHOULDER, KP_LEFT_WRIST),
                                   (KP_RIGHT_SHOULDER, KP_RIGHT_WRIST)]:
                if kp[sh_idx, 2] > 0.3 and kp[wr_idx, 2] > 0.3:
                    sh_y, wr_y = kp[sh_idx, 1], kp[wr_idx, 1]
                    sh_x, wr_x = kp[sh_idx, 0], kp[wr_idx, 0]

                    # Vertical raise score
                    v_score = 0.0
                    if wr_y < sh_y and sh_y > 0:
                        v_score = min((sh_y - wr_y) / max(sh_y, 1) * 3, 1.0)

                    # Lateral extension score
                    l_score = 0.0
                    if body_cx is not None:
                        shoulder_span = abs(sh_x - body_cx)
                        wrist_dist    = abs(wr_x - body_cx)
                        if shoulder_span > 0:
                            # Wrist more than 1.5× the shoulder-to-center distance = extended
                            l_score = min(max(wrist_dist / max(shoulder_span, 1) - 1.0, 0.0) / 1.5, 1.0)

                    score += max(v_score, l_score)
                    count += 1
            if count:
                scores.append(score / count)
        return float(np.mean(scores)) if scores else 0.0

    def _score_fall(self, persons: List[Dict]) -> float:
        """Detect fallen person — unusually wide bounding box."""
        if not persons:
            return 0.0
        max_score = 0.0
        for p in persons:
            b = p['bbox']
            w, h = b[2]-b[0], b[3]-b[1]
            if h < 1:
                continue
            aspect = w / h   # normal standing: ~0.3-0.6; fallen: >1.5
            if aspect > 1.5:
                max_score = max(max_score, min((aspect - 1.5) / 1.5, 1.0))
        return max_score

    def _score_motion(self, frame_gray: np.ndarray, persons: List[Dict]) -> float:
        """Dense optical flow magnitude inside person regions."""
        if self._prev_gray is None or not persons:
            return 0.0
        try:
            flow = cv2.calcOpticalFlowFarneback(
                self._prev_gray, frame_gray, None, 0.5, 3, 15, 3, 5, 1.2, 0
            )
            mag, _ = cv2.cartToPolar(flow[..., 0], flow[..., 1])
            h, w   = frame_gray.shape
            mask   = np.zeros((h, w), dtype=np.uint8)
            for p in persons:
                x1, y1, x2, y2 = [int(v) for v in p['bbox']]
                mask[max(y1,0):min(y2,h), max(x1,0):min(x2,w)] = 1
            person_mag = mag[mask == 1]
            if person_mag.size == 0:
                return 0.0
            # Raised denominator from 8.0 → 12.0 so that normal walking/fidgeting
            # (low optical-flow magnitude) doesn't produce a dangerously high score.
            # Only rapid, violent movement now reaches 0.5+.
            return min(float(np.mean(person_mag)) / 12.0, 1.0)
        except Exception:
            return 0.0

    # -----------------------------------------------------------------------
    # Core detection
    # -----------------------------------------------------------------------

    def detect(self, frame: Optional[np.ndarray] = None) -> Dict:
        """
        Analyse one frame and return threat assessment.

        Args:
            frame: BGR image array (H x W x 3)

        Returns:
            {
              'is_threat': bool,
              'threat_type': str or None,
              'confidence': float,
              'all_scores': dict,
              'status': str,
              'people_count': int,
            }
        """
        _SAFE = {
            'is_threat': False, 'threat_type': None,
            'confidence': 0.0,  'all_scores': {}, 'status': 'ok', 'people_count': 0,
        }
        if frame is None:
            _SAFE['status'] = 'no_frame'
            return _SAFE
        if self.pose_model is None:
            _SAFE['status'] = 'model_unavailable'
            return _SAFE

        try:
            frame_gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)

            # Pose estimation (persons only — COCO class 0)
            results = self.pose_model(frame, verbose=False, classes=[0])
            persons = self._extract_persons(results)
            n_persons = len(persons)

            # ── Person-count guard ─────────────────────────────────────────
            # Arm-raise and motion are normal, innocent behaviours for a
            # single person (waving, walking, exercising).  They must NOT
            # contribute to the threat score unless at least 2 people are
            # in the scene.  Only fall detection applies to a lone person.
            #
            # When the scene drops from ≥2 people back to <2 we also flush
            # the score history so stale high scores don't keep an alert
            # alive after the situation resolved.
            if n_persons < 2 and self._prev_person_count >= 2:
                self._score_history.clear()
                logger.debug("Person count dropped below 2 — score history cleared")

            self._prev_person_count = n_persons

            # Fall score — valid for any person count (a lone person can fall)
            s_fall = self._score_fall(persons)

            if n_persons >= 2:
                # Full multi-person scoring
                s_prox   = self._score_proximity(persons)
                s_arm    = self._score_arm_raise(persons)
                s_motion = self._score_motion(frame_gray, persons)

                # Weighted blend for interaction threats
                blended = (0.40 * s_prox + 0.25 * s_motion + 0.15 * s_arm + 0.20 * s_fall)

                # Boost only when BOTH signals are strongly elevated — i.e. people
                # are genuinely touching AND moving fast.  Raised from 0.2/0.2 to
                # 0.45/0.40 so two students standing/walking near each other never
                # trigger the multiplier and produce false "fighting" alerts.
                if s_prox > 0.45 and s_motion > 0.40:
                    blended = min(blended * 1.6, 1.0)
            else:
                # Single person (or no persons): only fall detection
                s_prox   = 0.0
                s_arm    = 0.0
                s_motion = self._score_motion(frame_gray, persons)  # still update optical flow
                # Blended = fall score only; motion is computed to keep prev_gray
                # updated but does NOT count toward the threat score here
                blended = s_fall

            # Temporal smoothing
            self._score_history.append(blended)
            smoothed = float(np.mean(self._score_history))

            # Update previous frame for next optical-flow call
            self._prev_gray = frame_gray

            # ── Classify threat type ───────────────────────────────────────
            # fighting / pushing / aggressive_behavior require ≥2 people.
            # fall_detected is the only single-person threat.
            threat_type = None
            if smoothed >= self.confidence_threshold:
                if s_fall > 0.5:
                    threat_type = "fall_detected"
                elif n_persons >= 2 and s_prox > 0.45 and s_motion > 0.35:
                    # Both proximity AND motion strongly elevated → fighting
                    threat_type = "fighting"
                elif n_persons >= 2 and s_arm > 0.40:
                    # Significant arm extension/raise with multiple people → aggression
                    threat_type = "aggressive_behavior"
                elif n_persons >= 2 and s_prox > 0.35 and s_motion > 0.25:
                    # Moderate proximity + motion — pushing/shoving but not full fight
                    # Requires BOTH signals to avoid firing on plain co-presence.
                    threat_type = "pushing"
                # If none of the above conditions are met (e.g. two people simply
                # standing in frame with low proximity/motion), no threat is declared
                # even if the smoothed score exceeds the threshold due to stale history.
                # If n_persons < 2 and score is high but no fall → do NOT alert

            all_scores = {
                "proximity":    round(s_prox, 3),
                "arm_raise":    round(s_arm,  3),
                "motion":       round(s_motion, 3),
                "fall":         round(s_fall, 3),
                "blended":      round(blended, 3),
                "smoothed":     round(smoothed, 3),
                "people_count": n_persons,
            }

            is_threat = (smoothed >= self.confidence_threshold) and (threat_type is not None)

            return {
                'is_threat':    is_threat,
                'threat_type':  threat_type,
                'confidence':   round(smoothed, 4),
                'all_scores':   all_scores,
                'status':       'threat' if is_threat else 'normal',
                'people_count': n_persons,
            }

        except Exception as exc:
            logger.error(f"ThreatDetector.detect error: {exc}")
            _SAFE['status'] = 'error'
            _SAFE['error']  = str(exc)
            return _SAFE

    # -----------------------------------------------------------------------
    # API-compatibility helpers
    # -----------------------------------------------------------------------

    def add_frame(self, frame: np.ndarray):
        """Buffer a frame (API compat — actual detection happens in detect())."""
        self.frame_buffer.append(frame)

    def reset_buffer(self):
        """Clear all buffers."""
        self.frame_buffer.clear()
        self._score_history.clear()
        self._prev_gray = None
        self._prev_person_count = 0

    def preprocess_frame(self, frame: np.ndarray, size=(224, 224)) -> np.ndarray:
        """Kept for API compatibility."""
        return cv2.resize(frame, size)

    def visualize_result(self, frame: np.ndarray, result: Dict) -> np.ndarray:
        """Draw threat status overlay on frame."""
        annotated   = frame.copy()
        h, w        = annotated.shape[:2]
        is_threat   = result.get('is_threat', False)
        color       = (0, 0, 255) if is_threat else (0, 180, 0)

        cv2.rectangle(annotated, (0, 0), (w, 90), color, -1)

        status_text = f"THREAT: {result.get('threat_type','?').upper()}" if is_threat else "NORMAL"
        cv2.putText(annotated, status_text,
                    (10, 32), cv2.FONT_HERSHEY_SIMPLEX, 0.9, (255, 255, 255), 2)
        cv2.putText(annotated, f"Confidence: {result.get('confidence', 0):.2%}",
                    (10, 60), cv2.FONT_HERSHEY_SIMPLEX, 0.65, (255, 255, 255), 2)
        cv2.putText(annotated, f"People: {result.get('people_count', 0)}",
                    (10, 83), cv2.FONT_HERSHEY_SIMPLEX, 0.55, (255, 255, 255), 1)

        scores = result.get('all_scores', {})
        y = 110
        for k, v in scores.items():
            cv2.putText(annotated, f"{k}: {v:.3f}", (10, y),
                        cv2.FONT_HERSHEY_SIMPLEX, 0.4, (200, 255, 200), 1)
            y += 18

        return annotated


