# Dataset Preparation Guide
## Video-Based Left Behind Object and Threat Detection System

This comprehensive guide will help you collect, prepare, and organize datasets for both components of the system.

---

## Table of Contents
1. [Left-Behind Object Detection Dataset](#1-left-behind-object-detection-dataset)
2. [Threat Detection Dataset](#2-threat-detection-dataset)
3. [Data Collection Tools](#3-data-collection-tools)
4. [Data Annotation](#4-data-annotation)
5. [Dataset Organization](#5-dataset-organization)
6. [Pre-trained Models and Transfer Learning](#6-pre-trained-models-and-transfer-learning)

---

## 1. Left-Behind Object Detection Dataset

### 1.1 Required Data

**Target Objects:**
- Backpacks/School bags
- Books and notebooks
- Water bottles
- Lunch boxes
- Umbrellas
- Jackets/Clothing
- Laptops/Tablets
- Sports equipment

**Dataset Size Recommendations:**
- **Minimum:** 500 images per class
- **Recommended:** 2,000-5,000 images per class
- **Optimal:** 10,000+ images per class

### 1.2 Data Collection Methods

#### Method 1: Record Your Own School Environment (RECOMMENDED)
```
Advantages:
✓ Domain-specific (matches your actual deployment environment)
✓ Correct lighting, angles, and classroom setup
✓ Real-world variations

Steps:
1. Set up cameras in actual classrooms
2. Record during different times of day (morning, afternoon, evening)
3. Capture different lighting conditions (sunny, cloudy, artificial light)
4. Include various object placements (desks, floor, chairs, shelves)
5. Record empty classrooms and classrooms with objects
6. Duration: 2-4 weeks of recording (2-3 hours per day)
```

#### Method 2: Use Existing Datasets + Fine-tuning
```
Base Datasets:
- COCO Dataset (Common Objects in Context)
  URL: https://cocodataset.org/
  Classes: backpack, handbag, suitcase, book, bottle, umbrella
  
- Open Images Dataset V7
  URL: https://storage.googleapis.com/openimages/web/index.html
  Search for: bag, backpack, book, bottle, clothing
  
- Roboflow Universe (School Object Detection)
  URL: https://universe.roboflow.com/
  Search: "school objects", "classroom objects"
```

#### Method 3: Synthetic Data Generation
```
Tools:
- Unity Perception Package
- NVIDIA Omniverse Replicator
- Blender with Python scripting

Benefits:
- Generate thousands of images quickly
- Control all variables (lighting, angles, backgrounds)
- Automatic annotation
```

### 1.3 Data Collection Checklist

**Environmental Variations:**
- [ ] Morning light (7 AM - 10 AM)
- [ ] Afternoon light (12 PM - 3 PM)
- [ ] Evening light (4 PM - 6 PM)
- [ ] Artificial lighting only
- [ ] Mixed natural and artificial light
- [ ] Different weather conditions (if windows present)

**Camera Angles:**
- [ ] Top-down view (ceiling mounted)
- [ ] 45-degree angle
- [ ] Side view
- [ ] Corner view

**Object Variations:**
- [ ] Different colors (red, blue, black, pink backpacks)
- [ ] Different sizes (small, medium, large)
- [ ] Different brands and styles
- [ ] Worn vs. new condition
- [ ] Open vs. closed bags

**Placement Scenarios:**
- [ ] On desks
- [ ] Under desks
- [ ] On chairs
- [ ] On floor
- [ ] Against walls
- [ ] In corners
- [ ] Partially occluded
- [ ] Fully visible

**Background Variations:**
- [ ] Empty classroom
- [ ] Classroom with students
- [ ] Classroom after students leave
- [ ] Different classroom layouts
- [ ] Different furniture arrangements

---

## 2. Threat Detection Dataset

### 2.1 Required Data

**Target Activities:**
- Physical fighting (punching, kicking)
- Pushing and shoving
- Aggressive gestures
- Weapon presence (sticks, knives - for detection only)
- Normal activities (for negative samples)

**Dataset Size Recommendations:**
- **Minimum:** 1,000 video clips per class (5-10 seconds each)
- **Recommended:** 5,000-10,000 clips per class
- **Optimal:** 20,000+ clips per class

### 2.2 Data Collection Methods

#### Method 1: Use Existing Action Recognition Datasets (RECOMMENDED FOR SAFETY)

**⚠️ IMPORTANT: DO NOT stage real fights or dangerous activities!**

```
Recommended Datasets:

1. UCF-Crime Dataset
   - URL: https://www.crcv.ucf.edu/projects/real-world/
   - Contains: Fighting, assault, abuse
   - Size: 1,900 videos
   
2. Violent Flows Dataset
   - URL: http://www.openu.ac.il/home/hassner/data/violentflows/
   - Contains: Violent/non-violent crowd behavior
   
3. Hockey Fight Detection Dataset
   - URL: https://www.kaggle.com/datasets/yassershrief/hockey-fight-detection
   - Contains: Fight vs. non-fight sequences
   
4. RLVS (Real Life Violence Situations) Dataset
   - URL: https://github.com/seominseok0429/Real-Life-Violence-Situations-Dataset
   - Contains: Real-world violence detection
   
5. RWF-2000 (Real World Fighting)
   - URL: https://github.com/mchengny/RWF2000-Video-Database-for-Violence-Detection
   - Contains: 2,000 videos of fights and normal activities

6. Kinetics-400/700 Dataset (for normal activities)
   - URL: https://deepmind.com/research/open-source/kinetics
   - Use for negative samples: walking, sitting, talking, playing
```

#### Method 2: Simulated Training Data (Controlled Environment)

```
Safe Simulation Approach:
1. Work with drama/theater students
2. Choreograph "stage fighting" (fake fighting)
3. Record in controlled environment
4. Include safety supervisors
5. Get proper permissions and consent forms

Activities to Record:
- Staged pushing (light contact)
- Simulated arguments (aggressive postures)
- Theatrical fight choreography
- Normal student interactions (control group)

⚠️ Safety Requirements:
- Adult supervision required
- Signed consent forms from participants
- No actual contact or harm
- Professional stunt coordinator (if possible)
```

#### Method 3: CCTV Footage Analysis (If Available)

```
If your school has existing CCTV footage:
1. Review historical footage with permission
2. Identify and extract relevant incidents
3. Ensure privacy compliance (blur faces if needed)
4. Get administrative approval
5. Follow data protection regulations (GDPR, etc.)
```

### 2.3 Data Collection Checklist

**Threat Scenarios:**
- [ ] Two-person physical altercation
- [ ] Group conflicts
- [ ] Pushing incidents
- [ ] Aggressive gesturing
- [ ] Object throwing
- [ ] Chasing behavior

**Normal Activities (Negative Samples):**
- [ ] Students walking
- [ ] Students sitting and studying
- [ ] Students talking (calm)
- [ ] Students playing (non-aggressive)
- [ ] Students entering/exiting classroom
- [ ] Teacher-student interactions
- [ ] Group discussions
- [ ] Sports activities (controlled)

**Environmental Variations:**
- [ ] Different locations (classroom, hallway, playground)
- [ ] Different times of day
- [ ] Different crowd densities
- [ ] Different camera angles
- [ ] Different lighting conditions

**Video Quality Variations:**
- [ ] High resolution (1080p)
- [ ] Medium resolution (720p)
- [ ] Low resolution (480p) - simulating ESP32-CAM
- [ ] Different frame rates (15fps, 30fps)

---

## 3. Data Collection Tools

### 3.1 Video Recording Tools

```bash
# Using OpenCV to record from webcam/IP camera
python scripts/data_collection/record_video.py --camera 0 --duration 3600 --output data/raw/

# Using ESP32-CAM (see ESP32 section for details)
# Stream to computer and record
python scripts/data_collection/record_esp32cam.py --ip 192.168.1.100 --output data/raw/
```

### 3.2 Frame Extraction

```bash
# Extract frames from video
python scripts/data_collection/extract_frames.py \
    --video data/raw/classroom_video.mp4 \
    --output data/frames/ \
    --fps 2  # Extract 2 frames per second
```

### 3.3 Data Augmentation

```python
# Augmentation techniques to expand your dataset:
- Horizontal flipping
- Rotation (-15° to +15°)
- Brightness adjustment (±20%)
- Contrast adjustment (±20%)
- Gaussian noise
- Motion blur
- Zoom (90%-110%)
- Color jittering
```

---

## 4. Data Annotation

### 4.1 Object Detection Annotation (Left-Behind Objects)

**Recommended Tools:**

1. **LabelImg** (Free, Easy to use)
   ```bash
   pip install labelImg
   labelImg
   ```
   - Format: YOLO, Pascal VOC, COCO
   - Best for: Beginners
   - URL: https://github.com/heartexlabs/labelImg

2. **CVAT (Computer Vision Annotation Tool)** (Free, Professional)
   ```bash
   # Web-based, supports team collaboration
   # URL: https://www.cvat.ai/
   ```
   - Format: YOLO, COCO, Pascal VOC, TFRecord
   - Best for: Team projects
   - Features: Auto-annotation, tracking

3. **Roboflow** (Free tier available, Cloud-based)
   - URL: https://roboflow.com/
   - Features: Auto-annotation, augmentation, export to multiple formats
   - Best for: Quick prototyping

4. **Label Studio** (Free, Open-source)
   ```bash
   pip install label-studio
   label-studio start
   ```
   - URL: https://labelstud.io/
   - Supports: Images, video, audio, text

**Annotation Format for YOLO:**
```
# Each image has a corresponding .txt file
# Format: <class_id> <x_center> <y_center> <width> <height>
# All values normalized to 0-1

Example (backpack.txt):
0 0.5 0.6 0.2 0.3
# class_id=0 (backpack), center at (50%, 60%), size 20%x30%
```

### 4.2 Action Recognition Annotation (Threat Detection)

**Recommended Tools:**

1. **VGG Image Annotator (VIA)** (Free)
   - URL: https://www.robots.ox.ac.uk/~vgg/software/via/
   - Supports: Video annotation, temporal segments

2. **CVAT** (Recommended)
   - Supports video annotation with temporal tracking
   - Can annotate frame-by-frame or by segments

**Annotation Format:**
```json
{
  "video": "classroom_001.mp4",
  "annotations": [
    {
      "start_frame": 150,
      "end_frame": 210,
      "label": "fighting",
      "confidence": 1.0,
      "bbox": [100, 150, 300, 400]
    },
    {
      "start_frame": 500,
      "end_frame": 800,
      "label": "normal",
      "confidence": 1.0
    }
  ]
}
```

---

## 5. Dataset Organization

### 5.1 Directory Structure

```
data/
├── left_behind_objects/
│   ├── raw/                          # Raw videos and images
│   │   ├── videos/
│   │   └── images/
│   ├── processed/
│   │   ├── train/
│   │   │   ├── images/
│   │   │   └── labels/              # YOLO format annotations
│   │   ├── val/
│   │   │   ├── images/
│   │   │   └── labels/
│   │   └── test/
│   │       ├── images/
│   │       └── labels/
│   └── dataset.yaml                  # Dataset configuration
│
├── threat_detection/
│   ├── raw/
│   │   └── videos/
│   ├── processed/
│   │   ├── train/
│   │   │   ├── fighting/
│   │   │   ├── pushing/
│   │   │   ├── aggressive/
│   │   │   └── normal/
│   │   ├── val/
│   │   │   ├── fighting/
│   │   │   ├── pushing/
│   │   │   ├── aggressive/
│   │   │   └── normal/
│   │   └── test/
│   │       ├── fighting/
│   │       ├── pushing/
│   │       ├── aggressive/
│   │       └── normal/
│   └── annotations.json
│
└── esp32_cam_samples/                # Test data from ESP32-CAM
    ├── images/
    └── videos/
```

### 5.2 Dataset Split Ratios

```
Recommended Split:
- Training: 70-80%
- Validation: 10-15%
- Testing: 10-15%

Example for 10,000 images:
- Train: 7,000 images
- Val: 1,500 images
- Test: 1,500 images
```

### 5.3 Dataset Configuration File (dataset.yaml)

```yaml
# Left-Behind Objects Dataset Configuration
path: ./data/left_behind_objects/processed
train: train/images
val: val/images
test: test/images

# Number of classes
nc: 7

# Class names
names:
  0: backpack
  1: book
  2: bottle
  3: umbrella
  4: jacket
  5: laptop
  6: lunchbox
```

---

## 6. Pre-trained Models and Transfer Learning

### 6.1 Object Detection (Left-Behind Objects)

**Recommended Approach: Use YOLOv8 with Transfer Learning**

```python
# Start with COCO pre-trained weights
# Fine-tune on your school-specific dataset

Base Models:
1. YOLOv8n (Nano) - Fast, suitable for ESP32 edge deployment
   - Speed: ~80 FPS on GPU
   - Size: 6.2 MB
   - mAP: 37.3%

2. YOLOv8s (Small) - Balanced
   - Speed: ~60 FPS on GPU
   - Size: 21.5 MB
   - mAP: 44.9%

3. YOLOv8m (Medium) - Better accuracy
   - Speed: ~40 FPS on GPU
   - Size: 49.7 MB
   - mAP: 50.2%
```

**Why Transfer Learning?**
- ✓ Requires less data (500-1000 images vs. 10,000+)
- ✓ Faster training (hours vs. days)
- ✓ Better performance with limited data
- ✓ Pre-trained on 80 COCO classes (includes bags, bottles, books)

### 6.2 Action Recognition (Threat Detection)

**Recommended Models:**

1. **SlowFast Networks** (Recommended)
   - Pre-trained on Kinetics-400
   - Excellent for violence detection
   - Two-pathway architecture (slow + fast)

2. **X3D (Efficient Video Networks)**
   - Lightweight, suitable for real-time
   - Pre-trained on Kinetics

3. **I3D (Inflated 3D ConvNet)**
   - Good accuracy
   - Pre-trained on Kinetics

4. **TimeSformer (Video Transformer)**
   - State-of-the-art accuracy
   - Requires more computational resources

**Transfer Learning Strategy:**
```
1. Start with Kinetics-400 pre-trained weights
2. Fine-tune on violence detection datasets (RWF-2000, UCF-Crime)
3. Further fine-tune on your school-specific data
4. Use data augmentation to improve generalization
```

---

## 7. Quick Start: Minimal Dataset Requirements

If you're just starting and want to test the system quickly:

### 7.1 Minimal Left-Behind Object Dataset

```
Minimum Viable Dataset:
- 200 images per class (7 classes = 1,400 images)
- Use COCO pre-trained YOLOv8
- Collect from your actual classrooms
- Annotate using LabelImg (2-3 hours work)

Quick Collection Method:
1. Place objects in classroom
2. Take photos from different angles (20 photos per setup)
3. Change object positions (10 different setups)
4. Repeat for different lighting (morning, afternoon)
5. Total time: 4-6 hours
```

### 7.2 Minimal Threat Detection Dataset

```
Minimum Viable Dataset:
- Use pre-existing datasets (RWF-2000, Hockey Fight)
- Download 500 fight videos + 500 normal videos
- Fine-tune pre-trained SlowFast model
- No need to collect your own initially

Quick Start:
1. Download RWF-2000 dataset
2. Use pre-trained model
3. Fine-tune for 10-20 epochs
4. Total time: 1-2 days (including download)
```

---

## 8. Data Collection Scripts

### 8.1 Automated Frame Extraction

See: `scripts/data_collection/extract_frames.py`

### 8.2 Data Augmentation

See: `scripts/data_collection/augment_dataset.py`

### 8.3 Dataset Validation

See: `scripts/data_collection/validate_dataset.py`

---

## 9. Ethical and Legal Considerations

### 9.1 Privacy and Consent

```
✓ Get written permission from school administration
✓ Obtain consent from students/parents for data collection
✓ Blur faces in stored footage (use anonymization)
✓ Comply with local data protection laws (GDPR, COPPA, etc.)
✓ Secure storage with encryption
✓ Define data retention policies
✓ Provide opt-out mechanisms
```

### 9.2 Bias and Fairness

```
✓ Ensure diverse representation in training data
✓ Test across different demographics
✓ Avoid bias in threat detection (don't associate threats with specific groups)
✓ Regular audits of model predictions
✓ Human oversight for all alerts
```

---

## 10. Next Steps

After preparing your dataset:

1. ✓ Organize data according to directory structure
2. ✓ Create dataset.yaml configuration
3. ✓ Validate annotations
4. ✓ Run data augmentation
5. ✓ Proceed to model training (see `docs/MODEL_TRAINING.md`)

---

## Resources and Links

### Datasets
- COCO: https://cocodataset.org/
- Open Images: https://storage.googleapis.com/openimages/web/index.html
- RWF-2000: https://github.com/mchengny/RWF2000-Video-Database-for-Violence-Detection
- UCF-Crime: https://www.crcv.ucf.edu/projects/real-world/
- Kinetics: https://deepmind.com/research/open-source/kinetics

### Annotation Tools
- LabelImg: https://github.com/heartexlabs/labelImg
- CVAT: https://www.cvat.ai/
- Roboflow: https://roboflow.com/
- Label Studio: https://labelstud.io/

### Pre-trained Models
- YOLOv8: https://github.com/ultralytics/ultralytics
- PyTorchVideo: https://pytorchvideo.org/
- MMAction2: https://github.com/open-mmlab/mmaction2

---

**Need Help?** Check the troubleshooting section in the main README or open an issue on GitHub.

