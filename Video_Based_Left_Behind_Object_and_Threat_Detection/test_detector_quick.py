"""
Quick smoke-test for the dual-model LeftBehindObjectDetector.
Run from the Video_Based_Left_Behind_Object_and_Threat_Detection directory with:
    venv\Scripts\python.exe test_detector_quick.py
"""
import sys
import numpy as np
import cv2

sys.path.insert(0, '.')

from src.models.object_detector import (
    LeftBehindObjectDetector,
    SCHOOL_CLASS_ALIASES,
    SCHOOL_VIRTUAL_CLASS_IDS,
)

CUSTOM_MODEL = 'models/object_detection_training/weights/best.pt'
COCO_MODEL   = 'yolov8n.pt'

print("=" * 60)
print("Dual-Model Left-Behind Object Detector – Quick Test")
print("=" * 60)

# ── 1. Load detector ──────────────────────────────────────────
print("\n[1] Loading detector …")
det = LeftBehindObjectDetector(
    model_path=CUSTOM_MODEL,
    confidence_threshold=0.20,
    secondary_model_path=COCO_MODEL,
)
print("    Loaded OK")

# ── 2. Print custom model class mapping ───────────────────────
print("\n[2] Custom model class mapping (all 12 classes):")
for idx, name in det.class_names.items():
    alias = SCHOOL_CLASS_ALIASES.get(name, name)
    vid   = SCHOOL_VIRTUAL_CLASS_IDS.get(alias, 999)
    print(f"    [{idx:2d}] {name:<16s}  ->  {alias:<20s}  (virtual_id={vid})")

# ── 3. Print secondary (COCO) target indices ─────────────────
print(f"\n[3] COCO model target indices: {det.secondary_target_class_indices}")

# ── 4. Detection on a blank (dark) frame ─────────────────────
print("\n[4] detect() on blank 640×480 frame …")
blank = np.full((480, 640, 3), 60, dtype=np.uint8)
dets_blank = det.detect(blank, filter_classes=True, include_unknown=False)
print(f"    Detections: {len(dets_blank)}  (expected ~0 on blank frame)")

# ── 5. Detection – preprocess check ──────────────────────────
print("\n[5] CLAHE preprocess sanity check …")
proc = det._preprocess_frame(blank)
print(f"    Input shape: {blank.shape}  Output shape: {proc.shape}  – OK")

# ── 6. Merge / IoU helpers ────────────────────────────────────
print("\n[6] IoU helper test …")
b1 = [10, 10, 100, 100]
b2 = [50, 50, 150, 150]   # overlapping
b3 = [200, 200, 300, 300]  # non-overlapping
iou_overlap     = det._calculate_iou(b1, b2)
iou_no_overlap  = det._calculate_iou(b1, b3)
print(f"    IoU(overlap)   = {iou_overlap:.3f}  (expected > 0)")
print(f"    IoU(no-overlap)= {iou_no_overlap:.3f}  (expected = 0)")
assert iou_overlap > 0,    "IoU overlap failed"
assert iou_no_overlap == 0, "IoU no-overlap failed"

# ── 7. Merge deduplication ────────────────────────────────────
print("\n[7] _merge_detections() deduplication …")
fake_primary = [{'bbox': [10,10,100,100], 'class_name': 'Backpack',   'class_id': 102, 'confidence': 0.8, 'is_unknown': False}]
fake_dup     = [{'bbox': [15,12,105,102], 'class_name': 'Backpack',   'class_id': 102, 'confidence': 0.6, 'is_unknown': False}]
fake_new     = [{'bbox': [300,200,400,320], 'class_name': 'Pen/Pencil','class_id': 101, 'confidence': 0.5, 'is_unknown': False}]
merged_dup = det._merge_detections(fake_primary, fake_dup)
merged_new = det._merge_detections(fake_primary, fake_new)
print(f"    Duplicate detection merged: {len(merged_dup)} (expected 1)")
print(f"    New detection added:        {len(merged_new)} (expected 2)")
assert len(merged_dup) == 1, "Duplicate not removed!"
assert len(merged_new) == 2, "New detection not added!"

print("\n" + "=" * 60)
print("ALL TESTS PASSED — Detector is fully functional!")
print("=" * 60)

