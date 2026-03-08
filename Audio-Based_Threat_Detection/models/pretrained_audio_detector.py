"""
PANNs CNN14 Pre-trained Audio Detector
Detects: crying, screaming, shouting, glass_breaking, normal
Pre-trained on AudioSet (527 classes). No custom training required.
"""
import os
import sys
import types
import importlib.util
import numpy as np
import time
from pathlib import Path

# ── AudioSet label → threat class mapping ────────────────────────────────
# Two tiers of matching are used to maximise recall without introducing
# noisy false positives:
#
#  Tier 1 – THREAT_EXACT_LABELS: exact display-name strings (highest priority).
#            Updated to match the AudioSet CSV exactly (v1).
#
#  Tier 2 – THREAT_KEYWORD_LABELS: case-insensitive substrings applied to any
#            label not already claimed by Tier 1.  This catches rare label
#            variants, regional spellings, and future AudioSet revisions.
#
# AudioSet CSV reference (527 classes, v1):
#  - Screaming, Crying/sobbing, Glass/Shatter, etc. verified against
#    http://storage.googleapis.com/us_audioset/…/class_labels_indices.csv

THREAT_EXACT_LABELS = {
    'crying': [
        'Crying, sobbing', 'Baby cry, infant cry', 'Whimper',
        'Wail, moan', 'Weeping', 'Sobbing', 'Cry',
    ],
    'screaming': [
        'Screaming', 'Scream', 'Shriek', 'Squeal',
        'Screech', 'Whoop',
    ],
    'shouting': [
        'Shout', 'Yell', 'Children shouting', 'Battle cry', 'War cry',
        'Howl', 'Bellow',
    ],
    'glass_breaking': [
        'Glass', 'Shatter', 'Smash, crash', 'Breaking', 'Crack',
        'Shatter', 'Broken glass', 'Glass shatter',
        'Crash', 'Smash',
    ],
}

# Tier-2 keyword matching (substring, case-insensitive).
# Only applied to labels NOT already matched by THREAT_EXACT_LABELS.
THREAT_KEYWORD_LABELS = {
    'crying': ['cry', 'sob', 'weep', 'wail', 'whimper', 'infant cry', 'baby cry'],
    'screaming': ['scream', 'shriek', 'screech', 'squeal', 'yell'],
    'shouting': ['shout', 'yell', 'holler', 'battle cry'],
    'glass_breaking': ['glass', 'shatter', 'smash', 'crash', 'splinter', 'break'],
}

# ── Paths & URLs ───────────────────────────────────────────────────────────
PANNS_DATA_DIR  = Path.home() / "panns_data"
LABELS_CSV_PATH = PANNS_DATA_DIR / "class_labels_indices.csv"
MODEL_PATH      = PANNS_DATA_DIR / "Cnn14_mAP=0.431.pth"
LABELS_CSV_URL  = ("http://storage.googleapis.com/us_audioset/youtube_corpus"
                   "/v1/csv/class_labels_indices.csv")
MODEL_URL       = ("https://zenodo.org/record/3987831/files/"
                   "Cnn14_mAP%3D0.431.pth?download=1")
AUDIOSET_CLASSES = 527


# ── Load PANNS model/utils WITHOUT triggering __init__.py (avoids matplotlib)
def _load_panns_modules():
    try:
        pkg_spec = importlib.util.find_spec('panns_inference')
        if pkg_spec is None:
            return None, None
        panns_dir = Path(pkg_spec.origin).parent

        # Register lightweight package stub so relative imports inside submodules work
        if 'panns_inference' not in sys.modules:
            stub = types.ModuleType('panns_inference')
            stub.__path__    = [str(panns_dir)]
            stub.__package__ = 'panns_inference'
            sys.modules['panns_inference'] = stub

        def _load_sub(name, filepath):
            spec = importlib.util.spec_from_file_location(
                f'panns_inference.{name}', str(filepath))
            mod  = importlib.util.module_from_spec(spec)
            mod.__package__ = 'panns_inference'
            sys.modules[f'panns_inference.{name}'] = mod
            spec.loader.exec_module(mod)
            return mod

        utils_mod  = _load_sub('pytorch_utils', panns_dir / 'pytorch_utils.py')
        models_mod = _load_sub('models',         panns_dir / 'models.py')
        return models_mod.Cnn14, utils_mod.move_data_to_device
    except Exception as exc:
        print(f"[PANNs] Module load error: {exc}")
        return None, None


_Cnn14, _move_data = _load_panns_modules()
_PANNS_AVAILABLE   = _Cnn14 is not None


