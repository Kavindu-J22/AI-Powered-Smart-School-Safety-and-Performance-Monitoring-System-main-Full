"""
Audio Dataset Loader
Loads and prepares audio data for training threat detection models.
Includes per-class augmentation and oversampling for minority classes.
Using torchaudio / numpy (no librosa dependency).
"""
import os
import random
import numpy as np
from pathlib import Path
from typing import Tuple, List, Dict, Optional
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder
from tqdm import tqdm
import sys

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import DATASET_DIR, ModelConfig, AudioConfig
from utils.audio_processor import AudioProcessor
from utils.feature_extractor import FeatureExtractor

# Minimum number of *processed chunks* we want per class before splitting.
# Classes below this are oversampled via augmentation.
MIN_SAMPLES_PER_CLASS = 200


def to_categorical(y: np.ndarray, num_classes: int = None) -> np.ndarray:
    """Convert class vector to one-hot encoded matrix (replaces keras version)"""
    y = np.array(y, dtype='int')
    if num_classes is None:
        num_classes = np.max(y) + 1
    n = y.shape[0]
    categorical = np.zeros((n, num_classes), dtype=np.float32)
    categorical[np.arange(n), y] = 1
    return categorical


class AudioDataLoader:
    """Load and prepare audio dataset for training with augmentation + oversampling"""

    def __init__(self, dataset_path: str = None):
        self.dataset_path = Path(dataset_path) if dataset_path else DATASET_DIR
        self.audio_processor = AudioProcessor()
        self.feature_extractor = FeatureExtractor()
        self.label_encoder = LabelEncoder()
        self.sr = AudioConfig.SAMPLE_RATE

        # Mapping of folder names to class labels.
        # On Windows the filesystem is case-insensitive, so only one variant of
        # each folder name should be listed here to avoid double-counting files.
        # The actual dataset folder names are:
        #   crying / glass_breaking / Normal / screaming / shouting
        self.folder_to_class = {
            # Primary folder names (match the actual dataset)
            'crying':           'crying',
            'screaming':        'screaming',
            'shouting':         'shouting',
            'glass_breaking':   'glass_breaking',
            'Normal':           'normal',
            # Legacy / alternative names (kept for backward-compat — skipped if absent)
            'crying-mp3':       'crying',
            'crying-wav':       'crying',
            'Screaming-mp3':    'screaming',
            'screaming-wav':    'screaming',
            'glass breaking':   'glass_breaking',
            'non_scream':       'normal',
            'normal':           'normal',
            'background':       'normal',
        }

    # ─────────────────────────────────────────────────────────────────────────
    #  Audio augmentation helpers (pure numpy, no librosa)
    # ─────────────────────────────────────────────────────────────────────────

    def _augment_audio(self, audio: np.ndarray, sr: int) -> np.ndarray:
        """
        Apply a random combination of augmentations to an audio array.
        Returns an augmented copy; original is unchanged.
        """
        aug = audio.copy()

        # 1. Amplitude scaling  ±20 %
        if random.random() < 0.6:
            scale = random.uniform(0.80, 1.20)
            aug = aug * scale

        # 2. Additive Gaussian noise  (SNR 20-40 dB)
        if random.random() < 0.5:
            rms   = float(np.sqrt(np.mean(aug ** 2))) + 1e-9
            snr   = random.uniform(20, 40)          # dB
            noise_rms = rms / (10 ** (snr / 20))
            aug   = aug + noise_rms * np.random.randn(len(aug)).astype(np.float32)

        # 3. Time shift  (up to ±20 % of length)
        if random.random() < 0.5:
            max_shift = int(0.20 * len(aug))
            shift     = random.randint(-max_shift, max_shift)
            aug       = np.roll(aug, shift)

        # 4. Pitch shift via resampling (±2 semitones → ×rate in [0.89, 1.12])
        if random.random() < 0.4:
            semitones  = random.uniform(-2, 2)
            rate       = 2 ** (semitones / 12)
            new_len    = int(len(aug) / rate)
            if new_len > 0:
                indices = np.linspace(0, len(aug) - 1, new_len)
                aug_resampled = np.interp(indices, np.arange(len(aug)), aug).astype(np.float32)
                # Pad / trim back to original length
                if len(aug_resampled) < len(audio):
                    aug = np.pad(aug_resampled, (0, len(audio) - len(aug_resampled)), mode='edge')
                else:
                    aug = aug_resampled[:len(audio)]

        # 5. Time stretching via polyphase interpolation (rate ×0.85 – ×1.15)
        if random.random() < 0.4:
            rate    = random.uniform(0.85, 1.15)
            new_len = int(len(aug) * rate)
            if new_len > 0:
                indices = np.linspace(0, len(aug) - 1, new_len)
                stretched = np.interp(indices, np.arange(len(aug)), aug).astype(np.float32)
                if len(stretched) < len(audio):
                    aug = np.pad(stretched, (0, len(audio) - len(stretched)), mode='edge')
                else:
                    aug = stretched[:len(audio)]

        # Clip to [-1, 1]
        aug = np.clip(aug, -1.0, 1.0)
        return aug.astype(np.float32)
    
    # ─────────────────────────────────────────────────────────────────────────
    #  Dataset loading
    # ─────────────────────────────────────────────────────────────────────────

    def get_audio_files(self) -> List[Tuple[str, str]]:
        """Get all audio files with their class labels.

        Deduplicates by resolved absolute path so that case-insensitive
        filesystems (Windows) don't count the same file twice when multiple
        folder-name variants in folder_to_class resolve to the same directory.
        """
        audio_files = []
        seen_file_paths: set = set()
        seen_folder_paths: set = set()

        for folder_name, class_label in self.folder_to_class.items():
            folder_path = self.dataset_path / folder_name
            if not folder_path.exists():
                continue  # silently skip missing/legacy folder variants

            # Resolve to absolute path for deduplication (handles case-insensitivity)
            resolved = folder_path.resolve()
            if resolved in seen_folder_paths:
                continue
            seen_folder_paths.add(resolved)

            found = 0
            for ext in ['*.wav', '*.mp3', '*.ogg', '*.flac']:
                for fp in folder_path.glob(ext):
                    fp_resolved = str(fp.resolve())
                    if fp_resolved not in seen_file_paths:
                        seen_file_paths.add(fp_resolved)
                        audio_files.append((str(fp), class_label))
                        found += 1
            if found:
                print(f"  Found folder '{folder_name}' → class '{class_label}' ({found} files)")

        return audio_files

    def _load_audio_file(self, file_path: str) -> Optional[np.ndarray]:
        """Load and preprocess one audio file; return numpy array or None on error."""
        try:
            audio, sr = self.audio_processor.load_audio(file_path)
            audio = self.audio_processor.preprocess_audio(audio)
            if len(audio) < self.sr * 0.3:
                return None
            return audio
        except Exception as e:
            print(f"  Error loading {file_path}: {e}")
            return None

    def _chunks_from_audio(self, audio: np.ndarray, label: str,
                           augment: bool = False) -> List[Tuple[np.ndarray, str]]:
        """
        Optionally augment a pre-loaded audio array, chunk it, and extract features.
        Returns a list of (feature_array (time, feat), label) pairs.
        """
        results = []
        try:
            if augment:
                audio = self._augment_audio(audio, self.sr)
            chunks = self.audio_processor.split_into_chunks(audio)
            for chunk in chunks:
                feats = self.feature_extractor.extract_fixed_length_features(chunk)
                feats_norm, _, _ = self.feature_extractor.normalize_features(feats)
                results.append((feats_norm.T, label))
        except Exception as e:
            print(f"  Error extracting features: {e}")
        return results

    def _process_file(self, file_path: str, label: str,
                      augment: bool = False) -> List[Tuple[np.ndarray, str]]:
        """Load one audio file, chunk it, optionally augment, extract features."""
        audio = self._load_audio_file(file_path)
        if audio is None:
            return []
        return self._chunks_from_audio(audio, label, augment=augment)

    def load_and_extract_features(
        self,
        audio_files: List[Tuple[str, str]],
        max_samples: Optional[int] = None
    ) -> Tuple[np.ndarray, np.ndarray]:
        """
        Load raw audio files, extract features, and apply class-level
        oversampling+augmentation so every class reaches MIN_SAMPLES_PER_CLASS.

        Key optimisation: each audio file is loaded from disk ONCE and cached
        in memory as a numpy array. Augmentation reuses the in-memory arrays
        instead of re-reading files (avoids repeated pydub/ffmpeg calls).
        """
        # ── 1. Group files by class ──────────────────────────────────────────
        class_files: Dict[str, List[str]] = {}
        for fp, lbl in audio_files:
            class_files.setdefault(lbl, []).append(fp)

        print(f"\n  File counts per class:")
        for cls in sorted(class_files):
            print(f"    {cls:20s}: {len(class_files[cls])} files")

        # ── 2. Pre-load all audio arrays into memory (ONE disk read per file) ─
        print("\n  Pre-loading audio files into memory...")
        audio_cache: Dict[str, np.ndarray] = {}
        all_fps = [fp for fps in class_files.values() for fp in fps]
        for fp in tqdm(all_fps, desc="  Loading audio", leave=False):
            arr = self._load_audio_file(fp)
            if arr is not None:
                audio_cache[fp] = arr
        print(f"  Loaded {len(audio_cache)}/{len(all_fps)} files into memory.")

        # ── 3. Process each class; oversample minorities from memory ─────────
        all_features: List[np.ndarray] = []
        all_labels:   List[str]        = []

        for cls, files in class_files.items():
            class_feats: List[np.ndarray] = []

            # a) Extract chunks from all real files (no augmentation)
            valid_files = [fp for fp in files if fp in audio_cache]
            for fp in tqdm(valid_files, desc=f"  Extracting {cls}", leave=False):
                for feat, _ in self._chunks_from_audio(audio_cache[fp], cls, augment=False):
                    class_feats.append(feat)

            real_count = len(class_feats)

            # b) Augment IN MEMORY until we reach MIN_SAMPLES_PER_CLASS
            if real_count < MIN_SAMPLES_PER_CLASS and len(valid_files) > 0:
                needed = MIN_SAMPLES_PER_CLASS - real_count
                aug_generated = 0
                # Cycle through valid files until we have enough samples
                file_cycle = valid_files * ((needed // max(len(valid_files), 1)) + 2)
                random.shuffle(file_cycle)
                for fp in tqdm(file_cycle, desc=f"  Augmenting {cls}", leave=False):
                    if aug_generated >= needed:
                        break
                    for feat, _ in self._chunks_from_audio(audio_cache[fp], cls, augment=True):
                        class_feats.append(feat)
                        aug_generated += 1
                        if aug_generated >= needed:
                            break

            print(f"    {cls:20s}: {real_count} real → {len(class_feats)} after augmentation")
            all_features.extend(class_feats)
            all_labels.extend([cls] * len(class_feats))

        if max_samples and len(all_features) > max_samples:
            idx = random.sample(range(len(all_features)), max_samples)
            all_features = [all_features[i] for i in idx]
            all_labels   = [all_labels[i]   for i in idx]

        X = np.array(all_features, dtype=np.float32)
        y = np.array(all_labels)
        print(f"\n  Total samples: {len(X)}")
        return X, y

    def prepare_dataset(self, test_size: float = 0.2,
                        max_samples: Optional[int] = None) -> Dict[str, np.ndarray]:
        """Prepare full dataset for training with augmentation & oversampling."""
        audio_files = self.get_audio_files()
        print(f"\nFound {len(audio_files)} audio files across all folders.")

        # Show raw file distribution
        raw_counts: Dict[str, int] = {}
        for _, lbl in audio_files:
            raw_counts[lbl] = raw_counts.get(lbl, 0) + 1
        print("Raw file distribution:")
        for cls in sorted(raw_counts):
            print(f"  {cls:20s}: {raw_counts[cls]} files")

        X, y = self.load_and_extract_features(audio_files, max_samples)

        if len(X) == 0:
            raise ValueError("No samples loaded. Check dataset path and folder names.")

        # Encode labels — ensure all 5 classes are present in the encoder
        all_classes = ModelConfig.NON_SPEECH_CLASSES
        self.label_encoder.fit(all_classes)
        y_encoded    = self.label_encoder.transform(y)
        y_categorical = to_categorical(y_encoded, num_classes=len(all_classes))

        # Stratified split
        X_train, X_test, y_train, y_test = train_test_split(
            X, y_categorical, test_size=test_size,
            random_state=42, stratify=y_encoded
        )

        print(f"\nDataset ready:")
        print(f"  Training samples : {len(X_train)}")
        print(f"  Test samples     : {len(X_test)}")
        print(f"  Feature shape    : {X_train.shape[1:]}")
        print(f"  Classes          : {list(self.label_encoder.classes_)}")

        return {
            'X_train':      X_train,
            'X_test':       X_test,
            'y_train':      y_train,
            'y_test':       y_test,
            'classes':      list(self.label_encoder.classes_),
            'label_encoder': self.label_encoder
        }
    
    def get_sample_for_demo(self) -> Tuple[np.ndarray, str]:
        """Get a single sample for demonstration"""
        audio_files = self.get_audio_files()
        if not audio_files:
            return None, None
        
        file_path, label = audio_files[0]
        audio, sr = self.audio_processor.load_audio(file_path)
        audio = self.audio_processor.preprocess_audio(audio)
        features = self.feature_extractor.extract_fixed_length_features(audio)
        features_normalized, _, _ = self.feature_extractor.normalize_features(features)
        
        return features_normalized.T, label

