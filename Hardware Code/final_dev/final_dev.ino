#include <WiFi.h>
#include <ArduinoHttpClient.h>
#include <ArduinoJson.h>
#include <Adafruit_AHTX0.h>

// Your WiFi connection credentials
const char wifiSSID[] = "BlackQR";
const char wifiPass[] = "blackqr_7632";

// Server details
const char device_id[] = "99119911";
const char server[] = "16.171.60.141";
const int port = 8000;
const char endpoint[] = "/api/sensordatastore";

WiFiClient client;
HttpClient http(client, server, port);

// Sensor pins
const int soilSensorPin = 32;  // A0 is equivalent to 0
const int pressureSensorPin = 36;  // A1 is equivalent to 1

// Sensor data
float soilSensorValue;
float pressureSensorValue;
float humiditySensorValue;
float temperatureSensorValue;

Adafruit_AHTX0 aht;

void setup() {
  Serial.begin(115200);
  Serial.println("Connecting to WiFi...");

  // Connect to WiFi
  WiFi.begin(wifiSSID, wifiPass);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("\nConnected to WiFi");

  if (!aht.begin()) {
    Serial.println("Could not find AHT? Check wiring");
    while (1) delay(10);
  }
  Serial.println("AHT10 or AHT20 found");
}

void loop() {
  // Read sensor values
  soilSensorValue = analogRead(soilSensorPin);
  pressureSensorValue = analogRead(pressureSensorPin);
  
  // Read AHTX0 sensor values
  sensors_event_t humidity, temp;
  aht.getEvent(&humidity, &temp);
  humiditySensorValue = humidity.relative_humidity;
  temperatureSensorValue = temp.temperature;

  // Create JSON object
  DynamicJsonDocument jsonDocument(256);
  jsonDocument["device_id"] = device_id;
  jsonDocument["soilSensorValue"] = soilSensorValue;
  jsonDocument["pressureSensorValue"] = pressureSensorValue;
  jsonDocument["humiditySensorValue"] = humiditySensorValue;
  jsonDocument["temperatureSensorValue"] = temperatureSensorValue;

  // Serialize JSON to string
  String jsonString;
  serializeJson(jsonDocument, jsonString);

  Serial.print("Sending JSON data: ");
  Serial.println(jsonString);

  // Perform HTTP POST request
  int err = http.post(endpoint, "application/json", jsonString);
  if (err != 0) {
    Serial.println("Failed to connect");
    delay(10000);
    return;
  }

  int status = http.responseStatusCode();
  Serial.print("Response status code: ");
  Serial.println(status);

  if (status == 200) {
    Serial.println("Data sent successfully");
  } else {
    Serial.println("Failed to send data");
  }

  http.stop();
  Serial.println("Server disconnected");

  // Delay before sending next data
  delay(60 * 5000);
}
