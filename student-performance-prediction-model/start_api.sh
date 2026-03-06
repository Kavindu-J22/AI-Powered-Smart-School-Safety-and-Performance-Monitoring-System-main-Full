#!/bin/bash

# Quick Start Script for Student Performance Prediction API
# This script starts the API server in the background

cd "$(dirname "$0")"

echo "🚀 Starting Student Performance Prediction API..."

# Kill any existing process on port 5001
lsof -ti:5001 | xargs kill -9 2>/dev/null

# Activate virtual environment and start API
source venv/bin/activate
cd api
python app.py &

API_PID=$!

echo ""
echo "✅ API Started Successfully!"
echo ""
echo "📊 API URL: http://localhost:5001"
echo "🔍 Process ID: $API_PID"
echo ""
echo "To stop: kill $API_PID"
echo "Or press Ctrl+C"
echo ""
echo "Test API:"
echo "  curl http://localhost:5001/health"
echo ""

# Keep script running to show logs
wait $API_PID
