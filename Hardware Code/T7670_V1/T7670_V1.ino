#define LILYGO_T_A7670
#include <ArduinoJson.h>
#include <Adafruit_AHTX0.h>
#include <Wire.h>

#define TINY_GSM_USE_GPRS true

const char device_id[] = "45264015";
// set sensors details
const int numSensors = 1;
const int sensorPins[numSensors] = {32}; // Example sensor pins
const char* sensorNames[numSensors] = {"SoilWetness"};
const char* sensorUnits[numSensors] = {"%"};
const float pressureMaxRange = 0.2; // set pressure sensor range in MPa

float sensorValues[numSensors] = {0};

// Server details
const char endpoint[] = "https://portal.agromolinainnova.com/api/sensordatastore";

// lora code with i2c
#define BUFFER_SIZE 6000 // Tamaño máximo del buffer para almacenar el paquete JSON
int CHUNK_SIZE  = 32;
DynamicJsonDocument jsonDoc(BUFFER_SIZE); // Crear un documento JSON dinámico
char rxBuffer[BUFFER_SIZE]; // Buffer para almacenar los datos recibidos por I2C
int rxIndex = 0; // Índice para realizar un seguimiento del tamaño actual del buffer
String i2cJsonString = "";
String serverJsonString = "";
// end lora code with i2c

#include <ArduinoHttpClient.h>
#if TINY_GSM_USE_GPRS

  #define TINY_GSM_RX_BUFFER          1024
  #include "utilities.h"
  #include <TinyGsmClient.h>

  #ifdef DUMP_AT_COMMANDS  // if enabled it requires the streamDebugger lib
  #include <StreamDebugger.h>
  StreamDebugger debugger(SerialAT, Serial);
  TinyGsm modem(debugger);
  #else
  TinyGsm modem(SerialAT);

  #endif
  TinyGsmClient client(modem);

#endif

// Sensor data
float soilSensorValue;
float pressureSensorValue;
float humiditySensorValue;
float temperatureSensorValue;

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
      if (c == '\0') {
        rxIndex = 0;
        Serial.print("Received JSON: ");
        DeserializationError error = deserializeJson(jsonDoc, rxBuffer);
        Serial.println(rxBuffer);
        memset(rxBuffer, 0, BUFFER_SIZE);
        // Verificar si se pudo deserializar correctamente
        if (error) {
          Serial.println("Error al parsear el JSON recibido");
          Serial.println(error.f_str());
        } else {
          processDataAndSend();
        }
        break;
      }
      rxBuffer[rxIndex++] = c;
    }
  }
}

void processDataAndSend() {
  // Crear un nuevo documento JSON para enviar
  DynamicJsonDocument jsonDocument(1024);
  updateJsonDocument(jsonDocument);

  // Enviar los datos al servidor
  sendJsonModem(endpoint, jsonDocument);
}

void requestEvent() {
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
        if (sensorDataNode.isNull()) {
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
//         serializeJsonPretty(jsonDoc, Serial);
      }
    }
  }
}
// end lora code with i2c

void sendJsonModem(const char* server_url, DynamicJsonDocument& jsonDocument) {
  String jsonString;
  serializeJson(jsonDocument, jsonString);

  Serial.print("Sending JSON data: ");
  Serial.print(server_url);
  Serial.println(jsonString);

  // Initialize HTTPS
  modem.https_begin();

  // Set GET URT
  if (!modem.https_set_url(server_url)) {
    Serial.println("Failed to set the URL. Please check the validity of the URL!");
    return;
  }

  modem.https_add_header("Accept-Language", "zh-CN,zh;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6");
  modem.https_add_header("Accept-Encoding", "gzip, deflate, br");
  modem.https_set_accept_type("application/json");
  modem.https_set_user_agent("TinyGSM/LilyGo-A76XX");
  modem.https_add_header("Content-Type", "application/json");

  String post_body = jsonString;

  int httpCode = modem.https_post(post_body);
  if (httpCode != 200) {
    Serial.print("HTTP post failed ! error code = ");
    Serial.println(httpCode);
    return;
  } else {
    // Get HTTPS header information
    String header = modem.https_header();
//     Serial.print("HTTP Header : ");
//     Serial.println(header);

    // Get HTTPS response
    String body = modem.https_body();
    if (body != "") {
      serverJsonString = body;
    }
    Serial.print("HTTP body : ");
    Serial.println(body);
    Serial.println("Server disconnected");
  }
}

