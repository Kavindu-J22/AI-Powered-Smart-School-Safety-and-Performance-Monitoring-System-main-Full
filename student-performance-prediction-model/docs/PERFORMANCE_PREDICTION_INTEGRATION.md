# Student Performance Prediction Integration

## Overview

The Student Performance Prediction Model has been successfully integrated into the Laravel dashboard. This system uses AI/ML to predict student performance based on their current marks and attendance data.

## Components Added

### 1. **Database Seeder** (`database/seeders/StudentMarksAndAttendanceSeeder.php`)

- Creates realistic marks data for students across all their enrolled subjects
- Generates attendance records for the last 30 school days
- Includes attendance rates (90% present, 10% absent)
- Data includes semester calculations and grade assignments

### 2. **Service Layer** (`app/Services/PerformancePredictionService.php`)

- `getPrediction($studentId)` - Retrieves prediction from AI model
- `buildStudentData()` - Prepares student data in the format required by the model
- `calculateAttendancePercentage()` - Calculates attendance rates per subject
- `formatPredictionForDisplay()` - Transforms API response for UI display

### 3. **API Controller** (`app/Http/Controllers/Api/PerformancePredictionController.php`)

- `getPrediction($studentId)` - Endpoint to get prediction for single student
- `batchPredictions()` - Get predictions for multiple students at once
- `health()` - Check if prediction API service is running

### 4. **Beautiful UI Component** (`resources/views/admin/pages/management/students/partials/performance_prediction.blade.php`)

- Displays live prediction with confidence intervals
- Summary statistics showing:
  - Total subjects
  - Number of improving subjects
  - Average improvement
  - Model version
- Detailed table view with:
  - Subject name
  - Current performance score
  - Predicted performance score
  - Expected improvement
  - Confidence percentage
  - Trend indicator (improving/declining/stable)
- Individual subject cards with:
  - Performance metrics
  - Confidence range (95% CI)
  - Trend indicators
  - Improvement badges

### 5. **API Routes** (`routes/api.php`)

```
GET  /api/prediction/health              - Check prediction API health
POST /api/students/predictions           - Batch predictions for multiple students
GET  /api/students/{studentId}/prediction - Get prediction for specific student
```

## How It Works

### Data Flow

```
Student View Page (Blade)
           ↓
    Load Page & Fetch Data
           ↓
   JavaScript Fetch Request
           ↓
   Laravel API Endpoint (/api/students/{id}/prediction)
           ↓
   PerformancePredictionService
           ↓
   Collect Student Marks & Attendance
           ↓
   Send to Python ML Model (Port 5002)
           ↓
   Model Returns Predictions with Confidence
           ↓
   Format & Return JSON Response
           ↓
   Display Beautiful UI with Charts/Cards
```

## Installation & Setup

### Step 1: Run Database Seeder

```bash
cd /path/to/laravel/project
php artisan db:seed --class=StudentMarksAndAttendanceSeeder
```

This will:

- Create marks for all active students across their subjects
- Generate 30 days of attendance records
- Automatically calculate grades and percentages

### Step 2: Start the Prediction API

```bash
cd /path/to/student-performance-prediction-model
python3 api/app.py
# or using the startup script from main folder
./start_all_services.sh
```

The API will run on `http://127.0.0.1:5002`

### Step 3: Start Laravel Dashboard

```bash
cd /path/to/laravel/project
php artisan serve
# or
php artisan serve --port=8000
```

### Step 4: View Student Predictions

Navigate to student view page:

```
http://127.0.0.1:8000/admin/management/students/show/[STUDENT_ID]
```

Example: `http://127.0.0.1:8000/admin/management/students/show/53`

## Features

### ✅ Live Predictions

- Predictions load automatically when viewing a student
- No page refresh needed
- Real-time data from the ML model

### ✅ Confidence Intervals

- 95% confidence intervals displayed for each prediction
- Shows range: lower_bound to upper_bound
- Confidence percentage indicator

### ✅ Performance Trends

- **Improving**: Student performance predicted to improve
- **Declining**: Student performance predicted to decline
- **Stable**: Student performance expected to remain stable

### ✅ Beautiful UI

- Responsive design works on desktop and tablet
- Color-coded trends (green=improving, red=declining, blue=stable)
- Summary statistics cards
- Detailed prediction cards for each subject
- Progress bars for confidence levels

### ✅ Error Handling

- Gracefully handles API connection errors
- Shows user-friendly error messages
- Fallback to loading state if API is unavailable

## API Request/Response Format

### Request Format Sent to Python Model

