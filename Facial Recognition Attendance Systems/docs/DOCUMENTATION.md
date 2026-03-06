# Facial Recognition Attendance System

Welcome to the documentation for the AI-Powered Smart School Safety and Performance Monitoring System's Attendance Module.

## Overview

This system provides automated, high-speed attendance tracking using facial recognition. It is designed for schools to monitor student movements, ensure safety, and generate real-time performance reports.

## Key Features

- **Real-time Detection**: Multi-face detection at 30+ FPS.
- **Biometric Security**: Anti-spoofing logic to prevent photo/video attacks.
- **High Accuracy**: Deep learning embeddings (FaceNet/ArcFace) for low false-positive rates.
- **Auto-Sync**: Background synchronization with the school's central dashboard.
- **Resilient**: Local SQLite database storage ensuring operation during network outages.

## Documentation Index

1. **[Architecture](file:///Users/tharusha_rashmika/Documents/projects/aleph/reserch/AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System/Facial%20Recognition%20Attendance%20Systems/docs/ARCHITECTURE.md)**: System design and data flow.
2. **[Model Details](file:///Users/tharusha_rashmika/Documents/projects/aleph/reserch/AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System/Facial%20Recognition%20Attendance%20Systems/docs/MODEL_DETAILS.md)**: Deep dive into AI models and backends.
3. **[Setup & Installation](file:///Users/tharusha_rashmika/Documents/projects/aleph/reserch/AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System/Facial%20Recognition%20Attendance%20Systems/docs/SETUP.md)**: Hardware and software requirements.
4. **[API Reference](file:///Users/tharusha_rashmika/Documents/projects/aleph/reserch/AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System/Facial%20Recognition%20Attendance%20Systems/docs/API.md)**: Service interactions and webhooks.

## Quick Start (Developer Mode)

```bash
# Clone and enter the directory
cd "Facial Recognition Attendance Systems"

# Initialize environment
python -m venv venv
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Run the attendance server
python main.py
```

## Maintenance

Periodic cleanup of the `server.log` and database backups are recommended. Student face embeddings can be re-generated via the `RegistrationService` for improved accuracy as students age.
