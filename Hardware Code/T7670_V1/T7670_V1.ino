#include <ArduinoJson.h>
#include <Adafruit_AHTX0.h>

#define TINY_GSM_USE_GPRS true


const char device_id[] = "41241112";
// Sensor pins
const int soilSensorPin = 32;  // A0 is equivalent to 0
const int pressureSensorPin = 36;  // A1 is equivalent to 1
const float pressureMaxRange = 0.2; // set pressure sensor range in MPa

// Server details
const char endpoint[] = "http://16.171.60.141:8000/api/sensordatastore";


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

#endif

// Sensor data
float soilSensorValue;
float pressureSensorValue;
float humiditySensorValue;
float temperatureSensorValue;

Adafruit_AHTX0 aht;

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
    jsonDocument["soilSensorValue"] = ((1 - (soilSensorValue/4095)) * 100);
    jsonDocument["pressureSensorValue"] = (pressureMaxRange/1023) * pressureSensorValue;
    jsonDocument["humiditySensorValue"] = humiditySensorValue;
    jsonDocument["temperatureSensorValue"] = temperatureSensorValue;
    jsonDocument["location"] = "0,0";

    #if TINY_GSM_USE_GPRS
      sendJsonModem(endpoint, jsonDocument);
    #endif
    // 15 min Delay before sending next data
    delay(60 * 1000 * 15);
}
