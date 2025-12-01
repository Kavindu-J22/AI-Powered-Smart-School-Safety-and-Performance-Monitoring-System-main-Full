"""
Model Trainer Module
Handles training and evaluation of threat detection models
Using PyTorch backend
"""
import numpy as np
import torch
from typing import Dict, List
from sklearn.metrics import classification_report, confusion_matrix, accuracy_score
import matplotlib
matplotlib.use('Agg')  # Use non-interactive backend
import matplotlib.pyplot as plt
import os
import sys

sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import ModelConfig, MODELS_DIR, LOGS_DIR
from models.non_speech_model import NonSpeechThreatModel


class ModelTrainer:
    """Train and evaluate threat detection models"""

    def __init__(self):
        self.model = NonSpeechThreatModel()
        self.history = None
        self.evaluation_results = None

    def train(self, X_train: np.ndarray, y_train: np.ndarray,
              X_val: np.ndarray = None, y_val: np.ndarray = None,
              epochs: int = None, batch_size: int = None) -> Dict:
        """Train the non-speech threat detection model"""
        print("\n" + "=" * 60)
        print("Starting Model Training (PyTorch Backend)")
        print("=" * 60)

        # Build model
        self.model.build_model()
        print("\nModel Architecture:")
        print(self.model.get_model_summary())

        # Train
        print("\nTraining...")
        self.history = self.model.train(
            X_train, y_train,
            X_val, y_val,
            epochs=epochs or ModelConfig.EPOCHS,
            batch_size=batch_size or ModelConfig.BATCH_SIZE
        )

        # Save model
        self.model.save_model()
        print(f"\nModel saved to: {self.model.model_path}")

        return self.history

    def evaluate(self, X_test: np.ndarray, y_test: np.ndarray,
                 classes: List[str]) -> Dict:
        """Evaluate model performance"""
        print("\n" + "=" * 60)
        print("Model Evaluation")
        print("=" * 60)

        # Get predictions using PyTorch
        self.model.model.eval()
        with torch.no_grad():
            X_test_t = torch.FloatTensor(X_test).to(self.model.device)
            outputs = self.model.model(X_test_t)
            y_pred_proba = torch.softmax(outputs, dim=1).cpu().numpy()

        y_pred = np.argmax(y_pred_proba, axis=1)
        y_true = np.argmax(y_test, axis=1)

        # Calculate metrics
        accuracy = accuracy_score(y_true, y_pred)
        report = classification_report(y_true, y_pred, target_names=classes, output_dict=True)
        conf_matrix = confusion_matrix(y_true, y_pred)

        print(f"\nTest Accuracy: {accuracy * 100:.2f}%")
        print("\nClassification Report:")
        print(classification_report(y_true, y_pred, target_names=classes))

        self.evaluation_results = {
            'accuracy': accuracy,
            'classification_report': report,
            'confusion_matrix': conf_matrix.tolist(),
            'classes': classes
        }

        return self.evaluation_results

    def plot_training_history(self, save_path: str = None) -> None:
        """Plot training history"""
        if self.history is None:
            print("No training history available")
            return

        fig, axes = plt.subplots(1, 2, figsize=(14, 5))

        # Accuracy plot
        axes[0].plot(self.history['accuracy'], label='Training')
        if 'val_accuracy' in self.history and self.history['val_accuracy']:
            axes[0].plot(self.history['val_accuracy'], label='Validation')
        axes[0].set_title('Model Accuracy')
        axes[0].set_xlabel('Epoch')
        axes[0].set_ylabel('Accuracy')
        axes[0].legend()
        axes[0].grid(True)

        # Loss plot
        axes[1].plot(self.history['loss'], label='Training')
        if 'val_loss' in self.history and self.history['val_loss']:
            axes[1].plot(self.history['val_loss'], label='Validation')
        axes[1].set_title('Model Loss')
        axes[1].set_xlabel('Epoch')
        axes[1].set_ylabel('Loss')
        axes[1].legend()
        axes[1].grid(True)

        plt.tight_layout()

        if save_path:
            plt.savefig(save_path, dpi=150)
            print(f"Training history plot saved to: {save_path}")
        plt.close()

    def plot_confusion_matrix(self, save_path: str = None) -> None:
        """Plot confusion matrix"""
        if self.evaluation_results is None:
            print("No evaluation results available")
            return

        conf_matrix = np.array(self.evaluation_results['confusion_matrix'])
        classes = self.evaluation_results['classes']

        plt.figure(figsize=(10, 8))
        # Manual heatmap without seaborn
        plt.imshow(conf_matrix, interpolation='nearest', cmap='Blues')
        plt.colorbar()
        tick_marks = np.arange(len(classes))
        plt.xticks(tick_marks, classes, rotation=45)
        plt.yticks(tick_marks, classes)

        # Add text annotations
        thresh = conf_matrix.max() / 2.
        for i in range(conf_matrix.shape[0]):
            for j in range(conf_matrix.shape[1]):
                plt.text(j, i, format(conf_matrix[i, j], 'd'),
                        ha="center", va="center",
                        color="white" if conf_matrix[i, j] > thresh else "black")

        plt.title('Confusion Matrix')
        plt.ylabel('True Label')
        plt.xlabel('Predicted Label')
        plt.tight_layout()

        if save_path:
            plt.savefig(save_path, dpi=150)
            print(f"Confusion matrix plot saved to: {save_path}")
        plt.close()

    def get_summary(self) -> Dict:
        """Get training summary"""
        summary = {
            'model_path': str(self.model.model_path),
            'classes': self.model.classes,
            'num_classes': self.model.num_classes
        }

        if self.history:
            summary['final_training_accuracy'] = self.history['accuracy'][-1]
            summary['final_training_loss'] = self.history['loss'][-1]
            if 'val_accuracy' in self.history and self.history['val_accuracy']:
                summary['final_validation_accuracy'] = self.history['val_accuracy'][-1]

        if self.evaluation_results:
            summary['test_accuracy'] = self.evaluation_results['accuracy']
            summary['per_class_metrics'] = self.evaluation_results['classification_report']

        return summary

