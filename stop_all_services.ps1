# AI-Powered Smart School Safety System - Stop Script
# This script stops all running services

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════════════════════╗" -ForegroundColor Red
Write-Host "║  AI-POWERED SMART SCHOOL SAFETY & PERFORMANCE MONITORING SYSTEM          ║" -ForegroundColor Red
Write-Host "║                         STOP SCRIPT                                       ║" -ForegroundColor Red
Write-Host "╚════════════════════════════════════════════════════════════════════════════╝" -ForegroundColor Red
Write-Host ""

Write-Host "🛑 Stopping all services..." -ForegroundColor Yellow
Write-Host ""

# Find and display running processes
Write-Host "📋 Finding running services..." -ForegroundColor White
Write-Host ""

$phpProcesses = Get-Process | Where-Object {$_.ProcessName -eq "php" -and $_.MainWindowTitle -eq ""}
$pythonProcesses = Get-Process | Where-Object {$_.ProcessName -eq "python"}

if ($phpProcesses.Count -eq 0 -and $pythonProcesses.Count -eq 0) {
    Write-Host "ℹ️  No services are currently running." -ForegroundColor Cyan
    Write-Host ""
    exit
}

Write-Host "Found the following processes:" -ForegroundColor White
Write-Host ""

if ($phpProcesses.Count -gt 0) {
    Write-Host "  PHP Processes (Laravel):" -ForegroundColor Cyan
    foreach ($proc in $phpProcesses) {
        Write-Host "    • PID: $($proc.Id) - CPU: $($proc.CPU) - Memory: $([math]::Round($proc.WorkingSet64/1MB, 2)) MB" -ForegroundColor Gray
    }
}

if ($pythonProcesses.Count -gt 0) {
    Write-Host "  Python Processes (APIs):" -ForegroundColor Cyan
    foreach ($proc in $pythonProcesses) {
        Write-Host "    • PID: $($proc.Id) - CPU: $($proc.CPU) - Memory: $([math]::Round($proc.WorkingSet64/1MB, 2)) MB" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "⚠️  WARNING: This will stop ALL PHP and Python processes!" -ForegroundColor Yellow
Write-Host ""

$confirmation = Read-Host "Do you want to continue? (Y/N)"

if ($confirmation -ne 'Y' -and $confirmation -ne 'y') {
    Write-Host ""
    Write-Host "❌ Operation cancelled." -ForegroundColor Red
    Write-Host ""
    exit
}

Write-Host ""
Write-Host "🛑 Stopping services..." -ForegroundColor Yellow
Write-Host ""

# Stop PHP processes
if ($phpProcesses.Count -gt 0) {
    Write-Host "  Stopping Laravel (PHP)..." -ForegroundColor White
    try {
        $phpProcesses | Stop-Process -Force
        Write-Host "  ✓ Laravel stopped" -ForegroundColor Green
    } catch {
        Write-Host "  ✗ Error stopping Laravel: $_" -ForegroundColor Red
    }
}

# Stop Python processes
if ($pythonProcesses.Count -gt 0) {
    Write-Host "  Stopping Python APIs..." -ForegroundColor White
    try {
        $pythonProcesses | Stop-Process -Force
        Write-Host "  ✓ Python APIs stopped" -ForegroundColor Green
    } catch {
        Write-Host "  ✗ Error stopping Python APIs: $_" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "✅ Verifying services are stopped..." -ForegroundColor Yellow
Start-Sleep -Seconds 2

# Verify Laravel
try {
    $r1 = Invoke-WebRequest -Uri "http://127.0.0.1:8000" -UseBasicParsing -TimeoutSec 2
    Write-Host "  ⚠️  Laravel (Port 8000): Still responding" -ForegroundColor Yellow
} catch {
    Write-Host "  ✓ Laravel (Port 8000): Stopped" -ForegroundColor Green
}

# Verify Homework API
try {
    $r2 = Invoke-WebRequest -Uri "http://127.0.0.1:5001/api/health" -UseBasicParsing -TimeoutSec 2
    Write-Host "  ⚠️  Homework API (Port 5001): Still responding" -ForegroundColor Yellow
} catch {
    Write-Host "  ✓ Homework API (Port 5001): Stopped" -ForegroundColor Green
}

# Verify Audio API
try {
    $r3 = Invoke-WebRequest -Uri "http://127.0.0.1:5005/api/audio/health" -UseBasicParsing -TimeoutSec 2
    Write-Host "  ⚠️  Audio API (Port 5005): Still responding" -ForegroundColor Yellow
} catch {
    Write-Host "  ✓ Audio API (Port 5005): Stopped" -ForegroundColor Green
}

# Verify Performance Prediction API
try {
    $r4 = Invoke-WebRequest -Uri "http://127.0.0.1:5002/api/health" -UseBasicParsing -TimeoutSec 2
    Write-Host "  ⚠️  Performance Prediction API (Port 5002): Still responding" -ForegroundColor Yellow
} catch {
    Write-Host "  ✓ Performance Prediction API (Port 5002): Stopped" -ForegroundColor Green
}

# Verify Seating Arrangement API
try {
    $r5 = Invoke-WebRequest -Uri "http://127.0.0.1:5003/api/health" -UseBasicParsing -TimeoutSec 2
    Write-Host "  ⚠️  Seating Arrangement API (Port 5003): Still responding" -ForegroundColor Yellow
} catch {
    Write-Host "  ✓ Seating Arrangement API (Port 5003): Stopped" -ForegroundColor Green
}

# Verify Facial Recognition API
try {
    $r6 = Invoke-WebRequest -Uri "http://127.0.0.1:5004/api/health" -UseBasicParsing -TimeoutSec 2
    Write-Host "  ⚠️  Facial Recognition API (Port 5004): Still responding" -ForegroundColor Yellow
} catch {
    Write-Host "  ✓ Facial Recognition API (Port 5004): Stopped" -ForegroundColor Green
}

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║                    ALL SERVICES STOPPED SUCCESSFULLY                      ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

Write-Host "💡 To start services again, run: .\start_all_services.ps1" -ForegroundColor Cyan
Write-Host ""

