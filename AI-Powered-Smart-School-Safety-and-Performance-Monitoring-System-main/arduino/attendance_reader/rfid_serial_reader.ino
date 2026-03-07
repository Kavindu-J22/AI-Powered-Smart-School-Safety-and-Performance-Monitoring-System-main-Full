/*
 * RFID Attendance Reader — Arduino UNO R3 + RC522 + RGB LED
 * ──────────────────────────────────────────────────────────
 * Hardware wiring
 * ────────────────
 *  RC522 RFID Reader (3.3 V logic):
 *    VCC  → 3.3 V
 *    GND  → GND
 *    RST  → D9
 *    SDA  → D10  (SPI SS)
 *    MOSI → D11  (SPI MOSI)
 *    MISO → D12  (SPI MISO)
 *    SCK  → D13  (SPI SCK)
 *
 *  RGB LED — Common Cathode (220 Ω series resistors):
 *    Red   → D6
 *    Green → D5
 *    Blue  → D3
 *    GND   → GND
 *
 * How it works
 * ─────────────
 *  1. Board waits in "idle" state → slow blue breathe on the LED.
 *  2. A card / wristband is brought near the RC522.
 *  3. If the same card was scanned within SAME_CARD_MS (2 s), it is ignored
 *     to avoid duplicate serial messages.
 *  4. The hardware UID is sent over USB-Serial as a JSON line:
 *        {"uid":"A1B2C3D4","device_id":"DOOR_01"}
 *  5. LED gives feedback:
 *       GREEN  flash × 2  → card read OK, data sent
 *       RED    flash × 2  → read error
 *  6. The Python serial bridge (rfid_bridge.py) running on the PC picks up the
 *     JSON and POSTs it to the Laravel web server at /api/rfid/scan.
 *
 * Serial baud rate: 9600
 *
 * Library dependencies (install via Arduino Library Manager):
 *   • MFRC522  by GithubCommunity  (search "MFRC522")
 *
 * NOTE: Do NOT open the Arduino Serial Monitor while rfid_bridge.py is running —
 *       only one program can own the serial port at a time.
 */

#include <SPI.h>
#include <MFRC522.h>

// ── Pin definitions ────────────────────────────────────────────────────────
#define RC522_RST_PIN 9
#define RC522_SS_PIN 10
#define LED_RED 6
#define LED_GREEN 5
#define LED_BLUE 3

// ── Configuration ──────────────────────────────────────────────────────────
#define DEVICE_ID "DOOR_01" // Unique ID for this reader; change per device
#define SERIAL_BAUD 115200  // High-speed serial for near-instant JSON delivery
#define SAME_CARD_MS 1500   // Ignore same card again within 1.5 seconds
#define LED_FLASH_MS 80     // Short flash — minimises blocking time in loop
#define BREATHE_PERIOD 3000 // Blue breathe cycle in ms

// ── Globals ────────────────────────────────────────────────────────────────
MFRC522 mfrc522(RC522_SS_PIN, RC522_RST_PIN);

char lastUID[20] = "";     // Last successfully read UID string
uint32_t lastScanTime = 0; // millis() when last UID was processed

// ── Setup ──────────────────────────────────────────────────────────────────
void setup()
{
    Serial.begin(SERIAL_BAUD);
    while (!Serial)
    {
    } // Wait for USB CDC on native-USB boards (harmless on UNO)

    SPI.begin();
    mfrc522.PCD_Init();
    mfrc522.PCD_SetAntennaGain(mfrc522.RxGain_max); // Maximum RF gain — faster, more reliable reads

    pinMode(LED_RED, OUTPUT);
    pinMode(LED_GREEN, OUTPUT);
    pinMode(LED_BLUE, OUTPUT);

    setRGB(0, 0, 0);
    Serial.println(F("{\"status\":\"ready\",\"device_id\":\"" DEVICE_ID "\"}"));
}

// ── Main loop ─────────────────────────────────────────────────────────────
void loop()
{
    breatheBlue(); // Idle animation — non-blocking

    // Detect a card: try REQA first (idle cards), then WUPA (previously-halted cards).
    // Without the WakeupA fallback the reader misses cards tapped a second time
    // because PICC_HaltA leaves them in HALT state, which ignores REQA (0x26).
    if (!mfrc522.PICC_IsNewCardPresent())
    {
        byte atqa[2];
        byte atqaSize = sizeof(atqa);
        if (mfrc522.PICC_WakeupA(atqa, &atqaSize) != MFRC522::STATUS_OK)
            return;
    }
    if (!mfrc522.PICC_ReadCardSerial())
        return;

    // Build UID hex string (4 or 7 bytes depending on card type)
    char uid[20];
    buildUIDString(mfrc522.uid.uidByte, mfrc522.uid.size, uid);
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();

    uint32_t now = millis();

    // ── Same-card debounce ────────────────────────────────────────────────
    if (strcmp(uid, lastUID) == 0 && (now - lastScanTime) < SAME_CARD_MS)
    {
        return; // Ignore duplicate tap
    }

    strncpy(lastUID, uid, sizeof(lastUID) - 1);
    lastScanTime = now;

    // ── Send JSON over Serial ─────────────────────────────────────────────
    Serial.print(F("{\"uid\":\""));
    Serial.print(uid);
    Serial.print(F("\",\"device_id\":\"" DEVICE_ID "\"}"));
    Serial.println(); // Newline terminates the JSON record

    flashGreen(1); // Single short flash (~160 ms) — was 2×200 ms (800 ms)
}

// ── Helpers ───────────────────────────────────────────────────────────────

/** Convert raw UID bytes to uppercase hex string (e.g."A1B2C3D4"). */
void buildUIDString(byte *buffer, byte length, char *out)
{
    char *p = out;
    for (byte i = 0; i < length; i++)
    {
        sprintf(p, "%02X", buffer[i]);
        p += 2;
    }
    *p = '\0';
}

/** Set the RGB LED colour (0–255 per channel). */
void setRGB(uint8_t r, uint8_t g, uint8_t b)
{
    analogWrite(LED_RED, r);
    analogWrite(LED_GREEN, g);
    analogWrite(LED_BLUE, b);
}

/** Flash green <count> times. Blocking but brief. */
void flashGreen(uint8_t count)
{
    for (uint8_t i = 0; i < count; i++)
    {
        setRGB(0, 255, 0);
        delay(LED_FLASH_MS);
        setRGB(0, 0, 0);
        delay(LED_FLASH_MS);
    }
}

/** Flash red <count> times. Blocking but brief. */
void flashRed(uint8_t count)
{
    for (uint8_t i = 0; i < count; i++)
    {
        setRGB(255, 0, 0);
        delay(LED_FLASH_MS);
        setRGB(0, 0, 0);
        delay(LED_FLASH_MS);
    }
}

/**
 * Non-blocking blue breathe animation using PWM.
 * Runs inside the main loop; does NOT use delay().
 */
void breatheBlue()
{
    static uint32_t lastUpdate = 0;
    uint32_t now = millis();
    if (now - lastUpdate < 20)
        return; // Update every 20 ms → ~50 fps
    lastUpdate = now;

    uint32_t phase = now % BREATHE_PERIOD;
    float t = (float)phase / BREATHE_PERIOD * 2.0f * PI;
    uint8_t level = (uint8_t)((sin(t) + 1.0f) / 2.0f * 60.0f); // 0–60 (dim)
    setRGB(0, 0, level);
}
