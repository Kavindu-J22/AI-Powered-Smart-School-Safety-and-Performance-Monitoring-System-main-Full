# 🚀 Quick Startup Documentation
## AI-Powered Smart School Safety and Performance Monitoring System

---

## 📚 Available Documentation

This project includes several helpful documents:

1. **QUICK_STARTUP_GUIDE.md** - Detailed startup instructions
2. **SYSTEM_STATUS_REPORT.md** - Current system status and configuration
3. **start_all_services.ps1** - Automated startup script
4. **stop_all_services.ps1** - Automated stop script

---

## ⚡ Fastest Way to Start (Recommended)

### **Single Command Startup**

Simply run this PowerShell script:

```powershell
.\start_all_services.ps1
```

This will:
- ✅ Start Laravel Web Application (Port 8000)
- ✅ Start Homework Management API (Port 5001)
- ✅ Start Audio Threat Detection API (Port 5002)
- ✅ Verify all services are running
- ✅ Open the application in your browser

---

## 🛑 Stop All Services

To stop all running services:

```powershell
.\stop_all_services.ps1
```

---

## 📋 Manual Startup (Alternative)

If you prefer to start services manually, open **3 separate terminals**:

### Terminal 1 - Laravel
```powershell
cd AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main
php artisan serve --port=8000
```

### Terminal 2 - Homework API
```powershell
cd AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING
python app.py
```

### Terminal 3 - Audio API
```powershell
cd Audio-Based_Threat_Detection
python app.py
```

Then open: **http://127.0.0.1:8000**

---

## ✅ Quick Health Check

Run this to verify all services:

```powershell
# Laravel
curl http://127.0.0.1:8000 -UseBasicParsing

# Homework API
curl http://127.0.0.1:5001/api/health -UseBasicParsing | ConvertFrom-Json

# Audio API
curl http://127.0.0.1:5002/api/audio/health -UseBasicParsing | ConvertFrom-Json
```

---

## 🌐 Service URLs

| Service | URL | Purpose |
|---------|-----|---------|
| **Main Application** | http://127.0.0.1:8000 | Web interface for all features |
| **Homework API** | http://127.0.0.1:5001 | AI-powered homework management |
| **Audio Detection API** | http://127.0.0.1:5002 | Real-time audio threat detection |

---

## 🔧 System Requirements

- **PHP**: 8.4+
- **Composer**: 2.9+
- **Node.js**: 18+
- **Python**: 3.10+
- **MySQL**: 8.x
- **Operating System**: Windows 10/11

---

## 📦 First-Time Setup

If this is your first time running the system, you need to install dependencies:

### 1. Laravel Dependencies
```powershell
cd AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main
composer install
npm install
npm run build
```

### 2. Database Setup
```powershell
# Create database (in MySQL)
CREATE DATABASE safe_learn_hub;

# Run migrations
php artisan migrate
```

### 3. Python Dependencies
```powershell
# Homework API
cd AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING
pip install -r requirements.txt

# Audio API
cd ..\Audio-Based_Threat_Detection
pip install -r requirements.txt

# Video System (Optional)
cd ..\Video_Based_Left_Behind_Object_and_Threat_Detection
pip install -r requirements.txt
```

---

## 🎯 Key Features

### 🌐 Laravel Web Application
- Student & Teacher Management
- Attendance Tracking
- Timetable Management
- Homework Assignment & Grading
- Performance Analytics
- Notifications System

### 📚 Homework Management API
- AI-powered question generation
- Automatic grading
- Performance tracking
- Supports: Science, History, English, Health Science
- Grades: 6-11

### 🔊 Audio Threat Detection API
- Real-time audio monitoring
- Threat detection (screaming, glass breaking, gunshots)
- Multi-language speech recognition
- 96.36% accuracy
- <3 second latency

### 📹 Video Detection System
- YOLOv8 object detection
- Left-behind object detection
- Threat behavior analysis
- ESP32-CAM integration
- Multi-channel alerts

---

## 🐛 Troubleshooting

### Port Already in Use
```powershell
# Find and kill process on port 8000
netstat -ano | findstr :8000
taskkill /PID <PID> /F
```

### Database Connection Error
- Verify MySQL is running
- Check `.env` file in Laravel directory
- Ensure database `safe_learn_hub` exists

### Python Module Not Found
```powershell
pip install -r requirements.txt --force-reinstall
```

---

## 📞 Support

For detailed information, refer to:
- **QUICK_STARTUP_GUIDE.md** - Complete startup guide
- **SYSTEM_STATUS_REPORT.md** - System configuration details

---

**Last Updated**: December 25, 2025
**Status**: ✅ All Systems Operational

