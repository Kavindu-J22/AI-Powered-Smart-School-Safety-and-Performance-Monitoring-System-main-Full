/*
 * Arduino WiFi RFID Attendance System
 * For UNO+WiFi R3 ATmega328P+ESP8266
 *
 * Hardware Components:
 * - UNO+WiFi R3 ATmega328P+ESP8266 (32Mb flash, USB-TTL CH340G)
 * - MFRC522 RFID Reader (RC522 13.56MHz)
 * - LCD1602 I2C Display Module (PCF8574 IIC Adapter)
 * - DS3231 Real Time Clock Module
 * - Micro SD Card Module TF Card Adapter
 * - RGB LED (5mm Common Cathode)
 * - Buzzer (optional for audio feedback)
 *
 * Pin Connections:
 *
 * MFRC522 RFID:
 *   RST/Reset   -> Digital pin 9
 *   SPI SS      -> Digital pin 10
 *   SPI MOSI    -> Digital pin 11
 *   SPI MISO    -> Digital pin 12
 *   SPI SCK     -> Digital pin 13
 *   3.3V        -> 3.3V
 *   GND         -> GND
 *
 * LCD1602 I2C:
 *   SDA         -> A4 (I2C Data)
 *   SCL         -> A5 (I2C Clock)
 *   VCC         -> 5V
 *   GND         -> GND
 *
 * DS3231 RTC:
 *   SDA         -> A4 (I2C Data)
 *   SCL         -> A5 (I2C Clock)
 *   VCC         -> 5V
 *   GND         -> GND
 *
 * RGB LED (Common Cathode):
 *   Red Pin     -> Digital pin 6 (via 220Ω resistor)
 *   Green Pin   -> Digital pin 5 (via 220Ω resistor)
 *   Blue Pin    -> Digital pin 3 (via 220Ω resistor)
 *   Cathode     -> GND
 *
 * SD Card Module:
 *   CS          -> Digital pin 4
 *   MOSI        -> Digital pin 11
 *   MISO        -> Digital pin 12
 *   SCK         -> Digital pin 13
 *   VCC         -> 5V
 *   GND         -> GND
 *
 * Buzzer (Optional):
 *   Positive    -> Digital pin 8
 *   Negative    -> GND
 */

#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <RTClib.h>
#include <SD.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

// Pin Definitions
#define RST_PIN 9
#define SS_PIN 10
#define SD_CS_PIN 4
#define LED_RED 6
#define LED_GREEN 5
#define LED_BLUE 3
#define BUZZER_PIN 8

// RFID Configuration
#define BLOCK_SIZE 16
#define MAX_DATA_LENGTH 256

// Network Configuration (Update these with your WiFi credentials)
const char* WIFI_SSID = "YOUR_WIFI_SSID";
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";

// Server Configuration (Update with your Laravel server details)
const char* SERVER_URL = "http://192.168.1.100:8000/api/attendance/rfid-scan";
const char* API_TOKEN = "YOUR_API_TOKEN";  // Optional: for API authentication

// Device Configuration
const char* DEVICE_ID = "ATTENDANCE_READER_01";
const int WIFI_TIMEOUT = 20000;  // 20 seconds
const int HTTP_TIMEOUT = 10000;  // 10 seconds
const int SCAN_COOLDOWN = 3000;  // 3 seconds between scans of same card

// Objects
MFRC522 mfrc522(SS_PIN, RST_PIN);
MFRC522::MIFARE_Key key;
LiquidCrystal_I2C lcd(0x27, 16, 2);  // I2C address 0x27, 16 columns, 2 rows
RTC_DS3231 rtc;
WiFiClient wifiClient;

// Variables
String lastScannedUID = "";
unsigned long lastScanTime = 0;
bool wifiConnected = false;
bool sdCardAvailable = false;
int failedScans = 0;

