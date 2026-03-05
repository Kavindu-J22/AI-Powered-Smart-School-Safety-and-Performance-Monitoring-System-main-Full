"""
Left-Behind Object Detection Model
Uses YOLOv8 for detecting objects left in classrooms.

Enhanced with dual-model detection:
  • Primary: custom-trained best.pt (Pen, Backpack/Tas-Ransel, Laptop,
             Water-bottle, Umbrella, Sports equipment …)
  • Secondary: yolov8n.pt COCO baseline (Book, Cell-phone, Keyboard …)

Together they cover all school-relevant items without any re-training.
"""

import torch
import cv2
import numpy as np
from ultralytics import YOLO
from pathlib import Path
from typing import List, Dict, Tuple, Optional
import logging

logger = logging.getLogger(__name__)

# ---------------------------------------------------------------------------
# School-context display-name aliases
# Maps raw model class names → human-friendly school labels
# ---------------------------------------------------------------------------
SCHOOL_CLASS_ALIASES: Dict[str, str] = {
    # Custom model (best.pt) — Indonesian & brand labels
    "tas-ransel":    "Backpack",          # Indonesian for backpack
    "Tas-Ransel":    "Backpack",
    "gateway":       "Laptop",            # Gateway brand device
    "Gateway":       "Laptop",
    "Laptop":        "Laptop",
    "laptop":        "Laptop",
    "Pen":           "Pen/Pencil",
    "pen":           "Pen/Pencil",
    "Baseball-bat":  "Baseball Bat",
    "baseball-bat":  "Baseball Bat",
    "Water-bottle":  "Water Bottle",
    "Water-Bottle":  "Water Bottle",
    "water-bottle":  "Water Bottle",
    "Tennis-racket": "Tennis Racket",
    "tennis-racket": "Tennis Racket",
    "Basketball":    "Basketball",
    "basketball":    "Basketball",
    "Soccer-ball":   "Soccer Ball",
    "soccer-ball":   "Soccer Ball",
    "umbrella":      "Umbrella",
    "Umbrella":      "Umbrella",
    # COCO model names
    "backpack":      "Backpack",
    "handbag":       "Handbag",
    "suitcase":      "Suitcase",
    "book":          "Book/Notebook",
    "bottle":        "Water Bottle",
    "cup":           "Cup",
    "cell phone":    "Mobile Phone",
    "keyboard":      "Keyboard",
    "mouse":         "Computer Mouse",
    "remote":        "Remote Control",
    "scissors":      "Scissors",
    "clock":         "Clock/Watch",
    "toothbrush":    "Pen-like Object",
    "teddy bear":    "Teddy Bear",
    "vase":          "Vase/Container",
}

# ---------------------------------------------------------------------------
# Virtual class IDs — unified across both models so the tracker stays sane
# (e.g. "Backpack" from best.pt and "backpack" from COCO both → 102)
# ---------------------------------------------------------------------------
SCHOOL_VIRTUAL_CLASS_IDS: Dict[str, int] = {
    "Pen/Pencil":       101,
    "Backpack":         102,
    "Laptop":           103,
    "Water Bottle":     104,
    "Umbrella":         105,
    "Book/Notebook":    106,
    "Mobile Phone":     107,
    "Keyboard":         108,
    "Computer Mouse":   109,
    "Scissors":         110,
    "Baseball Bat":     111,
    "Tennis Racket":    112,
    "Basketball":       113,
    "Soccer Ball":      114,
    "Handbag":          115,
    "Suitcase":         116,
    "Cup":              117,
    "Clock/Watch":      118,
    "Remote Control":   119,
    "Pen-like Object":  120,
    "Teddy Bear":       121,
    "Vase/Container":   122,
    "person":           200,   # person — never left-behind
    "unknown":          999,
}


