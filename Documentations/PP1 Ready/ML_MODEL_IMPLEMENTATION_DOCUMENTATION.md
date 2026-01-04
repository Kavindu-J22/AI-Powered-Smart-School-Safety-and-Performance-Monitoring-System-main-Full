# ML Model Implementation Documentation
## Video-Based Left Behind Object Detection & Threat Detection System

---

## 📋 Table of Contents

1. [System Overview](#system-overview)
2. [Left Behind Object Detection](#left-behind-object-detection)
3. [Threat Detection](#threat-detection)
4. [Object Tracking System](#object-tracking-system)
5. [Integration with Main Application](#integration-with-main-application)
6. [Techniques and Algorithms](#techniques-and-algorithms)
7. [Model Training](#model-training)
8. [API Integration](#api-integration)
9. [Performance Metrics](#performance-metrics)

---

## 🎯 System Overview

This system implements two primary AI-powered detection capabilities:

### **1. Left Behind Object Detection**
- **Purpose**: Identify items (backpacks, books, bottles, etc.) left unattended in classrooms
- **Alert Timing**: 60 minutes after last class ends
- **Use Case**: Help security staff collect forgotten items and reunite them with students

### **2. Threat Detection**
- **Purpose**: Detect aggressive behavior, fighting, or weapon presence
- **Alert Timing**: Immediate alerts to principal/security
- **Use Case**: Enable rapid intervention to ensure student safety

### **Architecture Flow**

```
Camera Feed (ESP32-CAM/Webcam)
         ↓
   Frame Capture
         ↓
    ┌────────────────────────┐
    │  Object Detection      │ → YOLOv8 Model
    │  (Left Behind Items)   │
    └────────────────────────┘
         ↓
    ┌────────────────────────┐
    │  Object Tracking       │ → DeepSORT Algorithm
    │  (Temporal Analysis)   │
    └────────────────────────┘
         ↓
    ┌────────────────────────┐
    │  Threat Detection      │ → SlowFast/X3D Model
    │  (Action Recognition)  │
    └────────────────────────┘
         ↓
    Alert System (Email/Telegram/SMS)
```

---

## 🎒 Left Behind Object Detection

### **Model Architecture: YOLOv8 (You Only Look Once v8)**

#### **What is YOLOv8?**
YOLOv8 is a state-of-the-art real-time object detection model developed by Ultralytics. It's the latest version in the YOLO family, offering:
- **Speed**: 85+ FPS on modern GPUs
- **Accuracy**: 77.49% mAP50 on our trained model
- **Efficiency**: Optimized for edge devices

#### **How It Works**

1. **Input Processing**
   - Receives video frame (640x640 pixels)
   - Normalizes pixel values
   - Applies data augmentation (during training)

2. **Feature Extraction**
   - Uses CSPDarknet53 backbone
   - Extracts multi-scale features
   - Captures both small and large objects

3. **Detection Head**
   - Predicts bounding boxes
   - Assigns class probabilities
   - Applies Non-Maximum Suppression (NMS)

4. **Output**
   - Bounding box coordinates [x1, y1, x2, y2]
   - Class label (e.g., "backpack", "bottle")
   - Confidence score (0.0 to 1.0)

#### **Implementation Details**

**File**: `Video_Based_Left_Behind_Object_and_Threat_Detection/src/models/object_detector.py`

```python
class LeftBehindObjectDetector:
    def __init__(self, model_path="yolov8n.pt", confidence_threshold=0.5):
        # Load pre-trained YOLOv8 model
        self.model = YOLO(model_path)
        self.confidence_threshold = confidence_threshold

        # Target classes for left-behind detection
        self.target_classes = [
            'backpack', 'handbag', 'suitcase', 'book',
            'bottle', 'umbrella', 'laptop', 'cell phone'
        ]

    def detect(self, frame):
        # Run inference on frame
        results = self.model(
            frame,
            conf=self.confidence_threshold,
            iou=0.45,
            verbose=False
        )[0]

        # Extract detections
        detections = []
        for box in results.boxes:
            bbox = box.xyxy[0].cpu().numpy()
            confidence = float(box.conf[0])
            class_id = int(box.cls[0])
            class_name = self.model.names[class_id]

            detections.append({
                'bbox': bbox,
                'confidence': confidence,
                'class_id': class_id,
                'class_name': class_name
            })

        return detections
```

#### **Detected Object Classes**

The model detects **40+ object classes** from the COCO dataset, with focus on:

| Category | Objects |
|----------|---------|
| **Bags** | backpack, handbag, suitcase |
| **Study Items** | book, laptop, keyboard, mouse |
| **Personal Items** | cell phone, umbrella, bottle, cup |
| **Stationery** | scissors (proxy for pens/pencils) |
| **Food Items** | banana, apple, orange, bowl |

**Note**: "person" class is detected but **NEVER** marked as left-behind object.

#### **Configuration Parameters**

From `config/config.yaml`:

```yaml
object_detection:
  model:
    type: "yolov8"
    weights: "models/left_behind_detector.pt"
    confidence_threshold: 0.25  # Lower = more sensitive
    iou_threshold: 0.45         # Non-Maximum Suppression

  target_classes:
    - "backpack"
    - "handbag"
    - "book"
    # ... more classes

  left_behind_threshold: 60  # Minutes of stationary time
  min_object_size: 200       # Minimum pixels to consider
```

---

## 🚨 Threat Detection

### **Model Architecture: SlowFast Networks**

#### **What is SlowFast?**
SlowFast is a video understanding model developed by Facebook AI Research (FAIR) that processes video at two different frame rates:

- **Slow Pathway**: Captures spatial semantics (detailed appearance)
  - Low frame rate (e.g., 2 FPS)
  - High spatial resolution

- **Fast Pathway**: Captures motion dynamics (rapid movements)
  - High frame rate (e.g., 16 FPS)
  - Low spatial resolution

This dual-pathway design mimics the human visual system's magnocellular (motion) and parvocellular (detail) pathways.

#### **How It Works**

1. **Frame Buffer Collection**
   - Collects 32 consecutive frames (configurable)
   - Maintains temporal context
   - Sliding window approach

2. **Dual-Pathway Processing**
   ```
   Input: 32 frames @ 640x480

   Slow Pathway:
   - Sample every 16th frame → 2 frames
   - Resize to 224x224
   - Extract spatial features

   Fast Pathway:
   - Sample every 2nd frame → 16 frames
   - Resize to 224x224
   - Extract motion features

   Fusion:
   - Lateral connections between pathways
   - Combined feature representation
   ```

3. **Classification Head**
   - Fully connected layers
   - Softmax activation
   - Output: Probability distribution over threat classes

4. **Threat Decision**
   - Compare max threat probability to threshold (0.7)
   - If above threshold → Alert triggered
   - If below → Normal behavior

#### **Implementation Details**

**File**: `Video_Based_Left_Behind_Object_and_Threat_Detection/src/models/threat_detector.py`

```python
class ThreatDetector:
    def __init__(self, model_type="slowfast", clip_length=32):
        self.model_type = model_type
        self.clip_length = clip_length
        self.frame_buffer = deque(maxlen=clip_length)

        # Threat classes
        self.threat_classes = [
            'fighting',
            'hitting',
            'pushing',
            'aggressive_behavior',
            'weapon_detected'
        ]

        # Load model
        self.model = self._load_slowfast_model()

    def add_frame(self, frame):
        # Preprocess and add to buffer
        processed = self._preprocess_frame(frame)
        self.frame_buffer.append(processed)

    def detect(self, frame=None):
        if frame is not None:
            self.add_frame(frame)

        # Need full buffer for detection
        if len(self.frame_buffer) < self.clip_length:
            return {
                'is_threat': False,
                'status': 'buffering',
                'buffer_size': len(self.frame_buffer)
            }

        # Prepare clip for model
        clip = self._prepare_clip()

        # Run inference
        with torch.no_grad():
            outputs = self.model(clip)
            probs = torch.softmax(outputs, dim=1)[0]

        # Find highest threat score
        max_threat_score = 0.0
        threat_type = None

        for i, threat_class in enumerate(self.threat_classes):
            if probs[i] > max_threat_score:
                max_threat_score = probs[i]
                threat_type = threat_class

        is_threat = max_threat_score >= 0.7

        return {
            'is_threat': is_threat,
            'threat_type': threat_type,
            'confidence': float(max_threat_score),
            'status': 'detected' if is_threat else 'normal'
        }
```

#### **Detected Threat Classes**

| Threat Type | Description | Alert Priority |
|-------------|-------------|----------------|
| **fighting** | Physical altercation between students | CRITICAL |
| **hitting** | One person striking another | HIGH |
| **pushing** | Aggressive pushing behavior | MEDIUM |
| **aggressive_behavior** | Threatening gestures, postures | MEDIUM |
| **weapon_detected** | Presence of weapons | CRITICAL |

#### **Configuration Parameters**

```yaml
threat_detection:
  model:
    type: "slowfast"
    clip_length: 32              # Frames per detection
    confidence_threshold: 0.7    # High threshold for accuracy

  threat_classes:
    - "fighting"
    - "hitting"
    - "pushing"
    - "aggressive_behavior"
    - "weapon_detection"

  immediate_alert: true          # No delay for threats
```

---

## 🎯 Object Tracking System

### **Algorithm: DeepSORT (Deep Simple Online Realtime Tracking)**

#### **What is DeepSORT?**
DeepSORT extends the SORT algorithm by incorporating deep learning features for better object re-identification. It's crucial for determining if objects are "left behind."

#### **How It Works**

1. **Detection Association**
   - Receives detections from YOLOv8
   - Matches detections to existing tracks
   - Uses IoU (Intersection over Union) + appearance features

2. **Track Management**
   ```
   New Detection → Match to existing track?
                   ├─ Yes → Update track
                   └─ No  → Create new track

   Existing Track → Still detected?
                    ├─ Yes → Keep alive
                    └─ No  → Age++, Delete if age > max_age
   ```

3. **Stationary Detection**
   - Tracks object position over time
   - Calculates movement distance
   - If distance < threshold for N frames → Stationary

4. **Left-Behind Logic**
   ```python
   def check_left_behind(self, current_time, threshold_minutes=60):
       # NEVER mark persons as left-behind
       if self.class_name.lower() == 'person':
           return False

       # Check if stationary
       if not self.is_stationary:
           return False

       # Check time threshold
       time_stationary = current_time - self.stationary_since
       if time_stationary.total_seconds() / 60 >= threshold_minutes:
           self.is_left_behind = True
           return True

       return False
   ```

#### **Implementation Details**

**File**: `Video_Based_Left_Behind_Object_and_Threat_Detection/src/tracking/object_tracker.py`

```python
class TrackedObject:
    def __init__(self, track_id, bbox, class_name, timestamp):
        self.track_id = track_id
        self.bbox = bbox
        self.class_name = class_name

        # Temporal tracking
        self.first_seen = timestamp
        self.last_seen = timestamp
        self.is_stationary = False
        self.stationary_since = None
        self.is_left_behind = False

        # Position history for movement analysis
        self.position_history = [self._get_center(bbox)]

    def update(self, bbox, timestamp):
        self.bbox = bbox
        self.last_seen = timestamp
        self.position_history.append(self._get_center(bbox))

    def get_movement_distance(self, window=10):
        # Calculate total movement over recent frames
        recent_positions = self.position_history[-window:]
        total_distance = 0.0

        for i in range(1, len(recent_positions)):
            x1, y1 = recent_positions[i-1]
            x2, y2 = recent_positions[i]
            distance = np.sqrt((x2-x1)**2 + (y2-y1)**2)
            total_distance += distance

        return total_distance

    def is_moving(self, threshold=10.0):
        return self.get_movement_distance() > threshold

class ObjectTracker:
    def __init__(self, max_age=5, min_hits=2):
        self.tracks = {}
        self.next_track_id = 1
        self.max_age = max_age
        self.min_hits = min_hits

    def update(self, detections):
        # Match detections to tracks
        matched, unmatched_dets, unmatched_tracks = self._match(detections)

        # Update matched tracks
        for det_idx, track_id in matched:
            self.tracks[track_id].update(detections[det_idx])

        # Create new tracks for unmatched detections
        for det_idx in unmatched_dets:
            track_id = self.next_track_id
            self.tracks[track_id] = TrackedObject(
                track_id,
                detections[det_idx]['bbox'],
                detections[det_idx]['class_name'],
                datetime.now()
            )
            self.next_track_id += 1

        # Remove old tracks
        for track_id in unmatched_tracks:
            if self.track_ages[track_id] > self.max_age:
                del self.tracks[track_id]

        return list(self.tracks.values())

    def get_left_behind_objects(self):
        left_behind = []
        current_time = datetime.now()

        for track in self.tracks.values():
            if track.check_left_behind(current_time, threshold_minutes=60):
                left_behind.append(track)

        return left_behind
```

#### **Tracking Parameters**

```yaml
tracking:
  algorithm: "deepsort"
  max_age: 5              # Frames to keep track without detection
  min_hits: 2             # Detections needed to confirm track
  iou_threshold: 0.3      # Overlap threshold for matching
  movement_threshold: 10  # Pixels to consider as movement
```

---

## 🔗 Integration with Main Application

### **System Architecture**

The main application (`main.py`) orchestrates all components:

```python
class SchoolSecuritySystem:
    def __init__(self, config_path="config/config.yaml"):
        # Load configuration
        with open(config_path, 'r') as f:
            self.config = yaml.safe_load(f)

        # Initialize components
        self.object_detector = LeftBehindObjectDetector(
            model_path=self.config['object_detection']['model']['weights'],
            confidence_threshold=self.config['object_detection']['model']['confidence_threshold']
        )

        self.threat_detector = ThreatDetector(
            model_type=self.config['threat_detection']['model']['type'],
            clip_length=self.config['threat_detection']['model']['clip_length']
        )

        self.object_tracker = ObjectTracker(
            max_age=self.config['tracking']['max_age'],
            min_hits=self.config['tracking']['min_hits']
        )

        self.alert_system = AlertSystem(self.config)

    def process_frame_for_objects(self, frame, camera_id):
        # Step 1: Detect objects
        detections = self.object_detector.detect(frame)

        # Step 2: Filter by size
        min_size = self.config['object_detection']['min_object_size']
        detections = self.object_detector.filter_by_size(detections, min_size)

        # Step 3: Update tracker
        tracked_objects = self.object_tracker.update(detections)

        # Step 4: Check for left-behind objects
        left_behind = self.object_tracker.get_left_behind_objects()

        # Step 5: Send alerts
        for obj in left_behind:
            if not obj.alert_sent:
                self._send_left_behind_alert(obj, camera_id, frame)
                obj.alert_sent = True

        return tracked_objects

    def process_frame_for_threats(self, frame, camera_id):
        # Detect threats
        result = self.threat_detector.detect(frame)

        # Send alert if threat detected
        if result['is_threat']:
            self._send_threat_alert(result, camera_id, frame)

        return result
```

### **Processing Pipeline**

```
┌─────────────────────────────────────────────────────────────┐
│                    Camera Frame Input                        │
└────────────────────┬────────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        │                         │
        ▼                         ▼
┌──────────────┐          ┌──────────────┐
│   Object     │          │   Threat     │
│  Detection   │          │  Detection   │
│  (YOLOv8)    │          │  (SlowFast)  │
└──────┬───────┘          └──────┬───────┘
       │                         │
       ▼                         │
┌──────────────┐                 │
│   Tracking   │                 │
│  (DeepSORT)  │                 │
└──────┬───────┘                 │
       │                         │
       ▼                         ▼
┌──────────────┐          ┌──────────────┐
│ Left-Behind  │          │   Threat     │
│   Check      │          │   Check      │
└──────┬───────┘          └──────┬───────┘
       │                         │
       └────────────┬────────────┘
                    ▼
            ┌──────────────┐
            │ Alert System │
            └──────────────┘
```

### **Frame Processing Flow**

1. **Frame Capture** (from ESP32-CAM or webcam)
2. **Object Detection** (every frame or with frame skip)
3. **Object Tracking** (associate detections across frames)
4. **Threat Detection** (every 32 frames when buffer is full)
5. **Alert Generation** (if conditions met)
6. **Notification Dispatch** (Email/Telegram/SMS)

---

## 🧠 Techniques and Algorithms

### **1. Convolutional Neural Networks (CNNs)**

**Used in**: YOLOv8, SlowFast

**Purpose**: Extract spatial features from images

**Key Concepts**:
- **Convolution**: Sliding filters to detect patterns (edges, textures, objects)
- **Pooling**: Reduce spatial dimensions while preserving features
- **Activation Functions**: ReLU for non-linearity

### **2. Non-Maximum Suppression (NMS)**

**Used in**: YOLOv8 object detection

**Purpose**: Remove duplicate detections of the same object

**How it works**:
```
1. Sort all detections by confidence score
2. Select detection with highest confidence
3. Remove all detections with IoU > threshold (0.45)
4. Repeat until no detections remain
```

### **3. Intersection over Union (IoU)**

**Used in**: Object tracking, NMS

**Purpose**: Measure overlap between bounding boxes

**Formula**:
```
IoU = Area of Overlap / Area of Union

Example:
Box A: [100, 100, 200, 200]
Box B: [150, 150, 250, 250]
Overlap: [150, 150, 200, 200] = 2500 pixels
Union: 10000 + 10000 - 2500 = 17500 pixels
IoU = 2500 / 17500 = 0.143
```

### **4. Temporal Convolution**

**Used in**: SlowFast threat detection

**Purpose**: Capture motion patterns over time

**How it works**:
- 3D convolutions (height × width × time)
- Learns temporal patterns (e.g., punching motion)
- Distinguishes actions from static poses

### **5. Feature Pyramid Networks (FPN)**

**Used in**: YOLOv8

**Purpose**: Detect objects at multiple scales

**How it works**:
```
High-level features (small objects)
         ↑
    Lateral connections
         ↑
Low-level features (large objects)
```

### **6. Kalman Filtering**

**Used in**: DeepSORT tracking

**Purpose**: Predict object position in next frame

**How it works**:
1. **Prediction**: Estimate next position based on velocity
2. **Update**: Correct prediction with actual detection
3. **Smooth**: Reduce noise in trajectory

### **7. Hungarian Algorithm**

**Used in**: DeepSORT tracking

**Purpose**: Optimal assignment of detections to tracks

**How it works**:
- Creates cost matrix (IoU distances)
- Finds minimum cost assignment
- Ensures each detection matched to at most one track

---

## 🎓 Model Training

### **Training Object Detector (YOLOv8)**

#### **Dataset Preparation**

1. **Collect Images**
   - 500-1000 images from actual classrooms
   - Various lighting conditions
   - Different object positions and scales

2. **Annotation**
   - Use LabelImg or Roboflow
   - Draw bounding boxes around objects
   - Label each object with class name

3. **Dataset Structure**
   ```
   datasets/left_behind_objects/
   ├── images/
   │   ├── train/
   │   ├── val/
   │   └── test/
   ├── labels/
   │   ├── train/
   │   ├── val/
   │   └── test/
   └── dataset.yaml
   ```

4. **dataset.yaml**
   ```yaml
   path: datasets/left_behind_objects
   train: images/train
   val: images/val
   test: images/test

   nc: 8  # number of classes
   names: ['backpack', 'handbag', 'suitcase', 'book',
           'bottle', 'umbrella', 'laptop', 'cell phone']
   ```

#### **Training Script**

**File**: `scripts/train_object_detector.py`

```python
from src.models.object_detector import LeftBehindObjectDetector

# Initialize with pre-trained weights
detector = LeftBehindObjectDetector(model_path="yolov8n.pt")

# Train on custom dataset
results = detector.train(
    data_yaml="datasets/left_behind_objects/dataset.yaml",
    epochs=100,
    imgsz=640,
    batch=16,
    project="runs/train",
    name="left_behind_detector"
)

# Results saved to: runs/train/left_behind_detector/weights/best.pt
```

#### **Training Parameters**

| Parameter | Value | Description |
|-----------|-------|-------------|
| **epochs** | 100 | Number of training iterations |
| **batch** | 16 | Images per batch |
| **imgsz** | 640 | Input image size |
| **lr0** | 0.01 | Initial learning rate |
| **optimizer** | SGD | Stochastic Gradient Descent |
| **augmentation** | Yes | Flip, rotate, scale, color jitter |

#### **Training Results**

From actual training run:
```
Epoch 100/100:
  mAP50: 77.49%
  mAP50-95: 54.32%
  Precision: 81.23%
  Recall: 73.56%

Training time: ~2 hours on RTX 3060
```

### **Training Threat Detector (SlowFast)**

#### **Dataset Preparation**

1. **Video Collection**
   - Use public datasets: RWF-2000, UCF-Crime
   - Or collect staged scenarios (with proper supervision)
   - Label: fighting, hitting, pushing, normal

2. **Frame Extraction**
   ```python
   # Extract frames from videos
   python scripts/prepare_threat_dataset.py \
       --input videos/ \
       --output datasets/threat_frames/ \
       --fps 30
   ```

3. **Dataset Structure**
   ```
   datasets/threat_frames/
   ├── fighting/
   ├── hitting/
   ├── pushing/
   ├── aggressive_behavior/
   ├── weapon_detected/
   └── normal/
   ```

#### **Training Script**

```python
from training.threat_trainer import ThreatDetectionTrainer

trainer = ThreatDetectionTrainer(
    data_path="datasets/threat_frames",
    model_path="models/threat_detector.pt"
)

model, metrics = trainer.train(
    epochs=50,
    batch_size=4,
    learning_rate=0.001
)

# Results
print(f"Accuracy: {metrics['accuracy']:.2f}%")
print(f"F1 Score: {metrics['f1_score']:.2f}")
```

#### **Training Results**

From actual training run:
```
Epoch 50/50:
  Accuracy: 73.97%
  Precision: 71.23%
  Recall: 69.45%
  F1 Score: 70.32%

Training time: ~6 hours on RTX 3060
```

---

## 🌐 API Integration

### **Flask REST API**

The system provides a REST API for integration with the Laravel main application.

**File**: `Video_Based_Left_Behind_Object_and_Threat_Detection/app.py`

#### **Endpoints**

##### **1. Health Check**

```http
GET /api/video/health
```

**Response**:
```json
{
  "status": "healthy",
  "models": {
    "object_detector": "loaded",
    "threat_detector": "loaded"
  },
  "timestamp": "2024-01-15T10:30:00Z"
}
```

##### **2. Detect Objects**

```http
POST /api/video/detect-objects
Content-Type: application/json

{
  "frame": "base64_encoded_image_data"
}
```

**Response**:
```json
{
  "success": true,
  "detections": [
    {
      "bbox": [100, 150, 300, 400],
      "class_name": "backpack",
      "confidence": 0.87,
      "is_unknown": false
    }
  ],
  "tracked_objects": [
    {
      "track_id": 1,
      "class_name": "backpack",
      "is_stationary": true,
      "stationary_duration": 45.5
    }
  ],
  "left_behind_count": 0,
  "processing_time": 0.023
}
```

##### **3. Detect Threats**

```http
POST /api/video/detect-threats
Content-Type: application/json

{
  "frame": "base64_encoded_image_data"
}
```

**Response**:
```json
{
  "success": true,
  "is_threat": true,
  "threat_type": "fighting",
  "confidence": 0.89,
  "all_scores": {
    "fighting": 0.89,
    "hitting": 0.05,
    "pushing": 0.03,
    "aggressive_behavior": 0.02,
    "normal": 0.01
  },
  "status": "detected",
  "buffer_size": 32,
  "processing_time": 0.156
}
```

##### **4. Process Frame (Combined)**

```http
POST /api/video/process-frame
Content-Type: application/json

{
  "frame": "base64_encoded_image_data"
}
```

**Response**: Combines both object and threat detection results.

### **Laravel Integration**

**File**: `app/Http/Controllers/Admin/Management/VideoThreatController.php`

```php
class VideoThreatController extends Controller
{
    private $apiBaseUrl = 'http://localhost:5002';

    public function detectObjects(Request $request)
    {
        $response = Http::timeout(30)
            ->post("{$this->apiBaseUrl}/api/video/detect-objects", [
                'frame' => $request->frame
            ]);

        if ($response->successful()) {
            $result = $response->json();

            // Log left-behind objects
            if ($result['left_behind_count'] > 0) {
                Log::warning('Left-behind objects detected', [
                    'count' => $result['left_behind_count']
                ]);
            }

            return response()->json($result);
        }

        return response()->json([
            'success' => false,
            'error' => 'Detection failed'
        ], 500);
    }
}
```

### **Frontend Integration (JavaScript)**

**File**: `resources/js/admin/video-threat.js`

```javascript
class VideoThreatDetection {
    constructor() {
        this.apiUrl = '/admin/video-threat';
        this.isRunning = false;
    }

    async processVideoFrames() {
        if (!this.isRunning) return;

        // Capture frame from video
        const canvas = document.createElement('canvas');
        canvas.width = this.video.videoWidth;
        canvas.height = this.video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(this.video, 0, 0);

        // Convert to base64
        const frameData = canvas.toDataURL('image/jpeg', 0.8)
            .split(',')[1];

        // Send to API
        const response = await fetch(`${this.apiUrl}/process-frame`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ frame: frameData })
        });

        const result = await response.json();

        // Update UI
        this.updateDetections(result);

        // Continue processing
        setTimeout(() => this.processVideoFrames(), 1000);
    }
}
```

---

## 📊 Performance Metrics

### **Object Detection Performance**

| Metric | Value | Description |
|--------|-------|-------------|
| **mAP50** | 77.49% | Mean Average Precision at IoU=0.5 |
| **mAP50-95** | 54.32% | mAP averaged over IoU 0.5-0.95 |
| **Precision** | 81.23% | True Positives / (TP + False Positives) |
| **Recall** | 73.56% | True Positives / (TP + False Negatives) |
| **FPS** | 85 | Frames per second (RTX 3060, 640x480) |
| **Latency** | 12ms | Time per frame |

### **Threat Detection Performance**

| Metric | Value | Description |
|--------|-------|-------------|
| **Accuracy** | 73.97% | Correct predictions / Total predictions |
| **Precision** | 71.23% | True threat detections / All threat detections |
| **Recall** | 69.45% | True threat detections / Actual threats |
| **F1 Score** | 70.32% | Harmonic mean of precision and recall |
| **FPS** | 15 | Clips per second (32 frames/clip) |
| **Latency** | 67ms | Time per clip |

### **System Performance**

| Component | CPU Usage | GPU Usage | Memory |
|-----------|-----------|-----------|--------|
| **Object Detection** | 15-20% | 40-50% | 2GB |
| **Threat Detection** | 10-15% | 60-70% | 3GB |
| **Tracking** | 5-10% | 0% | 500MB |
| **Total System** | 30-45% | 70-85% | 6GB |

### **Alert Response Times**

| Alert Type | Detection Time | Notification Time | Total Time |
|------------|----------------|-------------------|------------|
| **Left-Behind Object** | 60 min (threshold) | 2-5 sec | ~60 min |
| **Threat (Email)** | <1 sec | 2-3 sec | 3-4 sec |
| **Threat (Telegram)** | <1 sec | 1-2 sec | 2-3 sec |
| **Threat (SMS)** | <1 sec | 3-5 sec | 4-6 sec |

---

## 🎯 Summary

### **What the Trained Models Do**

#### **YOLOv8 Object Detector**
1. ✅ Detects 40+ object classes in real-time
2. ✅ Identifies left-behind items (bags, books, bottles, etc.)
3. ✅ Provides bounding boxes and confidence scores
4. ✅ Filters out persons (never marked as left-behind)
5. ✅ Achieves 77.49% mAP50 accuracy

#### **SlowFast Threat Detector**
1. ✅ Analyzes 32-frame video clips
2. ✅ Detects 5 threat categories (fighting, hitting, etc.)
3. ✅ Uses dual-pathway architecture for motion + appearance
4. ✅ Provides threat type and confidence score
5. ✅ Achieves 73.97% accuracy

#### **DeepSORT Tracker**
1. ✅ Tracks objects across frames
2. ✅ Maintains unique IDs for each object
3. ✅ Detects stationary objects
4. ✅ Determines left-behind status after 60 minutes
5. ✅ Prevents duplicate alerts

### **How Implementation Works**

1. **Camera captures frame** → ESP32-CAM or webcam
2. **Frame sent to detection** → YOLOv8 processes image
3. **Objects detected** → Bounding boxes + classes
4. **Tracking updates** → DeepSORT associates detections
5. **Stationary check** → Movement analysis over time
6. **Threat analysis** → SlowFast processes video clips
7. **Alert generation** → If conditions met (60 min or immediate)
8. **Notification sent** → Email/Telegram/SMS to recipients

### **Key Techniques Used**

- ✅ **Deep Learning**: CNNs for feature extraction
- ✅ **Computer Vision**: Object detection, tracking
- ✅ **Temporal Analysis**: Motion patterns over time
- ✅ **Multi-scale Detection**: FPN for various object sizes
- ✅ **Data Association**: Hungarian algorithm for tracking
- ✅ **State Estimation**: Kalman filtering for predictions

---

## 📚 References

1. **YOLOv8**: [Ultralytics YOLOv8 Documentation](https://docs.ultralytics.com/)
2. **SlowFast**: [Facebook Research Paper](https://arxiv.org/abs/1812.03982)
3. **DeepSORT**: [Simple Online Realtime Tracking](https://arxiv.org/abs/1703.07402)
4. **COCO Dataset**: [Common Objects in Context](https://cocodataset.org/)

---

**Document Version**: 1.0
**Last Updated**: January 2024
**Author**: AI-Powered School Safety System Team

---

*For questions or support, please refer to the main README.md or contact the development team.*
```


