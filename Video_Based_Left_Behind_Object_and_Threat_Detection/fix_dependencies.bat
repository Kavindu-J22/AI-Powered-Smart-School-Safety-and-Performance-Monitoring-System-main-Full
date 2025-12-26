@echo off
echo ========================================
echo Fixing NumPy and OpenCV Compatibility
echo ========================================
echo.

echo Step 1: Uninstalling incompatible packages...
pip uninstall numpy opencv-python opencv-contrib-python -y

echo.
echo Step 2: Installing NumPy 1.26.4 (compatible version)...
pip install numpy==1.26.4

echo.
echo Step 3: Installing OpenCV 4.9.0.80 (compatible with NumPy 1.x)...
pip install opencv-python==4.9.0.80 opencv-contrib-python==4.9.0.80

echo.
echo Step 4: Reinstalling Ultralytics to ensure compatibility...
pip install --upgrade --force-reinstall ultralytics

echo.
echo Step 5: Verifying installation...
python -c "import numpy; print(f'NumPy version: {numpy.__version__}')"
python -c "import cv2; print(f'OpenCV version: {cv2.__version__}')"
python -c "from ultralytics import YOLO; print('Ultralytics YOLO imported successfully!')"

echo.
echo ========================================
echo Installation Complete!
echo ========================================
echo.
echo You can now run: python app.py
pause

