# Setup & Installation Guide

This guide provides step-by-step instructions for setting up the Student Performance Prediction system.

## 1. System Requirements

- **Python**: 3.9 through 3.11.
- **Operating System**: Linux, macOS, or Windows 10+.
- **Memory**: 4GB+ RAM.

## 2. Installation Steps

### Step 1: Environment Setup

We recommend using a virtual environment to manage dependencies:

```bash
# Enter the project directory
cd student-performance-prediction-model

# Create virtual environment
python -m venv venv

# Activate it
source venv/bin/activate  # macOS/Linux
# OR
.\venv\Scripts\activate   # Windows
```

### Step 2: Install Dependencies

```bash
pip install -r requirements.txt
```

### Step 3: Train the Model

The system comes with a default dataset. Always train the model once after installation to ensure all artifacts (scalers, encoders) are generated for your specific environment.

```bash
python src/model_trainer.py
```

## 3. Running the Service

### Start the API Server

Use the provided shell script or run the app directly:

```bash
# Using script
./start_api.sh

# OR direct execution
python api/app.py
```

The server will start on `http://localhost:5001` by default.

## 4. Integration with Laravel

1. Ensure the Python API is running.
2. Update the Laravel `.env` file with the API URL:
   ```env
   PERFORMANCE_API_URL=http://localhost:5001
   ```
3. Use the `PredictionService` in the Laravel application to fetch forecasts during student evaluation.

## 5. Maintenance

- **Logs**: API logs are written to the console. Redirect to a file if needed.
- **Retraining**: It is recommended to retrain the model every semester using the `src/model_trainer.py` script once new student data is available.
