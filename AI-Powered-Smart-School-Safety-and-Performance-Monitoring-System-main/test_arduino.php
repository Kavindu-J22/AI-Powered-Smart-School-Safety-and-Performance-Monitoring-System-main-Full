#!/usr/bin/env php
<?php
/**
 * Arduino MFRC522 Connection Test
 * Tests the Arduino connection using PHP
 */

// Load environment variables
require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Log;

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "==========================================\n";
echo "Arduino MFRC522 PHP Connection Test\n";
echo "==========================================\n\n";

// Get configuration
$serialPort = env('ARDUINO_SERIAL_PORT');
$baudRate = env('ARDUINO_BAUD_RATE', 9600);
$timeout = env('ARDUINO_TIMEOUT', 30);

echo "Configuration:\n";
echo "  Serial Port: {$serialPort}\n";
echo "  Baud Rate: {$baudRate}\n";
echo "  Timeout: {$timeout}s\n\n";

// Test 1: Check if serial port exists
echo "Test 1: Checking if serial port exists...\n";
if (!file_exists($serialPort)) {
    echo "❌ Serial port does not exist: {$serialPort}\n";
    echo "\nAvailable ports:\n";
    $ports = glob('/dev/cu.*');
    foreach ($ports as $port) {
        echo "  - {$port}\n";
    }
    exit(1);
}
echo "✅ Serial port exists\n\n";

// Test 2: Check permissions
echo "Test 2: Checking port permissions...\n";
if (!is_readable($serialPort) || !is_writable($serialPort)) {
    echo "❌ Port permission denied\n";
    exit(1);
}
echo "✅ Port is readable and writable\n\n";

// Test 3: Try to communicate with Arduino
echo "Test 3: Attempting to connect to Arduino...\n";

try {
    // Set serial port settings using stty
    $sttyOutput = shell_exec("stty -f {$serialPort} {$baudRate} cs8 -cstopb -parenb 2>&1");

    // Open the port
    $handle = fopen($serialPort, 'r+b');

    if (!$handle) {
        echo "❌ Failed to open serial port\n";
        exit(1);
    }

    // Set non-blocking mode
    stream_set_blocking($handle, false);
    stream_set_timeout($handle, 2);

    echo "✅ Port opened successfully\n";

    // Wait a moment for Arduino to initialize
    echo "Waiting for Arduino to initialize...\n";
    usleep(2000000); // 2 seconds

    // Flush any existing data
    while (fgets($handle) !== false) {
        // Clear buffer
    }

    // Send PING command
    echo "Sending PING command...\n";
    fwrite($handle, "PING\n");
    fflush($handle);

    // Wait for response
    $response = '';
    $startTime = time();
    $responseReceived = false;

    while ((time() - $startTime) < 5) {
        $line = fgets($handle);
        if ($line !== false) {
            $line = trim($line);
            $response .= $line . "\n";
            echo "Received: {$line}\n";

            if (strpos($line, 'PONG') !== false || strpos($line, 'READY') !== false) {
                $responseReceived = true;
                break;
            }
        }
        usleep(100000); // 100ms
    }

    fclose($handle);

    echo "\n";
    if ($responseReceived) {
        echo "✅ Arduino is responding!\n";
        echo "✅ Communication test passed\n\n";
    } else {
        echo "❌ No response from Arduino\n";
        if (!empty($response)) {
            echo "Received data:\n{$response}\n";
        }
        echo "\nPossible issues:\n";
        echo "1. Arduino sketch not uploaded\n";
        echo "2. Wrong baud rate (should be 9600)\n";
        echo "3. Arduino is still initializing (wait a bit longer)\n";
        echo "4. USB cable issue\n\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Test ArduinoNFCService
echo "Test 4: Testing ArduinoNFCService class...\n";
try {
    $service = new \App\Services\ArduinoNFCService();
    $result = $service->testConnection();

    if ($result['success']) {
        echo "✅ ArduinoNFCService test passed\n";
        echo "   Message: {$result['message']}\n";
    } else {
        echo "❌ ArduinoNFCService test failed\n";
        echo "   Message: {$result['message']}\n";
    }
} catch (Exception $e) {
    echo "❌ ArduinoNFCService error: " . $e->getMessage() . "\n";
}

echo "\n==========================================\n";
echo "Test Complete!\n";
echo "==========================================\n\n";

echo "Next steps:\n";
echo "1. If Arduino is not responding, upload arduino_mfrc522_nfc_writer.ino\n";
echo "2. Open Arduino IDE Serial Monitor (9600 baud) to verify it's working\n";
echo "3. Type 'PING' in Serial Monitor - should see 'PONG'\n";
echo "4. Once working, test by creating a student in the web interface\n\n";

exit(0);
