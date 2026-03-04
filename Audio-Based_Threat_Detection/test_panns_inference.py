"""End-to-end inference test using real audio files from the dataset."""
import sys
import os
import numpy as np
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))

from models.pretrained_audio_detector import PANNsAudioDetector

print("Initializing PANNS detector (model already downloaded)…")
det = PANNsAudioDetector()
ok  = det.initialize()
if not ok:
    print("FAILED to initialize detector. Exiting.")
    sys.exit(1)

# Find sample audio files from the dataset
DATASET_DIR = Path(__file__).parent / "Non Speech Dataset"
TARGET_CLASSES = ['crying', 'screaming', 'shouting', 'glass_breaking', 'Normal']

def load_audio(filepath, sr=16000):
    """Load audio using scipy or soundfile."""
    try:
        import soundfile as sf
        audio, file_sr = sf.read(str(filepath))
        if audio.ndim > 1:
            audio = audio.mean(axis=1)
        if file_sr != sr:
            try:
                import librosa
                audio = librosa.resample(audio, orig_sr=file_sr, target_sr=sr)
            except Exception:
                from scipy.signal import resample_poly
                from math import gcd
                g = gcd(file_sr, sr)
                audio = resample_poly(audio, sr // g, file_sr // g)
        return audio.astype(np.float32), sr
    except Exception as e:
        print(f"  Could not load {filepath}: {e}")
        return None, sr

print("\n=== PANNS Inference Test on Dataset Samples ===\n")
correct = 0
total   = 0

for cls_dir in TARGET_CLASSES:
    folder = DATASET_DIR / cls_dir
    if not folder.exists():
        print(f"  [SKIP] {cls_dir}: folder not found")
        continue

    # Pick up to 3 audio files
    audio_files = list(folder.glob("*.wav"))[:3] + list(folder.glob("*.mp3"))[:3]
    audio_files = audio_files[:3]

    if not audio_files:
        print(f"  [SKIP] {cls_dir}: no audio files found")
        continue

    expected = cls_dir.lower().replace('normal', 'normal')
    cls_correct = 0

    for f in audio_files:
        audio, sr = load_audio(f)
        if audio is None:
            continue

        # Clip to 2 seconds
        n_samples = int(sr * 2.0)
        if len(audio) > n_samples:
            audio = audio[:n_samples]
        elif len(audio) < n_samples:
            audio = np.pad(audio, (0, n_samples - len(audio)))

        predicted, confidence, scores = det.detect(audio, src_sr=sr)
        is_correct = (predicted == expected) or (expected == 'normal' and predicted == 'normal')
        cls_correct += 1 if is_correct else 0
        total += 1
        correct += 1 if is_correct else 0

        top_scores = sorted(scores.items(), key=lambda x: -x[1])[:3]
        top_str = ", ".join(f"{k}={v:.3f}" for k, v in top_scores)
        mark = "✓" if is_correct else "✗"
        print(f"  [{mark}] {cls_dir}/{f.name[:30]}")
        print(f"       predicted={predicted} ({confidence:.3f}) | top: {top_str}")

    print(f"  → {cls_dir}: {cls_correct}/{len(audio_files)} correct\n")

if total > 0:
    print(f"Overall accuracy: {correct}/{total} ({correct/total*100:.0f}%)")
print("\nInference test complete.")

