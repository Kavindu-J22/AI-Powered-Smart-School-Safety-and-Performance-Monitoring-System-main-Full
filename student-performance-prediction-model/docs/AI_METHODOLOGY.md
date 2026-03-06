# AI Methodology

The Student Performance Prediction System utilizes state-of-the-art machine learning techniques to provide actionable insights into student success.

## 1. Machine Learning Model: Random Forest Regressor

The system employs a **Random Forest Regressor** as its core algorithm. Unlike traditional linear regression, Random Forest excels at:

- **Non-Linearity**: Capturing complex relationships between attendance, current marks, and future outcomes.
- **Robustness**: Handling outliers and variance in student performance without overfitting.
- **Feature Importance**: Specifically identifying which factors (e.g., attendance vs. study hours) are the most significant predictors.

### Model Parameters

- **n_estimators**: 200 (Number of trees in the forest)
- **max_depth**: 12 (Allows complex patterns while preventing overfitting)
- **min_samples_split**: 5 (Ensures splits are statistically significant)

---

## 2. Advanced Feature Engineering

We transform raw variables into sophisticated indicators that significantly boost accuracy:

| Feature               | Logic                             | Purpose                                                         |
| --------------------- | --------------------------------- | --------------------------------------------------------------- |
| **Attendance Score**  | `attendance / 100`                | Normalizes attendance to a standard scale.                      |
| **Risk Index**        | `((100-att) * (100-marks)) / 100` | Flags students with high absenteeism and low grades.            |
| **Grade Marks Ratio** | `marks / grade_level`             | Adjusts marks relative to the academic difficulty of the grade. |

---

## 3. Professional Confidence Estimation

One of the most unique features of this system is the **95% Confidence Interval (CI)** for every prediction.

Rather than providing a single "guess," the model outputs a range (e.g., `[74.2, 82.5]`).

- **Calculation**: Derived from the variance of individual trees in the random forest and weighted by input quality indicators.
- **Benefit**: Allows teachers to see not just the predicted score, but the _uncertainty_ of that prediction. Larger intervals indicate students with erratic patterns who may need more observation.

---

## 4. Stratified Training

To prevent the model from ignoring underperforming students (who are often a minority in the dataset), we use **Stratified Sampling**:

1. Performance is binned into `Low`, `Average`, and `High`.
2. The training process ensures each bin is proportionally represented in both training and validation sets.
3. This creates a model that is equally accurate for top performers and those at risk.