void setup() {
  Serial.begin(115200);
  Serial.println(F("\n\n================================="));
  Serial.println(F("WiFi RFID Attendance System"));
  Serial.println(F("=================================\n"));

  // Initialize pins
  pinMode(LED_RED, OUTPUT);
  pinMode(LED_GREEN, OUTPUT);
  pinMode(LED_BLUE, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);

  // Start with LED off
  setRGBColor(0, 0, 0);

  // Initialize LCD
  lcd.init();
  lcd.backlight();
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Attendance Sys");
  lcd.setCursor(0, 1);
  lcd.print("Initializing...");

  // Initialize SPI
  SPI.begin();

  // Initialize RFID Reader
  Serial.println(F("Initializing RFID..."));
  mfrc522.PCD_Init();
  delay(100);

  // Check RFID reader
  byte version = mfrc522.PCD_ReadRegister(mfrc522.VersionReg);
  if (version == 0x00 || version == 0xFF) {
    Serial.println(F("ERROR: RFID reader not found!"));
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("RFID Error!");
    setRGBColor(255, 0, 0);  // Red
    while (1) {
      beepError();
      delay(1000);
    }
  }
  Serial.print(F("RFID Reader Version: 0x"));
  Serial.println(version, HEX);

  // Prepare RFID key
  for (byte i = 0; i < 6; i++) {
    key.keyByte[i] = 0xFF;
  }

  // Initialize RTC
  Serial.println(F("Initializing RTC..."));
  if (!rtc.begin()) {
    Serial.println(F("WARNING: RTC not found!"));
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("RTC Warning!");
    delay(2000);
  } else {
    if (rtc.lostPower()) {
      Serial.println(F("RTC lost power, setting time!"));
      rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));
    }
    DateTime now = rtc.now();
    Serial.print(F("RTC Time: "));
    Serial.print(now.year());
    Serial.print('/');
    Serial.print(now.month());
    Serial.print('/');
    Serial.print(now.day());
    Serial.print(' ');
    Serial.print(now.hour());
    Serial.print(':');
    Serial.print(now.minute());
    Serial.print(':');
    Serial.println(now.second());
  }

  // Initialize SD Card
  Serial.println(F("Initializing SD Card..."));
  if (!SD.begin(SD_CS_PIN)) {
    Serial.println(F("WARNING: SD Card not found or failed to initialize!"));
    sdCardAvailable = false;
  } else {
    Serial.println(F("SD Card initialized successfully"));
    sdCardAvailable = true;

    // Create log file if it doesn't exist
    if (!SD.exists("attendance.log")) {
      File logFile = SD.open("attendance.log", FILE_WRITE);
      if (logFile) {
        logFile.println("=== Attendance Log Started ===");
        logFile.close();
      }
    }
  }

  // Connect to WiFi
  Serial.println(F("Connecting to WiFi..."));
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("WiFi Connecting");
  lcd.setCursor(0, 1);
  lcd.print(WIFI_SSID);

  connectToWiFi();

  // System ready
  Serial.println(F("\n=== System Ready ==="));
  Serial.println(F("Place RFID card near reader...\n"));

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Ready to Scan");
  displayDateTime();

  setRGBColor(0, 0, 255);  // Blue - Ready
  beepReady();
  delay(500);
  setRGBColor(0, 0, 0);  // Off
}

void loop() {
  // Check WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    wifiConnected = false;
    if (millis() % 30000 == 0) {  // Try to reconnect every 30 seconds
      connectToWiFi();
    }
  } else {
    wifiConnected = true;
  }

  // Update time display every second
  static unsigned long lastTimeUpdate = 0;
  if (millis() - lastTimeUpdate >= 1000) {
    displayDateTime();
    lastTimeUpdate = millis();
  }

  // Check for RFID card
  if (!mfrc522.PICC_IsNewCardPresent()) {
    return;
  }

  if (!mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  // Get card UID
  String cardUID = getCardUID();

  // Check scan cooldown (prevent duplicate scans)
  unsigned long currentTime = millis();
  if (cardUID == lastScannedUID && (currentTime - lastScanTime) < SCAN_COOLDOWN) {
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();
    return;
  }

  // Update last scan info
  lastScannedUID = cardUID;
  lastScanTime = currentTime;

  // Process card
  Serial.println(F("\n--- Card Detected ---"));
  Serial.print(F("UID: "));
  Serial.println(cardUID);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Reading Card...");
  setRGBColor(255, 255, 0);  // Yellow - Processing

  // Read student data from card
  String studentData = readStudentDataFromCard();

  if (studentData.length() > 0) {
    Serial.print(F("Student Data: "));
    Serial.println(studentData);

    // Parse student data
    String studentCode = parseStudentCode(studentData);

    if (studentCode.length() > 0) {
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Student: ");
      lcd.print(studentCode);
      lcd.setCursor(0, 1);
      lcd.print("Processing...");

      // Send to server
      bool success = sendAttendanceToServer(studentData, cardUID);

      if (success) {
        // Success
        Serial.println(F("✓ Attendance recorded successfully!"));
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Success!");
        lcd.setCursor(0, 1);
        lcd.print("Welcome!");
        setRGBColor(0, 255, 0);  // Green - Success
        beepSuccess();
        failedScans = 0;
      } else {
        // Failed - Log to SD card
        Serial.println(F("✗ Failed to send to server"));
        lcd.clear();
        lcd.setCursor(0, 0);
        lcd.print("Offline Mode");
        lcd.setCursor(0, 1);
        lcd.print("Saved Locally");
        setRGBColor(255, 165, 0);  // Orange - Offline
        beepWarning();

        // Save to SD card for later sync
        if (sdCardAvailable) {
          saveToSDCard(studentData, cardUID);
        }

        failedScans++;
      }

      delay(2000);
    } else {
      // Invalid data on card
      Serial.println(F("✗ Invalid student data"));
      lcd.clear();
      lcd.setCursor(0, 0);
      lcd.print("Invalid Card!");
      lcd.setCursor(0, 1);
      lcd.print("See Admin");
      setRGBColor(255, 0, 0);  // Red - Error
      beepError();
      delay(2000);
    }
  } else {
    // Failed to read card
    Serial.println(F("✗ Failed to read card data"));
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Read Error!");
    lcd.setCursor(0, 1);
    lcd.print("Try Again");
    setRGBColor(255, 0, 0);  // Red - Error
    beepError();
    delay(2000);
  }

  // Reset display
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("Ready to Scan");
  displayDateTime();
  setRGBColor(0, 0, 0);  // Off

  // Halt card
  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();

  Serial.println(F("--- Scan Complete ---\n"));
}

