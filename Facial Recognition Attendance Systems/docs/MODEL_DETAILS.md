# Model & Technology Stack

The system leverages advanced Deep Learning models for biometric identification and security.

## 1. Face Detection Backends

| Model          | Framework        | Strength             | Use Case             |
| -------------- | ---------------- | -------------------- | -------------------- |
| **MTCNN**      | PyTorch          | Reliable landmarks   | General stability    |
| **RetinaFace** | InsightFace      | High recall/accuracy | Crowded environments |
| **MediaPipe**  | Google BlazeFace | High FPS             | Mobile/Web browsers  |

## 2. Face Recognition (Feature Extraction)

### FaceNet (Inception-ResNet v1)

The default recognizer uses the FaceNet architecture trained on VGGFace2. It maps face images to a 128 or 512-dimensional Euclidean space where distances directly correspond to a measure of face similarity.

### ArcFace (Additive Angular Margin Loss)

An optional backend for higher precision. It enhances the discriminative power of face embeddings by mapping them onto a hypersphere and maximizing geodesic distance between classes.

## 3. Anti-Spoofing (Liveness Detection)

To prevent "presentation attacks" (holding a photo or tablet in front of the camera), the system uses a multi-layered approach:

### LBP-Based Texture Analysis

Local Binary Patterns are used to analyze the micro-texture of the face. Human skin has distinct reflection patterns compared to printed paper or digital screens.

### EAR (Eye Aspect Ratio)

Using MediaPipe Face Mesh, the system calculates the ratio of eye height to width. A sudden drop in EAR followed by a rise indicates a natural blink, proving the presence of a living human.

### Vectorized Matching

Recognized faces are compared against a pre-loaded **Embedding Matrix** using Cosine Similarity:
$$ \text{Similarity} = \frac{A \cdot B}{\|A\| \|B\|} $$

## 4. Technology Stack

- **Language**: Python 3.9+
- **Deep Learning**: PyTorch, TensorFlow/MediaPipe
- **Computer Vision**: OpenCV (Open Source Computer Vision Library)
- **Database**: SQLite3
- **Network**: Requests (for Webhooks)
- **Concurrency**: Threading & Event-driven architecture
