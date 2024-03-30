const char device_id[] = "57515554";
// Your WiFi connection credentials
char wifiSSID[] = "BlackQR";
char wifiPass[] = "blackqr_7632";

// set sensors details
const int numSensors = 4;
const int sensorPins[numSensors] = {32, 36, -1, -1}; // Example sensor pins
const char* sensorNames[numSensors] = {"SoilWetness", "AirPressure", "Humidity", "AirTemperature"};
const char* sensorUnits[numSensors] = {"%", "kPa", "%", "°C"};
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

// Function to read sensor values
void readSensors() {
    for (int i = 0; i < numSensors; ++i) {
        if (strcmp(sensorNames[i], "Humidity") == 0) {
            // Code to read humidity sensor differently
            sensors_event_t humidity, temp;
            aht.getEvent(&humidity, &temp);
            sensorValues[i] = humidity.relative_humidity;
        } else if (strcmp(sensorNames[i], "AirTemperature") == 0) {
            // Code to read temperature sensor differently
            sensors_event_t humidity, temp;
            aht.getEvent(&humidity, &temp);
            sensorValues[i] = temp.temperature;
        } else {
            // Code to read other sensors (like soil, pressure) using analogRead
            sensorValues[i] = analogRead(sensorPins[i]);
            if (strcmp(sensorNames[i], "SoilWetness") == 0) {
                // Transformation for soil sensor value
                sensorValues[i] = (1 - (sensorValues[i] / 4095.0)) * 100;
            } else if (strcmp(sensorNames[i], "AirPressure") == 0) {
                // Transformation for pressure sensor value
                sensorValues[i] = (pressureMaxRange / 1023.0) * sensorValues[i];
            }
        }
    }
}

// Function to add sensor data to JSON document
void addSensorDataToJson(DynamicJsonDocument& jsonDocument) {
    jsonDocument["device_id"] = device_id;
    JsonObject sensorData = jsonDocument.createNestedObject("sensor_data");
    for (int i = 0; i < numSensors; ++i) {
        JsonObject sensor = sensorData.createNestedObject(sensorNames[i]);
        sensor["value"] = sensorValues[i];
        sensor["unit"] = sensorUnits[i];
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
    int httpResponseCode = http.post(endpoint, "application/json", jsonString);

    if (httpResponseCode == 0) {
        Serial.print("HTTP Response code: ");
        Serial.println(httpResponseCode);
        String payload = http.readString();
        int bodyIndex = payload.indexOf("\r\n\r\n");

        // Extract the body part if the blank line was found
        if (bodyIndex != -1) {
            String body = payload.substring(bodyIndex + 4); // Add 4 to skip "\r\n\r\n"
            Serial.println("Body:");
            Serial.println(body);
            DynamicJsonDocument responseData(512); // Adjust buffer size according to your JSON payload size
            DeserializationError error = deserializeJson(responseData, body);
            const char* wifi_id = responseData["data"]["wifi_id"];
            const char* wifi_password = responseData["data"]["wifi_password"];
            
            
        } else {
            Serial.println("Error: Blank line indicating end of headers not found.");
        }

    } else {
        Serial.print("Error code: ");
        Serial.println(httpResponseCode);
    }

    http.stop();
    // Delay or other code here

    // Delay before sending next data
    delay(60 * 5000);
}