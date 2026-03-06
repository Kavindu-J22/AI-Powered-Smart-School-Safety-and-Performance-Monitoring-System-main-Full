#!/bin/bash

# Student Performance Prediction System - Setup Script
# This script automates the setup process

echo "============================================================"
echo "Student Performance Prediction System - Setup"
echo "============================================================"

# Check Python version
echo ""
echo "Checking Python version..."
python3 --version

if [ $? -ne 0 ]; then
    echo "❌ Python 3 is not installed. Please install Python 3.8 or higher."
    exit 1
fi

echo "✓ Python is installed"

# Create virtual environment if it doesn't exist
echo ""
if [ ! -d "venv" ]; then
    echo "Creating virtual environment..."
    python3 -m venv venv
    
    if [ $? -ne 0 ]; then
        echo "❌ Failed to create virtual environment"
        exit 1
    fi
    
    echo "✓ Virtual environment created"
else
    echo "✓ Virtual environment already exists"
fi

# Activate virtual environment
echo ""
echo "Activating virtual environment..."
source venv/bin/activate

if [ $? -ne 0 ]; then
    echo "❌ Failed to activate virtual environment"
    exit 1
fi

echo "✓ Virtual environment activated"

# Install Python dependencies
echo ""
echo "Installing Python dependencies..."
pip install -r requirements.txt

if [ $? -ne 0 ]; then
    echo "❌ Failed to install dependencies"
    deactivate
    exit 1
fi

echo "✓ Dependencies installed"

# Create necessary directories
echo ""
echo "Creating directories..."
mkdir -p data
mkdir -p models
echo "✓ Directories created"

# Run data preprocessing
echo ""
echo "============================================================"
echo "Step 1: Data Preprocessing"
echo "============================================================"
python src/data_preprocessing.py

if [ $? -ne 0 ]; then
    echo "❌ Data preprocessing failed"
    deactivate
    exit 1
fi

# Train model
echo ""
echo "============================================================"
echo "Step 2: Model Training"
echo "============================================================"
python src/model_trainer.py

if [ $? -ne 0 ]; then
    echo "❌ Model training failed"
    deactivate
    exit 1
fi

# Test predictor
echo ""
echo "============================================================"
echo "Step 3: Testing Predictor"
echo "============================================================"
python src/predictor.py

if [ $? -ne 0 ]; then
    echo "❌ Predictor test failed"
    deactivate
    exit 1
fi

echo ""
echo "============================================================"
echo "✓ Setup completed successfully!"
echo "============================================================"
echo ""
echo "⚠️  IMPORTANT: You are now in a virtual environment"
echo ""
echo "Next steps:"
echo "1. Start the API server (keep venv activated):"
echo "   cd api && python app.py"
echo ""
echo "2. In a new terminal, to activate virtual environment:"
echo "   source venv/bin/activate"
echo ""
echo "3. To deactivate when done:"
echo "   deactivate"
echo ""
echo "4. Configure Laravel .env file:"
echo "   PREDICTION_API_URL=http://localhost:5000"
echo ""
echo "5. Access the API:"
echo "   http://localhost:5000/health"
echo ""
echo "============================================================"