```json
{
  "student_id": 53,
  "age": 15,
  "grade": 10,
  "subjects": [
    {
      "subject_name": "Mathematics",
      "subject_id": 1,
      "marks": 78.5,
      "attendance": 92.3,
      "trend": "improving"
    },
    {
      "subject_name": "English",
      "subject_id": 2,
      "marks": 85.0,
      "attendance": 95.0,
      "trend": "stable"
    }
  ]
}
```

### Response Format from Python Model

```json
{
  "student_id": 53,
  "predictions": [
    {
      "subject": "Mathematics",
      "current_performance": 78.5,
      "predicted_performance": 82.3,
      "confidence": 0.89,
      "prediction_trend": "improving",
      "confidence_interval": {
        "lower_bound": 74.2,
        "upper_bound": 90.8,
        "confidence_level": 0.95
      }
    },
    {
      "subject": "English",
      "current_performance": 85.0,
      "predicted_performance": 87.1,
      "confidence": 0.91,
      "prediction_trend": "stable",
      "confidence_interval": {
        "lower_bound": 82.5,
        "upper_bound": 92.1,
        "confidence_level": 0.95
      }
    }
  ],
  "total_subjects": 2
}
```

## Database Tables Used

### marks

- Stores student marks per subject per term
- Used to get current performance
- Used to calculate trends

### attendance

- Stores daily attendance records
- Attendance date, check-in/check-out times
- Attendance status (present/absent)

## Troubleshooting

### ❌ Predictions Not Loading

**Problem**: "Failed to connect to prediction service"

**Solution**:

1. Ensure prediction API is running on port 5002:
   ```bash
   lsof -i :5002
   ```
2. Start the API if not running:
   ```bash
   cd student-performance-prediction-model
   python3 api/app.py
   ```

### ❌ No Predictions Data Available

**Problem**: Student shows "No prediction data available"

**Solution**:

1. Run the seeder to populate marks:
   ```bash
   php artisan db:seed --class=StudentMarksAndAttendanceSeeder
   ```
2. Check if student has marks in database:
   ```bash
   php artisan tinker
   >>> App\Models\Mark::where('student_id', 53)->count()
   ```

### ❌ API Errors (5xx)

**Problem**: Internal server error from Laravel API

**Solution**:

1. Check Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```
2. Verify service is available:
   ```bash
   curl http://127.0.0.1:5002/health
   ```

### ❌ CORS Errors

**Problem**: Cross-origin request blocked

**Solution**: Already configured in PerformancePredictionService, but verify:

1. Python API has CORS enabled (check `app.py`)
2. Laravel session authentication is working

## Performance Optimization

### Caching Predictions

Future Enhancement: Cache predictions for 1 hour to reduce API calls:

```php
$prediction = Cache::remember(
    "prediction_student_{$studentId}",
    3600,
    fn() => $this->predictionService->getPrediction($studentId)
);
```

### Batch Predictions

For class-level reports, use the batch endpoint:

```javascript
fetch("/api/prediction/batch", {
  method: "POST",
  body: JSON.stringify({
    student_ids: [53, 54, 55, 56, 57],
  }),
});
```

## Security

- All prediction endpoints require authentication via Sanctum
- Only authenticated dashboard users can access predictions
- Student data is not exposed directly; predictions are truncated/rounded

## Files Modified/Created

### New Files

- `database/seeders/StudentMarksAndAttendanceSeeder.php`
- `app/Services/PerformancePredictionService.php`
- `app/Http/Controllers/Api/PerformancePredictionController.php`
- `resources/views/admin/pages/management/students/partials/performance_prediction.blade.php`

### Modified Files

- `routes/api.php` - Added prediction routes
- `resources/views/admin/pages/management/students/view.blade.php` - Added prediction component

## Next Steps

1. **Customization**: Modify `PerformancePredictionService` to include additional features:
   - Subject-specific notes
   - Teacher recommendations
   - Peer comparison

2. **Notifications**: Implement alerts when predictions show declining trends

3. **Reports**: Create class-level prediction reports

4. **Analytics**: Track prediction accuracy over time

5. **Dashboard Widget**: Add prediction summary to main dashboard

## Model Documentation

For details on how the prediction model works:

- See: `/student-performance-prediction-model/docs/AI_MODELS_COMPLETE_GUIDE.md`
- Model Features:
  - RandomForestRegressor
  - 95% Confidence Intervals
  - 5-Fold Cross-Validated
  - One-Hot Encoding for subjects

## Support

For issues or questions:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Python API logs: `tail -f logs/performance.log`
3. Verify both services are running: `./start_all_services.sh`

---

**Integration Date**: March 6, 2026
**Status**: ✅ Live & Production Ready
