"""
Dataset Preparation Script
Converts CSV annotations to YOLO format and prepares video data for threat detection
"""

import os
import csv
import shutil
import random
from pathlib import Path
from typing import Dict, List, Tuple
import cv2
import numpy as np
from tqdm import tqdm
import yaml


class DatasetPreparer:
    """Prepares datasets for training object detection and threat detection models"""
    
    def __init__(self, base_path: str = "datasets"):
        self.base_path = Path(base_path)
        self.object_detection_path = self.base_path / "Left-Behind_Object_Detection_Dataset"
        self.threat_detection_path = self.base_path / "Threat_Detection_Dataset"
        
    def prepare_object_detection_dataset(self, output_path: str = "datasets/yolo_format"):
        """
        Convert CSV annotations to YOLO format and create dataset structure
        """
        output_path = Path(output_path)
        output_path.mkdir(parents=True, exist_ok=True)
        
        # Create YOLO directory structure
        for split in ['train', 'valid', 'test']:
            (output_path / split / 'images').mkdir(parents=True, exist_ok=True)
            (output_path / split / 'labels').mkdir(parents=True, exist_ok=True)
        
        # Collect all dataset folders
        dataset_folders = [
            "Backpacks_dataset",
            "Laptops_dataset",
            "PEN_Dataset1",
            "Pen_dataset2",
            "Sports_Equipment_dataset",
            "Water_Bottle_dataset1",
            "water_bottle_dataset2",
            "umbrella_dataset"
        ]
        
        # Class mapping
        class_mapping = {}
        class_counter = 0
        
        print("Processing Object Detection datasets...")
        
        for dataset_folder in dataset_folders:
            dataset_path = self.object_detection_path / dataset_folder
            if not dataset_path.exists():
                print(f"Warning: {dataset_folder} not found, skipping...")
                continue
                
            print(f"\nProcessing {dataset_folder}...")
            
            for split in ['train', 'valid', 'test']:
                split_path = dataset_path / split
                if not split_path.exists():
                    continue
                    
                # Read annotations CSV
                csv_path = split_path / '_annotations.csv'
                if not csv_path.exists():
                    print(f"  Warning: No annotations found for {split}")
                    continue
                
                # Process annotations
                annotations = self._read_csv_annotations(csv_path)
                
                for filename, boxes in tqdm(annotations.items(), desc=f"  {split}"):
                    # Copy image
                    src_img = split_path / filename
                    if not src_img.exists():
                        continue
                        
                    dst_img = output_path / split / 'images' / filename
                    if not dst_img.exists():
                        shutil.copy(src_img, dst_img)
                    
                    # Create YOLO label file
                    label_filename = filename.rsplit('.', 1)[0] + '.txt'
                    label_path = output_path / split / 'labels' / label_filename
                    
                    with open(label_path, 'a') as f:
                        for box in boxes:
                            class_name = box['class']
                            if class_name not in class_mapping:
                                class_mapping[class_name] = class_counter
                                class_counter += 1
                            
                            class_id = class_mapping[class_name]
                            # Convert to YOLO format (center_x, center_y, width, height)
                            yolo_box = self._convert_to_yolo(
                                box['xmin'], box['ymin'], 
                                box['xmax'], box['ymax'],
                                box['width'], box['height']
                            )
                            f.write(f"{class_id} {yolo_box}\n")
        
        # Create data.yaml
        self._create_yolo_yaml(output_path, class_mapping)
        print(f"\nDataset prepared at: {output_path}")
        print(f"Classes: {class_mapping}")
        return output_path, class_mapping
    
    def _read_csv_annotations(self, csv_path: Path) -> Dict[str, List]:
        """Read CSV annotations file"""
        annotations = {}
        with open(csv_path, 'r') as f:
            reader = csv.DictReader(f)
            for row in reader:
                filename = row['filename']
                if filename not in annotations:
                    annotations[filename] = []
                annotations[filename].append({
                    'class': row['class'],
                    'xmin': int(row['xmin']),
                    'ymin': int(row['ymin']),
                    'xmax': int(row['xmax']),
                    'ymax': int(row['ymax']),
                    'width': int(row['width']),
                    'height': int(row['height'])
                })
        return annotations
    
    def _convert_to_yolo(self, xmin, ymin, xmax, ymax, img_w, img_h) -> str:
        """Convert bounding box to YOLO format"""
        x_center = ((xmin + xmax) / 2) / img_w
        y_center = ((ymin + ymax) / 2) / img_h
        width = (xmax - xmin) / img_w
        height = (ymax - ymin) / img_h
        return f"{x_center:.6f} {y_center:.6f} {width:.6f} {height:.6f}"
    
    def _create_yolo_yaml(self, output_path: Path, class_mapping: Dict):
        """Create YOLO data.yaml file"""
        yaml_content = {
            'path': str(output_path.absolute()),
            'train': 'train/images',
            'val': 'valid/images',
            'test': 'test/images',
            'nc': len(class_mapping),
            'names': {v: k for k, v in class_mapping.items()}
        }
        
        yaml_path = output_path / 'data.yaml'
        with open(yaml_path, 'w') as f:
            yaml.dump(yaml_content, f, default_flow_style=False)
        print(f"Created: {yaml_path}")

    def prepare_threat_detection_dataset(self, output_path: str = "datasets/threat_frames",
                                         frames_per_video: int = 16, frame_size: Tuple[int, int] = (224, 224)):
        """
        Extract frames from fight/no-fight videos for threat detection training
        """
        output_path = Path(output_path)
        output_path.mkdir(parents=True, exist_ok=True)

        # Create directory structure
        for split in ['train', 'valid', 'test']:
            for label in ['fight', 'no_fight']:
                (output_path / split / label).mkdir(parents=True, exist_ok=True)

        # Process fight videos
        fight_folders = ['fight-Dataset_1', 'fight-Dataset_2']
        no_fight_folders = ['noFight-Dataset_1', 'noFight-Dataset_2']

        print("\nProcessing Threat Detection datasets...")

        # Collect all videos
        fight_videos = []
        no_fight_videos = []

        for folder in fight_folders:
            folder_path = self.threat_detection_path / folder
            if folder_path.exists():
                fight_videos.extend(list(folder_path.glob('*.mp4')))

        for folder in no_fight_folders:
            folder_path = self.threat_detection_path / folder
            if folder_path.exists():
                no_fight_videos.extend(list(folder_path.glob('*.mp4')))

        print(f"Found {len(fight_videos)} fight videos and {len(no_fight_videos)} no-fight videos")

        # Split data (70% train, 15% valid, 15% test)
        random.shuffle(fight_videos)
        random.shuffle(no_fight_videos)

        def split_data(videos):
            n = len(videos)
            train_end = int(0.7 * n)
            valid_end = int(0.85 * n)
            return {
                'train': videos[:train_end],
                'valid': videos[train_end:valid_end],
                'test': videos[valid_end:]
            }

        fight_splits = split_data(fight_videos)
        no_fight_splits = split_data(no_fight_videos)

        # Process videos
        for split in ['train', 'valid', 'test']:
            print(f"\nProcessing {split} split...")

            # Fight videos
            for video_path in tqdm(fight_splits[split], desc="  Fight videos"):
                self._extract_frames(video_path, output_path / split / 'fight',
                                   frames_per_video, frame_size)

            # No-fight videos
            for video_path in tqdm(no_fight_splits[split], desc="  No-fight videos"):
                self._extract_frames(video_path, output_path / split / 'no_fight',
                                   frames_per_video, frame_size)

        # Create metadata file
        metadata = {
            'classes': ['fight', 'no_fight'],
            'frames_per_video': frames_per_video,
            'frame_size': frame_size,
            'splits': {
                'train': {'fight': len(fight_splits['train']), 'no_fight': len(no_fight_splits['train'])},
                'valid': {'fight': len(fight_splits['valid']), 'no_fight': len(no_fight_splits['valid'])},
                'test': {'fight': len(fight_splits['test']), 'no_fight': len(no_fight_splits['test'])}
            }
        }

        with open(output_path / 'metadata.yaml', 'w') as f:
            yaml.dump(metadata, f, default_flow_style=False)

        print(f"\nThreat detection dataset prepared at: {output_path}")
        return output_path, metadata

    def _extract_frames(self, video_path: Path, output_dir: Path,
                       num_frames: int, frame_size: Tuple[int, int]):
        """Extract frames from a video"""
        cap = cv2.VideoCapture(str(video_path))
        total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))

        if total_frames < num_frames:
            frame_indices = list(range(total_frames))
        else:
            frame_indices = np.linspace(0, total_frames - 1, num_frames, dtype=int)

        video_name = video_path.stem
        frames_dir = output_dir / video_name
        frames_dir.mkdir(parents=True, exist_ok=True)

        for i, frame_idx in enumerate(frame_indices):
            cap.set(cv2.CAP_PROP_POS_FRAMES, frame_idx)
            ret, frame = cap.read()
            if ret:
                frame = cv2.resize(frame, frame_size)
                cv2.imwrite(str(frames_dir / f"frame_{i:04d}.jpg"), frame)

        cap.release()


