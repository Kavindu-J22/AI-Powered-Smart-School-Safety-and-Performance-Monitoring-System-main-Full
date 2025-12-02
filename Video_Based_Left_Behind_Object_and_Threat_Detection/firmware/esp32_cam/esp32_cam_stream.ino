/*
 * ESP32-CAM Video Streaming Firmware
 * For School Security System - Left Behind Object and Threat Detection
 *
 * Features:
 * - WiFi connectivity
 * - HTTP video streaming
 * - MQTT status reporting
 * - Motion detection
 * - Low power mode
 */

#include "esp_camera.h"
#include <WiFi.h>
#include <WebServer.h>
#include <PubSubClient.h>

// ==================== CONFIGURATION ====================

// WiFi Credentials
const char *ssid = "YOUR_WIFI_SSID";
const char *password = "YOUR_WIFI_PASSWORD";

// Camera Configuration
const char *camera_id = "CAM_001";
const char *camera_location = "Classroom 1A";

// Server Configuration
const char *mqtt_server = "192.168.1.100";
const int mqtt_port = 1883;

// Camera Settings
#define CAMERA_MODEL_AI_THINKER
#define FRAME_SIZE FRAMESIZE_VGA // 640x480
#define JPEG_QUALITY 10          // 10-63, lower = better quality
#define FRAME_RATE 15            // FPS

// LED Pin
#define LED_PIN 33

// ==================== PIN DEFINITIONS ====================

#if defined(CAMERA_MODEL_AI_THINKER)
#define PWDN_GPIO_NUM 32
#define RESET_GPIO_NUM -1
#define XCLK_GPIO_NUM 0
#define SIOD_GPIO_NUM 26
#define SIOC_GPIO_NUM 27

#define Y9_GPIO_NUM 35
#define Y8_GPIO_NUM 34
#define Y7_GPIO_NUM 39
#define Y6_GPIO_NUM 36
#define Y5_GPIO_NUM 21
#define Y4_GPIO_NUM 19
#define Y3_GPIO_NUM 18
#define Y2_GPIO_NUM 5
#define VSYNC_GPIO_NUM 25
#define HREF_GPIO_NUM 23
#define PCLK_GPIO_NUM 22
#endif

// ==================== GLOBAL VARIABLES ====================

WebServer server(80);
WiFiClient espClient;
PubSubClient mqtt(espClient);

unsigned long lastFrameTime = 0;
unsigned long frameInterval = 1000 / FRAME_RATE;
bool streamActive = false;

// ==================== CAMERA INITIALIZATION ====================