# ── Main detector class ────────────────────────────────────────────────────
class PANNsAudioDetector:
    """Real-time non-speech threat detector using PANNS CNN14."""

    def __init__(self):
        self.model           = None
        self.labels          = []
        self.threat_idx      = {}   # class → [audioset_indices]
        self.initialized     = False
        self.target_sr       = 32000
        self._resampler_cache = {}  # (orig_sr, target_sr) → torchaudio.transforms.Resample

    # ── public ────────────────────────────────────────────────────────────
    def initialize(self) -> bool:
        if not _PANNS_AVAILABLE:
            print("[PANNs] panns_inference not available.")
            return False
        try:
            if not self._ensure_labels():
                return False
            self.labels = self._read_labels()
            self._build_index()
            if not self._ensure_model():
                return False
            self._load_model()
            self.initialized = True
            print("[PANNs] Detector ready. AudioSet class coverage:")
            for cls, idxs in self.threat_idx.items():
                print(f"  {cls}: {len(idxs)} matching label(s)")
            return True
        except Exception as exc:
            import traceback
            print(f"[PANNs] init failed: {exc}")
            traceback.print_exc()
            return False

    # Minimum AudioSet probability for a threat class to be considered "present".
    # Below this, PANNS genuinely does not think any threat is in the audio → normal.
    # AudioSet probabilities are normalised across 527 classes so even 0.05 is
    # meaningful (99th percentile for most non-relevant classes).
    MIN_SIGNIFICANCE = 0.05

    def detect(self, audio: np.ndarray, src_sr: int = 16000):
        """
        Classify a mono audio array.

        Scoring strategy:
          • Compute the max AudioSet probability for each threat class.
          • If the best threat score ≥ MIN_SIGNIFICANCE, return that class.
          • Otherwise the audio is genuinely not a threat → 'normal'.
          This avoids the "1 − max_threat" formula making normal dominant when
          real threat scores are moderate (0.10–0.35 range is normal for AudioSet).

        Returns:
            (class_name: str, confidence: float, scores: dict[str, float])
        """
        if not self.initialized:
            return 'normal', 0.0, {}
        try:
            import torch
            audio = audio.astype(np.float32)
            if src_sr != self.target_sr:
                audio = self._resample_cached(audio, src_sr, self.target_sr)
            batch = torch.from_numpy(audio[np.newaxis, :])   # (1, T)
            with torch.no_grad():
                self.model.eval()
                out   = self.model(batch, None)
            probs = out['clipwise_output'].cpu().numpy()[0]   # (527,)

            # Threat class scores (max over all matching AudioSet labels)
            scores = {
                cls: float(np.max(probs[idxs])) if idxs else 0.0
                for cls, idxs in self.threat_idx.items()
            }
            best_threat = max(scores, key=scores.get)
            best_score  = scores[best_threat]

            # Normal score = complement of best threat (for display only)
            scores['normal'] = round(max(0.0, 1.0 - best_score), 4)

            # Decision: return threat only if score is meaningful
            if best_score >= self.MIN_SIGNIFICANCE:
                return best_threat, float(best_score), scores
            else:
                return 'normal', float(scores['normal']), scores

        except Exception as exc:
            print(f"[PANNs] detect error: {exc}")
            return 'normal', 0.0, {}

    # ── private helpers ───────────────────────────────────────────────────
    def _ensure_labels(self) -> bool:
        if LABELS_CSV_PATH.exists() and LABELS_CSV_PATH.stat().st_size > 1000:
            return True
        PANNS_DATA_DIR.mkdir(parents=True, exist_ok=True)
        try:
            import requests
            print("[PANNs] Downloading AudioSet labels CSV …")
            r = requests.get(LABELS_CSV_URL, timeout=30)
            r.raise_for_status()
            LABELS_CSV_PATH.write_bytes(r.content)
            print("[PANNs] Labels CSV saved.")
            return True
        except Exception as exc:
            print(f"[PANNs] Labels download failed: {exc}")
            return False

    def _read_labels(self):
        import csv
        labels = []
        with open(LABELS_CSV_PATH, 'r', encoding='utf-8') as f:
            for i, row in enumerate(csv.reader(f)):
                if i == 0:
                    continue          # skip header
                if len(row) >= 3:
                    labels.append(row[2])
        return labels

    def _build_index(self):
        """Build mapping: threat class → list of matching AudioSet indices.

        Two-tier matching strategy:
          Tier 1 – exact display-name match (highest precision).
          Tier 2 – case-insensitive keyword/substring match for labels that
                   were not matched in Tier 1 (improves recall for AudioSet
                   label variants not listed in THREAT_EXACT_LABELS).
        """
        self.threat_idx = {cls: [] for cls in THREAT_EXACT_LABELS}
        claimed: set = set()  # indices already matched by Tier 1

        # Tier 1: exact matching
        for idx, lbl in enumerate(self.labels):
            for cls, exact_set in THREAT_EXACT_LABELS.items():
                if lbl in exact_set:
                    self.threat_idx[cls].append(idx)
                    claimed.add(idx)
                    break

        # Tier 2: keyword/substring matching for unclaimed labels
        for idx, lbl in enumerate(self.labels):
            if idx in claimed:
                continue
            lbl_lower = lbl.lower()
            for cls, keywords in THREAT_KEYWORD_LABELS.items():
                if any(kw in lbl_lower for kw in keywords):
                    self.threat_idx[cls].append(idx)
                    claimed.add(idx)
                    break  # one label → one threat class max

        # Log coverage summary
        for cls, idxs in self.threat_idx.items():
            if not idxs:
                print(f"[PANNs] WARNING: No AudioSet labels matched for class '{cls}'."
                      " Detections will be unreliable.")

    def _ensure_model(self) -> bool:
        if MODEL_PATH.exists() and MODEL_PATH.stat().st_size > 3e8:
            print(f"[PANNs] Model found at {MODEL_PATH}")
            return True
        PANNS_DATA_DIR.mkdir(parents=True, exist_ok=True)
        try:
            import requests
            print("[PANNs] Downloading CNN14 model (~322 MB). Please wait …")
            r     = requests.get(MODEL_URL, stream=True, timeout=120)
            r.raise_for_status()
            total = int(r.headers.get('content-length', 0))
            done  = 0; t0 = time.time()
            with open(MODEL_PATH, 'wb') as f:
                for chunk in r.iter_content(1 << 20):  # 1 MB chunks
                    if chunk:
                        f.write(chunk)
                        done += len(chunk)
                        if total:
                            pct  = done / total * 100
                            mbps = done / max(time.time() - t0, 0.1) / 1e6
                            print(f"\r[PANNs] {pct:.1f}% — {mbps:.1f} MB/s",
                                  end='', flush=True)
            print("\n[PANNs] Model downloaded.")
            return True
        except Exception as exc:
            print(f"\n[PANNs] Model download failed: {exc}")
            return False

    def _load_model(self):
        import torch
        n = len(self.labels) if self.labels else AUDIOSET_CLASSES
        self.model = _Cnn14(
            sample_rate=32000, window_size=1024, hop_size=320,
            mel_bins=64, fmin=50, fmax=14000, classes_num=n,
        )
        ckpt = torch.load(str(MODEL_PATH), map_location='cpu')
        self.model.load_state_dict(ckpt['model'])
        self.model.eval()
        # Pre-warm the resampler cache for the common 16kHz→32kHz path
        self._get_or_create_resampler(16000, self.target_sr)
        print(f"[PANNs] CNN14 loaded ({n} AudioSet classes).")

    def _get_or_create_resampler(self, orig_sr: int, target_sr: int):
        """Return a cached torchaudio.transforms.Resample for the given rate pair."""
        key = (orig_sr, target_sr)
        if key not in self._resampler_cache:
            try:
                import torchaudio
                self._resampler_cache[key] = torchaudio.transforms.Resample(
                    orig_freq=orig_sr, new_freq=target_sr
                )
            except Exception:
                self._resampler_cache[key] = None  # mark as unavailable
        return self._resampler_cache[key]

    def _resample_cached(self, audio: np.ndarray, orig_sr: int, target_sr: int) -> np.ndarray:
        """Resample using a cached torchaudio resampler (fast path) with scipy/numpy fallbacks."""
        resampler = self._get_or_create_resampler(orig_sr, target_sr)
        if resampler is not None:
            try:
                import torch
                waveform = torch.from_numpy(audio).unsqueeze(0)   # (1, T)
                resampled = resampler(waveform).squeeze(0).numpy()
                return resampled.astype(np.float32)
            except Exception:
                pass  # fall through to legacy path
        return self._resample(audio, orig_sr, target_sr)

    @staticmethod
    def _resample(audio: np.ndarray, orig_sr: int, target_sr: int) -> np.ndarray:
        """Legacy resampler (librosa / scipy / linear interp fallback)."""
        try:
            import librosa
            return librosa.resample(audio, orig_sr=orig_sr, target_sr=target_sr)
        except Exception:
            pass
        try:
            from scipy.signal import resample_poly
            from math import gcd
            g = gcd(orig_sr, target_sr)
            return resample_poly(
                audio.astype(np.float64), target_sr // g, orig_sr // g
            ).astype(np.float32)
        except Exception:
            ratio = target_sr / orig_sr
            n_new = int(len(audio) * ratio)
            return np.interp(
                np.linspace(0, len(audio) - 1, n_new),
                np.arange(len(audio)), audio,
            ).astype(np.float32)

