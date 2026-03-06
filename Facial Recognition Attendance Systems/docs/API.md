# API & Service Documentation

The system provides several internal services and external notification mechanisms.

## AttendanceService (`services/attendance_service.py`)

Handles high-level business logic for attendance marking.

### Methods

#### `mark_attendance_from_image(image, location)`

- **Input**: NumPy array (BGR), Optional location string.
- **Logic**:
  1. Detects faces.
  2. Validates liveness.
  3. Matches against database.
  4. Applies `cooldown_seconds` (Default: 300) to prevent double-marking.
- **Output**: JSON object with success status and student details.

#### `sync_to_dashboard()`

Sends unsynced local records to the central dashboard API.

---

## Webhook Notifications

The system can send a POST request whenever attendance is marked.

### Payload Example

```json
{
  "event": "attendance_marked",
  "data": {
    "student_id": "STU12345",
    "timestamp": "2026-02-28T11:05:00Z",
    "status": "present",
    "confidence": 0.98
  }
}
```

---

## RegistrationService (`services/registration_service.py`)

Used for enrolling new students into the system.

### Workflow

1. **Capture**: Collect 5-10 high-quality face images from different angles.
2. **Preprocessing**: Detect, Align, and Normalize the faces.
3. **Embedding Generation**: Compute feature vectors and store them in `data/embeddings/`.
4. **Database Entry**: Link Student ID to their new embeddings in SQLite.

---

## Stream Access

The `CameraService` provides an MJPEG stream for remote monitoring:

- **Default Port**: `8080` (Configurable)
- **Path**: `/stream`
- **Output**: Standard `multipart/x-mixed-replace` boundaries.
