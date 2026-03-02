"""Quick pre-training sanity check for all modified modules."""
import sys
sys.path.insert(0, '.')

print("Checking config...")
from config import ModelConfig, AudioConfig
print(f"  EPOCHS={ModelConfig.EPOCHS}, BATCH_SIZE={ModelConfig.BATCH_SIZE}, EARLY_STOPPING_PATIENCE={ModelConfig.EARLY_STOPPING_PATIENCE}")

print("Checking feature extractor...")
from utils.feature_extractor import FeatureExtractor, N_FEATURES
import numpy as np
fe = FeatureExtractor()
test_audio = np.random.randn(16000).astype(np.float32)
feats = fe.extract_all_features(test_audio)
assert feats.shape[0] == N_FEATURES, f"Mismatch: got {feats.shape[0]}, expected {N_FEATURES}"
print(f"  N_FEATURES={N_FEATURES}, shape={feats.shape}  OK")

print("Checking model architecture...")
from models.non_speech_model import NonSpeechThreatModel, FocalLoss, SEBlock, CNNLSTMNetwork
m = NonSpeechThreatModel()
m.build_model()
print(f"  Model built OK on {m.device}")

print("Checking data loader...")
from training.data_loader import AudioDataLoader, MIN_SAMPLES_PER_CLASS
dl = AudioDataLoader()
print(f"  AudioDataLoader OK  (MIN_SAMPLES_PER_CLASS={MIN_SAMPLES_PER_CLASS})")

print("Checking trainer...")
from training.trainer import ModelTrainer
t = ModelTrainer()
assert hasattr(t, "save_results"), "save_results() missing from ModelTrainer!"
print("  ModelTrainer OK — save_results present")

print("Checking threat detector thresholds...")
from models.threat_detector import ThreatDetector
td = ThreatDetector()
print(f"  consecutive_required={td.consecutive_required}")
print(f"  crying={td.class_thresholds['crying']}, screaming={td.class_thresholds['screaming']}, shouting={td.class_thresholds['shouting']}, glass_breaking={td.class_thresholds['glass_breaking']}")

print()
print("=" * 50)
print("ALL CHECKS PASSED — Ready to train!")
print("=" * 50)

