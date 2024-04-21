#include <ArduinoJson.h>
#include <Adafruit_AHTX0.h>
#include <Wire.h>


#define TINY_GSM_USE_GPRS true


const char device_id[] = "41241112";
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

// lora code with i2c
#define BUFFER_SIZE 50 // Tamaño máximo del buffer para almacenar el paquete JSON

DynamicJsonDocument jsonDoc(BUFFER_SIZE); // Crear un documento JSON dinámico
char rxBuffer[BUFFER_SIZE]; // Buffer para almacenar los datos recibidos por I2C
int rxIndex = 0; // Índice para realizar un seguimiento del tamaño actual del buffer
JsonObject receivedData;
// end lora code with i2c

#include <ArduinoHttpClient.h>
#if TINY_GSM_USE_GPRS
  #define LILYGO_T_A7670
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
  HttpClient    http(client, server, port);

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
  memset(rxBuffer, 0, BUFFER_SIZE);
  rxIndex = 0;

  // Leer los datos recibidos por I2C y almacenarlos en el buffer
  while (Wire.available() > 0 && rxIndex < BUFFER_SIZE) {
    char c = Wire.read(); // Leer un byte del bus I2C
    rxBuffer[rxIndex++] = c; // Almacenar el byte en el buffer
  }

  // Deserializar el JSON solo si se recibieron datos
  if (rxIndex > 0) {
    // Deserializar el JSON almacenado en el buffer
    DeserializationError error = deserializeJson(jsonDoc, rxBuffer);

    // Verificar si se pudo deserializar correctamente
    if (error) {
      Serial.println("Error al parsear el JSON recibido");
    } else {
      // Obtener el ID del JSON recibido
      const char* id = jsonDoc["id"];

      // Verificar si ya hay datos almacenados para este ID
      if (receivedData.containsKey(id)) {
        // Remover los datos antiguos para este ID
        receivedData.remove(id);
      }

      // Almacenar los nuevos datos en el global variable usando el ID como clave
      receivedData[id] = jsonDoc;
      // Imprimir el JSON recibido
      serializeJsonPretty(jsonDoc, Serial);
    }
  }
}


void updateJsonDocument(DynamicJsonDocument& jsonDocument, const JsonObject& receivedData) {
    // Iterate over each entry in the received data
    for (JsonPair entry : receivedData) {
        const char* id = entry.key().c_str();
        JsonObject sensorData = entry.value().as<JsonObject>();

        // Create a new JSON object for the sensor data under the ID key
        JsonObject sensorDataNode = jsonDocument.createNestedObject("sensor_data");
        // Iterate over each sensor in the sensor data
        for (JsonPair sensorEntry : sensorData) {
            // Skip the entry if its key is "id"
            if (strcmp(sensorEntry.key().c_str(), "id") == 0) {
                continue;
            }

            // Extract sensor name, value, and unit from sensor data
            const char* sensorName = sensorEntry.key().c_str();
            JsonObject sensor = sensorEntry.value().as<JsonObject>();
            float value = sensor["value"];
            const char* unit = sensor["unit"];

            // Create a new JSON object for the sensor
            JsonObject sensorNode = sensorDataNode.createNestedObject(sensorName);
            sensorNode["name"] = sensorName;

            // Create a JSON object for the sensor value and unit
            JsonObject dataNode = sensorNode.createNestedObject("data");
            dataNode["value"] = value;
            dataNode["unit"] = unit;
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

    //
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
    }

    // Get HTTPS header information
    String header = modem.https_header();
    Serial.print("HTTP Header : ");
    Serial.println(header);

    // Get HTTPS response
    String body = modem.https_body();
    Serial.print("HTTP body : ");
    Serial.println(body);
    Serial.println("Server disconnected");

}

void setup() {
    Serial.begin(115200);
    Wire.begin(8); // Inicializar el dispositivo I2C con dirección 8
    Wire.onReceive(receiveEvent); // Configurar el evento de recepción de datos por I2C

    #if TINY_GSM_USE_GPRS
    // start sim part
      SerialAT.begin(115200, SERIAL_8N1, MODEM_RX_PIN, MODEM_TX_PIN);
      #ifdef BOARD_POWERON_PIN
          pinMode(BOARD_POWERON_PIN, OUTPUT);
          digitalWrite(BOARD_POWERON_PIN, HIGH);
      #endif

          // Set modem reset pin ,reset modem
          pinMode(MODEM_RESET_PIN, OUTPUT);
          digitalWrite(MODEM_RESET_PIN, !MODEM_RESET_LEVEL); delay(100);
          digitalWrite(MODEM_RESET_PIN, MODEM_RESET_LEVEL); delay(2600);
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
          int16_t sq ;
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
                  return ;
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
          Serial.print("Network IP:"); Serial.println(ipAddress);
    // end sim part
    #endif


    if (!aht.begin()) {
       Serial.println("Could not find AHT? Check wiring");
    }else{
      Serial.println("AHT10 or AHT20 found");
    }


}

void loop() {

    // Read sensor values
    readSensors();

    // Create JSON object
    DynamicJsonDocument jsonDocument(1024);
    addSensorDataToJson(jsonDocument);
    updateJsonDocument(jsonDocument, receivedData);

    // Serialize JSON document
    String jsonString;
    serializeJson(jsonDocument, jsonString);

    Serial.print("Sending JSON data: ");
    Serial.println(jsonString);

//     int err = http.post(endpoint, "application/json", jsonString);
//     if (err != 0) {
//         Serial.println(F("failed to connect"));
//         delay(10000);
//         return;
//     }
//
//     int status = http.responseStatusCode();
//     Serial.print(F("Response status code: "));
//     Serial.println(status);
//     if (!status) {
//         delay(10000);
//         return;
//     }
//
//     Serial.println(F("Response Headers:"));
//     while (http.headerAvailable()) {
//         String headerName  = http.readHeaderName();
//         String headerValue = http.readHeaderValue();
//         Serial.println("    " + headerName + " : " + headerValue);
//     }
//
//     int length = http.contentLength();
//     if (length >= 0) {
//         Serial.print(F("Content length is: "));
//         Serial.println(length);
//     }
//     if (http.isResponseChunked()) {
//         Serial.println(F("The response is chunked"));
//     }
//
//     String body = http.responseBody();
//     Serial.println(F("Response:"));
//     Serial.println(body);
//
//     Serial.print(F("Body length is: "));
//     Serial.println(body.length());
//
//     // Shutdown
//
//     http.stop();
    #if TINY_GSM_USE_GPRS
      sendJsonModem(endpoint, jsonDocument);
    #else
      sendJsonData(endpoint, jsonDocument);
    #endif
    // 15 min Delay before sending next data
    delay(60 * 1000 * 15);
}
