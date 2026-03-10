#!/usr/bin/env python3
"""
Display Video-Based Left-Behind Object & Threat Detection Model Accuracy
Reads YOLOv8 training results from results.csv and shows formatted metrics.
Run: python display_model_accuracy.py
"""
import csv
import os
from pathlib import Path

RESULTS_CSV = Path(__file__).parent / "models" / "object_detection_training" / "results.csv"
TRAINING_DIR = Path(__file__).parent / "models" / "object_detection_training"


def load_results(csv_path: Path):
    rows = []
    with open(csv_path, newline='') as f:
        reader = csv.DictReader(f)
        for row in reader:
            rows.append({k.strip(): v.strip() for k, v in row.items()})
    return rows


def bar(value: float, width: int = 30) -> str:
    filled = int(round(value / 100 * width))
    return "[" + "█" * filled + "░" * (width - filled) + "]"


def status_icon(value: float, good: float = 75.0, ok: float = 60.0) -> str:
    if value >= good:
        return "✅"
    elif value >= ok:
        return "⚠️ "
    return "❌"


def display_accuracy():
    if not RESULTS_CSV.exists():
        print("\n" + "=" * 72)
        print("  ❌ ERROR: results.csv not found!")
        print(f"     Expected: {RESULTS_CSV}")
        print("     Run training first: python run_training.py")
        print("=" * 72)
        return

    rows = load_results(RESULTS_CSV)
    if not rows:
        print("❌ No data found in results.csv")
        return

    # Best epoch: highest mAP50
    best = max(rows, key=lambda r: float(r.get("metrics/mAP50(B)", 0)))
    last = rows[-1]

    # Best epoch metrics
    best_epoch      = int(float(best["epoch"]))
    best_precision  = float(best["metrics/precision(B)"]) * 100
    best_recall     = float(best["metrics/recall(B)"]) * 100
    best_map50      = float(best["metrics/mAP50(B)"]) * 100
    best_map5095    = float(best["metrics/mAP50-95(B)"]) * 100
    best_box_loss   = float(best["val/box_loss"])
    best_cls_loss   = float(best["val/cls_loss"])
    # F1-Score and Detection Accuracy for best epoch
    best_f1         = (2 * best_precision * best_recall / (best_precision + best_recall)
                       if (best_precision + best_recall) > 0 else 0)
    best_accuracy   = best_map50   # mAP@0.50 is the standard detection accuracy proxy

    # Final epoch metrics
    final_epoch     = int(float(last["epoch"]))
    final_precision = float(last["metrics/precision(B)"]) * 100
    final_recall    = float(last["metrics/recall(B)"]) * 100
    final_map50     = float(last["metrics/mAP50(B)"]) * 100
    final_map5095   = float(last["metrics/mAP50-95(B)"]) * 100
    # F1-Score and Detection Accuracy for final epoch
    final_f1        = (2 * final_precision * final_recall / (final_precision + final_recall)
                       if (final_precision + final_recall) > 0 else 0)
    final_accuracy  = final_map50

    # Training progression
    first = rows[0]
    init_map50 = float(first["metrics/mAP50(B)"]) * 100
    improvement = best_map50 - init_map50

    print("\n" + "=" * 72)
    print("  🎯  VIDEO-BASED LEFT-BEHIND OBJECT & THREAT DETECTION — ACCURACY REPORT")
    print("=" * 72)
    print(f"  Model: YOLOv8   |   Total Training Epochs: {final_epoch}")
    print(f"  Training Results: {RESULTS_CSV.parent}")

    print("\n📊  BEST EPOCH PERFORMANCE  (Epoch {})".format(best_epoch))
    print("-" * 72)
    print(f"  Accuracy   {bar(best_accuracy)}  {best_accuracy:6.2f}%  {status_icon(best_accuracy)}  (Detection Accuracy — mAP@0.50)")
    print(f"  Precision  {bar(best_precision)}  {best_precision:6.2f}%  {status_icon(best_precision)}")
    print(f"  Recall     {bar(best_recall)}  {best_recall:6.2f}%  {status_icon(best_recall)}")
    print(f"  F1-Score   {bar(best_f1)}  {best_f1:6.2f}%  {status_icon(best_f1)}")
    print(f"  mAP@0.50   {bar(best_map50)}  {best_map50:6.2f}%  {status_icon(best_map50)}")
    print(f"  mAP@.5:.95 {bar(best_map5095, 30)}  {best_map5095:6.2f}%  {status_icon(best_map5095, 55, 40)}")
    print(f"\n  Val Box Loss : {best_box_loss:.5f}   Val Cls Loss : {best_cls_loss:.5f}")

    print("\n📉  FINAL EPOCH PERFORMANCE  (Epoch {})".format(final_epoch))
    print("-" * 72)
    print(f"  Accuracy   {bar(final_accuracy)}  {final_accuracy:6.2f}%  {status_icon(final_accuracy)}  (Detection Accuracy — mAP@0.50)")
    print(f"  Precision  {bar(final_precision)}  {final_precision:6.2f}%  {status_icon(final_precision)}")
    print(f"  Recall     {bar(final_recall)}  {final_recall:6.2f}%  {status_icon(final_recall)}")
    print(f"  F1-Score   {bar(final_f1)}  {final_f1:6.2f}%  {status_icon(final_f1)}")
    print(f"  mAP@0.50   {bar(final_map50)}  {final_map50:6.2f}%  {status_icon(final_map50)}")
    print(f"  mAP@.5:.95 {bar(final_map5095, 30)}  {final_map5095:6.2f}%  {status_icon(final_map5095, 55, 40)}")

    print("\n📈  TRAINING PROGRESS")
    print("-" * 72)
    print(f"  Initial mAP@0.50 (Epoch 1) : {init_map50:6.2f}%")
    print(f"  Best    mAP@0.50 (Epoch {best_epoch:2d}) : {best_map50:6.2f}%")
    print(f"  Improvement over training   : +{improvement:.2f}%")

    print("\n📁  AVAILABLE VISUALIZATIONS")
    print("-" * 72)
    viz_files = {
        "results.png"                    : "Training curves (loss + metrics)",
        "confusion_matrix.png"           : "Confusion Matrix",
        "confusion_matrix_normalized.png": "Confusion Matrix (Normalized)",
        "BoxF1_curve.png"                : "F1 vs Confidence Curve",
        "BoxPR_curve.png"                : "Precision-Recall Curve",
        "BoxP_curve.png"                 : "Precision vs Confidence",
        "BoxR_curve.png"                 : "Recall vs Confidence",
    }
    for fname, desc in viz_files.items():
        p = TRAINING_DIR / fname
        icon = "✅" if p.exists() else "❌"
        print(f"  {icon}  {desc:<42}  {fname}")

    print("\n💡  PERFORMANCE SUMMARY")
    print("-" * 72)
    if best_map50 >= 80:
        print("  ✅  Excellent detection performance! Model is production-ready.")
    elif best_map50 >= 70:
        print("  ✅  Good detection performance. Reliable for school safety use.")
    elif best_map50 >= 60:
        print("  ⚠️   Moderate performance. More training data may improve results.")
    else:
        print("  ❌  Low performance. Model needs more training data or tuning.")

    print(f"\n  Key Metrics — Accuracy (mAP@0.50): {best_accuracy:.2f}%  |  F1-Score: {best_f1:.2f}%  |  mAP@.5:.95: {best_map5095:.2f}%")
    print("=" * 72 + "\n")


if __name__ == "__main__":
    display_accuracy()

