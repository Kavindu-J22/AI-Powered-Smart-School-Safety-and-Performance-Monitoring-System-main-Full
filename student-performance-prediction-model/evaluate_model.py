"""
Model Evaluation Script
Evaluates the trained student performance prediction model and calculates accuracy metrics

This script:
1. Loads the trained model and test data
2. Makes predictions on test set
3. Calculates comprehensive accuracy metrics
4. Displays detailed evaluation results
"""

import pandas as pd
import numpy as np
import joblib
import os
import sys
from sklearn.model_selection import train_test_split
from sklearn.metrics import (
    mean_absolute_error, 
    mean_squared_error, 
    r2_score,
    mean_absolute_percentage_error
)
import matplotlib.pyplot as plt
import seaborn as sns

# Add parent directory to path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config.config import CLEANED_DATA_PATH, MODEL_PATH, SCALER_PATH, MODELS_DIR, RANDOM_STATE, TEST_SIZE


class ModelEvaluator:
    """Evaluate student performance prediction model"""
    
    def __init__(self):
        """Initialize the evaluator"""
        self.model = None
        self.scaler = None
        self.label_encoder = None
        self.feature_columns = ['age', 'grade', 'attendance', 'marks', 'subject_encoded']
        
    def load_model(self):
        """Load trained model, scaler, and label encoder"""
        print("Loading trained model...")
        
        if not os.path.exists(MODEL_PATH):
            raise FileNotFoundError(f"Model not found at: {MODEL_PATH}\nRun: python src/model_trainer.py")
        
        self.model = joblib.load(MODEL_PATH)
        self.scaler = joblib.load(SCALER_PATH)
        self.label_encoder = joblib.load(os.path.join(MODELS_DIR, 'label_encoder.pkl'))
        
        print(f"✓ Model loaded: {type(self.model).__name__}")
        print(f"✓ Scaler loaded: {type(self.scaler).__name__}")
        print(f"✓ Label encoder loaded")
        
    def load_test_data(self):
        """Load and prepare test dataset"""
        print("\nLoading test data...")
        
        if not os.path.exists(CLEANED_DATA_PATH):
            raise FileNotFoundError(f"Dataset not found at: {CLEANED_DATA_PATH}")
        
        df = pd.read_csv(CLEANED_DATA_PATH)
        print(f"✓ Loaded {len(df)} records")
        
        # Encode subjects
        df['subject_encoded'] = self.label_encoder.transform(df['subject'])
        
        # Features and target
        X = df[self.feature_columns].values
        y = df['future_performance'].values
        
        # Split data (same as training)
        _, X_test, _, y_test = train_test_split(
            X, y, test_size=TEST_SIZE, random_state=RANDOM_STATE
        )
        
        print(f"✓ Test set size: {len(X_test)} samples")
        
        return X_test, y_test, df
    
    def calculate_accuracy_metrics(self, y_true, y_pred):
        """
        Calculate comprehensive accuracy metrics
        
        Args:
            y_true: True values
            y_pred: Predicted values
            
        Returns:
            dict: Dictionary of metrics
        """
        # Regression metrics
        mae = mean_absolute_error(y_true, y_pred)
        mse = mean_squared_error(y_true, y_pred)
        rmse = np.sqrt(mse)
        r2 = r2_score(y_true, y_pred)
        mape = mean_absolute_percentage_error(y_true, y_pred) * 100
        
        # Custom accuracy metric (percentage within threshold)
        # Consider prediction accurate if within 5% of actual value
        threshold = 5.0
        accurate_predictions = np.abs(y_true - y_pred) <= threshold
        accuracy_within_5 = np.mean(accurate_predictions) * 100
        
        # Within 10% threshold
        threshold_10 = 10.0
        accurate_predictions_10 = np.abs(y_true - y_pred) <= threshold_10
        accuracy_within_10 = np.mean(accurate_predictions_10) * 100
        
        # Adjusted R² (explains variance better for multiple features)
        n = len(y_true)
        p = len(self.feature_columns)
        adjusted_r2 = 1 - (1 - r2) * (n - 1) / (n - p - 1)
        
        return {
            'mae': mae,
            'mse': mse,
            'rmse': rmse,
            'r2_score': r2,
            'adjusted_r2': adjusted_r2,
            'mape': mape,
            'accuracy_within_5': accuracy_within_5,
            'accuracy_within_10': accuracy_within_10,
            'total_predictions': n
        }
    
    def evaluate(self):
        """Execute complete evaluation"""
        print("=" * 70)
        print("STUDENT PERFORMANCE PREDICTION MODEL - EVALUATION")
        print("=" * 70)
        
        # Load model and data
        self.load_model()
        X_test, y_test, df = self.load_test_data()
        
        # Make predictions
        print("\nMaking predictions...")
        X_test_scaled = self.scaler.transform(X_test)
        y_pred = self.model.predict(X_test_scaled)
        print(f"✓ Generated {len(y_pred)} predictions")
        
        # Calculate metrics
        print("\nCalculating accuracy metrics...")
        metrics = self.calculate_accuracy_metrics(y_test, y_pred)
        
        # Display results
        self.display_results(metrics, y_test, y_pred)
        
        return metrics, y_test, y_pred
    
    def display_results(self, metrics, y_test, y_pred):
        """Display evaluation results in a formatted way"""
        print("\n" + "=" * 70)
        print("EVALUATION RESULTS")
        print("=" * 70)
        
        print("\n📊 ACCURACY METRICS:")
        print("-" * 70)
        print(f"  R² Score (Coefficient of Determination):  {metrics['r2_score']:.4f} ({metrics['r2_score']*100:.2f}%)")
        print(f"  Adjusted R² Score:                         {metrics['adjusted_r2']:.4f} ({metrics['adjusted_r2']*100:.2f}%)")
        print(f"  Accuracy within ±5 points:                 {metrics['accuracy_within_5']:.2f}%")
        print(f"  Accuracy within ±10 points:                {metrics['accuracy_within_10']:.2f}%")
        
        print("\n📏 ERROR METRICS:")
        print("-" * 70)
        print(f"  Mean Absolute Error (MAE):                 {metrics['mae']:.4f} points")
        print(f"  Root Mean Squared Error (RMSE):            {metrics['rmse']:.4f} points")
        print(f"  Mean Absolute Percentage Error (MAPE):     {metrics['mape']:.2f}%")
        
        print("\n📈 PREDICTION STATISTICS:")
        print("-" * 70)
        print(f"  Total test predictions:                    {metrics['total_predictions']}")
        print(f"  Actual values range:                       {y_test.min():.2f} - {y_test.max():.2f}")
        print(f"  Predicted values range:                    {y_pred.min():.2f} - {y_pred.max():.2f}")
        print(f"  Average prediction error:                  ±{metrics['mae']:.2f} points")
        
        print("\n💡 INTERPRETATION:")
        print("-" * 70)
        
        # R² Score interpretation
        if metrics['r2_score'] >= 0.9:
            r2_quality = "Excellent"
        elif metrics['r2_score'] >= 0.8:
            r2_quality = "Very Good"
        elif metrics['r2_score'] >= 0.7:
            r2_quality = "Good"
        elif metrics['r2_score'] >= 0.5:
            r2_quality = "Moderate"
        else:
            r2_quality = "Needs Improvement"
        
        print(f"  R² Score Quality: {r2_quality}")
        print(f"  The model explains {metrics['r2_score']*100:.1f}% of the variance in student performance.")
        
        if metrics['accuracy_within_5'] >= 80:
            print(f"  ✓ High accuracy: {metrics['accuracy_within_5']:.1f}% of predictions are within ±5 points")
        elif metrics['accuracy_within_5'] >= 60:
            print(f"  ~ Moderate accuracy: {metrics['accuracy_within_5']:.1f}% of predictions are within ±5 points")
        else:
            print(f"  ⚠ Low accuracy: Only {metrics['accuracy_within_5']:.1f}% of predictions are within ±5 points")
        
        print(f"  Average prediction is off by {metrics['mae']:.2f} points from actual performance.")
        
        print("\n" + "=" * 70)
    
    def create_visualization(self, y_test, y_pred):
        """Create visualization plots for model evaluation"""
        print("\nGenerating visualization plots...")
        
        try:
            fig, axes = plt.subplots(2, 2, figsize=(15, 12))
            
            # Plot 1: Actual vs Predicted
            axes[0, 0].scatter(y_test, y_pred, alpha=0.5)
            axes[0, 0].plot([y_test.min(), y_test.max()], [y_test.min(), y_test.max()], 'r--', lw=2)
            axes[0, 0].set_xlabel('Actual Performance')
            axes[0, 0].set_ylabel('Predicted Performance')
            axes[0, 0].set_title('Actual vs Predicted Performance')
            axes[0, 0].grid(True, alpha=0.3)
            
            # Plot 2: Residual Plot
            residuals = y_test - y_pred
            axes[0, 1].scatter(y_pred, residuals, alpha=0.5)
            axes[0, 1].axhline(y=0, color='r', linestyle='--', lw=2)
            axes[0, 1].set_xlabel('Predicted Performance')
            axes[0, 1].set_ylabel('Residuals')
            axes[0, 1].set_title('Residual Plot')
            axes[0, 1].grid(True, alpha=0.3)
            
            # Plot 3: Error Distribution
            axes[1, 0].hist(residuals, bins=30, edgecolor='black', alpha=0.7)
            axes[1, 0].set_xlabel('Prediction Error')
            axes[1, 0].set_ylabel('Frequency')
            axes[1, 0].set_title('Distribution of Prediction Errors')
            axes[1, 0].axvline(x=0, color='r', linestyle='--', lw=2)
            axes[1, 0].grid(True, alpha=0.3)
            
            # Plot 4: Accuracy within thresholds
            thresholds = [1, 2, 3, 4, 5, 7, 10, 15, 20]
            accuracies = [np.mean(np.abs(residuals) <= t) * 100 for t in thresholds]
            axes[1, 1].plot(thresholds, accuracies, marker='o', linewidth=2, markersize=8)
            axes[1, 1].set_xlabel('Error Threshold (points)')
            axes[1, 1].set_ylabel('Accuracy (%)')
            axes[1, 1].set_title('Accuracy vs Error Threshold')
            axes[1, 1].grid(True, alpha=0.3)
            axes[1, 1].set_ylim([0, 105])
            
            plt.tight_layout()
            
            # Save plot
            output_path = os.path.join(MODELS_DIR, 'model_evaluation.png')
            plt.savefig(output_path, dpi=300, bbox_inches='tight')
            print(f"✓ Visualization saved to: {output_path}")
            
            # Show plot
            # plt.show()  # Uncomment to display plots
            
        except Exception as e:
            print(f"⚠ Could not create visualization: {e}")
            print("  (This is optional - evaluation results are still valid)")


def main():
    """Main execution function"""
    evaluator = ModelEvaluator()
    
    try:
        metrics, y_test, y_pred = evaluator.evaluate()
        
        # Create visualizations
        evaluator.create_visualization(y_test, y_pred)
        
        print("\n✅ Evaluation completed successfully!")
        
    except FileNotFoundError as e:
        print(f"\n❌ Error: {e}")
        print("\nPlease ensure you have:")
        print("1. Cleaned data: python src/data_preprocessing.py")
        print("2. Trained model: python src/model_trainer.py")
    except Exception as e:
        print(f"\n❌ Unexpected error: {e}")
        import traceback
        traceback.print_exc()


if __name__ == "__main__":
    main()
