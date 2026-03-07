# 📹 DOCUMENT 3: Video-Based Left Behind Object & Threat Detection System
### AI-Powered Smart School Safety and Performance Monitoring System
---

## 1. EXECUTIVE SUMMARY

The Video-Based Left Behind Object and Threat Detection System provides 24/7 intelligent visual surveillance for school environments using camera feeds (IP cameras, ESP32-CAM). It operates as two tightly integrated detection modules:

1. **Left-Behind Object Detection** — Identifies school items (backpacks, laptops, water bottles, books, etc.) that remain stationary for a configurable threshold period (default: 60 minutes) after a person has left the vicinity, triggering alerts to security staff.

2. **Behavioral Threat Detection** — Detects threatening human behaviors (fighting, aggression, falls, pushing) using **YOLOv8-pose** keypoints and **dense optical flow** — without requiring any custom-trained classifier. It uses 4 heuristic scores blended and temporally smoothed.

**System Stack:** Python + Flask + YOLOv8 (Ultralytics) + OpenCV + PyTorch + Laravel (web integration)

---

## 2. TECHNOLOGY STACK

| Component | Technology |
|---|---|
| Object Detection | YOLOv8n (COCO) + custom-trained best.pt (school items) |
| Pose Estimation | YOLOv8n-pose (17 COCO keypoints) |
| Optical Flow | OpenCV Farneback Dense Optical Flow |
| Object Tracking | Custom IoU-based tracker (TrackedObject class) |
| Threat Detection | Heuristic pose scoring (no separate training) |
| Backend API | Python Flask REST API |
| Web Integration | Laravel PHP → Flask via HTTP (VideoThreatController) |
| Hardware | ESP32-CAM / IP Cameras / USB Webcam |
| Alert System | Laravel notifications + webhook alerts |

---

## 3. SYSTEM ARCHITECTURE

```
┌─────────────────────────────────────────────────────────────────────┐
│               Camera Input (ESP32-CAM / IP Camera / Webcam)          │
└──────────────────────────────┬──────────────────────────────────────┘
                               │  Video Frame (BGR image, H×W×3)
                               ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        Flask API (app.py)                            │
│  /api/video/detect-objects   |   /api/video/status                  │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
          ┌────────────────────┴───────────────────────┐
          │                                            │
          ▼  LEFT-BEHIND MODULE                        ▼  THREAT MODULE
┌────────────────────────────┐          ┌────────────────────────────┐
│      ObjectDetector         │          │      ThreatDetector         │
│  ┌──────────────────────┐  │          │  ┌──────────────────────┐  │
│  │ Primary: best.pt     │  │          │  │ YOLOv8n-pose         │  │
│  │ (school items YOLO)  │  │          │  │ 17 COCO keypoints    │  │
│  └──────────────────────┘  │          │  └──────────────────────┘  │
│  ┌──────────────────────┐  │          │  ┌──────────────────────┐  │
│  │ Secondary: yolov8n   │  │          │  │ 4 Heuristic Scores   │  │
│  │ (COCO baseline)      │  │          │  │ - Proximity (IoU)    │  │
│  └──────────────────────┘  │          │  │ - Arm Raise          │  │
│  filter_by_size()           │          │  │ - Fall Detection     │  │
└────────────┬───────────────┘          │  │ - Optical Flow Motion│  │
             │                          │  └──────────────────────┘  │
             ▼                          │  Weighted Blend + Smoothing │
┌────────────────────────────┐          └────────────┬───────────────┘
│       ObjectTracker         │                       │
│  - IoU matching             │                       │
│  - Position history         │                       │
│  - Movement distance calc   │                       │
│  - Stationary state machine │                       │
│  - 60-min left-behind check │                       │
└────────────┬───────────────┘                       │
             │                                        │
             └──────────────────┬─────────────────────┘
                                │
                                ▼
              ┌────────────────────────────────────┐
              │        Alert System                │
              │  - Left-behind alert (webhook/SMS)  │
              │  - Threat alert (Laravel log/notify)│
              └────────────────────────────────────┘
                                │
                                ▼
              ┌────────────────────────────────────┐
              │   Laravel VideoThreatController     │
              │   detectObjects() → Log + Notify    │
              └────────────────────────────────────┘
```

