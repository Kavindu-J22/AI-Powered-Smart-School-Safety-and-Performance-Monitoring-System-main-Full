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
        confidence_threshold: float = 0.55,
        clip_length: int = 16,
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
        """High score when two or more people's bounding boxes overlap."""
        if len(persons) < 2:
            return 0.0
        max_iou = 0.0
        for i in range(len(persons)):
            for j in range(i + 1, len(persons)):
                b1, b2 = persons[i]['bbox'], persons[j]['bbox']
                xi1 = max(b1[0], b2[0]); yi1 = max(b1[1], b2[1])
                xi2 = min(b1[2], b2[2]); yi2 = min(b1[3], b2[3])
                if xi2 > xi1 and yi2 > yi1:
                    inter = (xi2 - xi1) * (yi2 - yi1)
                    a1 = (b1[2]-b1[0]) * (b1[3]-b1[1])
                    a2 = (b2[2]-b2[0]) * (b2[3]-b2[1])
                    iou = inter / max(a1 + a2 - inter, 1e-6)
                    max_iou = max(max_iou, iou)
        return min(max(max_iou - 0.1, 0.0) / 0.5, 1.0)

    def _score_arm_raise(self, persons: List[Dict]) -> float:
        """Score high when wrists are above shoulders (punching / grabbing pose)."""
        if not persons:
            return 0.0
        scores = []
        for p in persons:
            kp = p['keypoints']
            score, count = 0.0, 0
            for sh_idx, wr_idx in [(KP_LEFT_SHOULDER, KP_LEFT_WRIST),
                                   (KP_RIGHT_SHOULDER, KP_RIGHT_WRIST)]:
                if kp[sh_idx, 2] > 0.3 and kp[wr_idx, 2] > 0.3:
                    sh_y, wr_y = kp[sh_idx, 1], kp[wr_idx, 1]
                    if wr_y < sh_y and sh_y > 0:
                        score += min((sh_y - wr_y) / sh_y * 3, 1.0)
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
            return min(float(np.mean(person_mag)) / 15.0, 1.0)
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

            # Individual heuristic scores
            s_prox   = self._score_proximity(persons)
            s_arm    = self._score_arm_raise(persons)
            s_fall   = self._score_fall(persons)
            s_motion = self._score_motion(frame_gray, persons)

            # Weighted blend
            blended = (0.35 * s_prox + 0.25 * s_motion + 0.20 * s_arm + 0.20 * s_fall)

            # Boost when proximity + motion both fire (most likely a fight)
            if s_prox > 0.3 and s_motion > 0.3:
                blended = min(blended * 1.4, 1.0)

            # Temporal smoothing
            self._score_history.append(blended)
            smoothed = float(np.mean(self._score_history))

            # Update previous frame for next optical-flow call
            self._prev_gray = frame_gray

            # Classify threat type
            threat_type = None
            if smoothed >= self.confidence_threshold:
                if s_fall > 0.5:
                    threat_type = "fall_detected"
                elif s_prox > 0.3 and s_motion > 0.3:
                    threat_type = "fighting"
                elif s_arm > 0.4:
                    threat_type = "aggressive_behavior"
                else:
                    threat_type = "pushing"

            all_scores = {
                "proximity": round(s_prox, 3),
                "arm_raise": round(s_arm,  3),
                "motion":    round(s_motion, 3),
                "fall":      round(s_fall, 3),
                "blended":   round(blended, 3),
                "smoothed":  round(smoothed, 3),
            }

            return {
                'is_threat':    smoothed >= self.confidence_threshold,
                'threat_type':  threat_type,
                'confidence':   round(smoothed, 4),
                'all_scores':   all_scores,
                'status':       'threat' if smoothed >= self.confidence_threshold else 'normal',
                'people_count': len(persons),
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