bool initCamera()
{
  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = Y2_GPIO_NUM;
  config.pin_d1 = Y3_GPIO_NUM;
  config.pin_d2 = Y4_GPIO_NUM;
  config.pin_d3 = Y5_GPIO_NUM;
  config.pin_d4 = Y6_GPIO_NUM;
  config.pin_d5 = Y7_GPIO_NUM;
  config.pin_d6 = Y8_GPIO_NUM;
  config.pin_d7 = Y9_GPIO_NUM;
  config.pin_xclk = XCLK_GPIO_NUM;
  config.pin_pclk = PCLK_GPIO_NUM;
  config.pin_vsync = VSYNC_GPIO_NUM;
  config.pin_href = HREF_GPIO_NUM;
  config.pin_sscb_sda = SIOD_GPIO_NUM;
  config.pin_sscb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn = PWDN_GPIO_NUM;
  config.pin_reset = RESET_GPIO_NUM;
  config.xclk_freq_hz = 20000000;
  config.pixel_format = PIXFORMAT_JPEG;

  // Init with high specs to pre-allocate larger buffers
  if (psramFound())
  {
    config.frame_size = FRAME_SIZE;
    config.jpeg_quality = JPEG_QUALITY;
    config.fb_count = 2;
  }
  else
  {
    config.frame_size = FRAMESIZE_SVGA;
    config.jpeg_quality = 12;
    config.fb_count = 1;
  }

  // Camera init
  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK)
  {
    Serial.printf("Camera init failed with error 0x%x\n", err);
    return false;
  }

  // Sensor settings
  sensor_t *s = esp_camera_sensor_get();
  s->set_framesize(s, FRAME_SIZE);
  s->set_quality(s, JPEG_QUALITY);

  // Additional settings for better image quality
  s->set_brightness(s, 0);                 // -2 to 2
  s->set_contrast(s, 0);                   // -2 to 2
  s->set_saturation(s, 0);                 // -2 to 2
  s->set_special_effect(s, 0);             // 0 to 6 (0 - No Effect)
  s->set_whitebal(s, 1);                   // 0 = disable , 1 = enable
  s->set_awb_gain(s, 1);                   // 0 = disable , 1 = enable
  s->set_wb_mode(s, 0);                    // 0 to 4
  s->set_exposure_ctrl(s, 1);              // 0 = disable , 1 = enable
  s->set_aec2(s, 0);                       // 0 = disable , 1 = enable
  s->set_ae_level(s, 0);                   // -2 to 2
  s->set_aec_value(s, 300);                // 0 to 1200
  s->set_gain_ctrl(s, 1);                  // 0 = disable , 1 = enable
  s->set_agc_gain(s, 0);                   // 0 to 30
  s->set_gainceiling(s, (gainceiling_t)0); // 0 to 6
  s->set_bpc(s, 0);                        // 0 = disable , 1 = enable
  s->set_wpc(s, 1);                        // 0 = disable , 1 = enable
  s->set_raw_gma(s, 1);                    // 0 = disable , 1 = enable
  s->set_lenc(s, 1);                       // 0 = disable , 1 = enable
  s->set_hmirror(s, 0);                    // 0 = disable , 1 = enable
  s->set_vflip(s, 0);                      // 0 = disable , 1 = enable
  s->set_dcw(s, 1);                        // 0 = disable , 1 = enable
  s->set_colorbar(s, 0);                   // 0 = disable , 1 = enable

  Serial.println("Camera initialized successfully");
  return true;
}

// ==================== WIFI CONNECTION ====================

void connectWiFi()
{
  Serial.print("Connecting to WiFi: ");
  Serial.println(ssid);

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30)
  {
    delay(500);
    Serial.print(".");
    attempts++;
  }

  if (WiFi.status() == WL_CONNECTED)
  {
    Serial.println("\nWiFi connected!");
    Serial.print("IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("Stream URL: http://");
    Serial.print(WiFi.localIP());
    Serial.println("/stream");

    // Blink LED to indicate connection
    for (int i = 0; i < 3; i++)
    {
      digitalWrite(LED_PIN, HIGH);
      delay(200);
      digitalWrite(LED_PIN, LOW);
      delay(200);
    }
  }
  else
  {
    Serial.println("\nWiFi connection failed!");
  }
}

// ==================== MQTT CONNECTION ====================

void connectMQTT()
{
  if (!mqtt.connected())
  {
    Serial.print("Connecting to MQTT...");
    String clientId = "ESP32CAM_" + String(camera_id);

    if (mqtt.connect(clientId.c_str()))
    {
      Serial.println("connected");

      // Publish online status
      String topic = "camera/" + String(camera_id) + "/status";
      mqtt.publish(topic.c_str(), "online");

      // Subscribe to control topic
      String controlTopic = "camera/" + String(camera_id) + "/control";
      mqtt.subscribe(controlTopic.c_str());
    }
    else
    {
      Serial.print("failed, rc=");
      Serial.println(mqtt.state());
    }
  }
}

// ==================== HTTP HANDLERS ====================

void handleRoot()
{
  String html = "<!DOCTYPE html><html><head><title>" + String(camera_id) + "</title></head>";
  html += "<body><h1>" + String(camera_id) + " - " + String(camera_location) + "</h1>";
  html += "<p>Status: <span style='color:green'>Online</span></p>";
  html += "<p>IP: " + WiFi.localIP().toString() + "</p>";
  html += "<p><a href='/stream'>View Stream</a></p>";
  html += "<p><a href='/capture'>Capture Image</a></p>";
  html += "<p><a href='/status'>System Status</a></p>";
  html += "</body></html>";

  server.send(200, "text/html", html);
}