---

## 4. IMPLEMENTATION DETAILS

### 4.1 Core Files and Their Roles

| File | Role |
|---|---|
| `app.py` | Flask REST API server, frame routing, dual detection |
| `main.py` | Standalone mode: camera loop, integrated detection + alerts |
| `src/models/object_detector.py` | Dual YOLOv8 object detection (custom + COCO) |
| `src/models/threat_detector.py` | Pose-based threat detection (YOLOv8n-pose + heuristics) |
| `src/tracking/object_tracker.py` | IoU-based multi-object tracking + left-behind logic |
| `src/notifications/alert_system.py` | Alert sending (webhook, SMS, email) |
| `config/config.yaml` | System configuration (thresholds, camera, detection params) |
| `VideoThreatController.php` | Laravel bridge to Flask API |
| `firmware/esp32_cam/` | ESP32-CAM Arduino firmware for camera streaming |

### 4.2 Left-Behind Object Detection Pipeline

```
VIDEO FRAME
     │
     ▼
ObjectDetector.detect(frame)
     │ YOLOv8n (COCO): detects books, backpacks, bottles, phones…
     │ best.pt (custom): detects Tas-Ransel, Pen, Laptop, Water-bottle…
     │ SCHOOL_CLASS_ALIASES: normalize class names to school labels
     │
     ▼
filter_by_size(detections, min_size=config['min_object_size'])
     │ Remove tiny detections (noise)
     │
     ▼
ObjectTracker.update(detections)
     │
     ├── IoU Matching:
     │    For each new detection, find closest existing TrackedObject
     │    IoU(new_bbox, tracked_bbox) > iou_threshold → update existing
     │    No match → create new TrackedObject (new track_id)
     │
     ├── TrackedObject.update(bbox, confidence, timestamp)
     │    Updates: position_history (max 100 entries)
     │             last_seen, last_update
     │
     ├── update_stationary_status()
     │    movement = get_movement_distance(window=10 frames)
     │    distance < 10 pixels → is_stationary = True
     │    Records: stationary_since timestamp
     │
     └── check_left_behind(threshold_minutes=60)
          if is_stationary AND time_stationary ≥ 60 min
          → is_left_behind = True
          → left_behind_since recorded

ObjectTracker.get_left_behind_objects()
     │
     ▼
For each left_behind object (alert_sent=False):
     _send_left_behind_alert(obj, camera_id, frame)
     obj.alert_sent = True
```

### 4.3 Detected School Object Categories

| Category | Source Model | Examples |
|---|---|---|
| Bags | Both models | Backpack (Tas-Ransel), Handbag, Suitcase |
| Stationery | Custom best.pt | Pen/Pencil |
| Electronics | Both models | Laptop (Gateway), Mobile Phone, Keyboard, Mouse |
| Containers | Both models | Water Bottle, Cup |
| Books | COCO yolov8n | Book/Notebook |
| Sports | Custom best.pt | Baseball Bat, Basketball, Soccer Ball, Tennis Racket |
| Accessories | Both models | Umbrella, Remote Control |

### 4.4 Behavioral Threat Detection Pipeline

