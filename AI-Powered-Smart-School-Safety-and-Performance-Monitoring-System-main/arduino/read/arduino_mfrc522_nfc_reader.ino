/*
 * Arduino MFRC522 RFID Reader for School Safety System
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


// read using serial monitor at 9600 baud


#include <SPI.h>
#include <MFRC522.h>

#define RST_PIN 9
#define SS_PIN 10
#define MAX_DATA_LENGTH 256
#define BLOCK_SIZE 16
#define READ_BUFFER_SIZE 18 // Buffer size must be at least 18 for MIFARE_Read (16 data + 2 CRC)

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

  // Print MFRC522 reader details for debugging
  Serial.println("RFID Reader Initialized.");
  mfrc522.PCD_DumpVersionToSerial(); // Show version of MFRC522
  Serial.println("READY: Place RFID card near the reader...");
  Serial.flush();
}

void loop() {
  // Look for new cards
  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    // Print card UID
    Serial.print("Card UID: ");
    for (byte i = 0; i < mfrc522.uid.size; i++) {
      Serial.print(mfrc522.uid.uidByte[i] < 0x10 ? " 0" : " ");
      Serial.print(mfrc522.uid.uidByte[i], HEX);
    }
    Serial.println();

    // Print card type
    MFRC522::PICC_Type piccType = mfrc522.PICC_GetType(mfrc522.uid.sak);
    Serial.print("Card Type: ");
    Serial.println(mfrc522.PICC_GetTypeName(piccType));

    // Check if card is MIFARE Classic
    if (piccType != MFRC522::PICC_TYPE_MIFARE_MINI &&
        piccType != MFRC522::PICC_TYPE_MIFARE_1K &&
        piccType != MFRC522::PICC_TYPE_MIFARE_4K) {
      Serial.println("ERROR: Card is not a MIFARE Classic card");
      mfrc522.PICC_HaltA();
      mfrc522.PCD_StopCrypto1();
      Serial.println("READY: Place RFID card near the reader...");
      Serial.flush();
      return;
    }

    Serial.println("INFO: Tag detected, reading data...");
    Serial.flush();

    // Read data from the card
    if (readFromRFID()) {
      Serial.println("SUCCESS: Data read complete");
    } else {
      Serial.println("ERROR: Failed to read from RFID tag");
    }

    // Halt the card
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();

    Serial.println("READY: Place RFID card near the reader...");
    Serial.flush();
  }

  delay(50); // Small delay to prevent tight loop
}

bool readFromRFID() {
  String readData = "";
  byte startBlock = 4; // Start from block 4 (same as write code)
  int maxBlocks = 12;  // Max blocks to read (same as write code)

  for (int i = 0; i < maxBlocks; i++) {
    byte block = startBlock + (i * 4); // Skip trailer blocks (every 4th block)
    byte buffer[READ_BUFFER_SIZE];
    byte bufferSize = READ_BUFFER_SIZE;

    // Authenticate with key A
    byte trailerBlock = ((block / 4) * 4) + 3;
    Serial.print("INFO: Authenticating block ");
    Serial.print(block);
    Serial.print(" using trailer block ");
    Serial.println(trailerBlock);
    Serial.flush();

    MFRC522::StatusCode status = mfrc522.PCD_Authenticate(
      MFRC522::PICC_CMD_MF_AUTH_KEY_A,
      trailerBlock,
      &key,
      &(mfrc522.uid)
    );

    if (status != MFRC522::STATUS_OK) {
      Serial.print("ERROR: Auth failed for block ");
      Serial.print(block);
      Serial.print(" - Error: ");
      Serial.println(mfrc522.GetStatusCodeName(status));
      Serial.flush();
      return false;
    }

    // Read data from block
    Serial.print("INFO: Reading block ");
    Serial.println(block);
    Serial.flush();

    status = mfrc522.MIFARE_Read(block, buffer, &bufferSize);
    if (status != MFRC522::STATUS_OK) {
      Serial.print("ERROR: Read failed for block ");
      Serial.print(block);
      Serial.print(" - Error: ");
      Serial.println(mfrc522.GetStatusCodeName(status));
      Serial.flush();
      return false;
    }

    // Print raw block data for debugging
    Serial.print("DEBUG: Block ");
    Serial.print(block);
    Serial.print(" data: ");
    for (byte j = 0; j < BLOCK_SIZE; j++) { // Only print first 16 bytes (data, excluding CRC)
      Serial.print(buffer[j] < 0x10 ? " 0" : " ");
      Serial.print(buffer[j], HEX);
    }
    Serial.println();

    // Append data to string, stop at null character
    for (byte j = 0; j < BLOCK_SIZE; j++) {
      if (buffer[j] == 0) { // Stop at null character
        break;
      }
      readData += (char)buffer[j];
    }

    // Stop reading if we encounter a block with all zeros (end of data)
    bool allZeros = true;
    for (byte j = 0; j < BLOCK_SIZE; j++) {
      if (buffer[j] != 0) {
        allZeros = false;
        break;
      }
    }
    if (allZeros) {
      break;
    }
  }

  // Print the read data
  if (readData.length() > 0) {
    Serial.print("DATA: ");
    Serial.println(readData);
    Serial.flush();
    return true;
  } else {
    Serial.println("ERROR: No data found on card");
    Serial.flush();
    return false;
  }
}
