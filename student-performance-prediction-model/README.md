# Student Performance Prediction System

A machine learning-based system to predict student performance for each subject based on attendance and marks.

## 📁 Project Structure

```
student-performance-prediction-model/
├── src/                    # Source code
│   ├── data_preprocessing.py   # Data cleaning and preparation
│   ├── model_trainer.py        # Model training logic
│   └── predictor.py           # Prediction engine
├── api/                    # Flask API
│   ├── app.py             # Main API application
│   └── requirements.txt   # API dependencies
├── models/                 # Saved ML models
│   └── (trained models saved here)
├── data/                   # Data files
│   └── cleaned_data.csv   # Processed dataset
├── config/                 # Configuration files
│   └── config.py          # App configuration
├── docs/                   # Documentation
│   └── METHODOLOGY.md     # Technical documentation
└── requirements.txt        # Python dependencies
```

## 🚀 Quick Start

### 1. Install Dependencies

```bash
cd student-performance-prediction-model
pip install -r requirements.txt
```

### 2. Clean Data

```bash
python src/data_preprocessing.py
```

### 3. Train Model

```bash
python src/model_trainer.py
```

### 4. Start API

```bash
cd api
python app.py
```

The API will be available at `http://localhost:5000`

## 📊 API Endpoints

### POST /predict

Predict student performance for all subjects

**Request Body:**

```json
{
  "student_id": 123,
  "age": 15,
  "grade": 10,
  "subjects": [
    {
      "subject_name": "Mathematics",
      "attendance": 85.5,
      "marks": 78.0
    },
    {
      "subject_name": "Science",
      "attendance": 90.0,
      "marks": 82.0
    }
  ]
}
```

**Response:**

```json
{
  "student_id": 123,
  "predictions": [
    {
      "subject": "Mathematics",
      "current_performance": 78.0,
      "predicted_performance": 82.5,
      "prediction_trend": "improving",
      "confidence": 0.89
    },
    {
      "subject": "Science",
      "current_performance": 82.0,
      "predicted_performance": 85.3,
      "prediction_trend": "improving",
      "confidence": 0.92
    }
  ]
}
```

## 🔗 Laravel Integration

The API integrates with your Laravel school management system to fetch student data and display predictions.

See [METHODOLOGY.md](docs/METHODOLOGY.md) for detailed technical documentation.

## 📝 Features

- ✅ Subject-wise performance prediction
- ✅ Handles missing data (0 for missing marks/attendance)
- ✅ Multiple subjects per student
- ✅ RESTful API for easy integration
- ✅ Clean, organized code structure
- ✅ Comprehensive documentation

## 🔧 Requirements

- Python 3.8+
- Flask
- scikit-learn
- pandas
- numpy
- joblib
