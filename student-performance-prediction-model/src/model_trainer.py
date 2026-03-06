"""
Model Training Module
Trains machine learning models to predict student performance

This module:
1. Loads cleaned data
2. Prepares features and labels with One-Hot Encoding
3. Trains RandomForestRegressor (replaced LinearRegression for better accuracy)
4. Uses 5-fold Cross-Validation for robust evaluation
5. Implements stratified sampling based on performance ranges
6. Saves trained models for prediction

IMPROVEMENTS MADE:
- RandomForestRegressor: Handles non-linear relationships, reduces bias toward average scores
  WHY: Linear Regression assumes linear relationships which is unrealistic for student data
- One-Hot Encoding: Better handles categorical subjects without ordinal assumptions
  WHY: LabelEncoder imposes artificial ordering which can confuse models
- 5-Fold Cross-Validation: More reliable performance estimates
  WHY: Single train-test split can be misleading, CV gives true generalization performance
- Stratified Sampling: Ensures balanced representation of low/average/high performers
  WHY: Prevents model from ignoring minority performance groups
"""

import pandas as pd
import numpy as np
import os
import sys
import joblib
from sklearn.model_selection import train_test_split, cross_val_score, StratifiedKFold
from sklearn.ensemble import RandomForestRegressor
from sklearn.preprocessing import StandardScaler, OneHotEncoder
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score

# Add parent directory to path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config.config import (
    CLEANED_DATA_PATH, MODELS_DIR, MODEL_PATH, SCALER_PATH, 
    RANDOM_STATE, TEST_SIZE, RF_N_ESTIMATORS, RF_MAX_DEPTH, CV_FOLDS
)


