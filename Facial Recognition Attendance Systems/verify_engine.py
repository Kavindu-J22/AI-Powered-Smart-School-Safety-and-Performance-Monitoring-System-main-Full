
import sys
import logging
from pathlib import Path

# Add project root to path
sys.path.append(str(Path(__file__).parent))

from core.attendance_engine import AttendanceEngine
from config.settings import Config

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("VerifyEngine")

def verify_engine_config():
    logger.info("Initializing AttendanceEngine with new settings...")
    
    try:
        engine = AttendanceEngine()
        
        logger.info(f"Engine Recognition Threshold: {engine.recognition_threshold}")
        logger.info(f"Detector Confidence Threshold: {engine.detector.confidence_threshold}")
        
        # Verify custom labels if any
        expected_recognition_threshold = 0.65
        expected_detector_threshold = 0.90
        
        success = True
        if engine.recognition_threshold != expected_recognition_threshold:
            logger.error(f"FAIL: Recognition threshold is {engine.recognition_threshold}, expected {expected_recognition_threshold}")
            success = False
        else:
            logger.info("PASS: Recognition threshold verified.")
            
        if engine.detector.confidence_threshold != expected_detector_threshold:
            logger.error(f"FAIL: Detector threshold is {engine.detector.confidence_threshold}, expected {expected_detector_threshold}")
            success = False
        else:
            logger.info("PASS: Detector threshold verified.")
            
        if success:
            logger.info("Configuration verification successful!")
        return success
        
    except Exception as e:
        logger.error(f"Error during engine initialization: {e}")
        return False

if __name__ == "__main__":
    if verify_engine_config():
        sys.exit(0)
    else:
        sys.exit(1)
