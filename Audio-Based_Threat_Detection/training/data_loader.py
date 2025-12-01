"""
Audio Dataset Loader
Loads and prepares audio data for training threat detection models
Using PyTorch backend for Python 3.14 compatibility
"""
import os
import numpy as np
from pathlib import Path
from typing import Tuple, List, Dict
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import LabelEncoder
from tqdm import tqdm
import sys

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import DATASET_DIR, ModelConfig
from utils.audio_processor import AudioProcessor
from utils.feature_extractor import FeatureExtractor


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
    """Load and prepare audio dataset for training"""
    
    def __init__(self, dataset_path: str = None):
        self.dataset_path = Path(dataset_path) if dataset_path else DATASET_DIR
        self.audio_processor = AudioProcessor()
        self.feature_extractor = FeatureExtractor()
        self.label_encoder = LabelEncoder()
        
        # Mapping of folder names to class labels
        self.folder_to_class = {
            'crying': 'crying',
            'crying-mp3': 'crying',
            'crying-wav': 'crying',
            'screaming': 'screaming',
            'Screaming-mp3': 'screaming',
            'screaming- wav': 'screaming',
            'shouting': 'shouting',
            'glass breaking': 'glass_breaking',
            'non_scream': 'normal'
        }
    
    def get_audio_files(self) -> List[Tuple[str, str]]:
        """Get all audio files with their labels"""
        audio_files = []
        
        for folder_name, class_label in self.folder_to_class.items():
            folder_path = self.dataset_path / folder_name
            
            if not folder_path.exists():
                print(f"Warning: Folder not found - {folder_path}")
                continue
            
            # Get all audio files
            for ext in ['*.wav', '*.mp3', '*.ogg', '*.flac']:
                for file_path in folder_path.glob(ext):
                    audio_files.append((str(file_path), class_label))
        
        return audio_files
    
    def load_and_extract_features(self, audio_files: List[Tuple[str, str]], 
                                   max_samples: int = None) -> Tuple[np.ndarray, np.ndarray]:
        """Load audio files and extract features"""
        features_list = []
        labels_list = []
        
        if max_samples:
            audio_files = audio_files[:max_samples]
        
        print(f"Loading {len(audio_files)} audio files...")
        
        for file_path, label in tqdm(audio_files, desc="Extracting features"):
            try:
                # Load audio
                audio, sr = self.audio_processor.load_audio(file_path)
                
                # Preprocess
                audio = self.audio_processor.preprocess_audio(audio)
                
                # Skip very short audio
                if len(audio) < sr * 0.3:  # Less than 0.3 seconds
                    continue
                
                # Split into chunks if long
                chunks = self.audio_processor.split_into_chunks(audio)
                
                for chunk in chunks:
                    # Extract features
                    features = self.feature_extractor.extract_fixed_length_features(chunk)
                    features_normalized, _, _ = self.feature_extractor.normalize_features(features)
                    
                    # Transpose to (time, features)
                    features_list.append(features_normalized.T)
                    labels_list.append(label)
                    
            except Exception as e:
                print(f"Error processing {file_path}: {e}")
                continue
        
        X = np.array(features_list)
        y = np.array(labels_list)
        
        print(f"Loaded {len(X)} samples")
        return X, y
    
    def prepare_dataset(self, test_size: float = 0.2, 
                        max_samples: int = None) -> Dict[str, np.ndarray]:
        """Prepare full dataset for training"""
        # Get audio files
        audio_files = self.get_audio_files()
        print(f"Found {len(audio_files)} audio files")
        
        # Print class distribution
        class_counts = {}
        for _, label in audio_files:
            class_counts[label] = class_counts.get(label, 0) + 1
        print("Class distribution:")
        for cls, count in sorted(class_counts.items()):
            print(f"  {cls}: {count}")
        
        # Load and extract features
        X, y = self.load_and_extract_features(audio_files, max_samples)
        
        if len(X) == 0:
            raise ValueError("No samples loaded. Check dataset path.")
        
        # Encode labels
        y_encoded = self.label_encoder.fit_transform(y)
        y_categorical = to_categorical(y_encoded)
        
        # Split dataset
        X_train, X_test, y_train, y_test = train_test_split(
            X, y_categorical, test_size=test_size, 
            random_state=42, stratify=y_encoded
        )
        
        print(f"\nDataset prepared:")
        print(f"  Training samples: {len(X_train)}")
        print(f"  Test samples: {len(X_test)}")
        print(f"  Feature shape: {X_train.shape[1:]}")
        print(f"  Classes: {list(self.label_encoder.classes_)}")
        
        return {
            'X_train': X_train,
            'X_test': X_test,
            'y_train': y_train,
            'y_test': y_test,
            'classes': list(self.label_encoder.classes_),
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

