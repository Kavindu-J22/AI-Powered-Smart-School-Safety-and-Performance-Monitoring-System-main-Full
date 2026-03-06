# API Reference

The Student Performance Prediction service is exposed via a REST API, typically running on `http://localhost:5001`.

## Authentication

(Note: Replace with your actual authentication header if configured in production.)

- **Header**: `Authorization: Bearer <API_TOKEN>` (Optional)

---

## Endpoints

### 1. Single Student Prediction

Predict performance across multiple subjects for a single student.

- **URL**: `/predict`
- **Method**: `POST`
- **Content-Type**: `application/json`

**Sample Request:**

```json
{
  "student_id": 1001,
  "age": 15,
  "grade": 10,
  "subjects": [
    {
      "subject_name": "Mathematics",
      "attendance": 88.5,
      "marks": 75.0
    },
    {
      "subject_name": "Science",
      "attendance": 92.0,
      "marks": 82.0
    }
  ]
}
```

**Sample Response:**

```json
{
    "student_id": 1001,
    "predictions": [
        {
            "subject": "Mathematics",
            "current_performance": 75.0,
            "predicted_performance": 79.2,
            "confidence_interval": {
                "lower_bound": 71.5,
                "upper_bound": 86.9,
                "confidence_level": 0.95
            },
            "prediction_trend": "improving",
            "performance_category": "Good",
            "recommendation": "Regular practice recommended"
        },
        ...
    ]
}
```

---

### 2. Batch Prediction

Process multiple students in a single request.

- **URL**: `/predict/batch`
- **Method**: `POST`

**Sample Request:**

```json
{
    "students": [
        { "student_id": 1001, "subjects": [...] },
        { "student_id": 1002, "subjects": [...] }
    ]
}
```

---

### 3. Health Check

Monitor service status and loaded model details.

- **URL**: `/health`
- **Method**: `GET`

**Response:**

```json
{
  "status": "healthy",
  "service": "Student Performance Prediction API",
  "version": "2.0.0",
  "model": "RandomForestRegressor"
}
```

---

## Error Handling

Standard HTTP status codes are used:

- `200`: Success
- `400`: Bad Request (Invalid JSON or missing fields)
- `404`: Endpoint not found
- `500`: Internal Server Error (Model failed or data corruption)
