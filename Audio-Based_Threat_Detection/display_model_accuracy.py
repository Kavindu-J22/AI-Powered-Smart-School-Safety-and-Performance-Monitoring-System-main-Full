#!/usr/bin/env python3
"""
Display Audio Threat Detection Model Accuracy
Shows model performance metrics in a formatted terminal output
"""
import json
import os
from pathlib import Path

def display_accuracy():
    """Display model accuracy and metrics from training summary"""
    
    # Path to training summary
    logs_dir = Path(__file__).parent / "logs"
    summary_path = logs_dir / "training_summary.json"
    
    # Check if summary exists
    if not summary_path.exists():
        print("\n" + "=" * 70)
        print("❌ ERROR: Training summary not found!")
        print("=" * 70)
        print(f"\nExpected location: {summary_path}")
        print("\n💡 Please train the model first by running:")
        print("   python run_training.py")
        print("\n" + "=" * 70)
        return
    
    # Load training summary
    with open(summary_path, 'r') as f:
        summary = json.load(f)
    
    # Extract metrics
    test_accuracy = summary.get('test_accuracy', 0) * 100
    train_accuracy = summary.get('final_training_accuracy', 0) * 100
    val_accuracy = summary.get('final_validation_accuracy', 0) * 100
    classes = summary.get('classes', [])
    per_class = summary.get('per_class_metrics', {})
    
    # Display header
    print("\n" + "=" * 70)
    print("   🎯 AUDIO THREAT DETECTION MODEL - ACCURACY REPORT")
    print("=" * 70)
    
    # Overall accuracy
    print("\n📊 OVERALL MODEL ACCURACY")
    print("-" * 70)
    print(f"   Test Accuracy:       {test_accuracy:.2f}%  {'✅' if test_accuracy >= 90 else '⚠️'}")
    print(f"   Training Accuracy:   {train_accuracy:.2f}%")
    print(f"   Validation Accuracy: {val_accuracy:.2f}%")
    
    # Model info
    print("\n📁 MODEL INFORMATION")
    print("-" * 70)
    print(f"   Model Path: {summary.get('model_path', 'N/A')}")
    print(f"   Classes: {', '.join(classes)}")
    print(f"   Number of Classes: {summary.get('num_classes', len(classes))}")
    
    # Per-class performance
    print("\n📈 PER-CLASS PERFORMANCE")
    print("-" * 70)
    print(f"{'Class':<20} {'Precision':<12} {'Recall':<12} {'F1-Score':<12} {'Support':<10}")
    print("-" * 70)
    
    for cls in classes:
        if cls in per_class:
            metrics = per_class[cls]
            precision = metrics.get('precision', 0) * 100
            recall = metrics.get('recall', 0) * 100
            f1 = metrics.get('f1-score', 0) * 100
            support = int(metrics.get('support', 0))
            
            # Color coding based on performance
            if f1 >= 95:
                status = "🟢"
            elif f1 >= 85:
                status = "🟡"
            else:
                status = "🔴"
            
            print(f"{cls:<20} {precision:>6.2f}%  {status}   {recall:>6.2f}%  {status}   {f1:>6.2f}%  {status}   {support:<10}")
    
    # Weighted average
    if 'weighted avg' in per_class:
        print("-" * 70)
        weighted = per_class['weighted avg']
        w_precision = weighted.get('precision', 0) * 100
        w_recall = weighted.get('recall', 0) * 100
        w_f1 = weighted.get('f1-score', 0) * 100
        w_support = int(weighted.get('support', 0))
        print(f"{'Weighted Average':<20} {w_precision:>6.2f}%      {w_recall:>6.2f}%      {w_f1:>6.2f}%      {w_support:<10}")
    
    # Macro average
    if 'macro avg' in per_class:
        macro = per_class['macro avg']
        m_precision = macro.get('precision', 0) * 100
        m_recall = macro.get('recall', 0) * 100
        m_f1 = macro.get('f1-score', 0) * 100
        m_support = int(macro.get('support', 0))
        print(f"{'Macro Average':<20} {m_precision:>6.2f}%      {m_recall:>6.2f}%      {m_f1:>6.2f}%      {m_support:<10}")
    
    # Performance summary
    print("\n💡 PERFORMANCE SUMMARY")
    print("-" * 70)
    if test_accuracy >= 95:
        print("   ✅ Excellent performance! Model is highly accurate.")
    elif test_accuracy >= 90:
        print("   ✅ Good performance! Model is reliable.")
    elif test_accuracy >= 80:
        print("   ⚠️  Moderate performance. Consider retraining with more data.")
    else:
        print("   ❌ Low performance. Model needs improvement.")
    
    # Check for visualizations
    print("\n📊 VISUALIZATIONS")
    print("-" * 70)
    
    history_plot = logs_dir / "training_history.png"
    confusion_plot = logs_dir / "confusion_matrix.png"
    
    if history_plot.exists():
        print(f"   ✅ Training History Plot: {history_plot}")
    else:
        print(f"   ❌ Training History Plot: Not found")
    
    if confusion_plot.exists():
        print(f"   ✅ Confusion Matrix Plot: {confusion_plot}")
    else:
        print(f"   ❌ Confusion Matrix Plot: Not found")
    
    print("\n" + "=" * 70)
    print()


if __name__ == '__main__':
    display_accuracy()