// ==================== WiFi Functions ====================

void connectToWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  Serial.print(F("Connecting to WiFi"));
  unsigned long startTime = millis();

  while (WiFi.status() != WL_CONNECTED && (millis() - startTime) < WIFI_TIMEOUT) {
    delay(500);
    Serial.print(F("."));
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println(F("\n✓ WiFi Connected!"));
    Serial.print(F("IP Address: "));
    Serial.println(WiFi.localIP());
    wifiConnected = true;

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Connected");
    lcd.setCursor(0, 1);
    lcd.print(WiFi.localIP());
    delay(2000);
  } else {
    Serial.println(F("\n✗ WiFi Connection Failed!"));
    Serial.println(F("Operating in offline mode..."));
    wifiConnected = false;

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Failed");
    lcd.setCursor(0, 1);
    lcd.print("Offline Mode");
    delay(2000);
  }
}

// ==================== RFID Functions ====================

String getCardUID() {
  String uid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) {
      uid += "0";
    }
    uid += String(mfrc522.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();
  return uid;
}

String readStudentDataFromCard() {
  String readData = "";
  byte startBlock = 4;  // Start from block 4 (same as write code)
  int maxBlocks = 12;   // Max blocks to read
  byte buffer[18];      // Buffer for reading (16 data + 2 CRC)
  byte bufferSize = 18;

  for (int i = 0; i < maxBlocks; i++) {
    byte block = startBlock + (i * 4);  // Skip trailer blocks

    // Authenticate
    byte trailerBlock = ((block / 4) * 4) + 3;
    MFRC522::StatusCode status = mfrc522.PCD_Authenticate(
      MFRC522::PICC_CMD_MF_AUTH_KEY_A,
      trailerBlock,
      &key,
      &(mfrc522.uid)
    );

    if (status != MFRC522::STATUS_OK) {
      Serial.print(F("Auth failed for block "));
      Serial.println(block);
      return "";
    }

    // Read block
    bufferSize = 18;
    status = mfrc522.MIFARE_Read(block, buffer, &bufferSize);

    if (status != MFRC522::STATUS_OK) {
      Serial.print(F("Read failed for block "));
      Serial.println(block);
      return "";
    }

    // Append data (only first 16 bytes, skip CRC)
    bool allZeros = true;
    for (byte j = 0; j < BLOCK_SIZE; j++) {
      if (buffer[j] == 0) {
        break;
      }
      allZeros = false;
      readData += (char)buffer[j];
    }

    // Stop if all zeros (end of data)
    if (allZeros) {
      break;
    }
  }

  return readData;
}

String parseStudentCode(String data) {
  // Format: STUDENT_CODE|FIRST_NAME|LAST_NAME|GRADE|CLASS|DATE
  int firstPipe = data.indexOf('|');
  if (firstPipe > 0) {
    return data.substring(0, firstPipe);
  }
  return "";
}

// ==================== Server Communication ====================

