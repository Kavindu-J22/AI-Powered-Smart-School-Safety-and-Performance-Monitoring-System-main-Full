"""
Comprehensive Training Script for Object Detection and Threat Detection Models
Displays accuracy, F1 score, precision, recall, and other metrics
"""

import os
import sys
import json
import time
from pathlib import Path
from datetime import datetime
from typing import Dict, List, Tuple, Optional

import numpy as np
import torch
import torch.nn as nn
import torch.optim as optim
from torch.utils.data import Dataset, DataLoader
import cv2
from tqdm import tqdm
from sklearn.metrics import (
    accuracy_score, precision_score, recall_score, f1_score,
    confusion_matrix, classification_report
)

# Add parent directory to path
sys.path.insert(0, str(Path(__file__).parent.parent))


def print_banner(text: str, char: str = "="):
    """Print a banner with text"""
    width = 70
    print("\n" + char * width)
    print(f" {text}")
    print(char * width)


def print_metrics(metrics: Dict, title: str = "Metrics"):
    """Print metrics in a formatted table"""
    print(f"\n{title}:")
    print("-" * 50)
    for key, value in metrics.items():
        if isinstance(value, float):
            print(f"  {key:25s}: {value:.4f}")
        else:
            print(f"  {key:25s}: {value}")
    print("-" * 50)


class ObjectDetectionTrainer:
    """Trainer for YOLOv8 object detection model"""
    
    def __init__(self, data_yaml: str, model_output: str = "models/left_behind_detector.pt"):
        self.data_yaml = Path(data_yaml)
        self.model_output = Path(model_output)
        self.model_output.parent.mkdir(parents=True, exist_ok=True)
        
    def train(self, epochs: int = 50, batch_size: int = 16, img_size: int = 640,
              pretrained: str = "yolov8n.pt"):
        """Train YOLOv8 model and display metrics"""
        try:
            from ultralytics import YOLO
        except ImportError:
            print("ERROR: ultralytics not installed. Run: pip install ultralytics")
            return None
        
        print_banner("TRAINING OBJECT DETECTION MODEL (YOLOv8)")
        print(f"Data config: {self.data_yaml}")
        print(f"Epochs: {epochs}")
        print(f"Batch size: {batch_size}")
        print(f"Image size: {img_size}")
        print(f"Pretrained model: {pretrained}")
        
        # Initialize model
        model = YOLO(pretrained)
        
        # Train
        start_time = time.time()
        results = model.train(
            data=str(self.data_yaml),
            epochs=epochs,
            batch=batch_size,
            imgsz=img_size,
            project=str(self.model_output.parent),
            name="object_detection_training",
            save=True,
            verbose=True
        )
        training_time = time.time() - start_time
        
        # Get best model path
        best_model_path = Path(results.save_dir) / "weights" / "best.pt"
        if best_model_path.exists():
            import shutil
            shutil.copy(best_model_path, self.model_output)
            print(f"\nBest model saved to: {self.model_output}")
        
        # Evaluate on validation set
        print_banner("EVALUATING OBJECT DETECTION MODEL")
        val_results = model.val(data=str(self.data_yaml))
        
        # Extract and display metrics
        metrics = {
            "mAP50": float(val_results.box.map50),
            "mAP50-95": float(val_results.box.map),
            "Precision": float(val_results.box.mp),
            "Recall": float(val_results.box.mr),
            "Training Time (s)": training_time,
            "Epochs": epochs
        }
        
        # Calculate F1 score
        if metrics["Precision"] + metrics["Recall"] > 0:
            metrics["F1 Score"] = 2 * (metrics["Precision"] * metrics["Recall"]) / \
                                  (metrics["Precision"] + metrics["Recall"])
        else:
            metrics["F1 Score"] = 0.0
        
        print_metrics(metrics, "Object Detection Model Metrics")
        
        # Per-class metrics
        if hasattr(val_results.box, 'ap_class_index'):
            print("\nPer-Class Performance:")
            print("-" * 50)
            class_names = val_results.names
            for i, class_idx in enumerate(val_results.box.ap_class_index):
                class_name = class_names[class_idx]
                ap50 = val_results.box.ap50[i]
                print(f"  {class_name:25s}: AP50 = {ap50:.4f}")
        
        return model, metrics
    
    def test(self, model_path: Optional[str] = None, test_images_dir: Optional[str] = None):
        """Test the trained model on test images"""
        try:
            from ultralytics import YOLO
        except ImportError:
            print("ERROR: ultralytics not installed")
            return None
        
        model_path = model_path or str(self.model_output)
        if not Path(model_path).exists():
            print(f"ERROR: Model not found at {model_path}")
            return None
        
        print_banner("TESTING OBJECT DETECTION MODEL")
        model = YOLO(model_path)
        
        # Run validation on test set
        test_results = model.val(data=str(self.data_yaml), split='test')
        
        metrics = {
            "Test mAP50": float(test_results.box.map50),
            "Test mAP50-95": float(test_results.box.map),
            "Test Precision": float(test_results.box.mp),
            "Test Recall": float(test_results.box.mr)
        }
        
        if metrics["Test Precision"] + metrics["Test Recall"] > 0:
            metrics["Test F1 Score"] = 2 * (metrics["Test Precision"] * metrics["Test Recall"]) / \
                                       (metrics["Test Precision"] + metrics["Test Recall"])
        
        print_metrics(metrics, "Object Detection Test Results")
        return metrics