```
VIDEO FRAME (BGR H×W×3)
     │
     ▼
frame_gray = cv2.cvtColor(frame, BGR2GRAY)
     │
     ▼
YOLOv8n-pose.predict(frame, classes=[0])  ← persons only (COCO class 0)
     │
     ▼
_extract_persons(results)
     │ Returns: [{'bbox': [x1,y1,x2,y2], 'keypoints': ndarray(17,3)}]
     │ keypoints[:,2] = confidence per keypoint
     │
     ├─── _score_proximity(persons) → s_prox [0,1]
     │    - Compute pairwise IoU between all person bboxes
     │    - High IoU (bboxes overlap) = people physically close
     │    - score = clamp((max_IoU - 0.1) / 0.5, 0, 1)
     │
     ├─── _score_arm_raise(persons) → s_arm [0,1]
     │    - For each person: check if WRIST_Y < SHOULDER_Y
     │    - (lower Y = higher on screen = raised arm)
     │    - Only if keypoint confidence > 0.3
     │    - Indicates: punching, grabbing, throwing pose
     │
     ├─── _score_fall(persons) → s_fall [0,1]
     │    - bbox aspect ratio = width/height
     │    - Normal standing person: aspect ~0.3–0.6
     │    - Fallen person: aspect > 1.5 (wide horizontal bbox)
     │    - score = clamp((aspect - 1.5) / 1.5, 0, 1)
     │
     └─── _score_motion(frame_gray, persons) → s_motion [0,1]
          - Dense optical flow (Farneback method)
          - cv2.calcOpticalFlowFarneback(prev_gray, curr_gray)
          - Compute magnitude of flow vectors
          - Mask restricted to person bounding box regions
          - score = mean(magnitude_in_person_regions) / 15.0

     ▼
WEIGHTED BLEND:
blended = 0.35×s_prox + 0.25×s_motion + 0.20×s_arm + 0.20×s_fall

PROXIMITY-MOTION BOOST:
if s_prox > 0.3 AND s_motion > 0.3:
    blended = min(blended × 1.4, 1.0)   ← fights have both high

TEMPORAL SMOOTHING:
_score_history.append(blended)          ← deque(maxlen=history_len)
smoothed = mean(_score_history)         ← average over recent frames

THREAT CLASSIFICATION:
if smoothed >= 0.55 (confidence_threshold):
    if s_fall > 0.5       → threat_type = "fall_detected"
    elif s_prox>0.3 AND s_motion>0.3 → "fighting"
    elif s_arm > 0.4      → "aggressive_behavior"
    else                  → "pushing"
```

### 4.5 IoU-Based Object Tracker

```python
# Simplified tracking logic (ObjectTracker.update)
for new_detection in detections:
    best_match_id = argmax(IoU(new_detection.bbox, tracked.bbox)
                           for tracked in active_tracks)
    if IoU > threshold:
        active_tracks[best_match_id].update(new_detection)
    else:
        new_track = TrackedObject(
            track_id=next_id++,
            bbox=new_detection.bbox,
            class_name=new_detection.class_name,
            timestamp=now()
        )
        active_tracks.append(new_track)

# Remove stale tracks
for track in active_tracks:
    if now() - track.last_seen > max_age:
        lost_tracks.append(track)
```

### 4.6 Alert System

```
Left-Behind Alert Triggered:
  ├── Log entry with: track_id, class_name, location, duration, timestamp
  ├── HTTP webhook to Laravel (VideoThreatController.detectObjects)
  ├── Laravel logs: Log::warning('Left-behind objects detected', [count])
  ├── Optional: SMS/email via alert_system.py
  └── Frame snapshot saved with bounding box overlay

Threat Alert Triggered:
  ├── Log::warning('Threat detected', [threat_type, confidence, timestamp])
  ├── Real-time dashboard notification (Laravel)
  └── Security staff notification
```

### 4.7 Heuristic Score Weights Rationale

| Score | Weight | Reason |
|---|---|---|
| Proximity (s_prox) | 0.35 (highest) | Physical closeness is the strongest fight indicator |
| Motion (s_motion) | 0.25 | High movement distinguishes active fights from standing |
| Arm Raise (s_arm) | 0.20 | Punching/grabbing gesture — good at detecting aggression |
| Fall (s_fall) | 0.20 | Aspect ratio change reliably detects falls/knockdowns |

**Fight Boost:** When BOTH proximity AND motion exceed 0.3, the blended score is multiplied by 1.4 (capped at 1.0), since this combination is the most reliable fight signature.

---

## 5. CONFIGURATION (config.yaml)

```yaml
object_detection:
  confidence_threshold: 0.5          # YOLO detection confidence
  min_object_size: 50                 # Minimum pixel area to track
  iou_threshold: 0.5                  # IoU for object matching

tracking:
  left_behind_threshold_minutes: 60  # Minutes to flag as left-behind
  movement_threshold_pixels: 10      # Max movement to be "stationary"
  max_frames_without_detection: 30   # Remove stale tracks

threat_detection:
  confidence_threshold: 0.55         # Threat detection threshold
  clip_length: 16                    # Temporal smoothing window

camera:
  source: 0                          # 0=webcam, "rtsp://..." for IP cam
  fps: 30
  resolution: [640, 480]
```

