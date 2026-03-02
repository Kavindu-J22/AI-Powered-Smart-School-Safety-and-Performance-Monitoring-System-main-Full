"""
Non-Speech Threat Detection Model
Architecture: 1D-CNN (with SE blocks) + BiLSTM + Multi-head Attention
Uses Focal Loss + class-balanced sampling for robust detection of:
  crying | screaming | shouting | glass_breaking | normal
Using PyTorch for Python 3.12+ compatibility
"""
import numpy as np
import torch
import torch.nn as nn
import torch.nn.functional as F
import torch.optim as optim
from torch.utils.data import DataLoader, TensorDataset, WeightedRandomSampler
import os
import sys
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import ModelConfig, AudioConfig
from utils.feature_extractor import N_FEATURES


# ─── Focal Loss ───────────────────────────────────────────────────────────────
class FocalLoss(nn.Module):
    """
    Focal Loss — down-weights easy examples so the model focuses on hard ones.
    Ideal for class-imbalanced data (shouting 31 vs screaming 1826 files).
    """
    def __init__(self, alpha: torch.Tensor = None, gamma: float = 2.0):
        super().__init__()
        self.alpha = alpha   # per-class weight tensor
        self.gamma = gamma

    def forward(self, inputs: torch.Tensor, targets: torch.Tensor) -> torch.Tensor:
        ce = F.cross_entropy(inputs, targets, weight=self.alpha, reduction='none')
        pt = torch.exp(-ce)
        focal = ((1 - pt) ** self.gamma) * ce
        return focal.mean()


