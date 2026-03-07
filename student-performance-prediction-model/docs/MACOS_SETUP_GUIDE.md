# macOS Setup & Usage Guide

## Quick Start

### 1. **Install Dependencies** (First time only)

```bash
./install_dependencies.sh
```

This installs all Python packages required for the services.

### 2. **Start All Services**

```bash
./start_all_services.sh
```

This starts all 6 services in the background:

- Laravel Web App (Port 8000)
- Homework Management API (Port 5001)
- Audio Threat Detection API (Port 5005)
- Student Performance Prediction API (Port 5002)
- Student Seating Arrangement API (Port 5003)
- Facial Recognition Attendance API (Port 5004)

### 3. **View Service Logs**

To view real-time logs from a service, use:

```bash
# View specific service logs
tail -f logs/homework.log
tail -f logs/performance.log
tail -f logs/seating.log
tail -f logs/audio.log
tail -f logs/facial.log
tail -f logs/laravel.log
```

### 4. **Access Services**

| Service                | URL                   | Notes               |
| ---------------------- | --------------------- | ------------------- |
| Laravel App            | http://127.0.0.1:8000 | Main web interface  |
| Homework API           | http://127.0.0.1:5001 | homework management |
| Performance Prediction | http://127.0.0.1:5002 | Student performance |
| Seating Arrangement    | http://127.0.0.1:5003 | Seating algorithms  |
| Facial Recognition     | http://127.0.0.1:5004 | Attendance marking  |
| Audio Threat API       | http://127.0.0.1:5005 | Threat detection    |

### 5. **Stop All Services**

```bash
./stop_all_services.sh
```

This will ask for confirmation, then stop all running services gracefully.

---

## Troubleshooting

### Services show as "STARTING/NOT RESPONDING"

- This is normal for the first check. Services take 5-10 seconds to warm up
- Check logs with `tail -f logs/[service].log` to see if they're initializing

### "Port already in use"

```bash
# Kill process on specific port (example: port 5001)
lsof -i :5001 | grep LISTEN | awk '{print $2}' | xargs kill -9
```

### Python dependency errors

Reinstall dependencies:

```bash
./install_dependencies.sh
```

### Permission denied on .sh files

Make them executable:

```bash
chmod +x *.sh
```

---

## What Each Script Does

### `install_dependencies.sh`

- Installs Python packages from requirements.txt for each service
- Uses `--break-system-packages` for development environments
- Should be run once before first startup

### `start_all_services.sh`

- Starts all 6 services in the background
- Creates `/logs` directory with service logs
- Performs health checks after startup
- Opens Laravel app in browser

### `stop_all_services.sh`

- Gracefully stops all running services
- Verifies they're stopped
- Asks for confirmation before stopping

---

## Running Individual Services

If you want to run a service manually:

```bash
# Performance Prediction
cd student-performance-prediction-model
python3 api/app.py

# Seating Arrangement
cd student-seating-arrangement-model
python3 api/app.py

# Homework Management
cd AI-POWERED_HOMEWORK_MANAGEMENT_AND_PERFORMANCE_MONITORING
python3 app.py

# Audio Threat Detection
cd Audio-Based_Threat_Detection
FLASK_PORT=5005 python3 app.py

# Facial Recognition
cd "Facial Recognition Attendance Systems"
python3 app.py

# Laravel (requires PHP)
cd AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main
php artisan serve --port=8000
```

---

## System Requirements

- macOS 10.15+
- Python 3.8+ (with pip)
- PHP 7.4+ (for Laravel)
- curl (for health checks)

---

## Notes

- Services run in background and continue running until you run `./stop_all_services.sh`
- All logs are stored in the `/logs` directory
- Each service gets its own log file
- The scripts are optimized for development on macOS

---

For issues or questions, check individual service documentation in their respective directories.
