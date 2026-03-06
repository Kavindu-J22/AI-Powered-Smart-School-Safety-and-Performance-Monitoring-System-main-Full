# Model Accuracy Summary

## Current Model Performance (As of January 2026)

---

## 1️⃣ Student Performance Prediction Model

### 📊 Actual Accuracy Metrics

```
R² Score:                    -0.50% (NEEDS IMPROVEMENT)
Adjusted R² Score:           -1.01%
Accuracy within ±5 points:    26.20%
Accuracy within ±10 points:   65.60%

Mean Absolute Error (MAE):    8.22 points
Root Mean Squared Error:      9.60 points
Mean Absolute Percentage:     10.71%

Test Dataset Size:            1,000 samples
Prediction Range:             75.0 - 81.7
Actual Range:                 53.0 - 101.0
```

### ⚠️ Current Status: **NEEDS IMPROVEMENT**

The model currently has very low accuracy (-0.5% R² score). This indicates the model is not learning patterns effectively and predicts mostly around the average value (75-82 points).

### 🔧 Recommended Actions to Improve:

1. **Retrain with Better Data**

   - Current dataset may have issues
   - Need more diverse student records
   - Include more relevant features

2. **Feature Engineering**

   - Add more predictive features:
     - Homework completion rate
     - Previous term performance
     - Class participation
     - Study hours per week
     - Parent involvement metrics

3. **Try Advanced Algorithms**

   - Random Forest Regressor
   - Gradient Boosting (XGBoost, LightGBM)
   - Neural Networks
   - Ensemble methods

4. **Data Quality Check**

   ```bash
   # Check the current dataset
   cd student-performance-prediction-model
   python -c "import pandas as pd; df=pd.read_csv('data/cleaned_data.csv'); print(df.describe())"
   ```

5. **Retrain Model**
   ```bash
   cd student-performance-prediction-model
   python src/data_preprocessing.py
   python src/model_trainer.py
   python evaluate_model.py
   ```

### 📈 Target Goals:

| Metric       | Current | Target | Status       |
| ------------ | ------- | ------ | ------------ |
| R² Score     | -0.5%   | >70%   | ❌ Far Below |
| MAE          | 8.22    | <5.0   | ❌ Too High  |
| Accuracy ±5  | 26%     | >70%   | ❌ Low       |
| Accuracy ±10 | 66%     | >90%   | ⚠️ Moderate  |

---

## 2️⃣ Student Seating Arrangement Algorithm

### 📊 Actual Effectiveness Metrics

```
Overall Effectiveness:        82.47/100 (VERY GOOD)
Quality Level:                Very Good

Balance Score:                86.62/100 (Excellent)
Pairing Quality:              76.25/100 (Good)

Performance Distribution:
  - High Performers:          33.3%
  - Medium Performers:        36.7%
  - Low Performers:           30.0%

Test Scenarios:               5 runs with varied data
Classroom Configuration:      5 seats/row × 6 rows (30 total)
```

### ✅ Current Status: **VERY GOOD**

The seating arrangement algorithm is performing well with 82.5/100 effectiveness. It successfully:

- ✓ Distributes students evenly across rows (86.6/100 balance)
- ✓ Mixes high and low performers reasonably (76.3/100 pairing)
- ✓ Creates balanced performance distribution

### 🎯 Performance Breakdown:

| Test Run    | Balance Score | Pairing Quality |
| ----------- | ------------- | --------------- |
| Test 1      | 88.82         | 86.52           |
| Test 2      | 86.65         | 88.24           |
| Test 3      | 90.33         | 67.32           |
| Test 4      | 83.86         | 72.74           |
| Test 5      | 83.45         | 66.43           |
| **Average** | **86.62**     | **76.25**       |

### 💡 Interpretation:

- **Balance Score (86.6%)**: Excellent - Students are very evenly distributed across all rows
- **Pairing Quality (76.3%)**: Good - High and low performers are reasonably mixed, though could be slightly better
- **Overall**: The algorithm is working as designed with "Very Good" effectiveness

### 🔧 Optional Enhancements (Already Working Well):

1. **Fine-tune Pairing Strategy**

   - Adjust high-low mixing ratio
   - Try different zigzag patterns

2. **Add Constraints**

   - Consider student preferences
   - Factor in behavioral compatibility
   - Account for special needs

3. **Multi-objective Optimization**
   - Balance performance + behavior
   - Consider friendship groups
   - Optimize for learning outcomes

---

## 📋 Comparison Summary

| Aspect              | Performance Model              | Seating Algorithm          |
| ------------------- | ------------------------------ | -------------------------- |
| **Type**            | Machine Learning               | Optimization Algorithm     |
| **Current Status**  | ❌ Needs Improvement           | ✅ Very Good               |
| **Score**           | -0.5% R²                       | 82.5/100 Effectiveness     |
| **Priority**        | **HIGH - Requires Retraining** | Low - Working Well         |
| **Action Required** | Yes - Immediate                | No - Optional improvements |

---

## 🚀 Quick Commands

### Evaluate Models

```bash
# Performance Prediction Model
cd student-performance-prediction-model
source venv/bin/activate
python evaluate_model.py

# Seating Arrangement Algorithm
cd student-seating-arrangement-model
python evaluate_algorithm.py
```

### Improve Performance Model

```bash
cd student-performance-prediction-model
source venv/bin/activate

# Step 1: Check data quality
python src/data_preprocessing.py

# Step 2: Retrain model
python src/model_trainer.py

# Step 3: Evaluate again
python evaluate_model.py
```

---

## 📊 Visualization Files

After running evaluations, check these files:

- **Performance Model**: `student-performance-prediction-model/models/model_evaluation.png`

  - 4 plots showing actual vs predicted, residuals, error distribution, accuracy curves

- **Seating Algorithm**: Terminal output shows all metrics
  - Can be extended to create visual classroom layouts

---

## 🎓 Understanding the Results

### What does R² = -0.5% mean?

A negative R² score means the model performs **worse than just predicting the average** for every student. This happens when:

- Model hasn't learned patterns
- Features aren't predictive
- Data quality issues
- Wrong algorithm for the problem

**Action**: The model needs to be retrained with better data or a different approach.

### What does 82.5/100 effectiveness mean?

The seating algorithm creates arrangements that:

- Distribute students evenly across rows (86.6% effectiveness)
- Mix high and low performers well (76.3% effectiveness)
- Achieve balanced learning environment

**Action**: Algorithm is working well, no immediate changes needed.

---

## 📞 Next Steps

1. **PRIORITY HIGH**: Fix Performance Prediction Model

   - Investigate data quality
   - Add more features
   - Try different algorithms
   - Retrain and re-evaluate

2. **OPTIONAL**: Enhance Seating Algorithm
   - Already working well (82.5%)
   - Can add constraints for specific needs
   - Consider multi-factor optimization

---

**Report Generated**: January 6, 2026  
**Last Evaluation**: January 6, 2026  
**System Version**: 1.0.0
