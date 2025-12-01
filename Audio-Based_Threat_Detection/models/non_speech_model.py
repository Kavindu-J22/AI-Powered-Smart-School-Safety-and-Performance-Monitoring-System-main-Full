"""
Non-Speech Threat Detection Model
1D-CNN + LSTM architecture for detecting non-verbal threat sounds
Using PyTorch for better Python 3.12+ compatibility
"""
import numpy as np
import torch
import torch.nn as nn
import torch.optim as optim
from torch.utils.data import DataLoader, TensorDataset
import os
import sys
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from config import ModelConfig, AudioConfig


class CNNLSTMNetwork(nn.Module):
    """PyTorch 1D-CNN + LSTM architecture"""

    def __init__(self, input_features=133, num_classes=5):
        super(CNNLSTMNetwork, self).__init__()

        # CNN layers
        self.conv1 = nn.Sequential(
            nn.Conv1d(input_features, 64, kernel_size=3, padding=1),
            nn.BatchNorm1d(64),
            nn.ReLU(),
            nn.Conv1d(64, 64, kernel_size=3, padding=1),
            nn.BatchNorm1d(64),
            nn.ReLU(),
            nn.MaxPool1d(2),
            nn.Dropout(0.25)
        )

        self.conv2 = nn.Sequential(
            nn.Conv1d(64, 128, kernel_size=3, padding=1),
            nn.BatchNorm1d(128),
            nn.ReLU(),
            nn.Conv1d(128, 128, kernel_size=3, padding=1),
            nn.BatchNorm1d(128),
            nn.ReLU(),
            nn.MaxPool1d(2),
            nn.Dropout(0.25)
        )

        self.conv3 = nn.Sequential(
            nn.Conv1d(128, 256, kernel_size=3, padding=1),
            nn.BatchNorm1d(256),
            nn.ReLU(),
            nn.MaxPool1d(2),
            nn.Dropout(0.3)
        )

        # Bidirectional LSTM
        self.lstm = nn.LSTM(256, 128, num_layers=2, batch_first=True,
                           bidirectional=True, dropout=0.3)

        # Dense layers
        self.fc = nn.Sequential(
            nn.Linear(256 + 256, 256),  # LSTM output + global avg pool
            nn.BatchNorm1d(256),
            nn.ReLU(),
            nn.Dropout(0.4),
            nn.Linear(256, 128),
            nn.BatchNorm1d(128),
            nn.ReLU(),
            nn.Dropout(0.3),
            nn.Linear(128, num_classes)
        )

    def forward(self, x):
        # x shape: (batch, time_steps, features) -> (batch, features, time_steps)
        x = x.transpose(1, 2)

        # CNN forward
        x = self.conv1(x)
        x = self.conv2(x)
        x = self.conv3(x)

        # Global average pooling from CNN
        cnn_out = torch.mean(x, dim=2)

        # LSTM forward (need to transpose back)
        x = x.transpose(1, 2)  # (batch, time, features)
        lstm_out, _ = self.lstm(x)
        lstm_out = lstm_out[:, -1, :]  # Take last timestep

        # Combine CNN and LSTM
        combined = torch.cat([cnn_out, lstm_out], dim=1)

        # Dense layers
        out = self.fc(combined)
        return out


class NonSpeechThreatModel:
    """1D-CNN + LSTM model for non-speech threat detection"""

    def __init__(self):
        self.classes = ModelConfig.NON_SPEECH_CLASSES
        self.num_classes = len(self.classes)
        self.model = None
        self.model_path = str(ModelConfig.NON_SPEECH_MODEL_PATH).replace('.h5', '.pth')
        self.input_shape = (128, 132)  # (time_steps, features) - 40 MFCC * 3 + 12 spectral
        self.device = torch.device('cuda' if torch.cuda.is_available() else 'cpu')

    def build_model(self, input_features: int = None) -> nn.Module:
        """Build 1D-CNN + LSTM architecture"""
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
        """Train the model"""
        if self.model is None:
            self.build_model()

        epochs = epochs or ModelConfig.EPOCHS
        batch_size = batch_size or ModelConfig.BATCH_SIZE

        # Convert to tensors
        X_train_t = torch.FloatTensor(X_train).to(self.device)
        y_train_t = torch.LongTensor(np.argmax(y_train, axis=1)).to(self.device)

        train_dataset = TensorDataset(X_train_t, y_train_t)
        train_loader = DataLoader(train_dataset, batch_size=batch_size, shuffle=True)

        if X_val is not None:
            X_val_t = torch.FloatTensor(X_val).to(self.device)
            y_val_t = torch.LongTensor(np.argmax(y_val, axis=1)).to(self.device)

        criterion = nn.CrossEntropyLoss()
        optimizer = optim.Adam(self.model.parameters(), lr=ModelConfig.LEARNING_RATE)
        scheduler = optim.lr_scheduler.ReduceLROnPlateau(optimizer, patience=5, factor=0.5)

        history = {'loss': [], 'accuracy': [], 'val_loss': [], 'val_accuracy': []}
        best_val_acc = 0
        patience_counter = 0

        for epoch in range(epochs):
            self.model.train()
            total_loss, correct, total = 0, 0, 0

            for batch_x, batch_y in train_loader:
                optimizer.zero_grad()
                outputs = self.model(batch_x)
                loss = criterion(outputs, batch_y)
                loss.backward()
                optimizer.step()

                total_loss += loss.item()
                _, predicted = torch.max(outputs, 1)
                correct += (predicted == batch_y).sum().item()
                total += batch_y.size(0)

            train_loss = total_loss / len(train_loader)
            train_acc = correct / total
            history['loss'].append(train_loss)
            history['accuracy'].append(train_acc)

            # Validation
            if X_val is not None:
                self.model.eval()
                with torch.no_grad():
                    val_outputs = self.model(X_val_t)
                    val_loss = criterion(val_outputs, y_val_t).item()
                    _, val_pred = torch.max(val_outputs, 1)
                    val_acc = (val_pred == y_val_t).sum().item() / len(y_val_t)

                history['val_loss'].append(val_loss)
                history['val_accuracy'].append(val_acc)
                scheduler.step(val_loss)

                print(f'Epoch {epoch+1}/{epochs} - Loss: {train_loss:.4f} - Acc: {train_acc:.4f} - Val Loss: {val_loss:.4f} - Val Acc: {val_acc:.4f}')

                # Early stopping
                if val_acc > best_val_acc:
                    best_val_acc = val_acc
                    self.save_model()
                    patience_counter = 0
                else:
                    patience_counter += 1
                    if patience_counter >= 10:
                        print(f'Early stopping at epoch {epoch+1}')
                        break
            else:
                print(f'Epoch {epoch+1}/{epochs} - Loss: {train_loss:.4f} - Acc: {train_acc:.4f}')

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
            outputs = self.model(x)
            probabilities = torch.softmax(outputs, dim=1)[0].cpu().numpy()

        class_idx = np.argmax(probabilities)
        confidence = float(probabilities[class_idx])
        class_name = self.classes[class_idx]

        return class_name, confidence, probabilities.tolist()

    def load_model(self) -> bool:
        """Load trained model from file"""
        if os.path.exists(self.model_path):
            self.build_model()
            self.model.load_state_dict(torch.load(self.model_path, map_location=self.device))
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

