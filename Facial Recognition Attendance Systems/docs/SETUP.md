# Setup & Installation Guide

This guide covers the necessary steps to deploy the Facial Recognition Attendance System in a school environment.

## Prerequisites

### Hardware Requirements

- **Camera**: High-definition (720p minimum, 1080p recommended) USB or IP Camera.
- **Compute**:
  - CPU: Quad-core 2.5GHz+ (Intel i5/i7 or Ryzen 5/7).
  - GPU: NVIDIA GTX 1050+ (Required for `CUDA` acceleration).
  - RAM: 8GB minimum.

### Software Requirements

- OS: Windows 10+, macOS, or Linux (Ubuntu 20.04+ recommended).
- Python 3.9 through 3.11.
- CUDA Toolkit 11.x (Optional, for GPU acceleration).

## Installation Steps

### 1. Clone the Repository

```bash
git clone <repository-url>
cd AI-Powered-Smart-School-Safety-and-Monitoring/Facial Recognition Attendance Systems
```

### 2. Environment Setup

Create a virtual environment to isolate dependencies:

```bash
python -m venv venv
# On macOS/Linux:
source venv/bin/activate
# On Windows:
venv\Scripts\activate
```

### 3. Install Dependencies

```bash
pip install -r requirements.txt
```

### 4. Database Initialization

Local student metadata and attendance databases are automatically initialized on the first run.

```bash
# To pre-populate or migrate, use:
python database/init_db.py
```

## Configuration

Modify `config.yaml` or use environment variables for custom settings:

| Variable              | Description                           | Default   |
| --------------------- | ------------------------------------- | --------- |
| `DETECTION_BACKEND`   | `mtcnn`, `retinaface`, or `mediapipe` | `mtcnn`   |
| `RECOGNITION_BACKEND` | `facenet` or `arcface`                | `facenet` |
| `DASHBOARD_API_URL`   | URL for central school dashboard      | `None`    |
| `CAMERA_INDEX`        | Device ID / RTSP URL                  | `0`       |

## Troubleshooting

- **Low FPS**: Switch `DETECTION_BACKEND` to `mediapipe`.
- **Face Not Recognized**:
  1. Ensure the student is registered in `data/faces`.
  2. Lower `RECOGNITION_THRESHOLD` in `core/attendance_engine.py`.
  3. Improve lighting conditions.