class PerformancePredictor:
    """Train and manage student performance prediction models"""
    
    def __init__(self, data_path=CLEANED_DATA_PATH):
        """
        Initialize the predictor
        
        Args:
            data_path: Path to cleaned dataset
        """
        self.data_path = data_path
        self.df = None
        self.models = {}
        self.scaler = StandardScaler()
        # IMPROVEMENT: Use OneHotEncoder instead of LabelEncoder
        # WHY: OneHotEncoder doesn't assume ordinal relationships between subjects
        # handle_unknown='ignore' prevents errors on unseen subjects during prediction
        self.subject_encoder = OneHotEncoder(sparse_output=False, handle_unknown='ignore')
        
        # Updated feature columns - base numerical features (subject handled separately)
        self.numerical_features = ['age', 'grade', 'attendance', 'marks']
        # Engineered features that improve accuracy
        self.engineered_features = ['attendance_score', 'grade_marks_ratio', 'risk_index']
        
        # Store feature order for consistency between training and prediction
        self.feature_order = None
        
    def load_data(self):
        """Load cleaned dataset"""
        print(f"Loading cleaned data from: {self.data_path}")
        self.df = pd.read_csv(self.data_path)
        print(f"Loaded {len(self.df)} records")
        print(f"Columns: {list(self.df.columns)}")
        return self
    
    def _create_performance_bins(self, y):
        """
        Create performance bins for stratified sampling
        
        WHY STRATIFIED SAMPLING IMPROVES ACCURACY:
        - Ensures training data has balanced representation of all performance levels
        - Prevents model from being biased toward majority class (usually 'average' students)
        - Crucial for improving predictions on low-performing students
        
        Args:
            y: Target values (performance scores)
            
        Returns:
            Array of bin labels for stratification
        """
        # Define performance ranges: low (<55), average (55-75), high (>75)
        bins = [0, 55, 75, 100]
        labels = ['low', 'average', 'high']
        return pd.cut(y, bins=bins, labels=labels, include_lowest=True)
        
    def prepare_features(self):
        """
        Prepare features for training:
        - One-Hot encode categorical variables (subjects)
        - Include engineered features
        - Create feature matrix X and target vector y
        
        IMPROVEMENT: Using One-Hot Encoding instead of Label Encoding
        WHY: Label encoding creates arbitrary numerical relationships (Math=0, Science=1)
        which can mislead the model. One-hot encoding treats each subject independently.
        """
        print("\n=== Preparing Features ===")
        
        # Check if engineered features exist, if not create them
        if 'attendance_score' not in self.df.columns:
            print("Creating engineered features...")
            self.df['attendance_score'] = self.df['attendance'] / 100.0
            self.df['grade_marks_ratio'] = self.df['marks'] / np.maximum(self.df['grade'], 1)
            self.df['risk_index'] = ((100 - self.df['attendance']) * (100 - self.df['marks'])) / 100.0
        
        # Get numerical features
        numerical_data = self.df[self.numerical_features + self.engineered_features].values
        
        # One-Hot encode subjects
        # WHY: Each subject becomes a separate binary feature, preventing ordinal bias
        subjects = self.df['subject'].values.reshape(-1, 1)
        subject_encoded = self.subject_encoder.fit_transform(subjects)
        
        # Get subject feature names for later reference
        subject_names = self.subject_encoder.get_feature_names_out(['subject'])
        
        # Combine numerical and encoded features
        X = np.hstack([numerical_data, subject_encoded])
        
        # Store feature order for consistency during prediction
        self.feature_order = self.numerical_features + self.engineered_features + list(subject_names)
        
        # Target: future performance
        y = self.df['future_performance'].values
        
        print(f"Features shape: {X.shape}")
        print(f"Target shape: {y.shape}")
        print(f"Numerical features: {self.numerical_features}")
        print(f"Engineered features: {self.engineered_features}")
        print(f"Subject encoding (One-Hot): {list(subject_names)}")
        print(f"Total features: {len(self.feature_order)}")
        
        return X, y
        
    def train_model(self, X, y):
        """
        Train RandomForestRegressor model with cross-validation
        
        IMPROVEMENT: RandomForest instead of LinearRegression
        WHY: 
        1. Handles non-linear relationships between features and performance
        2. Robust to outliers and doesn't require feature scaling (but we still scale for consistency)
        3. Provides feature importance for interpretability
        4. Reduces prediction clustering around mean values
        5. Better captures interactions between features (e.g., high attendance + low marks)
        
        Args:
            X: Feature matrix
            y: Target vector
            
        Returns:
            Trained model and evaluation metrics
        """
        print("\n=== Training Model ===")
        print("Algorithm: Random Forest Regressor (replacing Linear Regression)")
        print(f"  - n_estimators: {RF_N_ESTIMATORS} trees")
        print(f"  - max_depth: {RF_MAX_DEPTH}")
        print(f"  - random_state: {RANDOM_STATE}")
        print(f"Cross-Validation: {CV_FOLDS}-fold")
        
        # Create stratified bins for balanced sampling
        # WHY: Ensures train/test sets have proportional representation of performance levels
        y_bins = self._create_performance_bins(y)
        
        # Split data with stratification
        # WHY: Stratified split ensures both training and test sets represent all performance levels
        X_train, X_test, y_train, y_test = train_test_split(
            X, y, test_size=TEST_SIZE, random_state=RANDOM_STATE, 
            stratify=y_bins  # IMPROVEMENT: Stratified sampling
        )
        
        print(f"\nTraining samples: {len(X_train)}")
        print(f"Testing samples: {len(X_test)}")
        
        # Scale features
        # Note: RandomForest doesn't require scaling, but we do it for consistency
        # and to support potential future models that do require it
        X_train_scaled = self.scaler.fit_transform(X_train)
        X_test_scaled = self.scaler.transform(X_test)
        
        # Initialize RandomForest with tuned hyperparameters
        # WHY THESE HYPERPARAMETERS:
        # - n_estimators=200: More trees = more stable predictions, diminishing returns after ~200
        # - max_depth=12: Prevents overfitting while allowing complex patterns
        # - min_samples_split=5: Requires reasonable sample size before splitting
        # - min_samples_leaf=2: Ensures leaf nodes have multiple samples
        model = RandomForestRegressor(
            n_estimators=RF_N_ESTIMATORS,
            max_depth=RF_MAX_DEPTH,
            min_samples_split=5,
            min_samples_leaf=2,
            random_state=RANDOM_STATE,
            n_jobs=-1  # Use all CPU cores for faster training
        )
        
        # Perform 5-fold cross-validation BEFORE final training
        # WHY: CV gives more reliable estimate of model performance than single split
        print("\n=== Cross-Validation Results ===")
        cv_scores = self._cross_validate(model, X_train_scaled, y_train)
        
        # Train final model on full training set
        model.fit(X_train_scaled, y_train)
        
        # Make predictions (clamped to valid range)
        y_train_pred = self._clamp_predictions(model.predict(X_train_scaled))
        y_test_pred = self._clamp_predictions(model.predict(X_test_scaled))
        
        # Evaluate model
        metrics = self.evaluate_model(y_train, y_train_pred, y_test, y_test_pred, cv_scores)
        
        # Print feature importance
        print("\n=== Feature Importance (Random Forest) ===")
        importance = model.feature_importances_
        for feature, imp in sorted(zip(self.feature_order, importance), key=lambda x: -x[1]):
            print(f"  {feature}: {imp:.4f}")
        
        return model, metrics
    
    def _clamp_predictions(self, predictions):
        """
        Clamp predictions to valid range [0, 100]
        
        WHY: Prevents impossible predictions (negative scores or >100%)
        This is a safety measure for production use
        
        Args:
            predictions: Raw model predictions
            
        Returns:
            Clamped predictions within [0, 100]
        """
        return np.clip(predictions, 0, 100)
    
    def _cross_validate(self, model, X, y):
        """
        Perform k-fold cross-validation
        
        WHY CROSS-VALIDATION IMPROVES RELIABILITY:
        - Tests model on multiple different train/test splits
        - Gives more accurate estimate of true model performance
        - Reduces risk of overfitting to particular data split
        - Reports mean and std for confidence in results
        
        Args:
            model: Model to evaluate
            X: Feature matrix
            y: Target vector
            
        Returns:
            Dictionary of CV scores
        """
        # R² score (coefficient of determination)
        r2_scores = cross_val_score(model, X, y, cv=CV_FOLDS, scoring='r2')
        
        # Negative MSE (sklearn uses negative for "higher is better" convention)
        neg_mse_scores = cross_val_score(model, X, y, cv=CV_FOLDS, scoring='neg_mean_squared_error')
        rmse_scores = np.sqrt(-neg_mse_scores)
        
        # Negative MAE
        neg_mae_scores = cross_val_score(model, X, y, cv=CV_FOLDS, scoring='neg_mean_absolute_error')
        mae_scores = -neg_mae_scores
        
        print(f"  R² Score: {r2_scores.mean():.4f} ± {r2_scores.std():.4f}")
        print(f"  RMSE: {rmse_scores.mean():.4f} ± {rmse_scores.std():.4f}")
        print(f"  MAE: {mae_scores.mean():.4f} ± {mae_scores.std():.4f}")
        
        return {
            'cv_r2_mean': r2_scores.mean(),
            'cv_r2_std': r2_scores.std(),
            'cv_rmse_mean': rmse_scores.mean(),
            'cv_rmse_std': rmse_scores.std(),
            'cv_mae_mean': mae_scores.mean(),
            'cv_mae_std': mae_scores.std()
        }
        
    def evaluate_model(self, y_train, y_train_pred, y_test, y_test_pred, cv_scores):
        """
        Evaluate model performance
        
        Args:
            y_train, y_train_pred: Training actual and predicted values
            y_test, y_test_pred: Testing actual and predicted values
            cv_scores: Cross-validation scores
            
        Returns:
            Dictionary of evaluation metrics
        """
        print("\n=== Model Evaluation (Final Model) ===")
        
        # Training metrics
        train_mae = mean_absolute_error(y_train, y_train_pred)
        train_rmse = np.sqrt(mean_squared_error(y_train, y_train_pred))
        train_r2 = r2_score(y_train, y_train_pred)
        
        # Testing metrics
        test_mae = mean_absolute_error(y_test, y_test_pred)
        test_rmse = np.sqrt(mean_squared_error(y_test, y_test_pred))
        test_r2 = r2_score(y_test, y_test_pred)
        
        print("\nTraining Set Performance:")
        print(f"  Mean Absolute Error (MAE): {train_mae:.4f}")
        print(f"  Root Mean Squared Error (RMSE): {train_rmse:.4f}")
        print(f"  R² Score: {train_r2:.4f}")
        
        print("\nTest Set Performance:")
        print(f"  Mean Absolute Error (MAE): {test_mae:.4f}")
        print(f"  Root Mean Squared Error (RMSE): {test_rmse:.4f}")
        print(f"  R² Score: {test_r2:.4f}")
        
        # Check for improvement indicators
        print("\n=== Performance Summary ===")
        if test_r2 >= 0.88:
            print(f"✓ Target R² ≥ 0.88 achieved: {test_r2:.4f}")
        else:
            print(f"⚠ R² below target (0.88): {test_r2:.4f}")
        
        metrics = {
            'train_mae': train_mae,
            'train_rmse': train_rmse,
            'train_r2': train_r2,
            'test_mae': test_mae,
            'test_rmse': test_rmse,
            'test_r2': test_r2,
            **cv_scores  # Include cross-validation scores
        }
        
        return metrics
        
    def save_models(self, model):
        """
        Save trained model, scaler, and encoders
        
        Args:
            model: Trained model to save
        """
        print("\n=== Saving Models ===")
        
        # Create models directory if it doesn't exist
        os.makedirs(MODELS_DIR, exist_ok=True)
        
        # Save model
        joblib.dump(model, MODEL_PATH)
        print(f"✓ Model saved to: {MODEL_PATH}")
        
        # Save scaler
        joblib.dump(self.scaler, SCALER_PATH)
        print(f"✓ Scaler saved to: {SCALER_PATH}")
        
        # Save OneHotEncoder (replaces LabelEncoder)
        encoder_path = os.path.join(MODELS_DIR, 'subject_encoder.pkl')
        joblib.dump(self.subject_encoder, encoder_path)
        print(f"✓ Subject encoder (OneHot) saved to: {encoder_path}")
        
        # Save feature order for consistent prediction
        feature_order_path = os.path.join(MODELS_DIR, 'feature_order.pkl')
        joblib.dump(self.feature_order, feature_order_path)
        print(f"✓ Feature order saved to: {feature_order_path}")
        
        return self
        
    def train_and_save(self):
        """Execute complete training pipeline"""
        # Load and prepare data
        self.load_data()
        X, y = self.prepare_features()
        
        # Train model
        model, metrics = self.train_model(X, y)
        
        # Save model
        self.save_models(model)
        
        return model, metrics


