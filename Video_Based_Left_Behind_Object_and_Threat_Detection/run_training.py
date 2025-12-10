"""
Main Training Pipeline Script
Prepares datasets and trains both Object Detection and Threat Detection models
"""

import os
import sys
import argparse
from pathlib import Path
from datetime import datetime

# Add scripts directory to path
sys.path.insert(0, str(Path(__file__).parent / "scripts"))


def main():
    parser = argparse.ArgumentParser(description='Run the complete training pipeline')
    parser.add_argument('--skip-prepare', action='store_true',
                       help='Skip dataset preparation (use if already prepared)')
    parser.add_argument('--object-only', action='store_true',
                       help='Train only object detection model')
    parser.add_argument('--threat-only', action='store_true',
                       help='Train only threat detection model')
    parser.add_argument('--object-epochs', type=int, default=50,
                       help='Number of epochs for object detection training')
    parser.add_argument('--threat-epochs', type=int, default=30,
                       help='Number of epochs for threat detection training')
    parser.add_argument('--batch-size', type=int, default=16,
                       help='Batch size for training')
    parser.add_argument('--test-only', action='store_true',
                       help='Only run testing, skip training')
    
    args = parser.parse_args()
    
    print("=" * 70)
    print(" VIDEO-BASED LEFT BEHIND OBJECT AND THREAT DETECTION SYSTEM")
    print(" Training Pipeline")
    print("=" * 70)
    print(f"\nStarted at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    
    # Change to project directory
    project_dir = Path(__file__).parent
    os.chdir(project_dir)
    print(f"Working directory: {project_dir}")
    
    # Step 1: Prepare datasets
    if not args.skip_prepare and not args.test_only:
        print("\n" + "=" * 70)
        print(" STEP 1: PREPARING DATASETS")
        print("=" * 70)
        
        from prepare_datasets import DatasetPreparer
        preparer = DatasetPreparer("datasets")
        
        if not args.threat_only:
            print("\nPreparing Object Detection dataset...")
            preparer.prepare_object_detection_dataset("datasets/yolo_format")
        
        if not args.object_only:
            print("\nPreparing Threat Detection dataset...")
            preparer.prepare_threat_detection_dataset("datasets/threat_frames", frames_per_video=16)
    else:
        print("\nSkipping dataset preparation...")
    
    # Step 2: Train models
    print("\n" + "=" * 70)
    print(" STEP 2: TRAINING MODELS")
    print("=" * 70)
    
    from train_models import ObjectDetectionTrainer, ThreatDetectionTrainer
    
    results = {}
    
    # Train Object Detection Model
    if not args.threat_only:
        print("\n--- Object Detection Model ---")
        data_yaml = Path("datasets/yolo_format/data.yaml")
        
        if data_yaml.exists():
            trainer = ObjectDetectionTrainer(
                str(data_yaml),
                "models/left_behind_detector.pt"
            )
            
            if not args.test_only:
                model, metrics = trainer.train(
                    epochs=args.object_epochs,
                    batch_size=args.batch_size
                )
                results['object_detection_train'] = metrics
            
            test_metrics = trainer.test()
            results['object_detection_test'] = test_metrics
        else:
            print(f"ERROR: Data config not found at {data_yaml}")
            print("Please run dataset preparation first.")
    
    # Train Threat Detection Model
    if not args.object_only:
        print("\n--- Threat Detection Model ---")
        threat_data = Path("datasets/threat_frames")
        
        if threat_data.exists():
            trainer = ThreatDetectionTrainer(
                str(threat_data),
                "models/threat_detector.pt"
            )
            
            if not args.test_only:
                model, metrics = trainer.train(
                    epochs=args.threat_epochs,
                    batch_size=4
                )
                results['threat_detection_train'] = metrics
            
            test_metrics = trainer.test()
            results['threat_detection_test'] = test_metrics
        else:
            print(f"ERROR: Threat data not found at {threat_data}")
            print("Please run dataset preparation first.")
    
    # Step 3: Run system tests
    print("\n" + "=" * 70)
    print(" STEP 3: RUNNING SYSTEM TESTS")
    print("=" * 70)
    
    from test_system import main as run_tests
    run_tests()
    
    # Final Summary
    print("\n" + "=" * 70)
    print(" TRAINING PIPELINE COMPLETE")
    print("=" * 70)
    print(f"\nCompleted at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    
    if results:
        print("\n--- Final Results Summary ---")
        for key, value in results.items():
            if value:
                print(f"\n{key}:")
                for metric, val in value.items():
                    if isinstance(val, float):
                        print(f"  {metric}: {val:.4f}")
                    else:
                        print(f"  {metric}: {val}")
    
    print("\n" + "=" * 70)
    print(" Models saved to: models/")
    print(" - left_behind_detector.pt (Object Detection)")
    print(" - threat_detector.pt (Threat Detection)")
    print("=" * 70)


if __name__ == "__main__":
    main()