class LeftBehindObjectDetector:
    """
    Detects left-behind objects in classroom environments using YOLOv8.

    Dual-model approach
    -------------------
    • Primary model  (best.pt)   – custom school dataset: Pen, Backpack
      (Tas-Ransel), Laptop, Water-bottle, Umbrella, sports gear …
    • Secondary model (yolov8n.pt) – COCO: Book, Cell-phone, Keyboard …

    Both models run on every frame; results are merged with IoU-NMS so
    duplicates are removed and the tracker receives consistent virtual
    class-IDs regardless of which model fired.
    """

    def __init__(
        self,
        model_path: str = "yolov8n.pt",
        confidence_threshold: float = 0.25,
        iou_threshold: float = 0.45,
        target_classes: Optional[List[str]] = None,
        device: str = "cuda" if torch.cuda.is_available() else "cpu",
        secondary_model_path: Optional[str] = None,
        secondary_confidence_threshold: Optional[float] = None,
    ):
        """
        Args:
            model_path:                      Primary YOLOv8 weights (preferably custom best.pt)
            confidence_threshold:            Min confidence for primary model
            iou_threshold:                   IoU threshold for NMS
            target_classes:                  Class names to keep (from either model)
            device:                          'cuda' or 'cpu'
            secondary_model_path:            Optional second YOLOv8 weights (e.g. yolov8s.pt)
            secondary_confidence_threshold:  Confidence for secondary model (default: conf+0.08)
        """
        self.model_path = model_path
        self.confidence_threshold = confidence_threshold
        # Secondary model uses a slightly higher threshold to cut false positives
        self.secondary_confidence_threshold = (
            secondary_confidence_threshold
            if secondary_confidence_threshold is not None
            else min(confidence_threshold + 0.08, 0.60)
        )
        self.iou_threshold = iou_threshold
        self.device = device

        # Default target classes — covers both custom & COCO names
        if target_classes is None:
            self.target_classes = [
                # custom model
                'Tas-Ransel', 'Gateway', 'Laptop', 'Pen',
                'Baseball-bat', 'Water-bottle', 'Water-Bottle', 'water-bottle',
                'Tennis-racket', 'Basketball', 'Soccer-ball', 'umbrella',
                # COCO model
                'backpack', 'handbag', 'suitcase', 'book', 'bottle', 'cup',
                'cell phone', 'laptop', 'keyboard', 'mouse', 'remote',
                'scissors', 'clock', 'toothbrush', 'teddy bear', 'umbrella',
            ]
        else:
            self.target_classes = target_classes

        # ── Primary model ──────────────────────────────────────────────────
        logger.info(f"Loading primary YOLOv8 model from {model_path}")
        self.model = YOLO(model_path)
        self.model.to(self.device)
        self.class_names = self.model.names
        self.target_class_indices = self._get_target_class_indices(
            self.class_names, self.target_classes
        )
        logger.info(
            f"Primary model loaded | classes: {list(self.class_names.values())} "
            f"| target indices: {self.target_class_indices}"
        )

        # ── Secondary model (optional) ─────────────────────────────────────
        self.secondary_model: Optional[YOLO] = None
        self.secondary_class_names: Dict[int, str] = {}
        self.secondary_target_class_indices: List[int] = []

        if secondary_model_path and secondary_model_path != model_path:
            try:
                logger.info(f"Loading secondary YOLOv8 model from {secondary_model_path}")
                self.secondary_model = YOLO(secondary_model_path)
                self.secondary_model.to(self.device)
                self.secondary_class_names = self.secondary_model.names
                self.secondary_target_class_indices = self._get_target_class_indices(
                    self.secondary_class_names, self.target_classes
                )
                logger.info(
                    f"Secondary model loaded | target indices: "
                    f"{self.secondary_target_class_indices}"
                )
            except Exception as exc:
                logger.warning(
                    f"Could not load secondary model '{secondary_model_path}': {exc}. "
                    "Continuing with primary model only."
                )
                self.secondary_model = None

        logger.info(f"LeftBehindObjectDetector ready on {self.device}")

    # ── Class-index helpers ────────────────────────────────────────────────

    def _get_target_class_indices(
        self, class_names: Dict[int, str], target_classes: List[str]
    ) -> List[int]:
        """
        Return model class indices that match any entry in target_classes.
        Matching is case-insensitive and checks both directions (substring).
        """
        indices: List[int] = []
        target_lower = [t.lower() for t in target_classes]

        for idx, class_name in class_names.items():
            cn_lower = class_name.lower()
            for tgt in target_lower:
                if tgt == cn_lower or tgt in cn_lower or cn_lower in tgt:
                    if idx not in indices:
                        indices.append(idx)
                    break
        return indices

    def _get_display_name(self, raw_name: str) -> str:
        """Map a raw model class name to a school-friendly label."""
        if raw_name in SCHOOL_CLASS_ALIASES:
            return SCHOOL_CLASS_ALIASES[raw_name]
        # Case-insensitive fallback
        for key, val in SCHOOL_CLASS_ALIASES.items():
            if raw_name.lower() == key.lower():
                return val
        # Auto-format unknown names
        return raw_name.replace("-", " ").replace("_", " ").title()

    def _get_virtual_class_id(self, display_name: str) -> int:
        """
        Return a stable virtual class-ID for a display name so that
        detections from the custom model and the COCO model share the
        same ID for the same semantic category (e.g. both 'Backpack').
        """
        if display_name in SCHOOL_VIRTUAL_CLASS_IDS:
            return SCHOOL_VIRTUAL_CLASS_IDS[display_name]
        # Fallback: hash to a high integer range
        return abs(hash(display_name)) % 10000 + 1000

    # ── Frame pre-processing ───────────────────────────────────────────────

    def _preprocess_frame(self, frame: np.ndarray) -> np.ndarray:
        """
        Apply CLAHE contrast enhancement so small objects (pens, small books)
        stand out better against uniform classroom surfaces.
        """
        try:
            lab = cv2.cvtColor(frame, cv2.COLOR_BGR2LAB)
            l_ch, a_ch, b_ch = cv2.split(lab)
            clahe = cv2.createCLAHE(clipLimit=2.0, tileGridSize=(8, 8))
            l_ch = clahe.apply(l_ch)
            enhanced = cv2.merge([l_ch, a_ch, b_ch])
            return cv2.cvtColor(enhanced, cv2.COLOR_LAB2BGR)
        except Exception:
            return frame  # Return original on failure
    
    # ── Internal YOLO runner ───────────────────────────────────────────────

    def _run_model(
        self,
        model: YOLO,
        class_names: Dict[int, str],
        target_indices: List[int],
        frame: np.ndarray,
        source_label: str = "primary",
        filter_classes: bool = True,
        include_unknown: bool = True,
        conf: Optional[float] = None,
    ) -> List[Dict]:
        """Run one YOLO model and return normalised detection dicts."""
        detections: List[Dict] = []
        effective_conf = conf if conf is not None else self.confidence_threshold
        try:
            # Pass target class indices directly to YOLO so it filters at
            # inference time — faster and avoids false positives from
            # irrelevant classes being processed at all.
            infer_classes = target_indices if (filter_classes and target_indices) else None
            results = model(
                frame,
                conf=effective_conf,
                iou=self.iou_threshold,
                classes=infer_classes,
                verbose=False,
            )[0]

            if results.boxes is None:
                return detections

            boxes       = results.boxes.xyxy.cpu().numpy()
            confidences = results.boxes.conf.cpu().numpy()
            class_ids   = results.boxes.cls.cpu().numpy().astype(int)

            for box, conf, class_id in zip(boxes, confidences, class_ids):
                is_target = class_id in target_indices

                if filter_classes and not is_target and not include_unknown:
                    continue

                raw_name     = class_names.get(int(class_id), f"cls_{class_id}")
                display_name = self._get_display_name(raw_name) if is_target else "unknown"
                virtual_id   = self._get_virtual_class_id(display_name)

                detections.append({
                    'bbox':                box.tolist(),
                    'confidence':          float(conf),
                    'class_id':            virtual_id,       # cross-model stable ID
                    'raw_class_id':        int(class_id),    # original model ID
                    'class_name':          display_name,
                    'original_class_name': raw_name,
                    'is_unknown':          not is_target,
                    'source':              source_label,
                })
        except Exception as exc:
            logger.error(f"Error running {source_label} model: {exc}")
        return detections

    def _calculate_iou(self, b1: List[float], b2: List[float]) -> float:
        """Intersection-over-Union for two [x1,y1,x2,y2] boxes."""
        xi1, yi1 = max(b1[0], b2[0]), max(b1[1], b2[1])
        xi2, yi2 = min(b1[2], b2[2]), min(b1[3], b2[3])
        if xi2 < xi1 or yi2 < yi1:
            return 0.0
        inter = (xi2 - xi1) * (yi2 - yi1)
        area1 = (b1[2] - b1[0]) * (b1[3] - b1[1])
        area2 = (b2[2] - b2[0]) * (b2[3] - b2[1])
        union = area1 + area2 - inter
        return inter / union if union > 0 else 0.0

    def _merge_detections(
        self,
        primary: List[Dict],
        secondary: List[Dict],
        iou_merge_threshold: float = 0.45,
    ) -> List[Dict]:
        """
        Merge secondary detections into primary, skipping any secondary box
        that overlaps significantly with an already-present primary box.
        Primary model (custom-trained) takes precedence when overlap occurs.
        """
        if not secondary:
            return primary
        merged = list(primary)
        for sec in secondary:
            duplicate = any(
                self._calculate_iou(sec['bbox'], pri['bbox']) >= iou_merge_threshold
                for pri in merged
            )
            if not duplicate:
                merged.append(sec)
        return merged

    # ── Tiled inference helpers ────────────────────────────────────────────

    def _nms_detections(
        self,
        detections: List[Dict],
        iou_threshold: float = 0.45,
    ) -> List[Dict]:
        """
        Pure-Python NMS across a mixed list of detection dicts.
        Keeps the highest-confidence box when two boxes overlap >= iou_threshold.
        """
        if not detections:
            return []
        # Sort descending by confidence
        dets = sorted(detections, key=lambda d: d['confidence'], reverse=True)
        keep: List[Dict] = []
        for candidate in dets:
            suppress = False
            for kept in keep:
                if self._calculate_iou(candidate['bbox'], kept['bbox']) >= iou_threshold:
                    suppress = True
                    break
            if not suppress:
                keep.append(candidate)
        return keep

    def _run_primary_tiled(
        self,
        frame: np.ndarray,
        filter_classes: bool = True,
        include_unknown: bool = True,
    ) -> List[Dict]:
        """
        Run the primary model on overlapping 2x2 tiles of the frame.

        Tiles use a higher confidence bar (primary_conf + 0.05) to compensate
        for the zoomed-in view making weak textures look more object-like.
        Only genuinely strong small-object detections survive.
        """
        h, w = frame.shape[:2]
        # Tile confidence is higher so only sharp small detections pass
        tile_conf = min(self.confidence_threshold + 0.05, 0.85)

        tw = max(int(w * 0.60), 1)
        th = max(int(h * 0.60), 1)
        step_x = max(int(w * 0.40), 1)
        step_y = max(int(h * 0.40), 1)

        all_dets: List[Dict] = []

        for row in range(2):
            for col in range(2):
                x1 = min(col * step_x, w - tw)
                y1 = min(row * step_y, h - th)
                x2 = x1 + tw
                y2 = y1 + th

                tile = frame[y1:y2, x1:x2]
                tile_dets = self._run_model(
                    self.model,
                    self.class_names,
                    self.target_class_indices,
                    tile,
                    source_label="custom_tiled",
                    filter_classes=filter_classes,
                    include_unknown=include_unknown,
                    conf=tile_conf,
                )
                # Translate bboxes back to full-frame coordinates
                for det in tile_dets:
                    bx1, by1, bx2, by2 = det['bbox']
                    det['bbox'] = [bx1 + x1, by1 + y1, bx2 + x1, by2 + y1]
                all_dets.extend(tile_dets)

        return self._nms_detections(all_dets, iou_threshold=self.iou_threshold)

    # ── Quality filters ────────────────────────────────────────────────────

    # Classes where the detected object must be taller than wide.
    # Faces / heads are roughly square, so this rejects spurious detections.
    _PORTRAIT_CLASSES = {
        "Water Bottle", "Bottle", "water bottle", "bottle",
        "Umbrella", "umbrella",
    }

    def _apply_shape_filter(self, detections: List[Dict]) -> List[Dict]:
        """
        Reject detections whose bounding-box shape is physically impossible
        for the claimed class.

        Rule — tall/narrow classes (bottle, umbrella):
          The box must be at least as tall as it is wide (h/w >= 1.0).
          A human face is roughly square (h/w ≈ 1.1) but the box typically
          includes neck/shoulders when detected via the full-body, while a
          pure face crop near a bottle will be clearly wider than tall —
          both cases are caught by this guard.
        """
        filtered: List[Dict] = []
        for det in detections:
            name = det.get('class_name', '')
            if name in self._PORTRAIT_CLASSES:
                x1, y1, x2, y2 = det['bbox']
                bw = max(x2 - x1, 1)
                bh = max(y2 - y1, 1)
                aspect = bh / bw  # > 1 means taller than wide
                if aspect < 0.90:
                    logger.debug(
                        f"Shape filter dropped '{name}' (h/w={aspect:.2f}): {det['bbox']}"
                    )
                    continue
            filtered.append(det)
        return filtered

    def _suppress_person_overlap(self, detections: List[Dict]) -> List[Dict]:
        """
        Remove object detections that lie significantly inside a person's
        bounding box — this is the primary guard against faces, heads or
        hands being misclassified as objects (e.g. face → water bottle).

        Logic
        -----
        For every non-person detection, compute how much of its area is
        contained inside any person bbox.  If the containment fraction
        exceeds 45 %, the detection is suppressed.  Person detections
        themselves are always kept (so the tracker can use them).
        """
        person_boxes = [
            d['bbox'] for d in detections
            if d.get('class_name', '').lower() == 'person'
        ]
        if not person_boxes:
            return detections  # No people → nothing to suppress

        kept: List[Dict] = []
        for det in detections:
            if det.get('class_name', '').lower() == 'person':
                kept.append(det)
                continue

            dx1, dy1, dx2, dy2 = det['bbox']
            det_area = max((dx2 - dx1) * (dy2 - dy1), 1)
            suppressed = False

            for pb in person_boxes:
                ix1 = max(dx1, pb[0]);  iy1 = max(dy1, pb[1])
                ix2 = min(dx2, pb[2]);  iy2 = min(dy2, pb[3])
                if ix2 <= ix1 or iy2 <= iy1:
                    continue
                containment = (ix2 - ix1) * (iy2 - iy1) / det_area
                if containment >= 0.60:
                    logger.debug(
                        f"Person-overlap suppressed '{det['class_name']}' "
                        f"(containment={containment:.2f}): {det['bbox']}"
                    )
                    suppressed = True
                    break

            if not suppressed:
                kept.append(det)
        return kept

    # ── Public detection API ───────────────────────────────────────────────

    def detect(
        self,
        frame: np.ndarray,
        filter_classes: bool = True,
        include_unknown: bool = True,
        enhance_frame: bool = True,
    ) -> List[Dict]:
        """
        Detect objects in a frame using dual-model inference.

        Args:
            frame:          Input image (BGR format)
            filter_classes: Keep only target-class detections
            include_unknown: Also include non-target detections when filter_classes=True
            enhance_frame:  Apply CLAHE contrast enhancement before inference

        Returns:
            List of detections, each containing:
              bbox, confidence, class_id (virtual), class_name (display),
              original_class_name, is_unknown, source
        """
        proc = self._preprocess_frame(frame) if enhance_frame else frame

        # ── Primary model: tiled inference only ───────────────────────────
        # 2x2 tiles with 20% overlap already cover 100% of the frame —
        # there is no need for a separate full-frame pass.
        # Removing it saves one YOLO call per frame (~200-400ms on CPU).
        combined_primary = self._run_primary_tiled(
            proc, filter_classes=filter_classes, include_unknown=include_unknown
        )

        # ── Secondary model (COCO yolov8s) ────────────────────────────────
        secondary_dets: List[Dict] = []
        if self.secondary_model is not None:
            secondary_dets = self._run_model(
                self.secondary_model, self.secondary_class_names,
                self.secondary_target_class_indices,
                proc, source_label="coco", filter_classes=filter_classes,
                include_unknown=include_unknown,
                conf=self.secondary_confidence_threshold,
            )

        merged = self._merge_detections(combined_primary, secondary_dets)

        # ── Quality gates ──────────────────────────────────────────────────
        # 1. Remove objects that are significantly inside a person bounding box
        #    (prevents face/hand being labelled as bottle, phone, etc.)
        merged = self._suppress_person_overlap(merged)
        # 2. Reject bottle/umbrella boxes with wrong aspect ratio (too wide)
        merged = self._apply_shape_filter(merged)

        return merged
    
    def detect_batch(
        self,
        frames: List[np.ndarray],
        filter_classes: bool = True,
        include_unknown: bool = True,
        enhance_frame: bool = True,
    ) -> List[List[Dict]]:
        """
        Detect objects in multiple frames using dual-model inference.

        Args:
            frames:         List of input images (BGR)
            filter_classes: Keep only target-class detections
            include_unknown: Also include non-target detections
            enhance_frame:  Apply CLAHE before inference

        Returns:
            List of detection lists, one per frame
        """
        return [
            self.detect(
                frame,
                filter_classes=filter_classes,
                include_unknown=include_unknown,
                enhance_frame=enhance_frame,
            )
            for frame in frames
        ]

    def visualize_detections(
        self,
        frame: np.ndarray,
        detections: List[Dict],
        show_labels: bool = True,
        thickness: int = 2
    ) -> np.ndarray:
        """
        Draw bounding boxes and labels on frame

        Args:
            frame: Input image
            detections: List of detections from detect()
            show_labels: Whether to show class labels
            thickness: Line thickness for bounding boxes

        Returns:
            Annotated frame
        """
        annotated_frame = frame.copy()

        for det in detections:
            x1, y1, x2, y2 = map(int, det['bbox'])
            conf = det['confidence']
            class_name = det['class_name']

            # Draw bounding box
            color = (0, 255, 0)  # Green
            cv2.rectangle(annotated_frame, (x1, y1), (x2, y2), color, thickness)

            # Draw label
            if show_labels:
                label = f"{class_name}: {conf:.2f}"
                label_size, _ = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.5, 1)

                # Draw label background
                cv2.rectangle(
                    annotated_frame,
                    (x1, y1 - label_size[1] - 10),
                    (x1 + label_size[0], y1),
                    color,
                    -1
                )

                # Draw label text
                cv2.putText(
                    annotated_frame,
                    label,
                    (x1, y1 - 5),
                    cv2.FONT_HERSHEY_SIMPLEX,
                    0.5,
                    (0, 0, 0),
                    1
                )

        return annotated_frame

    def get_object_area(self, bbox: List[float]) -> float:
        """Calculate area of bounding box"""
        x1, y1, x2, y2 = bbox
        return (x2 - x1) * (y2 - y1)

    def filter_by_size(
        self,
        detections: List[Dict],
        min_area: int = 1000
    ) -> List[Dict]:
        """
        Filter detections by minimum bounding box area

        Args:
            detections: List of detections
            min_area: Minimum area in pixels

        Returns:
            Filtered detections
        """
        filtered = []
        for det in detections:
            area = self.get_object_area(det['bbox'])
            if area >= min_area:
                filtered.append(det)
        return filtered

    def train(
        self,
        data_yaml: str,
        epochs: int = 100,
        imgsz: int = 640,
        batch: int = 16,
        project: str = "runs/train",
        name: str = "left_behind_detector"
    ):
        """
        Train or fine-tune the model

        Args:
            data_yaml: Path to dataset YAML file
            epochs: Number of training epochs
            imgsz: Input image size
            batch: Batch size
            project: Project directory
            name: Experiment name
        """
        logger.info(f"Starting training with {data_yaml}")

        results = self.model.train(
            data=data_yaml,
            epochs=epochs,
            imgsz=imgsz,
            batch=batch,
            project=project,
            name=name,
            device=self.device
        )

        logger.info("Training completed")
        return results

    def export_model(
        self,
        format: str = "onnx",
        output_path: Optional[str] = None
    ):
        """
        Export model to different formats for deployment

        Args:
            format: Export format ('onnx', 'torchscript', 'tflite', 'edgetpu')
            output_path: Output path for exported model
        """
        logger.info(f"Exporting model to {format}")

        self.model.export(format=format)

        logger.info(f"Model exported successfully")


if __name__ == "__main__":
    # Example usage
    detector = LeftBehindObjectDetector(
        model_path="yolov8n.pt",
        confidence_threshold=0.5
    )

    # Test with an image
    test_image = cv2.imread("test_image.jpg")
    if test_image is not None:
        detections = detector.detect(test_image)
        print(f"Found {len(detections)} objects")

        # Visualize
        annotated = detector.visualize_detections(test_image, detections)
        cv2.imshow("Detections", annotated)
        cv2.waitKey(0)
        cv2.destroyAllWindows()


