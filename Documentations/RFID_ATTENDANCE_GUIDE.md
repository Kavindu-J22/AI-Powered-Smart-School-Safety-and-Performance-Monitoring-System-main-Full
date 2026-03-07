# RFID Wristband Attendance — Full Setup & Usage Guide

## Overview

This system uses an **Arduino UNO R3** with an **RC522 RFID reader** to let students tap their wristbands and automatically mark check-in / check-out attendance without any typing.

```
 [Student taps wristband]
         │
         ▼
 [Arduino RC522 reader]  ──USB──▶  [Python rfid_bridge.py]  ──HTTP──▶  [Laravel Server]
                                                                               │
                                          [Admin browser] ◀──polling──────────┘
```

---

## Part 1 — Hardware Setup

### What you need

| Item                                 | Quantity      |
| ------------------------------------ | ------------- |
| Arduino UNO R3 (CH340 USB)           | 1             |
| RC522 RFID Reader Module (13.56 MHz) | 1             |
| RFID wristbands / cards (MIFARE)     | 1 per student |
| RGB LED — Common Cathode (5 mm)      | 1             |
| 220 Ω resistors                      | 3             |
| Jumper wires + breadboard            | —             |

### Wiring diagram

```
Arduino         RC522 Reader
──────          ─────────────
3.3V     ──▶   VCC
GND      ──▶   GND
D9       ──▶   RST
D10      ──▶   SDA  (SS)
D11      ──▶   MOSI
D12      ──▶   MISO
D13      ──▶   SCK

Arduino         RGB LED (Common Cathode)
──────          ──────────────────────
D6       ──▶   RED   leg   (via 220 Ω)
D5       ──▶   GREEN leg   (via 220 Ω)
D3       ──▶   BLUE  leg   (via 220 Ω)
GND      ──▶   GND (common cathode)
```

> ⚠️ The RC522 is a **3.3 V module** — do NOT connect VCC to 5 V.

---

## Part 2 — Upload the Arduino Sketch

### Step 1 — Install the MFRC522 library

1. Open **Arduino IDE**
2. Go to **Tools → Manage Libraries…**
3. Search for **MFRC522**
4. Install **"MFRC522 by GithubCommunity"**

### Step 2 — Open the sketch

Open this file in Arduino IDE:

```
AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main/
  arduino/
    attendance_reader/
      rfid_serial_reader.ino
```

### Step 3 — (Optional) Change the device ID

If you have multiple readers (e.g. front door, back door), edit line 48 in the sketch:

```cpp
#define DEVICE_ID  "DOOR_01"   // ← change this per reader
```

### Step 4 — Upload

1. Connect Arduino to your Mac via USB
2. Select **Tools → Board → Arduino UNO**
3. Select **Tools → Port → /dev/tty.usbserial-XXXX** (or similar)
4. Click **Upload** (→)

### Step 5 — Verify LED behaviour

After upload the blue LED should **slowly breathe** — this means the reader is idle and waiting for a card.

---

## Part 3 — Running the Python Bridge

The bridge script reads the Arduino's USB serial output and forwards every scan to the Laravel server.

### The virtual environment is already set up at:

```
AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main-Full/
  rfid_venv/          ← Python virtual environment (pyserial + requests)
```

### Run manually (if needed)

```bash
# From the root project folder:
cd /path/to/AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main-Full

# Option A — auto-detect Arduino port:
SERVER_URL=http://127.0.0.1:8000 \
  rfid_venv/bin/python3 \
  AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main/arduino/rfid_bridge.py

# Option B — specify the port explicitly (if auto-detect fails):
RFID_PORT=/dev/tty.usbserial-110 \
SERVER_URL=http://127.0.0.1:8000 \
  rfid_venv/bin/python3 \
  AI-Powered-Smart-School-Safety-and-Performance-Monitoring-System-main/arduino/rfid_bridge.py
```

### Find your Arduino serial port

```bash
ls /dev/tty.usb*
# typical output:  /dev/tty.usbserial-110  or  /dev/tty.wchusbserial14110
```

### Automatic startup

The bridge is already added to `start_all_services.sh` as **Step 7**. Just run:

```bash
./start_all_services.sh
```

Bridge log is saved to: `logs/rfid_bridge.log`

---

## Part 4 — Assigning a Wristband to a Student

This is done once per student. You will scan their physical wristband and link its hardware ID to their profile.