class ThreatVideoDataset(Dataset):
    """Dataset for loading video frames for threat detection"""

    def __init__(self, data_dir: str, split: str = 'train', transform=None):
        self.data_dir = Path(data_dir) / split
        self.transform = transform
        self.samples = []
        self.class_to_idx = {'fight': 1, 'no_fight': 0}

        # Load all video frame directories
        for class_name in ['fight', 'no_fight']:
            class_dir = self.data_dir / class_name
            if class_dir.exists():
                for video_dir in class_dir.iterdir():
                    if video_dir.is_dir():
                        frames = sorted(list(video_dir.glob('*.jpg')))
                        if len(frames) > 0:
                            self.samples.append({
                                'frames': frames,
                                'label': self.class_to_idx[class_name],
                                'class_name': class_name
                            })

        print(f"Loaded {len(self.samples)} video samples for {split}")

    def __len__(self):
        return len(self.samples)

    def __getitem__(self, idx):
        sample = self.samples[idx]
        frames = []

        for frame_path in sample['frames']:
            frame = cv2.imread(str(frame_path))
            frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            frame = frame.astype(np.float32) / 255.0
            frames.append(frame)

        # Stack frames: (T, H, W, C) -> (C, T, H, W)
        frames = np.stack(frames, axis=0)
        frames = np.transpose(frames, (3, 0, 1, 2))

        return torch.tensor(frames, dtype=torch.float32), sample['label']


class Simple3DCNN(nn.Module):
    """Simple 3D CNN for video classification"""

    def __init__(self, num_classes: int = 2, num_frames: int = 16):
        super().__init__()

        self.features = nn.Sequential(
            nn.Conv3d(3, 32, kernel_size=(3, 3, 3), padding=(1, 1, 1)),
            nn.BatchNorm3d(32),
            nn.ReLU(inplace=True),
            nn.MaxPool3d(kernel_size=(1, 2, 2)),

            nn.Conv3d(32, 64, kernel_size=(3, 3, 3), padding=(1, 1, 1)),
            nn.BatchNorm3d(64),
            nn.ReLU(inplace=True),
            nn.MaxPool3d(kernel_size=(2, 2, 2)),

            nn.Conv3d(64, 128, kernel_size=(3, 3, 3), padding=(1, 1, 1)),
            nn.BatchNorm3d(128),
            nn.ReLU(inplace=True),
            nn.MaxPool3d(kernel_size=(2, 2, 2)),

            nn.Conv3d(128, 256, kernel_size=(3, 3, 3), padding=(1, 1, 1)),
            nn.BatchNorm3d(256),
            nn.ReLU(inplace=True),
            nn.AdaptiveAvgPool3d((1, 1, 1))
        )

        self.classifier = nn.Sequential(
            nn.Flatten(),
            nn.Dropout(0.5),
            nn.Linear(256, 128),
            nn.ReLU(inplace=True),
            nn.Dropout(0.3),
            nn.Linear(128, num_classes)
        )

    def forward(self, x):
        x = self.features(x)
        x = self.classifier(x)
        return x