---

## 6. ESP32-CAM INTEGRATION

The `firmware/esp32_cam/` directory contains Arduino firmware that:
- Captures JPEG frames via the ESP32-CAM module
- Streams frames over HTTP to the Flask API endpoint
- Configurable resolution (QVGA to UXGA), compression, and FPS
- WiFi-connected, making it a low-cost wireless camera solution
- Multiple ESP32-CAM units can cover multiple rooms simultaneously

---

## 7. LARAVEL INTEGRATION

```
Frontend (Browser/Dashboard)
     │ base64-encoded frame
     ▼
POST /admin/video-threat/detect-objects
     │
     ▼
VideoThreatController::detectObjects()
     │ Validates: frame (required|string)
     ▼
Http::timeout(30)->post("{apiBaseUrl}/api/video/detect-objects", [
    'frame' => $request->frame
])
     │
     ▼
If left_behind_count > 0:
     Log::warning('Left-behind objects detected', [
         'count' => $result['left_behind_count'],
         'timestamp' => now()->toIso8601String()
     ])
     │
     ▼
Return JSON: {
    success, detections, left_behind_objects, left_behind_count,
    threat_result, people_count, processing_time
}
```

---

## 8. SYSTEM FLOW DIAGRAM

```
CAMERA STREAM
     │  JPEG / BGR frame
     ▼
Flask API receives frame (/api/video/detect-objects)
     │
     ├──────────────────────────────────────────────────────┐
     │  LEFT-BEHIND PIPELINE                                │  THREAT PIPELINE
     ▼                                                      ▼
ObjectDetector.detect(frame)                  ThreatDetector.detect(frame)
     │ YOLOv8n + best.pt dual model                │ YOLOv8n-pose (17 keypoints)
     │ Filter by size                              │ Compute 4 heuristic scores
     ▼                                             │ Weighted blend + smoothing
ObjectTracker.update(detections)                  │ Classify: fight/fall/aggression
     │ IoU matching → update/create tracks         │
     │ Movement distance calculation               │
     │ Stationary state machine                    │
     │ 60-min left-behind check                   │
     ▼                                             │
get_left_behind_objects()                         │
     │ → [TrackedObject with is_left_behind=True]  │
     │                                             │
     └─────────────────────┬───────────────────────┘
                           │ Combined JSON response
                           ▼
Flask returns to Laravel VideoThreatController
     │
     ├── Log::warning if threats/left-behind detected
     ├── Dashboard real-time update
     └── Security staff notifications
```

---

## 9. RESEARCH PANEL — QUESTION & ANSWER (Q&A)

---

**Q1. What is the fundamental approach used for threat detection in this video system?**

> **A:** The system uses a **pose estimation + heuristic scoring approach** rather than a supervised classifier. YOLOv8n-pose detects 17 COCO skeleton keypoints per person in each frame. Four heuristic scores (proximity, arm-raise, motion, fall) are computed from these keypoints and optical flow, blended with fixed weights, and temporally smoothed. This approach requires NO custom training dataset and works on CPU, making it deployable in resource-constrained school environments.

---

**Q2. What is YOLOv8 and why is it chosen for both object detection and pose estimation?**

> **A:** YOLO (You Only Look Once) is a single-stage real-time object detector by Ultralytics. YOLOv8 is the 8th generation, offering improved accuracy and speed over predecessors. It was chosen because: (1) It runs in real-time on both GPU and CPU, (2) YOLOv8n (nano) variant is only ~6MB, suitable for edge devices, (3) The same framework provides object detection (yolov8n.pt) and pose estimation (yolov8n-pose.pt) with 17 COCO keypoints, and (4) It supports custom training on school-specific object datasets.

---

**Q3. What are the 17 COCO keypoints used for pose estimation?**

> **A:** COCO defines 17 body keypoints: Nose, Left/Right Eye, Left/Right Ear, Left/Right Shoulder, Left/Right Elbow, Left/Right Wrist, Left/Right Hip, Left/Right Knee, Left/Right Ankle. The threat detector specifically uses: LEFT_SHOULDER (index 5), RIGHT_SHOULDER (6), LEFT_WRIST (9), RIGHT_WRIST (10) for arm-raise detection; bounding box dimensions for fall detection; and full body bounding box overlap for proximity scoring.