### Step 1 — Open the student form

- Go to **Management → Students**
- Click **Edit** on a student (or **New Student** for a new enrollment)

### Step 2 — Find the RFID Wristband section

Scroll down on the form until you see the purple **RFID Wristband** card.

| State            | What you see                                                            |
| ---------------- | ----------------------------------------------------------------------- |
| Not assigned     | Grey badge "No wristband assigned" + **Assign Wristband** button        |
| Already assigned | Green badge showing the UID + **Change Wristband** / **Remove** buttons |

> 💡 For a brand-new student, save the form first (click **Create Student**) — then open Edit to assign the wristband.

### Step 3 — Click "Assign Wristband"

A modal dialog opens with an animated icon and the message **"Place the wristband on the reader"**.

### Step 4 — Tap the wristband on the RC522 reader

- The Python bridge must be running
- Hold the wristband / card near the RC522 reader
- The **blue LED** turns **green** and flashes twice
- The modal detects the card within ~1.5 seconds and shows the UID

### Step 5 — Confirm the assignment

- The modal shows the detected UID (e.g. `A1B2C3D4`)
- Click **Confirm**
- The student's profile is updated immediately — no need to re-save the form

### Step 6 — Verify

The RFID Wristband card now shows a **green badge** with the UID.

---

## Part 5 — Marking Attendance with RFID

### Step 1 — Go to Attendance → Create

**Management → Attendance → Record Attendance**

### Step 2 — Switch to RFID Scan Mode

At the top of the page are two tabs:

- **Manual Entry** (default)
- **RFID Scan Mode** ← click this

The page shows a pulsing blue icon and the message **"Waiting for scan…"**.

### Step 3 — Student taps wristband

- The student holds their wristband near the RC522 reader
- The bridge posts the UID to the server
- Within ~1.5 seconds the page automatically shows the result:

| Scenario                    | LED            | Screen                                          |
| --------------------------- | -------------- | ----------------------------------------------- |
| First tap of the day        | Green flash ×2 | ✅ Student name — **Checked In** @ 08:32        |
| Second tap (going home)     | Green flash ×2 | ✅ Student name — **Checked Out** @ 15:45       |
| Already fully recorded      | Red flash ×2   | ⚠ "Attendance already fully recorded for today" |
| Card not assigned to anyone | Red flash ×2   | ⚠ "No student is assigned to this wristband"    |

### Step 4 — Keep scanning

Leave the page open — the next student can tap immediately. There is no "submit" button; each scan is recorded automatically.

---

## Part 6 — Duplicate & Abuse Prevention Rules

| Rule                        | Detail                                                                                                                             |
| --------------------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| Same-card hardware debounce | Arduino ignores the same card for **2 seconds** after a read                                                                       |
| Server-side debounce        | Server ignores the same student for **3 seconds** (catches late serial retries)                                                    |
| Checkout cooldown           | A student cannot check out again for **5 minutes** after a checkout (prevents accidental double-taps)                              |
| Card conflict               | One wristband can only be assigned to **one student** — assigning to a second student shows an error with the first student's name |

---

## Part 7 — Removing or Replacing a Wristband

### Remove wristband (lost / broken)

1. Edit the student → **RFID Wristband** card → click **Remove**
2. Confirm the prompt
3. The student's wristband field is cleared; a new one can be assigned

### Replace wristband

1. Edit the student → **RFID Wristband** card → click **Change Wristband**
2. Tap the new wristband on the reader → Confirm
3. The old UID is overwritten

---

## Part 8 — Troubleshooting

| Problem                               | Solution                                                     |
| ------------------------------------- | ------------------------------------------------------------ |
| LED stays off after upload            | Check wiring — common cathode GND connected?                 |
| Modal opens but never detects card    | Check bridge is running: `tail -f logs/rfid_bridge.log`      |
| Bridge exits immediately              | Check port: `ls /dev/tty.usb*` — set `RFID_PORT` env var     |
| "No student assigned to wristband"    | Wristband not enrolled yet — complete Part 4 first           |
| "Duplicate scan" error                | Normal — wait 3 seconds and scan again                       |
| Checkout blocked (cooldown)           | Normal — wait 5 minutes after the previous checkout          |
| Laravel not reachable from bridge     | Make sure `php artisan serve` is running; check `SERVER_URL` |
| Port in use by Arduino Serial Monitor | Close Arduino Serial Monitor — only one app can use the port |