class ThreatDetectionTrainer:
    """Trainer for threat detection model"""

    def __init__(self, data_dir: str, model_output: str = "models/threat_detector.pt"):
        self.data_dir = Path(data_dir)
        self.model_output = Path(model_output)
        self.model_output.parent.mkdir(parents=True, exist_ok=True)
        self.device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')

    def train(self, epochs: int = 30, batch_size: int = 4, learning_rate: float = 0.001):
        """Train threat detection model"""
        print_banner("TRAINING THREAT DETECTION MODEL (3D CNN)")
        print(f"Data directory: {self.data_dir}")
        print(f"Device: {self.device}")
        print(f"Epochs: {epochs}")
        print(f"Batch size: {batch_size}")
        print(f"Learning rate: {learning_rate}")

        # Create datasets
        train_dataset = ThreatVideoDataset(self.data_dir, 'train')
        valid_dataset = ThreatVideoDataset(self.data_dir, 'valid')

        if len(train_dataset) == 0:
            print("ERROR: No training data found!")
            return None, None

        train_loader = DataLoader(train_dataset, batch_size=batch_size, shuffle=True, num_workers=0)
        valid_loader = DataLoader(valid_dataset, batch_size=batch_size, shuffle=False, num_workers=0)

        # Initialize model
        model = Simple3DCNN(num_classes=2).to(self.device)
        criterion = nn.CrossEntropyLoss()
        optimizer = optim.Adam(model.parameters(), lr=learning_rate)
        scheduler = optim.lr_scheduler.ReduceLROnPlateau(optimizer, mode='min', patience=5)

        best_val_loss = float('inf')
        best_metrics = {}
        history = {'train_loss': [], 'val_loss': [], 'val_acc': []}

        start_time = time.time()

        for epoch in range(epochs):
            # Training phase
            model.train()
            train_loss = 0.0

            pbar = tqdm(train_loader, desc=f"Epoch {epoch+1}/{epochs} [Train]")
            for frames, labels in pbar:
                frames = frames.to(self.device)
                labels = torch.tensor(labels).to(self.device)

                optimizer.zero_grad()
                outputs = model(frames)
                loss = criterion(outputs, labels)
                loss.backward()
                optimizer.step()

                train_loss += loss.item()
                pbar.set_postfix({'loss': f'{loss.item():.4f}'})

            train_loss /= len(train_loader)
            history['train_loss'].append(train_loss)

            # Validation phase
            val_metrics = self._evaluate(model, valid_loader, criterion)
            history['val_loss'].append(val_metrics['loss'])
            history['val_acc'].append(val_metrics['accuracy'])

            scheduler.step(val_metrics['loss'])

            print(f"\nEpoch {epoch+1}/{epochs}:")
            print(f"  Train Loss: {train_loss:.4f}")
            print(f"  Val Loss: {val_metrics['loss']:.4f}")
            print(f"  Val Accuracy: {val_metrics['accuracy']:.4f}")
            print(f"  Val F1 Score: {val_metrics['f1_score']:.4f}")

            # Save best model
            if val_metrics['loss'] < best_val_loss:
                best_val_loss = val_metrics['loss']
                best_metrics = val_metrics.copy()
                torch.save({
                    'model_state_dict': model.state_dict(),
                    'optimizer_state_dict': optimizer.state_dict(),
                    'epoch': epoch,
                    'metrics': best_metrics
                }, self.model_output)
                print(f"  -> Best model saved!")

        training_time = time.time() - start_time
        best_metrics['training_time'] = training_time
        best_metrics['epochs'] = epochs

        print_banner("THREAT DETECTION TRAINING COMPLETE")
        print_metrics(best_metrics, "Best Validation Metrics")

        return model, best_metrics

    def _evaluate(self, model, data_loader, criterion) -> Dict:
        """Evaluate model on a dataset"""
        model.eval()
        total_loss = 0.0
        all_preds = []
        all_labels = []

        with torch.no_grad():
            for frames, labels in data_loader:
                frames = frames.to(self.device)
                labels_tensor = torch.tensor(labels).to(self.device)

                outputs = model(frames)
                loss = criterion(outputs, labels_tensor)
                total_loss += loss.item()

                _, preds = torch.max(outputs, 1)
                all_preds.extend(preds.cpu().numpy())
                all_labels.extend(labels)

        avg_loss = total_loss / len(data_loader)

        metrics = {
            'loss': avg_loss,
            'accuracy': accuracy_score(all_labels, all_preds),
            'precision': precision_score(all_labels, all_preds, average='weighted', zero_division=0),
            'recall': recall_score(all_labels, all_preds, average='weighted', zero_division=0),
            'f1_score': f1_score(all_labels, all_preds, average='weighted', zero_division=0)
        }

        return metrics

    def test(self, model_path: Optional[str] = None):
        """Test the trained model"""
        model_path = model_path or str(self.model_output)
        if not Path(model_path).exists():
            print(f"ERROR: Model not found at {model_path}")
            return None

        print_banner("TESTING THREAT DETECTION MODEL")

        # Load model
        model = Simple3DCNN(num_classes=2).to(self.device)
        checkpoint = torch.load(model_path, map_location=self.device)
        model.load_state_dict(checkpoint['model_state_dict'])

        # Create test dataset
        test_dataset = ThreatVideoDataset(self.data_dir, 'test')
        if len(test_dataset) == 0:
            print("ERROR: No test data found!")
            return None

        test_loader = DataLoader(test_dataset, batch_size=4, shuffle=False, num_workers=0)
        criterion = nn.CrossEntropyLoss()

        # Evaluate
        metrics = self._evaluate(model, test_loader, criterion)

        # Get detailed predictions for confusion matrix
        model.eval()
        all_preds = []
        all_labels = []

        with torch.no_grad():
            for frames, labels in test_loader:
                frames = frames.to(self.device)
                outputs = model(frames)
                _, preds = torch.max(outputs, 1)
                all_preds.extend(preds.cpu().numpy())
                all_labels.extend(labels)

        # Print confusion matrix
        cm = confusion_matrix(all_labels, all_preds)
        print("\nConfusion Matrix:")
        print("                 Predicted")
        print("              No Fight  Fight")
        print(f"Actual No Fight   {cm[0][0]:5d}  {cm[0][1]:5d}")
        print(f"Actual Fight      {cm[1][0]:5d}  {cm[1][1]:5d}")

        # Print classification report
        print("\nClassification Report:")
        print(classification_report(all_labels, all_preds,
                                   target_names=['No Fight', 'Fight']))

        print_metrics(metrics, "Threat Detection Test Results")
        return metrics


