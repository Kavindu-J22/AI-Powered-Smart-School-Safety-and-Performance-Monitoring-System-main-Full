#!/bin/bash

# AI-Powered Smart School Safety System - Stop Script (macOS/Linux)
# This script stops all running services

clear

echo ""
echo "╔════════════════════════════════════════════════════════════════════════════╗"
echo "║  AI-POWERED SMART SCHOOL SAFETY & PERFORMANCE MONITORING SYSTEM          ║"
echo "║                         STOP SCRIPT                                       ║"
echo "╚════════════════════════════════════════════════════════════════════════════╝"
echo ""

echo "🛑 Stopping all services..."
echo ""

echo "📋 Finding running services..."
echo ""

# Find processes on specific ports
find_process_on_port() {
    lsof -i :$1 2>/dev/null | grep LISTEN | awk '{print $2}' | head -1
}

# Get PIDs for each service
LARAVEL_PID=$(find_process_on_port 8000)
HOMEWORK_PID=$(find_process_on_port 5001)
AUDIO_PID=$(find_process_on_port 5005)
PERFORMANCE_PID=$(find_process_on_port 5002)
SEATING_PID=$(find_process_on_port 5003)
FACIAL_PID=$(find_process_on_port 5004)

# Count processes
TOTAL_PIDS=0
[ -n "$LARAVEL_PID" ] && TOTAL_PIDS=$((TOTAL_PIDS + 1))
[ -n "$HOMEWORK_PID" ] && TOTAL_PIDS=$((TOTAL_PIDS + 1))
[ -n "$AUDIO_PID" ] && TOTAL_PIDS=$((TOTAL_PIDS + 1))
[ -n "$PERFORMANCE_PID" ] && TOTAL_PIDS=$((TOTAL_PIDS + 1))
[ -n "$SEATING_PID" ] && TOTAL_PIDS=$((TOTAL_PIDS + 1))
[ -n "$FACIAL_PID" ] && TOTAL_PIDS=$((TOTAL_PIDS + 1))

if [ $TOTAL_PIDS -eq 0 ]; then
    echo "ℹ️  No services are currently running."
    echo ""
    exit 0
fi

echo "Found the following running services:"
echo ""

[ -n "$LARAVEL_PID" ] && echo "  ✓ Laravel (Port 8000) - PID: $LARAVEL_PID"
[ -n "$HOMEWORK_PID" ] && echo "  ✓ Homework API (Port 5001) - PID: $HOMEWORK_PID"
[ -n "$AUDIO_PID" ] && echo "  ✓ Audio Threat API (Port 5005) - PID: $AUDIO_PID"
[ -n "$PERFORMANCE_PID" ] && echo "  ✓ Performance Prediction (Port 5002) - PID: $PERFORMANCE_PID"
[ -n "$SEATING_PID" ] && echo "  ✓ Seating Arrangement (Port 5003) - PID: $SEATING_PID"
[ -n "$FACIAL_PID" ] && echo "  ✓ Facial Recognition (Port 5004) - PID: $FACIAL_PID"

echo ""
read -p "Do you want to stop these services? (Y/N): " confirmation

if [[ "$confirmation" != "Y" && "$confirmation" != "y" ]]; then
    echo ""
    echo "❌ Operation cancelled."
    echo ""
    exit 0
fi

echo ""
echo "🛑 Stopping services..."
echo ""

# Stop each service
stop_service() {
    local pid=$1
    local name=$2
    
    if [ -n "$pid" ]; then
        kill $pid 2>/dev/null
        sleep 1
        if kill -0 $pid 2>/dev/null; then
            kill -9 $pid 2>/dev/null
        fi
        echo "  ✓ $name stopped"
    fi
}

stop_service "$LARAVEL_PID" "Laravel (Port 8000)"
stop_service "$HOMEWORK_PID" "Homework API (Port 5001)"
stop_service "$AUDIO_PID" "Audio Threat API (Port 5005)"
stop_service "$PERFORMANCE_PID" "Performance Prediction (Port 5002)"
stop_service "$SEATING_PID" "Seating Arrangement (Port 5003)"
stop_service "$FACIAL_PID" "Facial Recognition (Port 5004)"

echo ""
echo "✅ Verifying services are stopped..."
sleep 2

# Verify services
verify_stopped() {
    local port=$1
    local name=$2
    
    if ! lsof -i :$port >/dev/null 2>&1; then
        echo "  ✓ $name (Port $port): Stopped"
    else
        echo "  ⚠️  $name (Port $port): Still running"
    fi
}

verify_stopped 8000 "Laravel"
verify_stopped 5001 "Homework API"
verify_stopped 5005 "Audio API"
verify_stopped 5002 "Performance Prediction"
verify_stopped 5003 "Seating Arrangement"
verify_stopped 5004 "Facial Recognition"

echo ""
echo "╔════════════════════════════════════════════════════════════════════════════╗"
echo "║                    ALL SERVICES STOPPED SUCCESSFULLY                      ║"
echo "╚════════════════════════════════════════════════════════════════════════════╝"
echo ""

echo "💡 To start services again, run: ./start_all_services.sh"
echo ""
