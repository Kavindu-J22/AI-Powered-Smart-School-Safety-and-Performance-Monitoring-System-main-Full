# AI-Powered Smart School Safety System - Startup Script
# This script starts all services in separate terminal windows

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  AI-POWERED SMART SCHOOL SAFETY & PERFORMANCE MONITORING SYSTEM          ║" -ForegroundColor Cyan
Write-Host "║                         STARTUP SCRIPT                                    ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Get the script directory
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path

Write-Host "🚀 Starting all services..." -ForegroundColor Yellow
Write-Host ""

# Start Laravel Application
Write-Host "1. Starting Laravel Web Application (Port 8000)..." -ForegroundColor White
$laravelPath = Join-Path $scriptDir "AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$laravelPath'; Write-Host '🌐 Laravel Web Application' -ForegroundColor Cyan; Write-Host 'Port: 8000' -ForegroundColor Green; Write-Host ''; php artisan serve --port=8000"
Start-Sleep -Seconds 2

# Start Homework Management API
Write-Host "2. Starting Homework Management API (Port 5001)..." -ForegroundColor White
$homeworkPath = Join-Path $scriptDir "AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$homeworkPath'; Write-Host '📚 Homework Management API' -ForegroundColor Cyan; Write-Host 'Port: 5001' -ForegroundColor Green; Write-Host ''; python app.py"
Start-Sleep -Seconds 2

# Start Audio Threat Detection API
Write-Host "3. Starting Audio Threat Detection API (Port 5005)..." -ForegroundColor White
$audioPath = Join-Path $scriptDir "Audio-Based_Threat_Detection"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$audioPath'; Write-Host '🔊 Audio Threat Detection API' -ForegroundColor Cyan; Write-Host 'Port: 5005' -ForegroundColor Green; Write-Host ''; `$env:FLASK_PORT=5005; python app.py"
Start-Sleep -Seconds 2

# Start Student Performance Prediction API
Write-Host "4. Starting Student Performance Prediction API (Port 5002)..." -ForegroundColor White
$performancePath = Join-Path $scriptDir "student-performance-prediction-model"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$performancePath'; Write-Host '📊 Student Performance Prediction API' -ForegroundColor Cyan; Write-Host 'Port: 5002' -ForegroundColor Green; Write-Host ''; python api/app.py"
Start-Sleep -Seconds 2

# Start Student Seating Arrangement API
Write-Host "5. Starting Student Seating Arrangement API (Port 5003)..." -ForegroundColor White
$seatingPath = Join-Path $scriptDir "student-seating-arrangement-model"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$seatingPath'; Write-Host '🎓 Student Seating Arrangement API' -ForegroundColor Cyan; Write-Host 'Port: 5003' -ForegroundColor Green; Write-Host ''; python api/app.py"
Start-Sleep -Seconds 2

# Start Facial Recognition Attendance API
Write-Host "6. Starting Facial Recognition Attendance API (Port 5004)..." -ForegroundColor White
$facialPath = Join-Path $scriptDir "Facial Recognition Attendance Systems"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$facialPath'; Write-Host '👤 Facial Recognition Attendance API' -ForegroundColor Cyan; Write-Host 'Port: 5004' -ForegroundColor Green; Write-Host ''; python app.py"
Start-Sleep -Seconds 2

Write-Host ""
Write-Host "⏳ Waiting for services to start..." -ForegroundColor Yellow
Start-Sleep -Seconds 8

Write-Host ""
Write-Host "✅ Verifying services..." -ForegroundColor Yellow
Write-Host ""

# Check Laravel
try {
    $r1 = Invoke-WebRequest -Uri "http://127.0.0.1:8000" -UseBasicParsing -TimeoutSec 5
    Write-Host "  ✓ Laravel App (Port 8000): RUNNING" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Laravel App (Port 8000): NOT RESPONDING" -ForegroundColor Red
}

# Check Homework API
try {
    $r2 = Invoke-WebRequest -Uri "http://127.0.0.1:5001/api/health" -UseBasicParsing -TimeoutSec 5 | ConvertFrom-Json
    Write-Host "  ✓ Homework API (Port 5001): $($r2.status)" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Homework API (Port 5001): NOT RESPONDING" -ForegroundColor Red
}

# Check Audio API
try {
    $r3 = Invoke-WebRequest -Uri "http://127.0.0.1:5005/api/audio/health" -UseBasicParsing -TimeoutSec 5 | ConvertFrom-Json
    Write-Host "  ✓ Audio API (Port 5005): $($r3.status)" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Audio API (Port 5005): NOT RESPONDING" -ForegroundColor Red
}

# Check Performance Prediction API
try {
    $r4 = Invoke-WebRequest -Uri "http://127.0.0.1:5002/api/health" -UseBasicParsing -TimeoutSec 5 | ConvertFrom-Json
    Write-Host "  ✓ Performance Prediction API (Port 5002): $($r4.status)" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Performance Prediction API (Port 5002): NOT RESPONDING" -ForegroundColor Red
}

# Check Seating Arrangement API
try {
    $r5 = Invoke-WebRequest -Uri "http://127.0.0.1:5003/api/health" -UseBasicParsing -TimeoutSec 5 | ConvertFrom-Json
    Write-Host "  ✓ Seating Arrangement API (Port 5003): $($r5.status)" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Seating Arrangement API (Port 5003): NOT RESPONDING" -ForegroundColor Red
}

# Check Facial Recognition API
try {
    $r6 = Invoke-WebRequest -Uri "http://127.0.0.1:5004/api/health" -UseBasicParsing -TimeoutSec 5 | ConvertFrom-Json
    Write-Host "  ✓ Facial Recognition API (Port 5004): $($r6.status)" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Facial Recognition API (Port 5004): NOT RESPONDING" -ForegroundColor Red
}

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║                    ALL SERVICES STARTED SUCCESSFULLY                      ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

Write-Host "🌐 Service URLs:" -ForegroundColor Yellow
Write-Host "   • Laravel App:              http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host "   • Homework API:             http://127.0.0.1:5001" -ForegroundColor Cyan
Write-Host "   • Audio Threat API:         http://127.0.0.1:5005" -ForegroundColor Cyan
Write-Host "   • Performance Prediction:   http://127.0.0.1:5002" -ForegroundColor Cyan
Write-Host "   • Seating Arrangement:      http://127.0.0.1:5003" -ForegroundColor Cyan
Write-Host "   • Facial Recognition:       http://127.0.0.1:5004" -ForegroundColor Cyan
Write-Host ""

Write-Host "📝 Health Check URLs:" -ForegroundColor Yellow
Write-Host "   • Homework API:             http://127.0.0.1:5001/api/health" -ForegroundColor Cyan
Write-Host "   • Audio Threat API:         http://127.0.0.1:5005/api/audio/health" -ForegroundColor Cyan
Write-Host "   • Performance Prediction:   http://127.0.0.1:5002/api/health" -ForegroundColor Cyan
Write-Host "   • Seating Arrangement:      http://127.0.0.1:5003/api/health" -ForegroundColor Cyan
Write-Host "   • Facial Recognition:       http://127.0.0.1:5004/api/health" -ForegroundColor Cyan
Write-Host ""

Write-Host "🌐 Opening Laravel application in browser..." -ForegroundColor Yellow
Start-Sleep -Seconds 2
Start-Process "http://127.0.0.1:8000"

Write-Host ""
Write-Host "✅ System is ready! Check the opened browser window." -ForegroundColor Green
Write-Host ""
Write-Host "💡 To stop all services, close the terminal windows or press Ctrl+C in each." -ForegroundColor Gray
Write-Host ""

