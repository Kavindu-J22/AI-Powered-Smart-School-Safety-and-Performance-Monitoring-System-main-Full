import sys
print("Checking packages...")
print("Python:", sys.version)

import cv2
print("OpenCV:", cv2.__version__)

import numpy as np
print("NumPy:", np.__version__)

from sklearn.metrics import accuracy_score
print("Scikit-learn: OK")

import pandas
print("Pandas:", pandas.__version__)

import yaml
print("PyYAML: OK")

from tqdm import tqdm
print("tqdm: OK")

print("Basic packages OK! Now checking torch...")

import torch
print("PyTorch:", torch.__version__)
print("CUDA:", torch.cuda.is_available())

from ultralytics import YOLO
print("Ultralytics (YOLOv8): OK")

print("All packages available!")