---

**Q4. How does the proximity score detect physical altercations?**

> **A:** The proximity score computes **Intersection over Union (IoU)** between every pair of person bounding boxes in the frame. When two people are physically close or overlapping (as in a fight), their bounding boxes overlap significantly. The score formula normalizes the IoU: `score = clamp((max_IoU - 0.1) / 0.5, 0, 1)`. The 0.1 offset removes false positives from people simply walking near each other. High IoU (0.3–0.6+) means the people are in direct physical contact.

---

**Q5. How does optical flow motion detection work?**

> **A:** OpenCV's `calcOpticalFlowFarneback()` computes dense optical flow between consecutive grayscale frames — it estimates the motion vector (dx, dy) for every pixel. The magnitude of these vectors (`cv2.cartToPolar`) indicates how fast each pixel moved. A mask is applied restricting analysis to the bounding box regions of detected persons. The mean magnitude in person regions (normalized by 15.0) gives the motion score. High motion in person regions during high proximity = likely fighting.

---

**Q6. Why is temporal smoothing applied to the threat scores?**

> **A:** Single-frame detections are unreliable due to pose estimation errors, occlusion, lighting changes, and transient body positions. By maintaining a `_score_history` deque (default 16 frames) and averaging scores over this window, the system only triggers when threatening behavior is consistently present across multiple frames. This significantly reduces false positives from momentary poses (e.g., a student reaching across a desk) while still responding quickly to genuine sustained threats.

---

**Q7. What is the fall detection mechanism and why use bounding box aspect ratio?**

> **A:** A standing person has a tall, narrow bounding box (aspect ratio width/height ≈ 0.3–0.6). When a person falls to the ground, their body becomes horizontal, making the bounding box wide and short (aspect ratio > 1.5). The fall score formula: `score = clamp((aspect - 1.5) / 1.5, 0, 1)`. This is a simple, computationally efficient heuristic that works without any training data. Medical emergencies (fainting, seizures) are also captured by this mechanism.

---

**Q8. How does the arm-raise score detect aggressive posture?**

> **A:** Using YOLOv8-pose keypoints: the system checks if the wrist Y-coordinate is LESS than the shoulder Y-coordinate for each arm (in image coordinates, lower Y = higher position). The score formula: `score += min((shoulder_y - wrist_y) / shoulder_y × 3, 1.0)`. This fires when a person has raised their arms above shoulder level — the punching, grabbing, or throwing motion. Only keypoints with confidence > 0.3 are used to avoid using poorly-estimated keypoints.

---

**Q9. How does the dual-model object detection work for left-behind objects?**

> **A:** The `ObjectDetector` runs **two YOLO models simultaneously**: (1) A **custom-trained best.pt** model specifically trained on school items (Pen, Tas-Ransel/Backpack, Laptop, Water-bottle, Umbrella, Sports equipment) — often from datasets with multilingual class names, (2) **yolov8n.pt** (COCO baseline) detecting general objects (Book, Cell-phone, Backpack, Bottle, Keyboard). `SCHOOL_CLASS_ALIASES` normalizes all class names to friendly school labels. Together they cover a comprehensive range of school items without full retraining.

---

**Q10. How is the "left-behind" status determined for an object?**

> **A:** A `TrackedObject` goes through a state machine: (1) Initially tracked with position_history, (2) `update_stationary_status()` checks if movement distance over last 10 frames is < 10 pixels → `is_stationary = True`, records `stationary_since`, (3) `check_left_behind()` computes `timedelta(now - stationary_since)`: if ≥ 60 minutes (configurable) AND object is not a "person" → `is_left_behind = True`, (4) An alert is sent exactly ONCE per object (using `alert_sent` flag). Moving again resets the stationary state.

---

**Q11. How does the IoU-based tracker handle multiple objects of the same class?**

> **A:** The `ObjectTracker` maintains a dictionary of `TrackedObject` instances keyed by `track_id`. For each new detection in a frame: it computes IoU between the new bbox and all existing tracked bboxes of the same class. If IoU > threshold (default 0.5), the new detection is assigned to the closest existing track (update its bbox, confidence, timestamp, position history). If no match exceeds the threshold, a new track is created with a new ID. Tracks unseen for `max_frames_without_detection` frames are removed.

