# Quick Startup Guide
## AI-Powered Smart School Safety and Performance Monitoring System

---

## 🚀 Quick Start (All Systems)

### **Option 1: Automated Startup (Recommended)**

Run this single PowerShell command to start all services:

```powershell
# Start all services in separate terminals
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main'; php artisan serve --port=8000"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING'; python app.py"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd 'Audio-Based_Threat_Detection'; python app.py"
Start-Sleep -Seconds 5
Start-Process "http://127.0.0.1:8000"
```

---

## 📋 Manual Startup (Step by Step)

### **Step 1: Start Laravel Web Application**

```powershell
cd AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main
php artisan serve --port=8000
```

**Expected Output:**
```
INFO  Server running on [http://127.0.0.1:8000]
```

---

### **Step 2: Start Homework Management API**

Open a **new terminal** and run:

```powershell
cd AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING
python app.py
```

**Expected Output:**
```
* Running on http://127.0.0.1:5001
AI Homework Management API started successfully
```

---

### **Step 3: Start Audio Threat Detection API**

Open a **new terminal** and run:

```powershell
cd Audio-Based_Threat_Detection
python app.py
```

**Expected Output:**
```
* Running on http://127.0.0.1:5002
Audio Threat Detection API started successfully
```

---

### **Step 4: Access the Application**

Open your browser and navigate to:
```
http://127.0.0.1:8000
```

---

## ✅ Verify All Services

Run this command to check if all services are running:

```powershell
Write-Host "`n=== SERVICE STATUS CHECK ===`n" -ForegroundColor Cyan

# Check Laravel
try {
    $r1 = curl http://127.0.0.1:8000 -UseBasicParsing -TimeoutSec 3
    Write-Host "✓ Laravel App (Port 8000): RUNNING" -ForegroundColor Green
} catch {
    Write-Host "✗ Laravel App (Port 8000): NOT RUNNING" -ForegroundColor Red
}

# Check Homework API
try {
    $r2 = curl http://127.0.0.1:5001/api/health -UseBasicParsing -TimeoutSec 3 | ConvertFrom-Json
    Write-Host "✓ Homework API (Port 5001): $($r2.status)" -ForegroundColor Green
} catch {
    Write-Host "✗ Homework API (Port 5001): NOT RUNNING" -ForegroundColor Red
}

# Check Audio API
try {
    $r3 = curl http://127.0.0.1:5002/api/audio/health -UseBasicParsing -TimeoutSec 3 | ConvertFrom-Json
    Write-Host "✓ Audio API (Port 5002): $($r3.status)" -ForegroundColor Green
} catch {
    Write-Host "✗ Audio API (Port 5002): NOT RUNNING" -ForegroundColor Red
}

Write-Host ""
```

---

## 🔧 Prerequisites Check

Before starting, ensure you have:

- ✅ **PHP** 8.4+ installed
- ✅ **Composer** 2.9+ installed
- ✅ **Node.js** 18+ and npm installed
- ✅ **Python** 3.10+ installed
- ✅ **MySQL** 8.x running
- ✅ Database `safe_learn_hub` created
- ✅ All dependencies installed

---

## 📦 First-Time Setup (Run Once)

If this is your first time, run these commands:

### 1. **Install Laravel Dependencies**
```powershell
cd AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main
composer install
npm install
npm run build
php artisan migrate
```

### 2. **Install Python Dependencies (Homework API)**
```powershell
cd AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING
pip install -r requirements.txt
```

### 3. **Install Python Dependencies (Audio API)**
```powershell
cd Audio-Based_Threat_Detection
pip install -r requirements.txt
```

### 4. **Install Python Dependencies (Video System)**
```powershell
cd Video_Based_Left_Behind_Object_and_Threat_Detection
pip install -r requirements.txt
```

---

## 🌐 Service URLs

| Service | URL | Health Check |
|---------|-----|--------------|
| **Laravel Web App** | http://127.0.0.1:8000 | http://127.0.0.1:8000 |
| **Homework API** | http://127.0.0.1:5001 | http://127.0.0.1:5001/api/health |
| **Audio API** | http://127.0.0.1:5002 | http://127.0.0.1:5002/api/audio/health |

---

## 🛑 Stopping Services

Press `Ctrl + C` in each terminal window to stop the respective service.

Or use this command to kill all services:

```powershell
# Kill all PHP and Python processes (use with caution)
Get-Process | Where-Object {$_.ProcessName -match "php|python"} | Stop-Process -Force
```

---

## 🐛 Troubleshooting

### **Port Already in Use**
```powershell
# Find process using port 8000
netstat -ano | findstr :8000

# Kill process by PID
taskkill /PID <PID> /F
```

### **Database Connection Error**
- Check MySQL is running
- Verify `.env` file has correct database credentials
- Ensure database `safe_learn_hub` exists

### **Python Module Not Found**
```powershell
# Reinstall requirements
pip install -r requirements.txt --force-reinstall
```

---

## 📱 Default Login Credentials

After registration, you can create admin users via:
```powershell
php artisan tinker
```

Then run:
```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@school.com';
$user->password = bcrypt('password');
$user->save();
```

---

**Last Updated**: December 25, 2025

