<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class ArduinoNFCService
{
    private $serialPort;
    private $baudRate;
    private $timeout;
    private $handle;

    /**
     * Initialize Arduino NFC Service
     *
     * @param string $port Serial port (e.g., '/dev/ttyUSB0' for Linux/Mac, 'COM3' for Windows)
     * @param int $baudRate Communication speed (default: 9600)
     * @param int $timeout Read timeout in seconds (default: 10)
     */
    public function __construct(
        string $port = null,
        int $baudRate = 9600,
        int $timeout = 10
    ) {
        $this->serialPort = $port ?? $this->detectSerialPort();
        $this->baudRate = $baudRate;
        $this->timeout = $timeout;
    }

    /**
     * Detect the serial port automatically
     *
     * @return string
     */
    private function detectSerialPort(): string
    {
        // Check environment variable first
        if ($port = env('ARDUINO_SERIAL_PORT')) {
            return $port;
        }

        // Auto-detect based on OS
        if (PHP_OS_FAMILY === 'Windows') {
            // Try common Windows COM ports
            for ($i = 3; $i <= 10; $i++) {
                if (file_exists("\\\\.\\COM{$i}")) {
                    return "COM{$i}";
                }
            }
            return 'COM3'; // Default
        } else {
            // Linux/Mac - try common USB serial ports
            $possiblePorts = [
                '/dev/ttyUSB0',
                '/dev/ttyUSB1',
                '/dev/ttyACM0',
                '/dev/ttyACM1',
                '/dev/cu.usbserial',
                '/dev/cu.usbmodem'
            ];

            foreach ($possiblePorts as $port) {
                if (file_exists($port)) {
                    return $port;
                }
            }
            return '/dev/ttyUSB0'; // Default
        }
    }

    /**
     * Open serial port connection
     *
     * @return bool
     * @throws Exception
     */
    private function openPort(): bool
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows serial port configuration
                exec("mode {$this->serialPort} BAUD={$this->baudRate} PARITY=N DATA=8 STOP=1");
                $this->handle = fopen($this->serialPort, "r+b");
            } else {
                // Linux/Mac serial port configuration
                exec("stty -F {$this->serialPort} {$this->baudRate} cs8 -cstopb -parenb");
                $this->handle = fopen($this->serialPort, "r+b");
            }

            if ($this->handle === false) {
                throw new Exception("Failed to open serial port: {$this->serialPort}");
            }

            // Set stream timeout
            stream_set_timeout($this->handle, $this->timeout);
            stream_set_blocking($this->handle, true);

            // Wait for Arduino to initialize (it resets on serial connection)
            sleep(2);

            return true;
        } catch (Exception $e) {
            Log::error("Arduino NFC Service - Open Port Error: " . $e->getMessage());
            throw new Exception("Cannot connect to Arduino: " . $e->getMessage());
        }
    }

    /**
     * Close serial port connection
     */
    private function closePort(): void
    {
        if ($this->handle) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    /**
     * Write student data to NFC tag via Arduino
     *
     * @param array $studentData
     * @return array ['success' => bool, 'message' => string]
     */
    public function writeStudentDataToNFC(array $studentData): array
    {
        try {
            // Open serial port
            $this->openPort();

            // Prepare data packet for Arduino
            $nfcData = $this->prepareNFCData($studentData);

            // Send command to Arduino
            $command = "WRITE_NFC\n";
            fwrite($this->handle, $command);
            usleep(100000); // Wait 100ms

            // Send data length
            $dataLength = strlen($nfcData);
            fwrite($this->handle, $dataLength . "\n");
            usleep(100000);

            // Send actual data
            fwrite($this->handle, $nfcData . "\n");
            fflush($this->handle);

            Log::info("Arduino NFC Service - Sent data: " . $nfcData);

            // Wait for Arduino response
            $response = $this->waitForResponse();

            $this->closePort();

            return $response;
        } catch (Exception $e) {
            $this->closePort();
            Log::error("Arduino NFC Service - Write Error: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to write to NFC tag: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Prepare student data for NFC writing
     *
     * @param array $studentData
     * @return string
     */
    private function prepareNFCData(array $studentData): string
    {
        // Format: STUDENT_CODE|FIRST_NAME|LAST_NAME|GRADE|CLASS|DATE
        $nfcData = implode('|', [
            $studentData['student_code'] ?? '',
            $studentData['first_name'] ?? '',
            $studentData['last_name'] ?? '',
            $studentData['grade_level'] ?? '',
            $studentData['class_id'] ?? '',
            $studentData['enrollment_date'] ?? ''
        ]);

        // Limit to 256 characters for NFC compatibility
        if (strlen($nfcData) > 256) {
            $nfcData = substr($nfcData, 0, 256);
        }

        return $nfcData;
    }

    /**
     * Wait for response from Arduino
     *
     * @return array
     */
    private function waitForResponse(): array
    {
        $startTime = time();
        $response = '';
        $infoMessages = [];

        Log::info("Arduino NFC Service - Waiting for response (timeout: {$this->timeout}s)");

        while ((time() - $startTime) < $this->timeout) {
            if (feof($this->handle)) {
                Log::warning("Arduino NFC Service - Connection closed unexpectedly");
                break;
            }

            // Use stream_select for non-blocking read with timeout
            $read = [$this->handle];
            $write = null;
            $except = null;
            $tv_sec = 0;
            $tv_usec = 100000; // 100ms

            if (stream_select($read, $write, $except, $tv_sec, $tv_usec) > 0) {
                $line = fgets($this->handle);
                if ($line !== false) {
                    $line = trim($line);
                    $response .= $line . "\n";

                    Log::debug("Arduino NFC Service - Received: " . $line);

                    $normalizedLine = strtoupper($line);

                    // Handle INFO messages (progress updates)
                    if (strpos($normalizedLine, 'INFO:') === 0) {
                        $infoMessages[] = trim(substr($line, strpos($line, ':') + 1));
                        continue;
                    }

                    // Check for final response
                    if (strpos($normalizedLine, 'SUCCESS') !== false) {
                        Log::info("Arduino NFC Service - Success response received");
                        return [
                            'success' => true,
                            'message' => 'Student data successfully written to RFID tag!',
                            'details' => implode("\n", $infoMessages)
                        ];
                    } elseif (strpos($normalizedLine, 'ERROR') !== false) {
                        $errorMsg = trim(preg_replace('/^\s*ERROR\s*:*/i', '', $line));
                        Log::warning("Arduino NFC Service - Error response: " . $errorMsg);
                        return [
                            'success' => false,
                            'message' => 'Arduino reported an error: ' . $errorMsg,
                            'details' => implode("\n", $infoMessages)
                        ];
                    } elseif (strpos($normalizedLine, 'TIMEOUT') !== false) {
                        $timeoutMsg = trim(preg_replace('/^\s*TIMEOUT\s*:*/i', '', $line));
                        Log::warning("Arduino NFC Service - Timeout: " . $timeoutMsg);
                        return [
                            'success' => false,
                            'message' => 'Timeout: ' . $timeoutMsg,
                            'details' => implode("\n", $infoMessages)
                        ];
                    }
                }
            }
        }

        // Communication timeout occurred
        Log::warning("Arduino NFC Service - Communication timeout after {$this->timeout} seconds");
        Log::warning("Arduino NFC Service - Partial response: " . $response);

        return [
            'success' => false,
            'message' => 'Communication timeout. Please ensure Arduino is connected and the RFID tag is present.',
            'details' => 'Last response: ' . trim($response)
        ];
    }

    /**
     * Read student data from NFC tag via Arduino
     *
     * @return array ['success' => bool, 'message' => string, 'data' => array|null]
     */
    public function readNFCTag(): array
    {
        try {
            // Open serial port
            $this->openPort();

            // Send read command to Arduino
            $command = "READ_NFC\n";
            fwrite($this->handle, $command);
            fflush($this->handle);

            Log::info("Arduino NFC Service - Sent READ_NFC command");

            // Wait for Arduino response
            $response = $this->waitForReadResponse();

            $this->closePort();

            return $response;
        } catch (Exception $e) {
            $this->closePort();
            Log::error("Arduino NFC Service - Read Error: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to read NFC tag: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Wait for read response from Arduino and parse student data
     *
     * @return array
     */
    private function waitForReadResponse(): array
    {
        $startTime = time();
        $response = '';

        while ((time() - $startTime) < $this->timeout) {
            if (feof($this->handle)) {
                break;
            }

            $line = fgets($this->handle);
            if ($line !== false) {
                $response .= $line;
                $line = trim($line);

                // Check for success response with data
                if (strpos($line, 'NFC_DATA:') === 0) {
                    $data = substr($line, 9); // Remove "NFC_DATA:" prefix
                    Log::info("Arduino NFC Service - Read data: " . $data);

                    $studentData = $this->parseNFCData($data);

                    if ($studentData) {
                        return [
                            'success' => true,
                            'message' => 'NFC tag read successfully',
                            'data' => $studentData
                        ];
                    } else {
                        return [
                            'success' => false,
                            'message' => 'Invalid data format on NFC tag',
                            'data' => null
                        ];
                    }
                } elseif (strpos($line, 'ERROR') !== false) {
                    Log::warning("Arduino NFC Service - Read error: " . $line);
                    return [
                        'success' => false,
                        'message' => 'Arduino reported an error: ' . $line,
                        'data' => null
                    ];
                } elseif (strpos($line, 'NO_TAG') !== false) {
                    return [
                        'success' => false,
                        'message' => 'No NFC tag detected. Please place tag on reader.',
                        'data' => null
                    ];
                }
            }

            usleep(100000); // Wait 100ms before checking again
        }

        // Timeout occurred
        Log::warning("Arduino NFC Service - Read timeout");
        return [
            'success' => false,
            'message' => 'Timeout waiting for NFC tag. Please place tag on reader.',
            'data' => null
        ];
    }

    /**
     * Parse NFC data string into student data array
     *
     * @param string $data
     * @return array|null
     */
    private function parseNFCData(string $data): ?array
    {
        // Format: STUDENT_CODE|FIRST_NAME|LAST_NAME|GRADE|CLASS|DATE
        $parts = explode('|', $data);

        if (count($parts) >= 4) {
            return [
                'student_code' => $parts[0] ?? '',
                'first_name' => $parts[1] ?? '',
                'last_name' => $parts[2] ?? '',
                'grade_level' => $parts[3] ?? '',
                'class_id' => $parts[4] ?? '',
                'enrollment_date' => $parts[5] ?? ''
            ];
        }

        return null;
    }

    /**
     * Start continuous NFC reading mode (for attendance kiosk)
     * This method returns immediately and should be called in a loop
     *
     * @return array ['success' => bool, 'message' => string, 'data' => array|null]
     */
    public function startContinuousRead(): array
    {
        try {
            if (!$this->handle) {
                $this->openPort();

                // Send continuous read command once
                $command = "CONTINUOUS_READ\n";
                fwrite($this->handle, $command);
                fflush($this->handle);

                Log::info("Arduino NFC Service - Started continuous read mode");
            }

            // Check if there's any data available (non-blocking)
            $read = [$this->handle];
            $write = null;
            $except = null;

            // Check if data is available (0 second timeout = non-blocking)
            if (stream_select($read, $write, $except, 0, 100000) > 0) {
                $line = fgets($this->handle);
                if ($line !== false) {
                    $line = trim($line);

                    if (strpos($line, 'NFC_DATA:') === 0) {
                        $data = substr($line, 9);
                        Log::info("Arduino NFC Service - Continuous read data: " . $data);

                        $studentData = $this->parseNFCData($data);

                        if ($studentData) {
                            return [
                                'success' => true,
                                'message' => 'NFC tag detected',
                                'data' => $studentData
                            ];
                        }
                    }
                }
            }

            // No data available yet
            return [
                'success' => false,
                'message' => 'No tag detected',
                'data' => null
            ];
        } catch (Exception $e) {
            $this->closePort();
            Log::error("Arduino NFC Service - Continuous read error: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error in continuous read: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Stop continuous reading mode and close connection
     */
    public function stopContinuousRead(): void
    {
        $this->closePort();
        Log::info("Arduino NFC Service - Stopped continuous read mode");
    }

    /**
     * Test Arduino connection
     *
     * @return array
     */
    public function testConnection(): array
    {
        try {
            $this->openPort();

            // Send ping command
            fwrite($this->handle, "PING\n");
            fflush($this->handle);

            // Wait for response
            $startTime = time();
            while ((time() - $startTime) < 5) {
                $line = fgets($this->handle);
                if ($line !== false && strpos($line, 'PONG') !== false) {
                    $this->closePort();
                    return [
                        'success' => true,
                        'message' => 'Arduino connected successfully',
                        'port' => $this->serialPort
                    ];
                }
                usleep(100000);
            }

            $this->closePort();
            return [
                'success' => false,
                'message' => 'Arduino not responding',
                'port' => $this->serialPort
            ];
        } catch (Exception $e) {
            $this->closePort();
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'port' => $this->serialPort
            ];
        }
    }

    /**
     * Get current serial port
     *
     * @return string
     */
    public function getSerialPort(): string
    {
        return $this->serialPort;
    }

    /**
     * Set serial port
     *
     * @param string $port
     */
    public function setSerialPort(string $port): void
    {
        $this->serialPort = $port;
    }
}
