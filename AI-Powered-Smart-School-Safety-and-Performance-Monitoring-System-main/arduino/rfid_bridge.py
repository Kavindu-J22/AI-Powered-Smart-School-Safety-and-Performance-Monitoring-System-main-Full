#!/usr/bin/env python3
"""
rfid_bridge.py — Serial bridge for Arduino UNO R3 + RC522 RFID reader
=======================================================================

Reads newline-terminated JSON from the Arduino over USB-Serial and forwards
each card scan to the Laravel web server via HTTP POST.

The server endpoint ( /api/rfid/scan ) decides automatically whether the scan
should be treated as:
  • a wristband ENROLLMENT  (if an admin has an enrollment modal open), or
  • an ATTENDANCE scan      (normal check-in / check-out).

Usage
──────
  pip install pyserial requests
  python rfid_bridge.py

Environment variables (optional — override defaults below)
───────────────────────────────────────────────────────────
  RFID_PORT        Serial port, e.g. COM3 / /dev/ttyUSB0 / /dev/tty.usbserial-*
  RFID_BAUD        Baud rate (default 9600)
  SERVER_URL       Base URL of the Laravel server (default http://127.0.0.1:8000)

Auto-detect
───────────
  If RFID_PORT is not set the script tries to auto-detect the Arduino by
  scanning available serial ports for CH340 / CDC ACM devices.
"""

import json
import os
import sys
import time
import logging
import threading

try:
    import serial
    import serial.tools.list_ports
    import requests
except ImportError:
    print(
        "Missing dependencies.  Run:\n"
        "  pip install pyserial requests\n"
    )
    sys.exit(1)

# ── Configuration ──────────────────────────────────────────────────────────
SERIAL_PORT  = os.environ.get("RFID_PORT",   "")          # auto-detect if empty
BAUD_RATE    = int(os.environ.get("RFID_BAUD",   "115200"))  # matches Arduino 115200
SERVER_URL   = os.environ.get("SERVER_URL",  "http://127.0.0.1:8000")
SCAN_ENDPOINT = f"{SERVER_URL}/api/rfid/scan"

RECONNECT_DELAY = 5        # seconds to wait before retrying after disconnect
REQUEST_TIMEOUT = 3        # seconds for HTTP request timeout (localhost is fast)

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s  %(levelname)-7s  %(message)s",
    datefmt="%H:%M:%S",
)
log = logging.getLogger("rfid_bridge")

# Persistent HTTP session — reuses the TCP connection to localhost, cutting
# per-request latency from ~5 ms to <1 ms after the first request.
_session = requests.Session()


# ── Serial-port auto-detection ─────────────────────────────────────────────
# Ports to skip during auto-detection (macOS system / Bluetooth / WiFi ports)
_SKIP_KEYWORDS = ("wlan", "bluetooth", "debug-console", "bose", "baseus", "airpods", "bowie")


def detect_arduino_port() -> str:
    """Return the first serial port that looks like an Arduino / CH340."""
    candidates = serial.tools.list_ports.comports()

    # Priority 1: known Arduino/CH340 identifiers
    for port in candidates:
        desc = (port.description or "").lower()
        hwid = (port.hwid or "").lower()
        name = (port.device or "").lower()
        if any(kw in name for kw in _SKIP_KEYWORDS):
            continue
        if any(kw in desc or kw in hwid or kw in name
               for kw in ("ch340", "arduino", "usbserial", "usb serial", "acm", "ftdi")):
            log.info("Auto-detected Arduino on %s  (%s)", port.device, port.description)
            return port.device

    # Priority 2: any usbserial / usbmodem port that isn't a known system port
    for port in candidates:
        name = (port.device or "").lower()
        if any(kw in name for kw in _SKIP_KEYWORDS):
            continue
        if "usb" in name:
            log.info("Auto-detected likely Arduino on %s  (%s)", port.device, port.description)
            return port.device

    log.error("Could not auto-detect Arduino port. Set RFID_PORT env var (e.g. RFID_PORT=/dev/cu.usbserial-1110).")
    return ""


# ── HTTP forwarding ────────────────────────────────────────────────────────
def forward_to_server(uid: str, device_id: str) -> None:
    """POST the UID to the Laravel server (uses persistent session)."""
    payload = {"uid": uid, "device_id": device_id}
    try:
        resp = _session.post(SCAN_ENDPOINT, json=payload, timeout=REQUEST_TIMEOUT)
        data = resp.json()
        mode = data.get("mode", "attendance")
        if mode == "enrollment":
            log.info("UID %s  →  ENROLLMENT session captured", uid)
        else:
            action  = data.get("action",  data.get("message", "?"))
            name    = data.get("data", {}).get("student_name", "?") if data.get("success") else "?"
            success = "✓" if data.get("success") else "✗"
            log.info("UID %s  →  %s  %s  (%s)", uid, success, action, name)
    except requests.exceptions.ConnectionError:
        log.error("Cannot reach server at %s — is Laravel running?", SERVER_URL)
    except requests.exceptions.Timeout:
        log.warning("Server request timed out for UID %s", uid)
    except Exception as exc:
        log.error("Unexpected error forwarding UID %s: %s", uid, exc)


# ── Serial reading loop ────────────────────────────────────────────────────
def read_loop(port: str) -> None:
    log.info("Opening serial port %s @ %d baud …", port, BAUD_RATE)
    try:
        ser = serial.Serial(port, BAUD_RATE, timeout=1)
        log.info("Connected.  Waiting for RFID scans …\n")
    except serial.SerialException as exc:
        log.error("Cannot open port %s: %s", port, exc)
        return

    try:
        while True:
            raw = ser.readline()
            if not raw:
                continue

            line = raw.decode("utf-8", errors="ignore").strip()
            if not line or not line.startswith("{"):
                continue

            try:
                msg = json.loads(line)
            except json.JSONDecodeError:
                log.debug("Non-JSON line ignored: %s", line)
                continue

            # Status / heartbeat messages (no 'uid' key)
            if "uid" not in msg:
                log.debug("Arduino: %s", msg)
                continue

            uid       = msg.get("uid", "").strip().upper()
            device_id = msg.get("device_id", "UNKNOWN")

            if not uid:
                continue

            log.info("Card scanned: UID=%s  device=%s", uid, device_id)
            # Run the HTTP call in a daemon thread so serial reading resumes
            # immediately — the Arduino won’t miss a quick second tap while
            # waiting for the server response.
            threading.Thread(
                target=forward_to_server, args=(uid, device_id), daemon=True
            ).start()

    except serial.SerialException as exc:
        log.warning("Serial port disconnected: %s", exc)
    finally:
        try:
            ser.close()
        except Exception:
            pass


# ── Entry point ────────────────────────────────────────────────────────────
def main():
    log.info("RFID Bridge starting  |  server=%s", SERVER_URL)

    port = SERIAL_PORT or detect_arduino_port()
    if not port:
        log.error(
            "No serial port found.  Set the RFID_PORT environment variable, "
            "e.g.:  export RFID_PORT=/dev/ttyUSB0"
        )
        sys.exit(1)

    while True:
        read_loop(port)
        log.info("Reconnecting in %d s …", RECONNECT_DELAY)
        time.sleep(RECONNECT_DELAY)


if __name__ == "__main__":
    main()