def main():
    """Main function to prepare all datasets"""
    import argparse

    parser = argparse.ArgumentParser(description='Prepare datasets for training')
    parser.add_argument('--base-path', type=str, default='datasets',
                       help='Base path to datasets folder')
    parser.add_argument('--object-output', type=str, default='datasets/yolo_format',
                       help='Output path for YOLO format dataset')
    parser.add_argument('--threat-output', type=str, default='datasets/threat_frames',
                       help='Output path for threat detection frames')
    parser.add_argument('--frames-per-video', type=int, default=16,
                       help='Number of frames to extract per video')
    parser.add_argument('--only-object', action='store_true',
                       help='Only prepare object detection dataset')
    parser.add_argument('--only-threat', action='store_true',
                       help='Only prepare threat detection dataset')

    args = parser.parse_args()

    preparer = DatasetPreparer(args.base_path)

    if not args.only_threat:
        print("=" * 60)
        print("PREPARING OBJECT DETECTION DATASET")
        print("=" * 60)
        preparer.prepare_object_detection_dataset(args.object_output)

    if not args.only_object:
        print("\n" + "=" * 60)
        print("PREPARING THREAT DETECTION DATASET")
        print("=" * 60)
        preparer.prepare_threat_detection_dataset(args.threat_output, args.frames_per_video)

    print("\n" + "=" * 60)
    print("DATASET PREPARATION COMPLETE!")
    print("=" * 60)


if __name__ == "__main__":
    main()

