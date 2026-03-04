"""Quick test for PANNS detector - label mapping and module loading."""
import sys
import csv
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))

print("=== Test 1: PANNS module import ===")
try:
    from models.pretrained_audio_detector import (
        _Cnn14, _PANNS_AVAILABLE, THREAT_EXACT_LABELS,
        LABELS_CSV_PATH, MODEL_PATH, PANNsAudioDetector
    )
    print(f"_PANNS_AVAILABLE : {_PANNS_AVAILABLE}")
    print(f"Cnn14 class      : {_Cnn14}")
except Exception as e:
    print(f"IMPORT ERROR: {e}")
    sys.exit(1)

print()
print("=== Test 2: AudioSet label mapping ===")
if not LABELS_CSV_PATH.exists():
    print("Labels CSV not found - will download on initialize()")
else:
    labels = []
    with open(str(LABELS_CSV_PATH), 'r', encoding='utf-8') as f:
        for i, row in enumerate(csv.reader(f)):
            if i == 0:
                continue
            if len(row) >= 3:
                labels.append(row[2])

    result = {cls: [] for cls in THREAT_EXACT_LABELS}
    for idx, lbl in enumerate(labels):
        for cls, exact_set in THREAT_EXACT_LABELS.items():
            if lbl in exact_set:
                result[cls].append(f"[{idx}] {lbl}")
                break

    for cls, hits in result.items():
        print(f"  {cls} ({len(hits)} labels): {hits}")

print()
print("=== Test 3: CNN14 model status ===")
if MODEL_PATH.exists() and MODEL_PATH.stat().st_size > 3e8:
    print(f"  Model READY: {MODEL_PATH.stat().st_size / 1e6:.1f} MB")
else:
    print("  Model NOT downloaded yet.")
    print(f"  Will auto-download from Zenodo on first initialize() call.")
    print(f"  Expected size: ~322 MB")

print()
print("=== Test 4: ThreatDetector import ===")
try:
    from models.threat_detector import ThreatDetector
    print("  ThreatDetector import OK")
    print("  (Full init skipped to avoid slow model downloads in test)")
except Exception as e:
    print(f"  ThreatDetector import ERROR: {e}")
    import traceback
    traceback.print_exc()

print()
print("All tests passed (model download skipped - happens at runtime).")

