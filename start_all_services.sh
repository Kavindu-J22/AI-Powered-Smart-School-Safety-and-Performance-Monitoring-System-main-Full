#!/bin/bash

# AI-Powered Smart School Safety System - Startup Script (macOS/Linux)
# This script starts all services in the background

clear

echo ""
echo "╔════════════════════════════════════════════════════════════════════════════╗"
echo "║  AI-POWERED SMART SCHOOL SAFETY & PERFORMANCE MONITORING SYSTEM          ║"
echo "║                         STARTUP SCRIPT                                    ║"
echo "╚════════════════════════════════════════════════════════════════════════════╝"
echo ""

# Get the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
LOGS_DIR="$SCRIPT_DIR/logs"

# Create logs directory if it doesn't exist
mkdir -p "$LOGS_DIR"

echo "🚀 Starting all services..."
echo ""
echo "📁 Logs directory: $LOGS_DIR"
echo "💡 View logs with: tail -f logs/[service].log"
echo ""

# 1. Start Laravel Application
echo "1. Starting Laravel Web Application (Port 8000)..."
cd "$SCRIPT_DIR"
php artisan serve --port=8000 > "$LOGS_DIR/laravel.log" 2>&1 &
echo "   ✓ Laravel started (PID: $!)"
sleep 2

# 2. Start Homework Management API
echo "2. Starting Homework Management API (Port 5001)..."
cd "$SCRIPT_DIR/AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING"
python3 app.py > "$LOGS_DIR/homework.log" 2>&1 &
echo "   ✓ Homework API started (PID: $!)"
sleep 2

# 3. Start Audio Threat Detection API
echo "3. Starting Audio Threat Detection API (Port 5005)..."
cd "$SCRIPT_DIR/Audio-Based_Threat_Detection"
FLASK_PORT=5005 python3 app.py > "$LOGS_DIR/audio.log" 2>&1 &
echo "   ✓ Audio Threat API started (PID: $!)"
sleep 2

# 4. Start Student Performance Prediction API
echo "4. Starting Student Performance Prediction API (Port 5002)..."
cd "$SCRIPT_DIR/student-performance-prediction-model"
python3 api/app.py > "$LOGS_DIR/performance.log" 2>&1 &
echo "   ✓ Performance Prediction API started (PID: $!)"
sleep 2

# 5. Start Student Seating Arrangement API
echo "5. Starting Student Seating Arrangement API (Port 5003)..."
cd "$SCRIPT_DIR/student-seating-arrangement-model"
python3 api/app.py > "$LOGS_DIR/seating.log" 2>&1 &
echo "   ✓ Seating Arrangement API started (PID: $!)"
sleep 2

# 6. Start Facial Recognition Attendance API
echo "6. Starting Facial Recognition Attendance API (Port 5004)..."
cd "$SCRIPT_DIR/Facial Recognition Attendance Systems"
FACE_VENV_PY="$SCRIPT_DIR/face_venv/bin/python3"
if [ -f "$FACE_VENV_PY" ]; then
    "$FACE_VENV_PY" app.py > "$LOGS_DIR/facial.log" 2>&1 &
else
    python3 app.py > "$LOGS_DIR/facial.log" 2>&1 &
fi
echo "   ✓ Facial Recognition API started (PID: $!)"
sleep 2

# 7. Start RFID Serial Bridge (Arduino UNO R3 + RC522)
echo "7. Starting RFID Serial Bridge..."
VENV_PY="$SCRIPT_DIR/rfid_venv/bin/python3"
RFID_SCRIPT="$SCRIPT_DIR/AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main/arduino/rfid_bridge.py"
if [ -f "$VENV_PY" ] && [ -f "$RFID_SCRIPT" ]; then
    # Auto-detect Arduino port (prefer usbserial, skip WiFi/Bluetooth ports)
    ARDUINO_PORT=$(ls /dev/cu.usbserial-* /dev/cu.usbmodem* 2>/dev/null | head -1)
    if [ -z "$ARDUINO_PORT" ]; then
        ARDUINO_PORT=$(ls /dev/tty.usbserial-* /dev/tty.usbmodem* 2>/dev/null | head -1)
    fi
    RFID_ENV="SERVER_URL=http://127.0.0.1:8000"
    if [ -n "$ARDUINO_PORT" ]; then
        RFID_ENV="$RFID_ENV RFID_PORT=$ARDUINO_PORT"
        echo "   📡 Arduino detected on $ARDUINO_PORT"
    else
        echo "   ⚠ Arduino port not found — bridge will attempt auto-detect"
    fi
    env SERVER_URL="http://127.0.0.1:8000" RFID_PORT="${ARDUINO_PORT:-}" "$VENV_PY" "$RFID_SCRIPT" > "$LOGS_DIR/rfid_bridge.log" 2>&1 &
    echo "   ✓ RFID Bridge started (PID: $!) — watching for Arduino on USB"