def main():
    """Main execution function"""
    print("=" * 60)
    print("STUDENT PERFORMANCE PREDICTION MODEL TRAINING")
    print("=" * 60)
    print("Model: Random Forest Regressor (improved from Linear Regression)")
    print("=" * 60)
    
    # Initialize predictor
    predictor = PerformancePredictor()
    
    # Train and save model
    model, metrics = predictor.train_and_save()
    
    print("\n" + "=" * 60)
    print("✓ Model training completed successfully!")
    print("=" * 60)
    print("\nFinal Performance Metrics:")
    print(f"  Test R² Score: {metrics['test_r2']:.4f}")
    print(f"  Test RMSE: {metrics['test_rmse']:.4f}")
    print(f"  Test MAE: {metrics['test_mae']:.4f}")
    print(f"\nCross-Validation Results ({CV_FOLDS}-fold):")
    print(f"  CV R² Score: {metrics['cv_r2_mean']:.4f} ± {metrics['cv_r2_std']:.4f}")
    print(f"  CV RMSE: {metrics['cv_rmse_mean']:.4f} ± {metrics['cv_rmse_std']:.4f}")
    print(f"  CV MAE: {metrics['cv_mae_mean']:.4f} ± {metrics['cv_mae_std']:.4f}")
    print("=" * 60)


if __name__ == "__main__":
    main()
