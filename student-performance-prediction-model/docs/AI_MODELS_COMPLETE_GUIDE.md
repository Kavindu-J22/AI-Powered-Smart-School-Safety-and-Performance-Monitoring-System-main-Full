# AI Models - Complete Guide

This guide covers everything you need to know about running the AI prediction models.

---

## 📋 Table of Contents

1. [Quick Start](#quick-start)
2. [Starting APIs](#starting-apis)
3. [Stopping APIs](#stopping-apis)
4. [Manual Operation](#manual-operation)
5. [Training Models from Scratch](#training-models-from-scratch)
6. [Troubleshooting](#troubleshooting)

---

## 🚀 Quick Start

### Prerequisites

- Python 3.8+
- Virtual environments set up
- Laravel dashboard configured

### Start Everything

```bash
# 1. Start both APIs
./start_both_apis.sh

# 2. In a new terminal, start Laravel
cd "Smart-School-Safety-and-Performance-Monitoring-System Dashboard"
php artisan serve
```

### Stop Everything

```bash
# Stop Laravel: Press Ctrl+C in Laravel terminal

# Stop APIs
./stop_both_apis.sh
```

---

## 🎯 Starting APIs

### Option 1: Start Both APIs Together (Recommended)

```bash
# Make executable (first time only)
chmod +x start_both_apis.sh

# Start both APIs
./start_both_apis.sh
```

**What happens:**

- Performance Prediction API starts on port **5002**
- Seating Arrangement API starts on port **5001**
- Both run in background
- Logs saved to `/tmp/performance_api.log` and `/tmp/seating_api.log`

**Expected Output:**

```
============================================================
Starting AI Model APIs
============================================================
Cleaning up existing processes...

Starting Performance Prediction API on port 5002...
Performance API started with PID: 12345

Starting Seating Arrangement API on port 5001...
Seating API started with PID: 12346

============================================================
API Status Check
============================================================
✓ Performance Prediction API is running on http://localhost:5002
✓ Seating Arrangement API is running on http://localhost:5001
============================================================
```

### Option 2: Start APIs Manually

#### Performance Prediction API

```bash
# Navigate to model directory
cd student-performance-prediction-model

# Activate virtual environment
source venv/bin/activate

# Start API
cd api
python app.py
```

**Port:** 5002  
**URL:** http://localhost:5002

#### Seating Arrangement API

```bash
# Navigate to model directory
cd student-seating-arrangement-model

# Activate virtual environment
source venv/bin/activate

# Start API
cd api
python app.py
```

**Port:** 5001  
**URL:** http://localhost:5001

### Verify APIs are Running

```bash
# Check health endpoints
curl http://localhost:5002/health  # Performance API
curl http://localhost:5001/health  # Seating API

# Or check running processes
lsof -ti:5001,5002
```

---

## 🛑 Stopping APIs

### Option 1: Use Stop Script (Easiest)

```bash
# Make executable (first time only)
chmod +x stop_both_apis.sh

# Stop both APIs
./stop_both_apis.sh
```

**Expected Output:**

```
============================================================
Stopping AI Model APIs
============================================================
Looking for running API processes...
Found processes: 12345 12346
Stopping APIs...

============================================================
✅ All APIs stopped successfully!
============================================================
```

### Option 2: Manual Stop by Port

```bash
# Find processes on ports 5001 and 5002
lsof -ti:5001,5002

# Kill the processes
lsof -ti:5001,5002 | xargs kill -9
```

### Option 3: Stop by PID

If you know the process IDs from startup:

```bash
# Kill specific PIDs (shown in start_both_apis.sh output)
kill 12345 12346
```

### Option 4: Stop from Running Terminal

If APIs are running in foreground (manual start):

```bash
# Press Ctrl+C in the terminal where API is running
```

---

## 🔧 Manual Operation

### Run Each API Separately

#### Performance Prediction API Only

```bash
cd student-performance-prediction-model
source venv/bin/activate
cd api
python app.py
```

Keep this terminal open. API runs on: http://localhost:5002

#### Seating Arrangement API Only

```bash
cd student-seating-arrangement-model
source venv/bin/activate
cd api
python app.py
```

Keep this terminal open. API runs on: http://localhost:5001

### Using Different Ports

Edit the config files before starting:

**Performance Prediction:**

```bash
# Edit: student-performance-prediction-model/config/config.py
API_PORT = 5002  # Change to your desired port
```

**Seating Arrangement:**

```bash
# Edit: student-seating-arrangement-model/config/config.py
API_PORT = 5001  # Change to your desired port
```

Then update Laravel `.env`:

```env
PERFORMANCE_PREDICTION_API_URL=http://localhost:YOUR_PORT
SEATING_ARRANGEMENT_API_URL=http://localhost:YOUR_PORT
```

---

## 🎓 Training Models from Scratch

### Performance Prediction Model

#### Step 1: Setup Environment

```bash
cd student-performance-prediction-model

# First time: Run setup
chmod +x setup.sh
./setup.sh
```

**What setup does:**

1. Creates virtual environment
2. Installs dependencies
3. Preprocesses data
4. Trains model
5. Tests predictions

#### Step 2: Manual Training (Optional)

If you want to train manually or retrain:

```bash
# Activate environment
source venv/bin/activate

# Step 1: Preprocess data
python src/data_preprocessing.py

# Step 2: Train model
python src/model_trainer.py

# Step 3: Test predictions
python src/predictor.py
```

**Training Process:**

- Loads data from `dataset/student_performance_updated_1000.csv`
- Cleans and prepares data
- Creates subject-wise records
- Trains Linear Regression model
- Saves models to `models/` directory
  - `performance_predictor.pkl`
  - `scaler.pkl`
  - `label_encoder.pkl`

#### Step 3: Start API

```bash
cd api
python app.py
```

### Seating Arrangement Model

#### Step 1: Setup Environment

```bash
cd student-seating-arrangement-model

# First time: Run setup
chmod +x setup.sh
./setup.sh
```

**What setup does:**

1. Creates virtual environment
2. Installs dependencies
3. Creates necessary directories
4. Tests the seating algorithm

#### Step 2: No Training Required! 🎉

The seating arrangement uses a **rule-based algorithm** (high-low pairing), so no ML training is needed. It's ready to use immediately after setup!

#### Step 3: Start API

```bash
cd api
python app.py
```

### Understanding the Models

#### Performance Prediction (Machine Learning)

**Type:** Linear Regression  
**Input Features:**

- Student age
- Grade level
- Subject
- Current marks
- Attendance percentage

**Output:** Predicted future performance

**When to Retrain:**

- When you have new student data
- When accuracy decreases
- After significant curriculum changes
- Recommended: Every semester

**How to Retrain:**

```bash
cd student-performance-prediction-model
source venv/bin/activate
python src/model_trainer.py  # Uses latest data from dataset/
```

#### Seating Arrangement (Rule-Based)

**Type:** Algorithmic (no ML training)  
**Algorithm:** High-Low Pairing Strategy

**How it works:**

1. Sort students by average marks
2. Pair high performers with low performers
3. Arrange in zigzag pattern
4. Map to classroom grid

**No training needed** - just call the API with student data!

---

## 🐛 Troubleshooting

### APIs Won't Start

**Problem:** `venv/bin/activate: No such file or directory`

**Solution:**

```bash
# Run setup first
cd student-performance-prediction-model
./setup.sh

cd student-seating-arrangement-model
./setup.sh
```

### Port Already in Use

**Problem:** `Address already in use`

**Solution:**

```bash
# Find and kill process using the port
lsof -ti:5001,5002 | xargs kill -9

# Then start again
./start_both_apis.sh
```

### APIs Not Responding

**Check if running:**

```bash
lsof -ti:5001,5002
```

**Check logs:**

```bash
tail -f /tmp/performance_api.log
tail -f /tmp/seating_api.log
```

**Restart:**

```bash
./stop_both_apis.sh
./start_both_apis.sh
```

### Module Not Found Errors

**Solution:**

```bash
cd student-performance-prediction-model  # or student-seating-arrangement-model
source venv/bin/activate
pip install -r requirements.txt
```

### Laravel Not Connecting to APIs

**Check API URLs in `.env`:**

```env
PERFORMANCE_PREDICTION_API_URL=http://localhost:5002
SEATING_ARRANGEMENT_API_URL=http://localhost:5001
```

**Clear Laravel cache:**

```bash
cd "Smart-School-Safety-and-Performance-Monitoring-System Dashboard"
php artisan config:clear
php artisan cache:clear
```

### Model Training Fails

**Problem:** Data file not found

**Solution:**

```bash
# Ensure dataset exists
ls student-performance-prediction-model/dataset/

# If missing, add your CSV file to dataset/ folder
# File should be named: student_performance_updated_1000.csv
```

---

## 📊 Testing the System

### Test API Health

```bash
# Performance Prediction API
curl http://localhost:5002/health

# Expected:
# {
#   "service": "Student Performance Prediction API",
#   "status": "healthy",
#   "version": "1.0.0"
# }

# Seating Arrangement API
curl http://localhost:5001/health

# Expected:
# {
#   "service": "Seating Arrangement API",
#   "status": "healthy",
#   "version": "1.0.0"
# }
```

### Test Prediction

```bash
cd student-performance-prediction-model
source venv/bin/activate
python test_system.py
```

### Test Seating Arrangement

```bash
cd student-seating-arrangement-model
source venv/bin/activate
python test_system.py
```

---

## 📁 File Structure Reference

```
AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System/
│
├── start_both_apis.sh          # Start both APIs
├── stop_both_apis.sh           # Stop both APIs
│
├── student-performance-prediction-model/
│   ├── setup.sh                # Setup and train
│   ├── start_api.sh            # Start this API only
│   ├── test_system.py          # Test predictions
│   ├── requirements.txt        # Python dependencies
│   ├── venv/                   # Virtual environment
│   ├── api/
│   │   └── app.py             # Flask API (port 5002)
│   ├── src/
│   │   ├── data_preprocessing.py
│   │   ├── model_trainer.py
│   │   └── predictor.py
│   ├── models/                 # Trained models saved here
│   └── dataset/                # Training data
│
├── student-seating-arrangement-model/
│   ├── setup.sh                # Setup environment
│   ├── start_api.sh            # Start this API only
│   ├── test_system.py          # Test seating algorithm
│   ├── requirements.txt        # Python dependencies
│   ├── venv/                   # Virtual environment
│   ├── api/
│   │   └── app.py             # Flask API (port 5001)
│   ├── src/
│   │   ├── seating_generator.py
│   │   └── utils.py
│   └── config/
│       └── config.py
│
└── Smart-School-Safety-and-Performance-Monitoring-System Dashboard/
    └── .env                    # API URLs configured here
```

---

## 🔄 Complete Workflow

### First Time Setup

```bash
# 1. Setup Performance Prediction Model
cd student-performance-prediction-model
./setup.sh

# 2. Setup Seating Arrangement Model
cd ../student-seating-arrangement-model
./setup.sh

# 3. Configure Laravel
cd "../Smart-School-Safety-and-Performance-Monitoring-System Dashboard"
# Add to .env:
# PERFORMANCE_PREDICTION_API_URL=http://localhost:5002
# SEATING_ARRANGEMENT_API_URL=http://localhost:5001
```

### Daily Development

```bash
# 1. Start APIs (in project root)
./start_both_apis.sh

# 2. Start Laravel
cd "Smart-School-Safety-and-Performance-Monitoring-System Dashboard"
php artisan serve

# 3. Work on your project...

# 4. When done:
# - Press Ctrl+C in Laravel terminal
./stop_both_apis.sh
```

### Retraining Models

```bash
# Only for Performance Prediction (when you have new data)
cd student-performance-prediction-model
source venv/bin/activate

# Update dataset file first (dataset/student_performance_updated_1000.csv)
# Then retrain:
python src/data_preprocessing.py
python src/model_trainer.py

# Test the new model
python src/predictor.py

# Restart API to use new model
cd ../
./stop_both_apis.sh
./start_both_apis.sh
```

---

## 📞 Quick Commands Cheat Sheet

```bash
# Start everything
./start_both_apis.sh
cd "Smart-School-Safety-and-Performance-Monitoring-System Dashboard" && php artisan serve

# Stop everything
./stop_both_apis.sh

# Check if APIs running
lsof -ti:5001,5002

# Check API health
curl http://localhost:5002/health && curl http://localhost:5001/health

# View API logs
tail -f /tmp/performance_api.log
tail -f /tmp/seating_api.log

# Retrain performance model
cd student-performance-prediction-model && source venv/bin/activate && python src/model_trainer.py

# Test models
cd student-performance-prediction-model && python test_system.py
cd student-seating-arrangement-model && python test_system.py

# Clear Laravel cache
php artisan optimize:clear
```

---

## ✅ Checklist

**Before Starting Work:**

- [ ] Virtual environments exist (`venv/` folders)
- [ ] Models are trained (performance model only)
- [ ] `.env` configured with API URLs
- [ ] Ports 5001, 5002, 8000 are available

**Starting Work:**

- [ ] Run `./start_both_apis.sh`
- [ ] Verify APIs are healthy
- [ ] Start Laravel: `php artisan serve`
- [ ] Test in browser

**Ending Work:**

- [ ] Stop Laravel (Ctrl+C)
- [ ] Run `./stop_both_apis.sh`
- [ ] Verify APIs stopped

---

**Need Help?**

- Check [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md) for basic usage
- Check [API_STATUS_CHECK_UPDATES.md](API_STATUS_CHECK_UPDATES.md) for troubleshooting
- Check individual model README files for detailed info

---

**Last Updated:** January 3, 2026