else
    echo "   ⚠ RFID Bridge skipped (venv or script not found — this is optional)"
fi
sleep 1

cd "$SCRIPT_DIR"

echo ""
echo "⏳ Waiting for services to start..."
sleep 20

echo ""
echo "✅ Verifying services..."
echo ""

# Function to check health
check_service() {
    local port="$1"
    local name="$2"
    local endpoint="${3:-}"
    
    if curl -s "http://127.0.0.1:$port$endpoint" > /dev/null 2>&1; then
        echo "  ✓ $name (Port $port): RUNNING"
        return 0
    else
        echo "  ✗ $name (Port $port): STARTING/NOT RESPONDING"
        return 1
    fi
}

check_service 8000 "Laravel App"
check_service 5001 "Homework API" "/api/health"
check_service 5005 "Audio Threat API" "/api/audio/health"
check_service 5002 "Performance Prediction API" "/api/health"
check_service 5003 "Seating Arrangement API" "/api/health"
check_service 5004 "Facial Recognition API" "/api/health"

# RFID bridge is a background process, not an HTTP service — just report its log
if pgrep -f "rfid_bridge.py" > /dev/null 2>&1; then
    echo "  ✓ RFID Serial Bridge: RUNNING"
else
    echo "  - RFID Serial Bridge: NOT RUNNING (Arduino may not be connected)"
fi

echo ""
echo "╔════════════════════════════════════════════════════════════════════════════╗"
echo "║                    ALL SERVICES STARTED SUCCESSFULLY                      ║"
echo "╚════════════════════════════════════════════════════════════════════════════╝"
echo ""

echo "🌐 Service URLs:"
echo "   • Laravel App:              http://127.0.0.1:8000"
echo "   • Homework API:             http://127.0.0.1:5001"
echo "   • Audio Threat API:         http://127.0.0.1:5005"
echo "   • Performance Prediction:   http://127.0.0.1:5002"
echo "   • Seating Arrangement:      http://127.0.0.1:5003"
echo "   • Facial Recognition:       http://127.0.0.1:5004"
echo "   • RFID Bridge:              (background process — USB serial)"
echo ""

echo "📝 Health Check URLs:"
echo "   • Homework API:             http://127.0.0.1:5001/api/health"
echo "   • Audio Threat API:         http://127.0.0.1:5005/api/audio/health"
echo "   • Performance Prediction:   http://127.0.0.1:5002/api/health"
echo "   • Seating Arrangement:      http://127.0.0.1:5003/api/health"
echo "   • Facial Recognition:       http://127.0.0.1:5004/api/health"
echo ""

echo "📋 View Service Logs:"
echo "   • tail -f logs/laravel.log"
echo "   • tail -f logs/homework.log"
echo "   • tail -f logs/audio.log"
echo "   • tail -f logs/performance.log"
echo "   • tail -f logs/seating.log"
echo "   • tail -f logs/facial.log"
echo "   • tail -f logs/rfid_bridge.log"
echo ""

echo "🌐 Opening Laravel application in browser..."
sleep 2
open "http://127.0.0.1:8000" 2>/dev/null || echo "   (Manually open http://127.0.0.1:8000 in your browser)"

echo ""
echo "✅ System is ready!"
echo ""
echo "🛑 To stop all services, run: ./stop_all_services.sh"
echo "💡 Services are running in the background. Use the log commands above to view output."
echo ""
