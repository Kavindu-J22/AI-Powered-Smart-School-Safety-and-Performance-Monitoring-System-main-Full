#!/bin/bash
# ============================================
# Facial Recognition Attendance System
# Startup Script
# ============================================

# Change to the script's directory
cd "$(dirname "$0")"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo ""
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║     Facial Recognition Attendance System                     ║"
echo "║     Starting API Server...                                   ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# Check if virtual environment exists
if [ ! -d "venv" ]; then
    echo -e "${YELLOW}Creating virtual environment...${NC}"
    python3 -m venv venv
    source venv/bin/activate
    pip install --upgrade pip
    pip install -r requirements.txt
else
    source venv/bin/activate
fi

# Kill any existing process on port 5004
echo -e "${YELLOW}Checking for existing processes...${NC}"
lsof -ti:5004 | xargs kill -9 2>/dev/null || true

# Set environment variables
export FLASK_ENV=development
export DEBUG=true
export DASHBOARD_API_URL=http://localhost:8000/api

echo -e "${GREEN}Starting server on port 5004...${NC}"
echo ""

# Run the application
python3 app.py
