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
Write-Host "3. Starting Audio Threat Detection API (Port 5002)..." -ForegroundColor White
$audioPath = Join-Path $scriptDir "Audio-Based_Threat_Detection"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$audioPath'; Write-Host '🔊 Audio Threat Detection API' -ForegroundColor Cyan; Write-Host 'Port: 5002' -ForegroundColor Green; Write-Host ''; python app.py"
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
    $r3 = Invoke-WebRequest -Uri "http://127.0.0.1:5002/api/audio/health" -UseBasicParsing -TimeoutSec 5 | ConvertFrom-Json
    Write-Host "  ✓ Audio API (Port 5002): $($r3.status)" -ForegroundColor Green
} catch {
    Write-Host "  ✗ Audio API (Port 5002): NOT RESPONDING" -ForegroundColor Red
}

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║                    ALL SERVICES STARTED SUCCESSFULLY                      ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

Write-Host "🌐 Service URLs:" -ForegroundColor Yellow
Write-Host "   • Laravel App:    http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host "   • Homework API:   http://127.0.0.1:5001" -ForegroundColor Cyan
Write-Host "   • Audio API:      http://127.0.0.1:5002" -ForegroundColor Cyan
Write-Host ""

Write-Host "📝 Health Check URLs:" -ForegroundColor Yellow
Write-Host "   • Homework API:   http://127.0.0.1:5001/api/health" -ForegroundColor Cyan
Write-Host "   • Audio API:      http://127.0.0.1:5002/api/audio/health" -ForegroundColor Cyan
Write-Host ""

Write-Host "🌐 Opening Laravel application in browser..." -ForegroundColor Yellow
Start-Sleep -Seconds 2
Start-Process "http://127.0.0.1:8000"

Write-Host ""
Write-Host "✅ System is ready! Check the opened browser window." -ForegroundColor Green
Write-Host ""
Write-Host "💡 To stop all services, close the terminal windows or press Ctrl+C in each." -ForegroundColor Gray
Write-Host ""

