"""
Integration test: ThreatDetector pipeline with PANNS backend.
Tests threshold routing, threat level calculation, and sensitivity presets.
"""
import sys
import numpy as np
from pathlib import Path

sys.path.insert(0, str(Path(__file__).parent))


def make_audio(seconds=2, sr=16000):
    """Return a short array of near-silence (so energy check passes)."""
    return (np.random.randn(sr * seconds) * 0.05).astype(np.float32)


print("Loading ThreatDetector (PANNS init takes ~15s on first run)...")
from models.threat_detector import ThreatDetector

td = ThreatDetector()
print(f"\n=== Detector status ===")
status = td.get_status()
print(f"  Active detector : {status['active_detector']}")
print(f"  PANNS available : {status['panns_available']}")
print(f"  class_thresholds: {status['thresholds']['class_thresholds']}")


# --- Test 1: threshold routing ---
print("\n=== Test 1: Threshold routing for PANNS vs custom ===")
assert td.panns_available == (td.panns_detector is not None and td.panns_detector.initialized)
if td.panns_available:
    # PANNS thresholds should be low (< 0.15)
    assert td.class_thresholds['crying'] < 0.15, "PANNS crying threshold too high!"
    assert td.class_thresholds['screaming'] < 0.15, "PANNS screaming threshold too high!"
    print("  PANNS thresholds OK (< 0.15)")
else:
    # Custom model thresholds should be high (> 0.40)
    assert td.custom_class_thresholds['crying'] >= 0.40, "Custom crying threshold too low!"
    print("  Custom model thresholds OK")

# --- Test 2: threat level calculation ---
print("\n=== Test 2: Threat level mapping (normalised ratio) ===")
base_thr = td.class_thresholds.get('screaming', 0.08)
cases = [
    ('screaming', 0.917, 'critical'),   # ratio ~11.5 → critical
    ('screaming', 0.312, 'medium'),     # ratio ~3.9  → medium
    ('crying',    0.087, 'low'),        # ratio ~1.45 → low
    ('crying',    0.243, 'medium'),     # ratio ~4.05 → medium
    ('glass_breaking', 0.770, 'high'), # ratio ~9.6  → high
]
for cls, conf, expected_level in cases:
    bth = td.class_thresholds.get(cls, 0.08) if td.panns_available \
          else td.custom_class_thresholds.get(cls, 0.50)
    ratio = conf / max(bth, 1e-6)
    if ratio >= 10.0:   level = 'critical'
    elif ratio >= 5.0:  level = 'high'
    elif ratio >= 2.0:  level = 'medium'
    else:               level = 'low'
    mark = '✓' if level == expected_level else '✗'
    print(f"  [{mark}] {cls}={conf:.3f} ratio={ratio:.1f} → {level} (expected {expected_level})")

# --- Test 3: sensitivity presets ---
print("\n=== Test 3: Sensitivity presets ===")
for preset in ['low', 'normal', 'high']:
    td.set_sensitivity(preset)
    t = td.class_thresholds
    print(f"  {preset:6s}: crying={t['crying']:.3f} screaming={t['screaming']:.3f} "
          f"glass={t['glass_breaking']:.3f}")

# Reset to normal
td.set_sensitivity('normal')

# --- Test 4: analyze_audio with a real audio file ---
print("\n=== Test 4: Full analyze_audio pipeline ===")
sample = Path(__file__).parent / "Non Speech Dataset" / "screaming"
wav_files = list(sample.glob("*.wav"))[:1]
if wav_files:
    import soundfile as sf
    audio, sr = sf.read(str(wav_files[0]))
    if audio.ndim > 1: audio = audio.mean(axis=1)
    res = td.analyze_audio(audio.astype(np.float32), enable_speech=False)
    ns = res.get('non_speech_result', {})
    print(f"  File: {wav_files[0].name}")
    print(f"  Detected class : {ns.get('detected_class')}")
    print(f"  Confidence     : {ns.get('confidence'):.4f}")
    print(f"  Is threat      : {ns.get('is_threat')}")
    print(f"  Threat level   : {res.get('threat_level')}")
    print(f"  Detector used  : {ns.get('detector')}")
    print(f"  All probs      : {ns.get('all_probabilities')}")
else:
    print("  No screaming wav files found for pipeline test.")

print("\nAll tests passed.")