bool sendAttendanceToServer(String studentData, String cardUID) {
  if (!wifiConnected) {
    Serial.println(F("No WiFi connection"));
    return false;
  }

  HTTPClient http;
  http.setTimeout(HTTP_TIMEOUT);

  Serial.println(F("Sending to server..."));
  Serial.print(F("URL: "));
  Serial.println(SERVER_URL);

  // Begin HTTP connection
  http.begin(wifiClient, SERVER_URL);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");

  // Add API token if configured
  if (strlen(API_TOKEN) > 0) {
    String authHeader = "Bearer ";
    authHeader += API_TOKEN;
    http.addHeader("Authorization", authHeader);
  }

  // Get current timestamp
  DateTime now = rtc.now();
  char timestamp[25];
  sprintf(timestamp, "%04d-%02d-%02d %02d:%02d:%02d",
          now.year(), now.month(), now.day(),
          now.hour(), now.minute(), now.second());

  // Create JSON payload
  StaticJsonDocument<512> doc;
  doc["student_data"] = studentData;
  doc["card_uid"] = cardUID;
  doc["device_id"] = DEVICE_ID;
  doc["timestamp"] = timestamp;
  doc["device_time"] = timestamp;

  String jsonPayload;
  serializeJson(doc, jsonPayload);

  Serial.print(F("Payload: "));
  Serial.println(jsonPayload);

  // Send POST request
  int httpCode = http.POST(jsonPayload);

  Serial.print(F("HTTP Response Code: "));
  Serial.println(httpCode);

  bool success = false;

  if (httpCode > 0) {
    String response = http.getString();
    Serial.print(F("Response: "));
    Serial.println(response);

    // Parse response
    StaticJsonDocument<512> responseDoc;
    DeserializationError error = deserializeJson(responseDoc, response);

    if (!error) {
      bool responseSuccess = responseDoc["success"] | false;
      const char* message = responseDoc["message"] | "Unknown response";

      Serial.print(F("Server says: "));
      Serial.println(message);

      if (responseSuccess) {
        success = true;

        // Display student info if available
        if (responseDoc.containsKey("data")) {
          const char* studentName = responseDoc["data"]["student_name"] | "";
          const char* action = responseDoc["data"]["action"] | "";
          const char* time = responseDoc["data"]["time"] | "";

          if (strlen(studentName) > 0) {
            Serial.print(F("Student: "));
            Serial.println(studentName);
            Serial.print(F("Action: "));
            Serial.println(action);
            Serial.print(F("Time: "));
            Serial.println(time);
          }
        }
      }
    }
  } else {
    Serial.print(F("HTTP Error: "));
    Serial.println(http.errorToString(httpCode));
  }

  http.end();
  return success;
}

// ==================== SD Card Functions ====================

void saveToSDCard(String studentData, String cardUID) {
  if (!sdCardAvailable) {
    Serial.println(F("SD Card not available"));
    return;
  }

  // Get timestamp
  DateTime now = rtc.now();
  char timestamp[25];
  sprintf(timestamp, "%04d-%02d-%02d %02d:%02d:%02d",
          now.year(), now.month(), now.day(),
          now.hour(), now.minute(), now.second());

  // Create log entry
  String logEntry = String(timestamp) + "," + studentData + "," + cardUID;

  // Append to log file
  File logFile = SD.open("attendance.log", FILE_WRITE);
  if (logFile) {
    logFile.println(logEntry);
    logFile.close();
    Serial.println(F("✓ Saved to SD Card"));
  } else {
    Serial.println(F("✗ Failed to write to SD Card"));
  }

  // Also save to pending sync file
  File syncFile = SD.open("pending.csv", FILE_WRITE);
  if (syncFile) {
    syncFile.println(logEntry);
    syncFile.close();
  }
}

// ==================== Display Functions ====================

void displayDateTime() {
  if (!rtc.begin()) {
    return;
  }

  DateTime now = rtc.now();

  lcd.setCursor(0, 1);

  // Time
  if (now.hour() < 10) lcd.print('0');
  lcd.print(now.hour());
  lcd.print(':');
  if (now.minute() < 10) lcd.print('0');
  lcd.print(now.minute());
  lcd.print(':');
  if (now.second() < 10) lcd.print('0');
  lcd.print(now.second());

  lcd.print(' ');

  // Date
  if (now.day() < 10) lcd.print('0');
  lcd.print(now.day());
  lcd.print('/');
  if (now.month() < 10) lcd.print('0');
  lcd.print(now.month());
}

// ==================== LED Functions ====================

void setRGBColor(int red, int green, int blue) {
  analogWrite(LED_RED, red);
  analogWrite(LED_GREEN, green);
  analogWrite(LED_BLUE, blue);
}

// ==================== Buzzer Functions ====================

void beepSuccess() {
  tone(BUZZER_PIN, 2000, 100);  // 2kHz for 100ms
  delay(150);
  tone(BUZZER_PIN, 2500, 100);  // 2.5kHz for 100ms
}

void beepError() {
  tone(BUZZER_PIN, 500, 200);   // 500Hz for 200ms
  delay(250);
  tone(BUZZER_PIN, 400, 200);   // 400Hz for 200ms
}

void beepWarning() {
  tone(BUZZER_PIN, 1500, 150);  // 1.5kHz for 150ms
}

void beepReady() {
  tone(BUZZER_PIN, 1000, 100);  // 1kHz for 100ms
}