void setup() {
  Serial.begin(115200);
  // start lora code with i2c
  Wire.begin(8); // Inicializar el dispositivo I2C con dirección 8
  Wire.onReceive(receiveEvent); // Configurar el evento de recepción de datos por I2C
  Wire.onRequest(requestEvent); // register event
  // end lora code with i2c

#if TINY_GSM_USE_GPRS
  // start sim part
  SerialAT.begin(115200, SERIAL_8N1, MODEM_RX_PIN, MODEM_TX_PIN);
#ifdef BOARD_POWERON_PIN
  pinMode(BOARD_POWERON_PIN, OUTPUT);
  digitalWrite(BOARD_POWERON_PIN, HIGH);
#endif

  // Set modem reset pin ,reset modem
  pinMode(MODEM_RESET_PIN, OUTPUT);
  digitalWrite(MODEM_RESET_PIN, !MODEM_RESET_LEVEL);
  delay(100);
  digitalWrite(MODEM_RESET_PIN, MODEM_RESET_LEVEL);
  delay(2600);
  digitalWrite(MODEM_RESET_PIN, !MODEM_RESET_LEVEL);

  pinMode(BOARD_PWRKEY_PIN, OUTPUT);
  digitalWrite(BOARD_PWRKEY_PIN, LOW);
  delay(100);
  digitalWrite(BOARD_PWRKEY_PIN, HIGH);
  delay(100);
  digitalWrite(BOARD_PWRKEY_PIN, LOW);

  // Check if the modem is online
  Serial.println("Start modem...");

  int retry = 0;
  while (!modem.testAT(1000)) {
    Serial.println(".");
    if (retry++ > 10) {
      digitalWrite(BOARD_PWRKEY_PIN, LOW);
      delay(100);
      digitalWrite(BOARD_PWRKEY_PIN, HIGH);
      delay(1000);
      digitalWrite(BOARD_PWRKEY_PIN, LOW);
      retry = 0;
    }
  }
  Serial.println();

  // Check if SIM card is online
  SimStatus sim = SIM_ERROR;
  while (sim != SIM_READY) {
    sim = modem.getSimStatus();
    switch (sim) {
      case SIM_READY:
        Serial.println("SIM card online");
        break;
      case SIM_LOCKED:
        Serial.println("The SIM card is locked. Please unlock the SIM card first.");
        // const char *SIMCARD_PIN_CODE = "123456";
        // modem.simUnlock(SIMCARD_PIN_CODE);
        break;
      default:
        break;
    }
    delay(1000);
  }

#ifndef TINY_GSM_MODEM_SIM7672
  if (!modem.setNetworkMode(MODEM_NETWORK_AUTO)) {
    Serial.println("Set network mode failed!");
  }
  String mode = modem.getNetworkModes();
  Serial.print("Current network mode : ");
  Serial.println(mode);
#endif

  // Check network registration status and network signal status
  int16_t sq;
  Serial.print("Wait for the modem to register with the network.");
  RegStatus status = REG_NO_RESULT;
  while (status == REG_NO_RESULT || status == REG_SEARCHING || status == REG_UNREGISTERED) {
    status = modem.getRegistrationStatus();
    switch (status) {
      case REG_UNREGISTERED:
      case REG_SEARCHING:
        sq = modem.getSignalQuality();
        Serial.printf("[%lu] Signal Quality:%d", millis() / 1000, sq);
        delay(1000);
        break;
      case REG_DENIED:
        Serial.println("Network registration was rejected, please check if the APN is correct");
        return;
      case REG_OK_HOME:
        Serial.println("Online registration successful");
        break;
      case REG_OK_ROAMING:
        Serial.println("Network registration successful, currently in roaming mode");
        break;
      default:
        Serial.printf("Registration Status:%d\n", status);
        delay(1000);
        break;
    }
  }
  Serial.println();

  Serial.printf("Registration Status:%d\n", status);
  delay(1000);

  String ueInfo;
  if (modem.getSystemInformation(ueInfo)) {
    Serial.print("Inquiring UE system information:");
    Serial.println(ueInfo);
  }

  if (!modem.enableNetwork()) {
    Serial.println("Enable network failed!");
  }

  delay(5000);

  String ipAddress = modem.getLocalIP();
  Serial.print("Network IP:");
  Serial.println(ipAddress);
  // end sim part
#endif
}

void loop() {
  // Nada que hacer en el loop principal por ahora
}
