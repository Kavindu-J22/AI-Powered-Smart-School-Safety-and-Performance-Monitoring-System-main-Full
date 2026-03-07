#!/bin/bash

# Performance Prediction Integration - Quick Setup
# This script sets up everything needed to run performance predictions

echo ""
echo "╔════════════════════════════════════════════════════════════════════════════╗"
echo "║         PERFORMANCE PREDICTION INTEGRATION - QUICK SETUP                   ║"
echo "╚════════════════════════════════════════════════════════════════════════════╝"
echo ""

LARAVEL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main" && pwd)"

echo "🔍 Checking prerequisites..."
echo ""

# Check if Laravel exists
if [ ! -d "$LARAVEL_DIR" ]; then
    echo "❌ Laravel directory not found"
    exit 1
fi

echo "✅ Laravel directory found: $LARAVEL_DIR"

# Check if Python is available
if ! command -v python3 &> /dev/null; then
    echo "❌ Python 3 is required but not installed"
    exit 1
fi

echo "✅ Python 3 is available"

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "❌ PHP is required but not installed"
    exit 1
fi

echo "✅ PHP is available"

echo ""
echo "═══════════════════════════════════════════════════════════════════════════"
echo "📋 SETUP STEPS"
echo "═══════════════════════════════════════════════════════════════════════════"
echo ""

# Step 1: Run Database Seeder
echo "1️⃣  Running database seeder to populate marks and attendance..."
cd "$LARAVEL_DIR"

if php artisan db:seed --class=StudentMarksAndAttendanceSeeder; then
    echo "✅ Database seeded successfully"
else
    echo "❌ Failed to seed database"
    echo "   Trying with --force flag..."
    php artisan db:seed --class=StudentMarksAndAttendanceSeeder --force || exit 1
fi

echo ""
echo "═══════════════════════════════════════════════════════════════════════════"
echo "✅ SETUP COMPLETE"
echo "═══════════════════════════════════════════════════════════════════════════"
echo ""
echo "🚀 Next Steps:"
echo ""
echo "1. Start all services in the main project directory:"
echo "   cd /path/to/project"
echo "   ./start_all_services.sh"
echo ""
echo "2. Wait for services to start (10-15 seconds)"
echo ""
echo "3. Open dashboard in your browser:"
echo "   http://127.0.0.1:8000"
echo ""
echo "4. Navigate to a student view:"
echo "   Admin > Students > Management > Click on any student"
echo ""
echo "5. You should see the 'Performance Prediction (AI Powered)' card"
echo ""
echo "📊 Example URLs:"
echo "   http://127.0.0.1:8000/admin/management/students/show/53"
echo "   http://127.0.0.1:8000/admin/management/students/show/54"
echo "   http://127.0.0.1:8000/admin/management/students/show/55"
echo ""
echo "🐛 To check if prediction API is running:"
echo "   curl http://127.0.0.1:5002/health"
echo ""
echo "📝 For troubleshooting, see:"
echo "   PERFORMANCE_PREDICTION_INTEGRATION.md"
echo ""