void handleStream()
{
  WiFiClient client = server.client();

  String response = "HTTP/1.1 200 OK\r\n";
  response += "Content-Type: multipart/x-mixed-replace; boundary=frame\r\n\r\n";
  server.sendContent(response);

  streamActive = true;

  while (client.connected())
  {
    unsigned long currentTime = millis();

    if (currentTime - lastFrameTime >= frameInterval)
    {
      camera_fb_t *fb = esp_camera_fb_get();

      if (!fb)
      {
        Serial.println("Camera capture failed");
        break;
      }

      String header = "--frame\r\n";
      header += "Content-Type: image/jpeg\r\n";
      header += "Content-Length: " + String(fb->len) + "\r\n\r\n";

      server.sendContent(header);
      client.write(fb->buf, fb->len);
      server.sendContent("\r\n");

      esp_camera_fb_return(fb);

      lastFrameTime = currentTime;

      // Blink LED during streaming
      digitalWrite(LED_PIN, !digitalRead(LED_PIN));
    }

    delay(1);
  }

  streamActive = false;
  digitalWrite(LED_PIN, LOW);
}

void handleCapture()
{
  camera_fb_t *fb = esp_camera_fb_get();

  if (!fb)
  {
    server.send(500, "text/plain", "Camera capture failed");
    return;
  }

  server.sendHeader("Content-Disposition", "inline; filename=capture.jpg");
  server.send_P(200, "image/jpeg", (const char *)fb->buf, fb->len);

  esp_camera_fb_return(fb);
}

void handleStatus()
{
  String json = "{";
  json += "\"camera_id\":\"" + String(camera_id) + "\",";
  json += "\"location\":\"" + String(camera_location) + "\",";
  json += "\"ip\":\"" + WiFi.localIP().toString() + "\",";
  json += "\"rssi\":" + String(WiFi.RSSI()) + ",";
  json += "\"streaming\":" + String(streamActive ? "true" : "false") + ",";
  json += "\"uptime\":" + String(millis() / 1000) + ",";
  json += "\"free_heap\":" + String(ESP.getFreeHeap());
  json += "}";

  server.send(200, "application/json", json);
}

// ==================== SETUP ====================

void setup()
{
  Serial.begin(115200);
  Serial.println("\n\nESP32-CAM Starting...");
  Serial.println("Camera ID: " + String(camera_id));
  Serial.println("Location: " + String(camera_location));

  // Initialize LED
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(LED_PIN, LOW);

  // Initialize camera
  if (!initCamera())
  {
    Serial.println("Camera initialization failed!");
    ESP.restart();
  }

  // Connect to WiFi
  connectWiFi();

  // Setup MQTT
  mqtt.setServer(mqtt_server, mqtt_port);

  // Setup HTTP server
  server.on("/", handleRoot);
  server.on("/stream", handleStream);
  server.on("/capture", handleCapture);
  server.on("/status", handleStatus);

  server.begin();
  Serial.println("HTTP server started");

  Serial.println("\nReady to stream!");
  Serial.println("================================");
}

// ==================== MAIN LOOP ====================

void loop()
{
  // Handle HTTP requests
  server.handleClient();

  // Maintain MQTT connection
  if (!mqtt.connected())
  {
    connectMQTT();
  }
  mqtt.loop();

  // Reconnect WiFi if disconnected
  if (WiFi.status() != WL_CONNECTED)
  {
    Serial.println("WiFi disconnected, reconnecting...");
    connectWiFi();
  }

  // Publish status every 30 seconds
  static unsigned long lastStatusTime = 0;
  if (millis() - lastStatusTime > 30000)
  {
    if (mqtt.connected())
    {
      String topic = "camera/" + String(camera_id) + "/heartbeat";
      String payload = "{\"uptime\":" + String(millis() / 1000) + ",\"rssi\":" + String(WiFi.RSSI()) + "}";
      mqtt.publish(topic.c_str(), payload.c_str());
    }
    lastStatusTime = millis();
  }

  delay(10);
}
