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
const char server[] = "portal.agromolinainnova.com";
const int port = 8000;
const char endpoint[] = "/api/sensordatastore";

#include <WiFi.h>
#include <ArduinoHttpClient.h>
#include <ArduinoJson.h>
#include <Adafruit_AHTX0.h>

// lora code with i2c
#define BUFFER_SIZE 6000 // Tamaño máximo del buffer para almacenar el paquete JSON
int CHUNK_SIZE  = 32;
DynamicJsonDocument jsonDoc(BUFFER_SIZE); // Crear un documento JSON dinámico
char rxBuffer[BUFFER_SIZE]; // Buffer para almacenar los datos recibidos por I2C
int rxIndex = 0; // Índice para realizar un seguimiento del tamaño actual del buffer
String i2cJsonString = "";
String serverJsonString = "";
// end lora code with i2c

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

// lora code with i2c
void receiveEvent(int numBytes) {
  // Reiniciar el buffer y el índice

  if (numBytes > 0) {
    while (Wire.available()) {
      char c = Wire.read();
      if (c=='\0')
      {
          rxIndex = 0;
          Serial.print("Received JSON: ");
          DeserializationError error = deserializeJson(jsonDoc, rxBuffer);
          Serial.println(rxBuffer);
          memset(rxBuffer, 0, BUFFER_SIZE);
          // Verificar si se pudo deserializar correctamente
          if (error) {
//             Serial.println("Error al parsear el JSON recibido");
//             Serial.println(error.f_str());
          }
          break;
      }
      rxBuffer[rxIndex++] = c;
      //i2cJsonString += c;  // receive the actual JSON string chunk
      }

  }
}
void requestEvent() {
//   String jsonString = "{\"time\":15000}";
  int jsonLength = serverJsonString.length();
  int numChunks = jsonLength / CHUNK_SIZE;
  int remainder = jsonLength % CHUNK_SIZE;
  // Send JSON string in chunks
  for (int i = 0; i < numChunks; i++) {
    for (int j = 0; j < CHUNK_SIZE; j++) {
      Wire.write((uint8_t)serverJsonString[i * CHUNK_SIZE + j]);
    }
  }
  if (remainder > 0) {
    for (int i = 0; i < remainder; i++) {
      Wire.write((uint8_t)serverJsonString[numChunks * CHUNK_SIZE + i]);
    }
  }
  Wire.write('\0');
}


void updateJsonDocument(DynamicJsonDocument& jsonDocument) {
     // Deserializar el JSON solo si se recibieron datos
    JsonObject receivedData;
    if (true) {
      // Deserializar el JSON almacenado en el buffer
      Serial.println("checkpoint before string to json of i2c");
//       serializeJsonPretty(jsonDoc, Serial);
      // Verificar si se pudo deserializar correctamente
      if (1) {
        // Obtener el ID del JSON recibido
            // Iterate over the top-level keys (IDs)
            for (JsonPair idEntry : jsonDoc.as<JsonObject>()) {
              JsonObject idObject = idEntry.value().as<JsonObject>();

              const char* id = idObject["id"];

              // Create a new JSON object for the sensor data under the ID key
              jsonDocument["device_id"] = device_id;
              JsonObject sensorDataNode = jsonDocument["sensor_data"];
              if(sensorDataNode.isNull()){
                sensorDataNode = jsonDocument.createNestedObject("sensor_data");
              }

              // Iterate over each sensor in the sensor data
              for (JsonPair sensorEntry : idObject) {
                // Skip the entry if its key is "id"
                if (strcmp(sensorEntry.key().c_str(), "id") == 0) {
                  continue;
                }

                // Extract sensor name, value, and unit from sensor data
                const char* sensorName = sensorEntry.key().c_str();
                String finalSensorName = String(id) + sensorName;
                JsonObject sensor = sensorEntry.value().as<JsonObject>();
                float value = sensor["value"];
                const char* unit = sensor["unit"];

                // Create a new JSON object for the sensor
                JsonObject sensorNode = sensorDataNode.createNestedObject(finalSensorName);
                sensorNode["value"] = value;
                sensorNode["unit"] = unit;
              }

              // Update receivedData with the modified JSON document (if you have such a structure)
              receivedData[id] = idObject;

              // Print the JSON received
//               serializeJsonPretty(jsonDoc, Serial);
            }
      }
    }

}
// end lora code with i2c

void setup() {
    Serial.begin(115200);
    // start lora code with i2c
    Wire.begin(8); // Inicializar el dispositivo I2C con dirección 8
    Wire.onReceive(receiveEvent); // Configurar el evento de recepción de datos por I2C
    Wire.onRequest(requestEvent); // register event
    // end lora code with i2c
    Serial.println("Connecting to WiFi...");

    // Connect to WiFi
    WiFi.begin(wifiSSID, wifiPass);
    while (WiFi.status() != WL_CONNECTED) {
        delay(1000);
        Serial.print(".");
    }
    Serial.println("\nConnected to WiFi");


}

void loop() {

    // Create JSON object
    DynamicJsonDocument jsonDocument(256);
    updateJsonDocument(jsonDocument);

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
            serverJsonString = body;
//             DynamicJsonDocument responseData(512); // Adjust buffer size according to your JSON payload size
//             DeserializationError error = deserializeJson(responseData, body);
//             const char* wifi_id = responseData["data"]["wifi_id"];
//             const char* wifi_password = responseData["data"]["wifi_password"];


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
    delay(20 * 1000);
}
