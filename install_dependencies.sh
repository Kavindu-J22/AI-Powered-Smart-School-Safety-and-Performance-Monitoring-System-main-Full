#!/bin/bash

# AI-Powered Smart School Safety System - Dependencies Setup Script
# This script installs all Python requirements for the services

clear

echo ""
echo "╔════════════════════════════════════════════════════════════════════════════╗"
echo "║  INSTALLING DEPENDENCIES FOR ALL SERVICES                                 ║"
echo "╚════════════════════════════════════════════════════════════════════════════╝"
echo ""

# Get the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Color output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to install requirements
install_requirements() {
    local service_name=$1
    local service_dir=$2
    local req_file="$service_dir/requirements.txt"
    
    if [ ! -f "$req_file" ]; then
        echo -e "${YELLOW}⚠️  No requirements.txt found in $service_name${NC}"
        return 1
    fi
    
    echo -e "${YELLOW}Installing $service_name dependencies...${NC}"
    cd "$service_dir"
    python3 -m pip install --break-system-packages -q -r requirements.txt
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ $service_name dependencies installed${NC}"
        return 0
    else
        echo -e "${RED}✗ Failed to install $service_name dependencies${NC}"
        return 1
    fi
}

echo "Checking and installing Python dependencies for all services..."
echo ""

# 1. Homework Management API
install_requirements "Homework Management API" "$SCRIPT_DIR/AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING"
echo ""

# 2. Audio Threat Detection API
install_requirements "Audio Threat Detection API" "$SCRIPT_DIR/Audio-Based_Threat_Detection"
echo ""

# 3. Student Performance Prediction API
install_requirements "Student Performance Prediction API" "$SCRIPT_DIR/student-performance-prediction-model"
echo ""

# 4. Student Seating Arrangement API
install_requirements "Student Seating Arrangement API" "$SCRIPT_DIR/student-seating-arrangement-model"
echo ""

# 5. Facial Recognition Attendance API
install_requirements "Facial Recognition Attendance API" "$SCRIPT_DIR/Facial\ Recognition\ Attendance\ Systems"
echo ""

cd "$SCRIPT_DIR"

echo ""
echo "╔════════════════════════════════════════════════════════════════════════════╗"
echo "║              ALL DEPENDENCIES INSTALLED SUCCESSFULLY                      ║"
echo "╚════════════════════════════════════════════════════════════════════════════╝"
echo ""
echo "✅ Ready to start services with: ./start_all_services.sh"
echo ""
