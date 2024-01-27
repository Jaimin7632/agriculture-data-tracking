const char device_id[] = "41241112";
// Your WiFi connection credentials
const char wifiSSID[] = "BlackQR";
const char wifiPass[] = "blackqr_7632";

// Sensor pins
const int soilSensorPin = 32;  // A0 is equivalent to 0
const int pressureSensorPin = 36;  // A1 is equivalent to 1

// Server details
const char server[] = "16.171.60.141";
const int port = 8000;
const char endpoint[] = "/api/sensordatastore";

#define SerialAT Serial1

#define TINY_GSM_MODEM_SIM7000
#define TINY_GSM_RX_BUFFER 1024 // Set RX buffer to 1Kb
#define SerialAT Serial1

#define GSM_PIN ""

// Your GPRS credentials, if any
const char apn[]  = "YOUR-APN";     //SET TO YOUR APN
const char gprsUser[] = "";
const char gprsPass[] = "";

#include <TinyGsmClient.h>
#include <SPI.h>
#include <SD.h>
#include <Ticker.h>

#ifdef DUMP_AT_COMMANDS
#include <StreamDebugger.h>
StreamDebugger debugger(SerialAT, SerialMon);
TinyGsm modem(debugger);
#else
TinyGsm modem(SerialAT);
#endif

#define uS_TO_S_FACTOR      1000000ULL  // Conversion factor for micro seconds to seconds
#define TIME_TO_SLEEP       60          // Time ESP32 will go to sleep (in seconds)

#define UART_BAUD           9600
#define PIN_DTR             25
#define PIN_TX              27
#define PIN_RX              26
#define PWR_PIN             4

#define SD_MISO             2
#define SD_MOSI             15
#define SD_SCLK             14
#define SD_CS               13
#define LED_PIN             12


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


void enableGPS(void)
{
    // Set Modem GPS Power Control Pin to HIGH ,turn on GPS power
    // Only in version 20200415 is there a function to control GPS power
    modem.sendAT("+CGPIO=0,48,1,1");
    if (modem.waitResponse(10000L) != 1) {
        DBG("Set GPS Power HIGH Failed");
    }
    modem.enableGPS();
}

void disableGPS(void)
{
    // Set Modem GPS Power Control Pin to LOW ,turn off GPS power
    // Only in version 20200415 is there a function to control GPS power
    modem.sendAT("+CGPIO=0,48,1,0");
    if (modem.waitResponse(10000L) != 1) {
        DBG("Set GPS Power LOW Failed");
    }
    modem.disableGPS();
}

void modemPowerOn()
{
    pinMode(PWR_PIN, OUTPUT);
    digitalWrite(PWR_PIN, HIGH);
    delay(1000);    //Datasheet Ton mintues = 1S
    digitalWrite(PWR_PIN, LOW);
}

void modemPowerOff()
{
    pinMode(PWR_PIN, OUTPUT);
    digitalWrite(PWR_PIN, HIGH);
    delay(1500);    //Datasheet Ton mintues = 1.2S
    digitalWrite(PWR_PIN, LOW);
}


void modemRestart()
{
    modemPowerOff();
    delay(1000);
    modemPowerOn();
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

    // Set up for GPS
    pinMode(LED_PIN, OUTPUT);
    digitalWrite(LED_PIN, HIGH);

    modemPowerOn();

    SerialAT.begin(UART_BAUD, SERIAL_8N1, PIN_RX, PIN_TX);

    Serial.println("/**********************************************************/");
    Serial.println("To initialize the network test, please make sure your GPS");
    Serial.println("antenna has been connected to the GPS port on the board.");
    Serial.println("/**********************************************************/\n\n");

    delay(10000);
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

    // get gps
    if (!modem.testAT()) {
            Serial.println("Failed to restart modem, attempting to continue without restarting");
            modemRestart();
            return;
        }

        Serial.println("Start positioning . Make sure to locate outdoors.");
        Serial.println("The blue indicator light flashes to indicate positioning.");

        enableGPS();

        float lat,  lon;
        while (1) {
            if (modem.getGPS(&lat, &lon)) {
                // Serial.println("The location has been locked, the latitude and longitude are:");
                // Serial.print("latitude:"); Serial.println(lat);
                // Serial.print("longitude:"); Serial.println(lon);
                locationString = String(lat, 6) + "," + String(lon, 6);
                break;
            }
            digitalWrite(LED_PIN, !digitalRead(LED_PIN));
            delay(2000);
        }
        disableGPS();
    // end gps

    // Create JSON object
    DynamicJsonDocument jsonDocument(256);
    jsonDocument["device_id"] = device_id;
    jsonDocument["soilSensorValue"] = soilSensorValue;
    jsonDocument["pressureSensorValue"] = pressureSensorValue;
    jsonDocument["humiditySensorValue"] = humiditySensorValue;
    jsonDocument["temperatureSensorValue"] = temperatureSensorValue;
    jsonDocument["location"] = locationString;

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
