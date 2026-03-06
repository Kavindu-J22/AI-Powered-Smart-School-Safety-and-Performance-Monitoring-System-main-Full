#!/bin/bash

# Student Seating Arrangement Model - Setup Script
# This script automates the setup process

echo "============================================================"
echo "Student Seating Arrangement Model - Setup"
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
mkdir -p dataset
echo "✓ Directories created"

# Test the system
echo ""
echo "============================================================"
echo "Testing System"
echo "============================================================"
python test_system.py

if [ $? -ne 0 ]; then
    echo ""
    echo "⚠️  System test failed. This is expected if the API is not running yet."
    echo "    You can start the API with: ./start_api.sh"
fi

echo ""
echo "============================================================"
echo "Setup Complete!"
echo "============================================================"
echo ""
echo "Next steps:"
echo "  1. Start the API: ./start_api.sh"
echo "  2. Or activate the virtual environment: source venv/bin/activate"
echo "  3. Then run: cd api && python app.py"
echo ""
echo "To test the system:"
echo "  python test_system.py (with API running)"
echo ""
echo "To deactivate virtual environment:"
echo "  deactivate"
echo ""

# Deactivate virtual environment
deactivate
