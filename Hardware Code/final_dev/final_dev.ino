const char device_id[] = "57515554";
// Your WiFi connection credentials
const char wifiSSID[] = "BlackQR";
const char wifiPass[] = "blackqr_7632";

// set sensors details
const int numSensors = 4;
const int sensorPins[numSensors] = {32, 36, -1, -1}; // Example sensor pins
const char* sensorNames[numSensors] = {"Soil", "Pressure", "Humidity", "Temperature"};
const float pressureMaxRange = 0.2; // set pressure sensor range in MPa


float sensorValues[numSensors] = {0};
// Server details
const char server[] = "16.171.60.141";
const int port = 8000;
const char endpoint[] = "/api/sensordatastore";

#include <WiFi.h>
#include <ArduinoHttpClient.h>
#include <ArduinoJson.h>
#include <Adafruit_AHTX0.h>

WiFiClient client;
HttpClient http(client, server, port);

// Sensor data
float soilSensorValue;
float pressureSensorValue;
float humiditySensorValue;
float temperatureSensorValue;
String locationString;

Adafruit_AHTX0 aht;

void readSensors() {
    for (int i = 0; i < numSensors; ++i) {
        if (strcmp(sensorNames[i], "Humidity") == 0) {
            // Code to read humidity sensor differently
            sensors_event_t humidity, temp;
            aht.getEvent(&humidity, &temp);
            sensorValues[i] = humidity.relative_humidity;
        } else if (strcmp(sensorNames[i], "Temperature") == 0) {
            // Code to read temperature sensor differently
            sensors_event_t humidity, temp;
            aht.getEvent(&humidity, &temp);
            sensorValues[i] = temp.temperature;
        } else {
            // Code to read other sensors (like soil, pressure) using analogRead
            sensorValues[i] = analogRead(sensorPins[i]);
        }
    }
}

// Function to add sensor data to JSON document
void addSensorDataToJson(DynamicJsonDocument& jsonDocument) {
    jsonDocument["device_id"] = device_id;
    for (int i = 0; i < numSensors; ++i) {
        jsonDocument[sensorNames[i]] = sensorValues[i];
    }
}

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
    readSensors();

    // Create JSON object
    DynamicJsonDocument jsonDocument(256);
    addSensorDataToJson(jsonDocument);

    // Serialize JSON document
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
