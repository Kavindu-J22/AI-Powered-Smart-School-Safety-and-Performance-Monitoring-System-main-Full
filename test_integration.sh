#!/bin/bash

# 🚀 Quick Start - Test Performance Prediction Integration
# Run this to verify everything is working

echo "🎓 Student Performance Prediction - Integration Tester"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "📋 Checking Prerequisites..."
echo ""

# Check if services are running
check_service() {
    local port=$1
    local name=$2
    
    if nc -z localhost $port 2>/dev/null; then
        echo -e "${GREEN}✅${NC} $name (Port $port) - Running"
        return 0
    else
        echo -e "${RED}❌${NC} $name (Port $port) - Not Running"
        return 1
    fi
}

echo "🔌 Service Status:"
check_service 8000 "Laravel Dashboard"
check_service 5002 "Prediction API"
echo ""

# Test database seeding
echo "📊 Database Status:"
php_check=$(cd "AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main" && php artisan tinker --execute="echo json_encode(['marks' => \App\Models\Mark::count(), 'attendance' => \App\Models\Attendance::count()]); die();" 2>/dev/null)

if [ ! -z "$php_check" ]; then
    echo -e "${GREEN}✅${NC} Database Connected"
    echo "   Records: $php_check"
else
    echo -e "${RED}⚠️ ${NC} Unable to verify database"
fi
echo ""

# Test Prediction API Health
echo "🏥 Prediction API Health:"
health_check=$(curl -s http://127.0.0.1:5002/health 2>/dev/null)

if echo "$health_check" | grep -q "healthy\|ok"; then
    echo -e "${GREEN}✅${NC} Prediction API - Healthy"
else
    echo -e "${RED}❌${NC} Prediction API - Not Responding"
    echo "   Make sure ./start_all_services.sh has been run"
fi
echo ""

# Test Student Prediction Endpoint
echo "🎯 Testing Prediction Endpoint:"
prediction=$(curl -s -H "Accept: application/json" http://127.0.0.1:5002/predict -X POST -d '{"student_id": 53, "age": 15, "grade": 10, "subjects": [{"subject_name": "Math", "marks": 85, "attendance": 92}]}' 2>/dev/null)

if echo "$prediction" | grep -q "predicted_performance\|prediction"; then
    echo -e "${GREEN}✅${NC} Prediction API - Responding"
else
    echo -e "${YELLOW}⚠️ ${NC} Check Python API is running"
fi
echo ""

# Ready to test
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🎉 Integration Status:"
echo ""
echo "If all checks passed above, you're ready to:"
echo ""
echo "  1️⃣  Open: http://127.0.0.1:8000"
echo "  2️⃣  Login to Dashboard"
echo "  3️⃣  Go to Students → Select a Student"
echo "  4️⃣  Scroll down to see 'Performance Prediction' card"
echo ""
echo "📊 Test URLs:"
echo "   http://127.0.0.1:8000/admin/management/students/show/53"
echo "   http://127.0.0.1:8000/admin/management/students/show/54"
echo "   http://127.0.0.1:8000/admin/management/students/show/55"
echo ""
echo "📂 Files to check:"
echo "   - INTEGRATION_SUMMARY.md (This overview)"
echo "   - PERFORMANCE_PREDICTION_INTEGRATION.md (Full documentation)"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
