/*
 * Arduino MFRC522 RFID Writer for School Safety System
 *
 * Hardware Setup:
 * - Arduino Uno
 * - MFRC522 RFID Reader
 *
 * Connections:
 * RST/Reset   -> Digital pin 9
 * SPI SS      -> Digital pin 10
 * SPI MOSI    -> Digital pin 11
 * SPI MISO    -> Digital pin 12
 * SPI SCK     -> Digital pin 13
 * 3.3V        -> 3.3V
 * GND         -> GND
 */

#include <SPI.h>
#include <MFRC522.h>

#define RST_PIN 9
#define SS_PIN 10
#define MAX_DATA_LENGTH 256
#define BLOCK_SIZE 16

MFRC522 mfrc522(SS_PIN, RST_PIN);
MFRC522::MIFARE_Key key;

void setup() {
  Serial.begin(9600);
  while (!Serial); // Wait for serial port to connect

  SPI.begin();
  mfrc522.PCD_Init();

  // Prepare the default key (0xFF for all 6 bytes)
  for (byte i = 0; i < 6; i++) {
    key.keyByte[i] = 0xFF;
  }

  Serial.println("READY");
  Serial.flush();
}

void loop() {
  if (Serial.available() > 0) {
    String command = Serial.readStringUntil('\n');
    command.trim();

    if (command == "WRITE_NFC") {
      handleWriteCommand();
    } else if (command == "PING") {
      Serial.println("PONG");
      Serial.flush();
    } else if (command == "STATUS") {
      Serial.println("READY");
      Serial.flush();
    }
  }

  delay(10); // Small delay to prevent tight loop
}

void handleWriteCommand() {
  // Read data length
  while (Serial.available() == 0) {
    delay(10);
  }

  int dataLength = Serial.parseInt();

  // Consume the newline after parseInt
  while (Serial.available() && Serial.peek() == '\n') {
    Serial.read();
  }

  if (dataLength <= 0 || dataLength > MAX_DATA_LENGTH) {
    Serial.println("ERROR: Invalid data length");
    Serial.flush();
    return;
  }

  // Read the actual data
  while (Serial.available() == 0) {
    delay(10);
  }

  String nfcData = Serial.readStringUntil('\n');
  nfcData.trim();

  if (nfcData.length() == 0) {
    Serial.println("ERROR: No data received");
    Serial.flush();
    return;
  }

  // Write to NFC tag
  if (writeToRFID(nfcData)) {
    Serial.println("SUCCESS");
    Serial.flush();
  } else {
    Serial.println("ERROR: Failed to write to RFID tag");
    Serial.flush();
  }
}

bool writeToRFID(String data) {
  unsigned long startTime = millis();
  const unsigned long timeout = 10000; // 10 seconds timeout

  // Wait for a card to be present
  Serial.println("INFO: Waiting for RFID tag...");
  Serial.flush();

  while (millis() - startTime < timeout) {
    // Look for new cards
    if (!mfrc522.PICC_IsNewCardPresent()) {
      delay(50);
      continue;
    }

    // Select one of the cards
    if (!mfrc522.PICC_ReadCardSerial()) {
      delay(50);
      continue;
    }

    Serial.println("INFO: Tag detected, writing data...");
    Serial.flush();

    // Card detected, proceed with writing
    bool writeSuccess = writeDataToCard(data);

    // Halt the card
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();

    return writeSuccess;
  }

  Serial.println("TIMEOUT: No tag detected within timeout period");
  Serial.flush();
  return false;
}

bool writeDataToCard(String data) {
  // Convert string to byte array
  byte dataBuffer[MAX_DATA_LENGTH];
  int dataLen = data.length();
  data.getBytes(dataBuffer, dataLen + 1);

  // Calculate how many blocks we need (16 bytes per block)
  int numBlocks = (dataLen / BLOCK_SIZE) + 1;

  // Start from block 4 (blocks 0-3 are reserved/trailer blocks)
  byte startBlock = 4;

  for (int i = 0; i < numBlocks && i < 12; i++) { // Max 12 blocks for safety
    byte block = startBlock + (i * 4); // Skip trailer blocks (every 4th block)

    // Prepare data for this block
    byte blockData[BLOCK_SIZE];
    int offset = i * BLOCK_SIZE;

    for (int j = 0; j < BLOCK_SIZE; j++) {
      if (offset + j < dataLen) {
        blockData[j] = dataBuffer[offset + j];
      } else {
        blockData[j] = 0; // Pad with zeros
      }
    }

    // Authenticate with key A
    byte trailerBlock = ((block / 4) * 4) + 3;
    MFRC522::StatusCode status = mfrc522.PCD_Authenticate(
      MFRC522::PICC_CMD_MF_AUTH_KEY_A,
      trailerBlock,
      &key,
      &(mfrc522.uid)
    );

    if (status != MFRC522::STATUS_OK) {
      Serial.print("ERROR: Auth failed for block ");
      Serial.println(block);
      Serial.flush();
      return false;
    }

    // Write data to block
    status = mfrc522.MIFARE_Write(block, blockData, BLOCK_SIZE);

    if (status != MFRC522::STATUS_OK) {
      Serial.print("ERROR: Write failed for block ");
      Serial.println(block);
      Serial.flush();
      return false;
    }
  }

  return true;
}
