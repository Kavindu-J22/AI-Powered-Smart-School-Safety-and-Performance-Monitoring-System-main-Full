#!/usr/bin/env python3
"""
Audio-Based Threat Detection - Training Script
Trains and evaluates the non-speech threat detection model
"""
import os
import sys
import json
from pathlib import Path

# Add project root to path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from config import MODELS_DIR, LOGS_DIR, ModelConfig
from training.data_loader import AudioDataLoader
from training.trainer import ModelTrainer


def print_banner():
    """Print welcome banner"""
    print("\n" + "=" * 70)
    print("   AUDIO-BASED THREAT DETECTION SYSTEM - TRAINING")
    print("   Smart School Safety Monitoring")
    print("=" * 70)


def main():
    """Main training pipeline"""
    print_banner()
    
    # Initialize
    print("\n1️⃣  Initializing...")
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
        print(f"Error loading dataset: {e}")
        print("\nPlease ensure the dataset is in the correct location:")
        print("  Audio-Based_Threat_Detection/Non Speech Dataset/")
        return
    
    X_train = dataset['X_train']
    X_test = dataset['X_test']
    y_train = dataset['y_train']
    y_test = dataset['y_test']
    classes = dataset['classes']
    
    # Train model
    print("\n3️⃣  Training model...")
    history = trainer.train(
        X_train, y_train,
        X_val=X_test, y_val=y_test,
        epochs=ModelConfig.EPOCHS,
        batch_size=ModelConfig.BATCH_SIZE
    )
    
    # Evaluate model
    print("\n4️⃣  Evaluating model...")
    results = trainer.evaluate(X_test, y_test, classes)
    
    # Save plots
    print("\n5️⃣  Saving training plots...")
    os.makedirs(LOGS_DIR, exist_ok=True)
    trainer.plot_training_history(save_path=str(LOGS_DIR / "training_history.png"))
    trainer.plot_confusion_matrix(save_path=str(LOGS_DIR / "confusion_matrix.png"))
    
    # Save training summary
    summary = trainer.get_summary()
    summary_path = LOGS_DIR / "training_summary.json"
    with open(summary_path, 'w') as f:
        json.dump(summary, f, indent=2, default=str)
    print(f"Training summary saved to: {summary_path}")
    
    # Print final summary
    print("\n" + "=" * 70)
    print("   TRAINING COMPLETE")
    print("=" * 70)
    print(f"\n✅ Model saved to: {trainer.model.model_path}")
    print(f"✅ Test Accuracy: {results['accuracy'] * 100:.2f}%")
    print(f"✅ Classes: {classes}")
    
    print("\n📊 Per-class Performance:")
    for cls in classes:
        if cls in results['classification_report']:
            metrics = results['classification_report'][cls]
            print(f"   {cls}:")
            print(f"      Precision: {metrics['precision']:.3f}")
            print(f"      Recall: {metrics['recall']:.3f}")
            print(f"      F1-Score: {metrics['f1-score']:.3f}")
    
    print("\n" + "=" * 70)
    print("Next Steps:")
    print("  1. Start the Flask API: python app.py")
    print("  2. Start Laravel server: php artisan serve")
    print("  3. Access Audio Threat Detection in admin panel")
    print("=" * 70 + "\n")


def demo():
    """Quick demo with sample audio"""
    print_banner()
    print("\n🎯 Running Demo Mode...")
    
    from models.threat_detector import ThreatDetector
    
    detector = ThreatDetector()
    data_loader = AudioDataLoader()
    
    # Get a sample
    sample, label = data_loader.get_sample_for_demo()
    
    if sample is None:
        print("No samples found for demo")
        return
    
    print(f"\n📝 Testing with sample labeled: {label}")
    
    # Run detection
    result = detector.analyze_audio(sample, enable_speech=False)
    
    print(f"\n📊 Detection Results:")
    print(f"   Is Threat: {result['is_threat']}")
    print(f"   Threat Level: {result['threat_level']}")
    print(f"   Confidence: {result['confidence']:.3f}")
    print(f"   Processing Time: {result['processing_time']}s")
    
    if result['non_speech_result']:
        print(f"\n   Non-Speech Analysis:")
        print(f"      Detected Class: {result['non_speech_result']['detected_class']}")
        print(f"      Probabilities: {result['non_speech_result']['all_probabilities']}")


if __name__ == "__main__":
    import argparse
    
    parser = argparse.ArgumentParser(description="Audio Threat Detection Training")
    parser.add_argument('--demo', action='store_true', help='Run demo mode')
    args = parser.parse_args()
    
    if args.demo:
        demo()
    else:
        main()