---

**Q12. How does the system ensure that a "person" is never flagged as a left-behind object?**

> **A:** In `TrackedObject.check_left_behind()`, the very first check is: `if self.class_name.lower() == 'person': return False`. This hard-coded guard ensures that even if a person stands still for more than 60 minutes (e.g., a security guard at their post, or a student reading quietly), they will NEVER be flagged as a "left-behind object." Only non-person objects trigger this alert.

---

**Q13. How is the system integrated with the Laravel web application?**

> **A:** Laravel's `VideoThreatController` handles two endpoints: `/status` (GET — checks if the Flask API is alive) and `/detectObjects` (POST — sends a base64-encoded frame and receives detection results). The frontend captures frames from a video stream, encodes them as base64 strings, and POSTs them to the Laravel route. Laravel forwards these to the Flask API and returns the combined JSON response (object detections + left-behind count + threat result) to the dashboard in real time.

---

**Q14. What is Farneback optical flow and what are its advantages for this application?**

> **A:** Farneback dense optical flow (Gunnar Farneback, 2003) computes motion vectors for **every pixel** in the frame by fitting quadratic polynomials to pixel neighborhoods and tracking their displacement between frames. Advantages: (1) Dense — captures motion everywhere, not just feature points; (2) Works on CPU in real time for school-typical resolution (640×480); (3) No training required; (4) Excellent at detecting subtle vs. violent motion differences within person regions. Parameters: pyramid_scale=0.5, levels=3, win_size=15, iterations=3, poly_n=5.

---

**Q15. What cameras are supported and how does the system handle different camera sources?**

> **A:** The `config.yaml` `camera.source` parameter accepts: (1) `0` — USB/built-in webcam (OpenCV default), (2) RTSP stream URLs (e.g., `rtsp://192.168.1.100:554/stream`) for IP cameras, (3) HTTP MJPEG streams from ESP32-CAM (e.g., `http://192.168.1.X:81/stream`). The `main.py` standalone mode uses `cv2.VideoCapture(source)`. The Flask API `/detect-objects` endpoint accepts base64-encoded frames, making it camera-agnostic — any source that can deliver JPEG/PNG frames can be integrated.

---

**Q16. Why is the proximity-motion BOOST (×1.4) applied in the threat blending formula?**

> **A:** Fighting exhibits two simultaneous signatures: people are CLOSE (high s_prox) AND moving rapidly relative to each other (high s_motion). A simple weighted sum might still produce a borderline score when both are moderate (e.g., 0.35 + 0.3 = moderate blended). The ×1.4 boost — applied ONLY when BOTH exceed 0.3 — amplifies this specific co-occurrence pattern. This makes the system more sensitive to the "close + moving" fight signature while not inflating scores for scenarios with only one indicator (e.g., people close but stationary, or high motion but spread out).

---

**Q17. What happens if only one person is in the camera frame?**

> **A:** With only one person: (1) `_score_proximity()` returns 0.0 immediately (requires ≥ 2 persons), (2) `_score_arm_raise()` can still detect aggressive arm positions, (3) `_score_fall()` can still detect a fallen aspect ratio, (4) `_score_motion()` can still detect violent self-movement. With proximity=0, the blended score maximum is: `0 + 0.25×motion + 0.20×arm + 0.20×fall = max 0.65`, which can still exceed the 0.55 threshold for single-person threats (fall, aggressive motion), though fighting requires at least 2 persons.

---

**Q18. How is the system tested and validated?**

> **A:** The system includes multiple test scripts: `test_detector_quick.py` (quick detector initialization test), `validate_workflow.py` (end-to-end pipeline validation), `check_system.py` (dependency and model availability check), `quick_test.py` (frame processing benchmark), `test_main_initialization.py` (integration test). The `Documentations/SYSTEM_CHECK_REPORT.md` and `VERIFICATION_COMPLETE.md` document verified test outcomes, and `COMPLETE_STATUS_REPORT.md` provides performance benchmarks across different hardware configurations.

---

*End of Document 3*