# ─── Squeeze-and-Excitation block ────────────────────────────────────────────
class SEBlock(nn.Module):
    """Channel attention: recalibrate channel-wise feature responses."""
    def __init__(self, channels: int, reduction: int = 8):
        super().__init__()
        self.fc = nn.Sequential(
            nn.Linear(channels, max(channels // reduction, 4)),
            nn.ReLU(),
            nn.Linear(max(channels // reduction, 4), channels),
            nn.Sigmoid()
        )

    def forward(self, x: torch.Tensor) -> torch.Tensor:
        # x: (batch, channels, time)
        w = x.mean(dim=2)          # global avg pool → (batch, channels)
        w = self.fc(w).unsqueeze(2)   # (batch, channels, 1)
        return x * w


# ─── Main Network ─────────────────────────────────────────────────────────────
class CNNLSTMNetwork(nn.Module):
    """
    1D-CNN + SE + BiLSTM + Multi-head Self-Attention classifier.
    input_features: number of acoustic features per time-frame (default 144)
    """

    def __init__(self, input_features: int = N_FEATURES, num_classes: int = 5):
        super().__init__()

        # ── CNN Block 1 ───────────────────────────────────────────────────
        self.conv1 = nn.Sequential(
            nn.Conv1d(input_features, 128, kernel_size=3, padding=1),
            nn.BatchNorm1d(128),
            nn.GELU(),
            nn.Conv1d(128, 128, kernel_size=3, padding=1),
            nn.BatchNorm1d(128),
            nn.GELU(),
            nn.MaxPool1d(2),
            nn.Dropout(0.2)
        )
        self.se1 = SEBlock(128)

        # ── CNN Block 2 ───────────────────────────────────────────────────
        self.conv2 = nn.Sequential(
            nn.Conv1d(128, 256, kernel_size=3, padding=1),
            nn.BatchNorm1d(256),
            nn.GELU(),
            nn.Conv1d(256, 256, kernel_size=3, padding=1),
            nn.BatchNorm1d(256),
            nn.GELU(),
            nn.MaxPool1d(2),
            nn.Dropout(0.2)
        )
        self.se2 = SEBlock(256)

        # ── CNN Block 3 ───────────────────────────────────────────────────
        self.conv3 = nn.Sequential(
            nn.Conv1d(256, 256, kernel_size=3, padding=1),
            nn.BatchNorm1d(256),
            nn.GELU(),
            nn.Dropout(0.25)
        )
        self.se3 = SEBlock(256)

        # ── Bidirectional LSTM ────────────────────────────────────────────
        self.lstm = nn.LSTM(256, 256, num_layers=2, batch_first=True,
                            bidirectional=True, dropout=0.3)

        # ── Multi-head Self-Attention over time-steps ─────────────────────
        self.attn = nn.MultiheadAttention(embed_dim=512, num_heads=8,
                                          dropout=0.1, batch_first=True)
        self.attn_norm = nn.LayerNorm(512)

        # ── Dense head ───────────────────────────────────────────────────
        # CNN global avg-pool (256) + attention-pooled LSTM (512) = 768
        self.fc = nn.Sequential(
            nn.Linear(256 + 512, 384),
            nn.BatchNorm1d(384),
            nn.GELU(),
            nn.Dropout(0.4),
            nn.Linear(384, 128),
            nn.BatchNorm1d(128),
            nn.GELU(),
            nn.Dropout(0.3),
            nn.Linear(128, num_classes)
        )

    def forward(self, x: torch.Tensor) -> torch.Tensor:
        # x: (batch, time_steps, n_features) → (batch, n_features, time_steps)
        x = x.transpose(1, 2)

        x = self.se1(self.conv1(x))   # (batch, 128, T/2)
        x = self.se2(self.conv2(x))   # (batch, 256, T/4)
        x = self.se3(self.conv3(x))   # (batch, 256, T/4)

        # CNN global average pool
        cnn_out = x.mean(dim=2)       # (batch, 256)

        # BiLSTM
        x = x.transpose(1, 2)        # (batch, T/4, 256)
        lstm_out, _ = self.lstm(x)   # (batch, T/4, 512)

        # Multi-head self-attention
        attn_out, _ = self.attn(lstm_out, lstm_out, lstm_out)
        attn_out = self.attn_norm(attn_out + lstm_out)  # residual
        attn_pool = attn_out.mean(dim=1)                # (batch, 512)

        combined = torch.cat([cnn_out, attn_pool], dim=1)   # (batch, 768)
        return self.fc(combined)


class NonSpeechThreatModel:
    """CNN + SE + BiLSTM + Attention model for non-speech threat detection"""

    def __init__(self):
        self.classes = ModelConfig.NON_SPEECH_CLASSES
        self.num_classes = len(self.classes)
        self.model = None
        self.model_path = str(ModelConfig.NON_SPEECH_MODEL_PATH).replace('.h5', '.pth')
        # (time_steps, n_features) — matches AudioConfig.CHUNK_DURATION + N_FEATURES
        self.input_shape = (128, N_FEATURES)
        self.device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')
        print(f"  Device: {self.device}  |  Features: {N_FEATURES}  |  Classes: {self.num_classes}")

    def build_model(self, input_features: int = None) -> nn.Module:
        """Build CNN+SE+BiLSTM+Attention architecture"""
        if input_features is None:
            input_features = self.input_shape[1]
        self.model = CNNLSTMNetwork(
            input_features=input_features,
            num_classes=self.num_classes
        ).to(self.device)
        return self.model

    def train(self, X_train: np.ndarray, y_train: np.ndarray,
              X_val: np.ndarray = None, y_val: np.ndarray = None,
              epochs: int = None, batch_size: int = None) -> dict:
        """Train with Focal Loss + AdamW + CosineAnnealingWarmRestarts + early stopping"""
        if self.model is None:
            self.build_model()

        epochs     = epochs     or ModelConfig.EPOCHS
        batch_size = batch_size or ModelConfig.BATCH_SIZE

        y_train_labels = np.argmax(y_train, axis=1)

        # ── Class distribution ──────────────────────────────────────────────
        n_classes    = self.num_classes
        class_counts = np.bincount(y_train_labels, minlength=n_classes).astype(np.float64)
        class_counts  = np.maximum(class_counts, 1)   # avoid div/0
        total_samples = float(len(y_train_labels))

        # Inverse-frequency weights (stronger correction for minority classes)
        class_weights_np = total_samples / (n_classes * class_counts)
        class_weights    = torch.FloatTensor(class_weights_np).to(self.device)

        print(f"\n  Class distribution (training):")
        for i, (cls, cnt) in enumerate(zip(self.classes, class_counts)):
            print(f"    {cls:20s}: {int(cnt):5d} samples  |  weight: {class_weights_np[i]:.4f}")

        # ── Weighted random sampler (over-sample minority classes in every batch) ──
        sample_weights = torch.DoubleTensor(
            [class_weights_np[lbl] for lbl in y_train_labels]
        )
        sampler = WeightedRandomSampler(sample_weights, len(sample_weights), replacement=True)

        X_train_t = torch.FloatTensor(X_train).to(self.device)
        y_train_t = torch.LongTensor(y_train_labels).to(self.device)
        train_dataset = TensorDataset(X_train_t, y_train_t)
        train_loader  = DataLoader(train_dataset, batch_size=batch_size,
                                   sampler=sampler, drop_last=True)

        if X_val is not None:
            X_val_t = torch.FloatTensor(X_val).to(self.device)
            y_val_t = torch.LongTensor(np.argmax(y_val, axis=1)).to(self.device)

        # ── Loss & Optimiser ───────────────────────────────────────────────
        criterion = FocalLoss(alpha=class_weights, gamma=2.0)
        optimizer = optim.AdamW(self.model.parameters(),
                                lr=ModelConfig.LEARNING_RATE,
                                weight_decay=2e-4)
        # Cosine annealing: restarts every T_0 epochs
        scheduler = optim.lr_scheduler.CosineAnnealingWarmRestarts(
            optimizer, T_0=20, T_mult=2, eta_min=1e-6
        )

        history = {'loss': [], 'accuracy': [], 'val_loss': [], 'val_accuracy': []}
        best_val_acc   = 0.0
        patience_counter = 0
        patience_limit   = ModelConfig.EARLY_STOPPING_PATIENCE

        for epoch in range(epochs):
            self.model.train()
            total_loss, correct, total = 0.0, 0, 0

            for batch_x, batch_y in train_loader:
                optimizer.zero_grad()
                outputs = self.model(batch_x)
                loss    = criterion(outputs, batch_y)
                loss.backward()
                # Gradient clipping for stability
                nn.utils.clip_grad_norm_(self.model.parameters(), max_norm=1.0)
                optimizer.step()

                total_loss += loss.item()
                _, predicted = torch.max(outputs, 1)
                correct += (predicted == batch_y).sum().item()
                total   += batch_y.size(0)

            scheduler.step(epoch)
            train_loss = total_loss / max(len(train_loader), 1)
            train_acc  = correct / max(total, 1)
            history['loss'].append(train_loss)
            history['accuracy'].append(train_acc)

            # ── Validation ─────────────────────────────────────────────────
            if X_val is not None:
                self.model.eval()
                with torch.no_grad():
                    val_outputs = self.model(X_val_t)
                    val_loss    = criterion(val_outputs, y_val_t).item()
                    _, val_pred = torch.max(val_outputs, 1)
                    val_acc     = (val_pred == y_val_t).float().mean().item()

                history['val_loss'].append(val_loss)
                history['val_accuracy'].append(val_acc)

                lr_now = optimizer.param_groups[0]['lr']
                print(f"Epoch {epoch+1:3d}/{epochs} | "
                      f"Loss {train_loss:.4f} | Acc {train_acc:.4f} | "
                      f"Val Loss {val_loss:.4f} | Val Acc {val_acc:.4f} | "
                      f"LR {lr_now:.2e}")

                if val_acc > best_val_acc:
                    best_val_acc     = val_acc
                    patience_counter = 0
                    self.save_model()
                else:
                    patience_counter += 1
                    if patience_counter >= patience_limit:
                        print(f"  Early stopping at epoch {epoch+1} "
                              f"(best val acc: {best_val_acc:.4f})")
                        break
            else:
                print(f"Epoch {epoch+1:3d}/{epochs} | "
                      f"Loss {train_loss:.4f} | Acc {train_acc:.4f}")

        return history

    def predict(self, features: np.ndarray) -> tuple:
        """Predict threat class and confidence"""
        if self.model is None:
            if not self.load_model():
                self.build_model()

        self.model.eval()
        if features.ndim == 2:
            features = np.expand_dims(features, axis=0)

        with torch.no_grad():
            x = torch.FloatTensor(features).to(self.device)
            outputs       = self.model(x)
            probabilities = torch.softmax(outputs, dim=1)[0].cpu().numpy()

        class_idx  = int(np.argmax(probabilities))
        confidence = float(probabilities[class_idx])
        class_name = self.classes[class_idx]
        return class_name, confidence, probabilities.tolist()

    def load_model(self) -> bool:
        """Load trained model weights from file"""
        if os.path.exists(self.model_path):
            self.build_model()
            self.model.load_state_dict(
                torch.load(self.model_path, map_location=self.device, weights_only=True)
            )
            self.model.eval()
            return True
        return False

    def save_model(self) -> None:
        """Save model to file"""
        if self.model is not None:
            os.makedirs(os.path.dirname(self.model_path), exist_ok=True)
            torch.save(self.model.state_dict(), self.model_path)

    def get_model_summary(self) -> str:
        """Get model architecture summary"""
        if self.model is None:
            self.build_model()
        return str(self.model)

