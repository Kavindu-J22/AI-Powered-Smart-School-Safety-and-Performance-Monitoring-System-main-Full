#!/bin/bash

# Startup script for Seating Arrangement API

echo "Starting Seating Arrangement API..."

# Navigate to the api directory
cd "$(dirname "$0")/api"

# Activate virtual environment if it exists
if [ -d "../venv" ]; then
    source ../venv/bin/activate
fi

# Set environment variables
export SEATING_API_HOST=0.0.0.0
export SEATING_API_PORT=5001
export SEATING_API_DEBUG=False

# Start the Flask API (use python3 for macOS)
python3 app.py
