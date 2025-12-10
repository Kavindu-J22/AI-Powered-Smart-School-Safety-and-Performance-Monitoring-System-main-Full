# ESP32-CAM Setup Guide
## Hardware Setup and Integration for School Security System

This guide will help you set up ESP32-CAM modules for the Video-Based Left Behind Object and Threat Detection System.

---

## Table of Contents
1. [Hardware Requirements](#1-hardware-requirements)
2. [Hardware Assembly](#2-hardware-assembly)
3. [Software Setup](#3-software-setup)
4. [Firmware Installation](#4-firmware-installation)
5. [Network Configuration](#5-network-configuration)
6. [Integration with Main System](#6-integration-with-main-system)
7. [Troubleshooting](#7-troubleshooting)

---

## 1. Hardware Requirements

### Required Components (Per Camera Unit)

**Main Components:**
- [ ] ESP32-CAM Module (AI-Thinker or similar)
- [ ] ESP32-CAM-MB (Micro USB Programmer Board) OR FTDI Programmer
- [ ] OV2640 Camera Module (usually included with ESP32-CAM)
- [ ] Micro USB Cable
- [ ] 5V Power Supply (minimum 2A recommended)

**Optional but Recommended:**
- [ ] External WiFi Antenna (for better range)
- [ ] Protective Case/Housing
- [ ] SD Card (4GB-32GB) for local storage
- [ ] LED Indicator (for status)
- [ ] Jumper Wires (if using FTDI programmer)

### Cost Estimate (Per Unit)
```
ESP32-CAM Module:        $8-12
ESP32-CAM-MB Programmer: $3-5
Power Supply:            $5-8
SD Card (optional):      $5-10
Case (optional):         $3-5
-----------------------------------
Total per camera:        $16-40
```

### Recommended Vendors
- **AliExpress**: Cheapest option (2-4 weeks shipping)
- **Amazon**: Faster shipping (1-3 days)
- **Local Electronics Store**: Immediate availability

---

## 2. Hardware Assembly

### Step 1: Inspect ESP32-CAM Module

```
Components on ESP32-CAM:
┌─────────────────────────────┐
│  [Antenna Connector]        │
│                             │
│  [ESP32-S Chip]             │
│                             │
│  [Camera Connector] ──→ OV2640
│                             │
│  [SD Card Slot]             │
│                             │
│  [GPIO Pins]                │
└─────────────────────────────┘
```

**Pin Layout:**
```
GND  - Ground
5V   - Power (5V input)
3.3V - 3.3V output
GPIO - General purpose I/O pins
U0R  - UART RX (for programming)
U0T  - UART TX (for programming)
IO0  - GPIO 0 (boot mode selection)
```

### Step 2: Connect Camera Module

1. **Locate the camera connector** on ESP32-CAM (small white connector)
2. **Gently lift the black latch** on the connector
3. **Insert the camera ribbon cable** (blue side facing chip)
4. **Press down the latch** to secure

⚠️ **IMPORTANT**: Ensure correct orientation! Blue side should face the ESP32 chip.

### Step 3: Insert SD Card (Optional)

1. Format SD Card as FAT32
2. Insert into SD card slot on back of ESP32-CAM
3. Push until it clicks

### Step 4: Connect to Programmer

**Option A: Using ESP32-CAM-MB (Recommended for Beginners)**
```
1. Align ESP32-CAM pins with ESP32-CAM-MB socket
2. Press firmly to insert
3. Connect Micro USB cable to ESP32-CAM-MB
4. Connect USB to computer
```

**Option B: Using FTDI Programmer**
```
ESP32-CAM    →    FTDI
--------------------------
GND          →    GND
5V           →    VCC (5V)
U0R          →    TX
U0T          →    RX
IO0          →    GND (for programming mode)
```

⚠️ **IMPORTANT**: 
- Cross TX/RX connections (ESP32 RX → FTDI TX, ESP32 TX → FTDI RX)
- Connect IO0 to GND ONLY during programming
- Disconnect IO0 from GND for normal operation

---

## 3. Software Setup

### Step 1: Install Arduino IDE

1. Download Arduino IDE from: https://www.arduino.cc/en/software
2. Install for your operating system
3. Launch Arduino IDE

### Step 2: Install ESP32 Board Support

1. Open Arduino IDE
2. Go to **File → Preferences**
3. Add to "Additional Board Manager URLs":
   ```
   https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
   ```
4. Click **OK**
5. Go to **Tools → Board → Boards Manager**
6. Search for "esp32"
7. Install "esp32 by Espressif Systems"
8. Wait for installation to complete

### Step 3: Select Board

1. Go to **Tools → Board → ESP32 Arduino**
2. Select **"AI Thinker ESP32-CAM"**

### Step 4: Configure Settings

```
Board: "AI Thinker ESP32-CAM"
Upload Speed: "115200"
Flash Frequency: "80MHz"
Flash Mode: "QIO"
Partition Scheme: "Huge APP (3MB No OTA/1MB SPIFFS)"
Core Debug Level: "None"
Port: [Select your COM port]
```

---

## 4. Firmware Installation

### Step 1: Open Firmware Code

1. Navigate to `firmware/esp32_cam/` in this project
2. Open `esp32_cam_stream.ino` in Arduino IDE

### Step 2: Configure WiFi Credentials

Edit these lines in the code:
```cpp
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";
```

### Step 3: Configure Camera Settings

```cpp
// Camera ID (unique for each camera)
const char* camera_id = "CAM_001";

// Server settings (your main computer)
const char* server_ip = "192.168.1.100";
const int server_port = 8080;

// Camera resolution
// Options: FRAMESIZE_QVGA (320x240)
//          FRAMESIZE_VGA (640x480)
//          FRAMESIZE_SVGA (800x600)
//          FRAMESIZE_HD (1280x720)
framesize_t frameSize = FRAMESIZE_VGA;

// JPEG quality (10-63, lower = better quality)
int jpegQuality = 10;

// Frame rate
int frameRate = 15;  // FPS
```

### Step 4: Upload Firmware

1. **Enter Programming Mode**:
   - If using ESP32-CAM-MB: Just connect USB
   - If using FTDI: Connect IO0 to GND, press reset button

2. **Click Upload** button in Arduino IDE

3. **Wait for upload** (takes 1-2 minutes)

4. **Exit Programming Mode**:
   - If using FTDI: Disconnect IO0 from GND, press reset

5. **Open Serial Monitor** (Tools → Serial Monitor)
   - Set baud rate to 115200
   - You should see boot messages

### Step 5: Verify Connection

Serial Monitor should show:
```
ESP32-CAM Starting...
Camera ID: CAM_001
Connecting to WiFi: YOUR_SSID
.....
WiFi connected!
IP Address: 192.168.1.101
Stream URL: http://192.168.1.101/stream
Ready to stream!
```

---

## 5. Network Configuration

### Step 1: Find Camera IP Address

Check Serial Monitor for IP address, or use network scanner:
```bash
# Windows
arp -a

# Linux/Mac
arp-scan --localnet
# or
nmap -sn 192.168.1.0/24
```

### Step 2: Test Camera Stream

Open web browser and navigate to:
```
http://[CAMERA_IP_ADDRESS]/stream
```

You should see live video feed!

### Step 3: Configure Static IP (Recommended)

Add to firmware code:
```cpp
// Static IP configuration
IPAddress local_IP(192, 168, 1, 101);
IPAddress gateway(192, 168, 1, 1);
IPAddress subnet(255, 255, 255, 0);
IPAddress primaryDNS(8, 8, 8, 8);

// In setup():
if (!WiFi.config(local_IP, gateway, subnet, primaryDNS)) {
  Serial.println("STA Failed to configure");
}
```

---

## 6. Integration with Main System

### Step 1: Install Python Dependencies

On your main computer (server):
```bash
pip install opencv-python flask paho-mqtt requests
```

### Step 2: Start Camera Receiver

```bash
cd src/esp32_integration
python camera_receiver.py
```

This will:
- Start HTTP server on port 8080
- Receive streams from all ESP32-CAM modules
- Forward frames to detection system

### Step 3: Configure Camera in config.yaml

Edit `config/config.yaml`:
```yaml
cameras:
  - id: "CAM_001"
    name: "Classroom 1A"
    location: "Building A, Floor 1, Room 1A"
    type: "ESP32-CAM"
    ip: "192.168.1.101"
    stream_url: "http://192.168.1.101/stream"
    enabled: true
```

### Step 4: Test Integration

```bash
python scripts/test_camera.py --camera CAM_001
```

---

## 7. Troubleshooting

### Problem: Camera won't connect to WiFi

**Solutions:**
1. Check WiFi credentials in code
2. Ensure 2.4GHz WiFi (ESP32 doesn't support 5GHz)
3. Check WiFi signal strength
4. Try moving closer to router
5. Check router settings (disable MAC filtering temporarily)

### Problem: Upload fails / "Timed out waiting for packet header"

**Solutions:**
1. Press and hold RESET button, then press UPLOAD
2. Release RESET when "Connecting..." appears
3. Check USB cable (try different cable)
4. Reduce upload speed to 115200
5. If using FTDI: Ensure IO0 is connected to GND

### Problem: Brown-out detector triggered

**Solutions:**
1. Use better power supply (minimum 2A)
2. Add 100-470µF capacitor between 5V and GND
3. Use shorter/thicker USB cable
4. Disable WiFi power saving in code

### Problem: Poor image quality

**Solutions:**
1. Adjust JPEG quality (lower number = better quality)
2. Increase lighting in room
3. Clean camera lens
4. Adjust camera focus (rotate lens gently)
5. Change resolution settings

### Problem: Low frame rate

**Solutions:**
1. Reduce resolution
2. Increase JPEG quality number (lower quality, faster)
3. Improve WiFi signal
4. Reduce network traffic
5. Use wired connection if possible

### Problem: Camera keeps rebooting

**Solutions:**
1. Check power supply (needs stable 5V, 2A)
2. Add capacitor for power stability
3. Check for loose connections
4. Update firmware
5. Check for overheating

---

## 8. Advanced Configuration

### Enable Motion Detection on ESP32

Add to firmware:
```cpp
// Motion detection threshold
int motionThreshold = 20;

// Only send frames when motion detected
bool motionDetected = detectMotion();
if (motionDetected) {
  sendFrame();
}
```

### Enable Local Recording to SD Card

```cpp
// Record to SD card
void recordFrame() {
  String filename = "/frame_" + String(millis()) + ".jpg";
  fs::FS &fs = SD_MMC;
  File file = fs.open(filename, FILE_WRITE);
  if (file) {
    file.write(fb->buf, fb->len);
    file.close();
  }
}
```

### Enable MQTT for IoT Integration

```cpp
#include <PubSubClient.h>

WiFiClient espClient;
PubSubClient mqtt(espClient);

void setup() {
  mqtt.setServer("192.168.1.100", 1883);
  mqtt.connect("ESP32CAM_001");
}

void publishStatus() {
  mqtt.publish("camera/CAM_001/status", "online");
}
```

---

## 9. Multiple Camera Setup

### Wiring Multiple Cameras

```
Router
  ├── ESP32-CAM #1 (192.168.1.101) - Classroom 1A
  ├── ESP32-CAM #2 (192.168.1.102) - Classroom 1B
  ├── ESP32-CAM #3 (192.168.1.103) - Classroom 2A
  └── ESP32-CAM #4 (192.168.1.104) - Hallway
```

### Power Distribution

**Option 1: Individual Power Supplies**
- Each camera has its own 5V adapter
- Most reliable
- More expensive

**Option 2: Centralized Power Supply**
- Use 5V 10A power supply
- Distribute to all cameras
- More economical
- Ensure proper wire gauge

### Network Considerations

- Use Quality of Service (QoS) on router
- Prioritize camera traffic
- Consider separate VLAN for cameras
- Use WiFi extenders if needed

---

## 10. Mounting and Installation

### Camera Placement Guidelines

**Height**: 2.5-3 meters (8-10 feet)
**Angle**: 30-45 degrees downward
**Coverage**: Each camera covers ~50-70 sq meters

### Mounting Options

1. **Wall Mount**
   - Use L-bracket
   - Drill holes for screws
   - Run power cable through wall

2. **Ceiling Mount**
   - Use ceiling bracket
   - Better coverage
   - Less tampering risk

3. **Corner Mount**
   - Maximum coverage
   - Good for hallways

### Weatherproofing (if outdoor)

- Use IP66 rated enclosure
- Seal all cable entries
- Add desiccant pack for moisture
- Use outdoor-rated power supply

---

## 11. Maintenance

### Regular Checks (Weekly)

- [ ] Clean camera lens
- [ ] Check WiFi connection
- [ ] Verify video quality
- [ ] Check power supply

### Monthly Maintenance

- [ ] Update firmware if available
- [ ] Check SD card (if used)
- [ ] Verify all cameras online
- [ ] Test alert system

### Troubleshooting Logs

Check logs at:
```bash
# On ESP32 (Serial Monitor)
Tools → Serial Monitor

# On Server
tail -f logs/camera_receiver.log
```

---

## 12. Security Considerations

### Network Security

1. **Change default passwords**
2. **Use WPA3 WiFi encryption**
3. **Enable firewall rules**
4. **Use VPN for remote access**
5. **Disable UPnP on router**

### Camera Security

1. **Update firmware regularly**
2. **Disable unused features**
3. **Use HTTPS for streams**
4. **Implement authentication**
5. **Monitor access logs**

---

## Resources

### Official Documentation
- ESP32-CAM Datasheet: https://github.com/raphaelbs/esp32-cam-ai-thinker
- Arduino ESP32: https://github.com/espressif/arduino-esp32

### Useful Tools
- Arduino IDE: https://www.arduino.cc/
- ESPTool: https://github.com/espressif/esptool
- Angry IP Scanner: https://angryip.org/

### Community Support
- ESP32 Forum: https://www.esp32.com/
- Arduino Forum: https://forum.arduino.cc/

---

**Next Steps**: After setting up ESP32-CAM, proceed to `docs/SYSTEM_INTEGRATION.md` for full system integration.

