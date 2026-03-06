
import sys
import numpy as np
import cv2
import logging
from pathlib import Path

# Add project root to path
sys.path.append(str(Path(__file__).parent))

from training.embedding_generator import EmbeddingGenerator
from core.face_recognizer import FaceRecognizer

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("VerifyBlur")

def verify_blur_detection():
    logger.info("Initializing components for blur verification...")
    
    try:
        # Create dummy recognizer (won't be used for blur check)
        recognizer = FaceRecognizer(backend='facenet', device='cpu')
        generator = EmbeddingGenerator(face_recognizer=recognizer)
        
        # 1. Create a sharp image (random noise but with some structure)
        sharp_img = np.zeros((160, 160, 3), dtype=np.uint8)
        cv2.putText(sharp_img, "SHARP TEXT", (20, 80), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (255, 255, 255), 2)
        
        is_blurry_sharp, var_sharp = generator.is_image_blurry(sharp_img)
        logger.info(f"Sharp image - Is Blurry: {is_blurry_sharp}, Variance: {var_sharp:.2f}")
        
        # 2. Create a blurry image
        blurry_img = cv2.GaussianBlur(sharp_img, (15, 15), 0)
        is_blurry_blur, var_blur = generator.is_image_blurry(blurry_img)
        logger.info(f"Blurry image - Is Blurry: {is_blurry_blur}, Variance: {var_blur:.2f}")
        
        success = True
        if is_blurry_sharp:
            logger.error("FAIL: Sharp image flagged as blurry")
            success = False
        else:
            logger.info("PASS: Sharp image correctly identified")
            
        if not is_blurry_blur:
            logger.error("FAIL: Blurry image not flagged")
            success = False
        else:
            logger.info("PASS: Blurry image correctly detected")
            
        if success:
            logger.info("Blur detection verification successful!")
        return success
        
    except Exception as e:
        logger.error(f"Error during blur verification: {e}")
        return False

if __name__ == "__main__":
    if verify_blur_detection():
        sys.exit(0)
    else:
        sys.exit(1)
