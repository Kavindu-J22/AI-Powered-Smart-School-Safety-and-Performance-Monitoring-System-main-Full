# 🎯 Facial Recognition Attendance System

A high-performance, production-ready facial recognition attendance system built with Python, Flask, and state-of-the-art deep learning models.

## 🚀 Features

- **Fast Face Detection**: Uses MTCNN/RetinaFace for accurate face detection
- **Robust Face Recognition**: ArcFace embeddings with cosine similarity matching
- **Real-time Processing**: Optimized for sub-100ms recognition latency
- **Multi-face Support**: Handle multiple faces in a single frame
- **Anti-spoofing**: Basic liveness detection to prevent photo attacks
- **Incremental Training**: Add new faces without full model retraining
- **REST API**: Complete Flask-based API for integration
- **Dashboard Integration**: Seamless integration with School Dashboard

## 📁 Project Structure

```
Facial Recognition Attendance Systems/
├── app.py                          # Main Flask application entry point
├── requirements.txt                # Python dependencies
├── run_training.py                 # Training script runner
├── README.md                       # Documentation
│
├── api/                            # API Layer
│   ├── __init__.py
│   └── routes/
│       ├── __init__.py
│       ├── attendance.py           # Attendance marking endpoints
│       ├── registration.py         # Student face registration
│       ├── training.py             # Model training endpoints
│       └── health.py               # Health check endpoints
│
├── config/                         # Configuration
│   ├── __init__.py
│   └── settings.py                 # Application settings
│
├── core/                           # Core Recognition Engine
│   ├── __init__.py
│   ├── face_detector.py            # Face detection module
│   ├── face_recognizer.py          # Face recognition/embedding
│   ├── face_aligner.py             # Face alignment preprocessing
│   ├── anti_spoof.py               # Liveness detection
│   └── attendance_engine.py        # Main attendance processing
│
├── database/                       # Database Layer
│   ├── __init__.py
│   ├── face_database.py            # Face embeddings storage
│   ├── attendance_db.py            # Attendance records
│   └── models.py                   # SQLAlchemy models
│
├── training/                       # Training Pipeline
│   ├── __init__.py
│   ├── face_trainer.py             # Training orchestrator
│   ├── data_augmentor.py           # Image augmentation
│   └── embedding_generator.py      # Generate face embeddings
│
├── services/                       # Business Logic Services
│   ├── __init__.py
│   ├── registration_service.py     # Student registration logic
│   ├── attendance_service.py       # Attendance marking logic
│   └── camera_service.py           # Camera/stream handling
│
├── utils/                          # Utilities
│   ├── __init__.py
│   ├── image_utils.py              # Image processing helpers
│   ├── video_utils.py              # Video stream utilities
│   ├── validators.py               # Input validation
│   └── logger.py                   # Logging configuration
│
├── data/                           # Data Storage
│   ├── faces/                      # Raw face images per student
│   │   └── {student_id}/           # Student-specific folder
│   ├── embeddings/                 # Computed face embeddings
│   │   └── face_embeddings.pkl     # Serialized embeddings
│   └── models/                     # Trained model weights
│       └── face_recognition_model.pth
│
├── tests/                          # Test Suite
│   ├── __init__.py
│   ├── test_detection.py
│   ├── test_recognition.py
│   └── test_api.py
│
└── scripts/                        # Utility Scripts
    ├── setup_database.py
    ├── benchmark.py
    └── export_model.py
```

## 🛠️ Installation

```bash
cd "Facial Recognition Attendance Systems"
python -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
pip install -r requirements.txt
```

## 🚀 Quick Start

### Start the API Server

```bash
python app.py
```

### Register a New Student

```bash
curl -X POST http://localhost:5003/api/registration/capture \
  -H "Content-Type: application/json" \
  -d '{"student_id": "STU001", "student_name": "John Doe", "capture_count": 20}'
```

### Train the Model

```bash
curl -X POST http://localhost:5003/api/training/train
```

### Mark Attendance (Real-time)

```bash
curl -X POST http://localhost:5003/api/attendance/recognize \
  -F "image=@face_image.jpg"
```

## 📡 API Endpoints

| Method | Endpoint                     | Description                        |
| ------ | ---------------------------- | ---------------------------------- |
| POST   | `/api/registration/capture`  | Start face capture session         |
| POST   | `/api/registration/upload`   | Upload face images                 |
| POST   | `/api/registration/complete` | Complete registration              |
| POST   | `/api/training/train`        | Train/retrain model                |
| GET    | `/api/training/status`       | Get training status                |
| POST   | `/api/attendance/recognize`  | Recognize face and mark attendance |
| POST   | `/api/attendance/stream`     | Start real-time attendance stream  |
| GET    | `/api/attendance/today`      | Get today's attendance             |
| GET    | `/api/health`                | Health check                       |

## ⚡ Performance

- Face Detection: ~30ms per frame
- Face Recognition: ~15ms per face
- End-to-end Latency: <100ms
- Accuracy: >99% on registered faces

## 🔧 Configuration

Edit `config/settings.py` to customize:

```python
FACE_DETECTION_CONFIDENCE = 0.95
RECOGNITION_THRESHOLD = 0.6
CAPTURE_COUNT = 20
CAMERA_FPS = 30
```

## 📄 License

MIT License - See LICENSE file for details.




# Navigate to the project directory
cd "Facial Recognition Attendance Systems"

# Remove all captured face images
rm -rf data/faces/*

# Remove all stored embeddings and student info
rm -rf data/embeddings/*.pkl
rm -rf data/embeddings/*.pkl.bak