def main():
    """Main training function"""
    import argparse

    parser = argparse.ArgumentParser(description='Train detection models')
    parser.add_argument('--mode', type=str, choices=['object', 'threat', 'both'], default='both',
                       help='Which model to train')
    parser.add_argument('--object-data', type=str, default='datasets/yolo_format/data.yaml',
                       help='Path to YOLO data.yaml')
    parser.add_argument('--threat-data', type=str, default='datasets/threat_frames',
                       help='Path to threat detection frames')
    parser.add_argument('--object-epochs', type=int, default=50,
                       help='Epochs for object detection')
    parser.add_argument('--threat-epochs', type=int, default=30,
                       help='Epochs for threat detection')
    parser.add_argument('--batch-size', type=int, default=16,
                       help='Batch size for training')
    parser.add_argument('--test-only', action='store_true',
                       help='Only run testing, skip training')

    args = parser.parse_args()

    results = {}

    if args.mode in ['object', 'both']:
        trainer = ObjectDetectionTrainer(args.object_data)
        if not args.test_only:
            model, metrics = trainer.train(epochs=args.object_epochs, batch_size=args.batch_size)
            results['object_detection_train'] = metrics
        test_metrics = trainer.test()
        results['object_detection_test'] = test_metrics

    if args.mode in ['threat', 'both']:
        trainer = ThreatDetectionTrainer(args.threat_data)
        if not args.test_only:
            model, metrics = trainer.train(epochs=args.threat_epochs, batch_size=4)
            results['threat_detection_train'] = metrics
        test_metrics = trainer.test()
        results['threat_detection_test'] = test_metrics

    # Print final summary
    print_banner("FINAL TRAINING SUMMARY", "=")

    if 'object_detection_train' in results and results['object_detection_train']:
        print("\n[Object Detection Model]")
        print(f"  mAP50: {results['object_detection_train'].get('mAP50', 'N/A')}")
        print(f"  F1 Score: {results['object_detection_train'].get('F1 Score', 'N/A')}")
        print(f"  Precision: {results['object_detection_train'].get('Precision', 'N/A')}")
        print(f"  Recall: {results['object_detection_train'].get('Recall', 'N/A')}")

    if 'threat_detection_train' in results and results['threat_detection_train']:
        print("\n[Threat Detection Model]")
        print(f"  Accuracy: {results['threat_detection_train'].get('accuracy', 'N/A')}")
        print(f"  F1 Score: {results['threat_detection_train'].get('f1_score', 'N/A')}")
        print(f"  Precision: {results['threat_detection_train'].get('precision', 'N/A')}")
        print(f"  Recall: {results['threat_detection_train'].get('recall', 'N/A')}")

    # Save results to file
    results_file = Path('models/training_results.json')
    results_file.parent.mkdir(parents=True, exist_ok=True)

    # Convert numpy types to Python types for JSON serialization
    def convert_to_serializable(obj):
        if isinstance(obj, np.floating):
            return float(obj)
        elif isinstance(obj, np.integer):
            return int(obj)
        elif isinstance(obj, dict):
            return {k: convert_to_serializable(v) for k, v in obj.items()}
        return obj

    serializable_results = convert_to_serializable(results)
    with open(results_file, 'w') as f:
        json.dump(serializable_results, f, indent=2)

    print(f"\nResults saved to: {results_file}")
    print("\n" + "=" * 70)
    print(" TRAINING COMPLETE!")
    print("=" * 70)


if __name__ == "__main__":
    main()

