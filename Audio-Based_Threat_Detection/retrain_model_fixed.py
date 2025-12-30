"""
Retrain the model with fixes for overfitting
- Class weighting for imbalanced data
- Label smoothing to prevent overconfidence
- Better regularization
"""
import sys
import os
import numpy as np

sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from training.data_loader import AudioDataLoader
from training.trainer import ModelTrainer
from config import ModelConfig

def print_banner():
    print("\n" + "="*70)
    print("  AUDIO THREAT DETECTION - MODEL RETRAINING (FIXED)")
    print("  Fixes: Class Weighting + Label Smoothing + Regularization")
    print("="*70 + "\n")

def main():
    print_banner()
    
    # Initialize
    print("1️⃣  Initializing...")
    data_loader = AudioDataLoader()
    trainer = ModelTrainer()
    
    # Load dataset
    print("\n2️⃣  Loading and preparing dataset...")
    try:
        dataset = data_loader.prepare_dataset(
            test_size=0.2,
            max_samples=None  # Use all samples
        )
    except Exception as e:
        print(f"❌ Error loading dataset: {e}")
        print("\nPlease ensure the dataset is in the correct location:")
        print("  Audio-Based_Threat_Detection/Non Speech Dataset/")
        return
    
    X_train = dataset['X_train']
    X_test = dataset['X_test']
    y_train = dataset['y_train']
    y_test = dataset['y_test']
    classes = dataset['classes']
    
    print(f"\n✓ Dataset loaded:")
    print(f"  Training samples: {len(X_train)}")
    print(f"  Test samples: {len(X_test)}")
    print(f"  Classes: {classes}")
    print(f"  Feature shape: {X_train.shape}")
    
    # Train model with fixed parameters
    print("\n3️⃣  Training model with fixes...")
    print("  - Class weighting for imbalanced data")
    print("  - Label smoothing (0.1) to prevent overconfidence")
    print("  - Weight decay (1e-4) for regularization")
    
    history = trainer.train(
        X_train, y_train,
        X_val=X_test, y_val=y_test,
        epochs=ModelConfig.EPOCHS,
        batch_size=ModelConfig.BATCH_SIZE
    )
    
    # Evaluate
    print("\n4️⃣  Evaluating model...")
    results = trainer.evaluate(X_test, y_test, classes)
    
    # Plot results
    print("\n5️⃣  Generating visualizations...")
    trainer.plot_training_history()
    trainer.plot_confusion_matrix()
    trainer.save_results()
    
    print("\n" + "="*70)
    print("✅ TRAINING COMPLETE!")
    print("="*70)
    print(f"\nModel saved to: {trainer.model.model_path}")
    print(f"Test Accuracy: {results['accuracy']*100:.2f}%")
    print("\nPer-class performance:")
    for cls in classes:
        if cls in results['classification_report']:
            metrics = results['classification_report'][cls]
            print(f"  {cls:20s}: Precision={metrics['precision']:.3f}, Recall={metrics['recall']:.3f}, F1={metrics['f1-score']:.3f}")
    
    print("\n📊 Visualizations saved to: logs/")
    print("  - training_history.png")
    print("  - confusion_matrix.png")
    print("  - training_summary.json")
    
    print("\n🚀 Next steps:")
    print("  1. Review the confusion matrix to check for class imbalance issues")
    print("  2. If accuracy is still low, consider:")
    print("     - Collecting more training data")
    print("     - Adjusting class thresholds in threat_detector.py")
    print("     - Using data augmentation")
    print("  3. Restart the API server: python app.py")
    print("\n")

if __name__ == '__main__':
    main()

