#include "LoRaWan_APP.h"
#include "Arduino.h"
#include <ArduinoJson.h>

#define RF_FREQUENCY                868000000 // Hz
#define TX_OUTPUT_POWER             20 // dBm
#define LORA_BANDWIDTH              0 // [0: 125 kHz, 1: 250 kHz, 2: 500 kHz, 3: Reserved]
#define LORA_SPREADING_FACTOR       7 // [SF7..SF12]
#define LORA_CODINGRATE             1 // [1: 4/5, 2: 4/6, 3: 4/7, 4: 4/8]
#define LORA_PREAMBLE_LENGTH        8 // Same for Tx and Rx
#define LORA_SYMBOL_TIMEOUT         0 // Symbols
#define LORA_FIX_LENGTH_PAYLOAD_ON  false
#define LORA_IQ_INVERSION_ON        false
#define RX_TIMEOUT_VALUE            0 // No timeout, continuous listening
#define BUFFER_SIZE                 100 // Define the payload size here
#define DEFAULT_SLEEP_TIME          10 // Default sleep time in seconds
#define SLAVE_ID                    "Slave0" // Unique ID of the slave
#define RESPONSE_TIMEOUT            5000 // Timeout for response from master in milliseconds

const char* MASTER_ID = "Master1"; // Define the master ID

char txpacket[BUFFER_SIZE];
char rxpacket[BUFFER_SIZE];

static RadioEvents_t RadioEvents;
unsigned long sleepTime = DEFAULT_SLEEP_TIME * 1000000; // Convert to microseconds for deep sleep
unsigned long responseStartTime = 0;
bool waitingForResponse = false;
int backoffAttempts = 0;

bool isChannelFree() {
    // Verificamos si el estado del dispositivo LoRaWAN es compatible con la función
    if (deviceState == DEVICE_STATE_SEND || deviceState == DEVICE_STATE_CYCLE) {
        return false; // El canal no está libre si estamos enviando o en un ciclo
    }
    return true; // El canal está libre en cualquier otro caso
}

void performBackoff() {
    // Retraso aleatorio antes de volver a intentar la transmisión
    unsigned long backoffTime = random(0, 500); // Ajustar los límites a 500 ms
    delay(backoffTime);
    backoffAttempts++;
}

void setup() {
    Serial.begin(115200);
    Mcu.begin(HELTEC_BOARD, SLOW_CLK_TPYE);

    RadioEvents.TxDone = OnTxDone;
    RadioEvents.TxTimeout = OnTxTimeout;
    RadioEvents.RxDone = OnRxDone;

    Radio.Init(&RadioEvents);
    Radio.SetChannel(RF_FREQUENCY);
    Radio.SetTxConfig(MODEM_LORA, TX_OUTPUT_POWER, 0, LORA_BANDWIDTH,
                      LORA_SPREADING_FACTOR, LORA_CODINGRATE,
                      LORA_PREAMBLE_LENGTH, LORA_FIX_LENGTH_PAYLOAD_ON,
                      true, 0, 0, LORA_IQ_INVERSION_ON, 3000);
    Radio.SetRxConfig(MODEM_LORA, LORA_BANDWIDTH, LORA_SPREADING_FACTOR,
                      LORA_CODINGRATE, 0, LORA_PREAMBLE_LENGTH,
                      LORA_SYMBOL_TIMEOUT, LORA_FIX_LENGTH_PAYLOAD_ON,
                      0, true, 0, 0, LORA_IQ_INVERSION_ON, true);

    // Enviamos datos inmediatamente después del inicio
    sendResponse();
}

void loop() {
    Radio.IrqProcess();

    if (waitingForResponse && millis() - responseStartTime >= RESPONSE_TIMEOUT) {
        Serial.println("Slave: Response timeout, performing backoff...");
        performBackoff();
        sendResponse();
    }
}

void sendResponse() {
    if (!isChannelFree()) {
        Serial.println("Slave: Channel not clear, performing backoff...");
        performBackoff();
        return; // Intentar más tarde
    }

    int pressureValue = analogRead(A0);

    DynamicJsonDocument jsonDoc(2048);
    jsonDoc["id"] = SLAVE_ID;
    JsonObject sensor1 = jsonDoc.createNestedObject("pressure");
    sensor1["value"] = pressureValue;
    sensor1["unit"] = "C";
    char txpacket[256];
    serializeJson(jsonDoc, txpacket);

    Serial.print("Slave: Sending response: ");
    Serial.println(txpacket);
    Radio.Send((uint8_t *)txpacket, strlen(txpacket));
    waitingForResponse = true;
    responseStartTime = millis();
}

void enterDeepSleep() {
    Serial.printf("Slave: Entering deep sleep for %lu seconds...\n", sleepTime / 1000000);
    esp_deep_sleep(sleepTime);
}

void OnTxDone(void) {
    Serial.println("Slave: TX done...");
    Radio.Rx(RX_TIMEOUT_VALUE); // Entramos en modo de escucha después de la transmisión para recibir el nuevo parámetro "time"
    backoffAttempts = 0; // Reset backoff attempts
}

void OnTxTimeout(void) {
    Serial.println("Slave: TX Timeout...");
    performBackoff();
    sendResponse(); // Reattempt sending the response
}

void OnRxDone(uint8_t *payload, uint16_t size, int16_t rssi, int8_t snr) {
    Serial.println("Slave: RX done...");
    // Procesamos los datos recibidos del maestro
    StaticJsonDocument<BUFFER_SIZE> doc;
    DeserializationError error = deserializeJson(doc, payload, size);
    if (error) {
        Serial.println("Slave: Failed to parse JSON");
        return;
    }

    const char* senderID = doc["id"];
    if (strcmp(senderID, MASTER_ID) == 0) {
        Serial.println("Slave: Request received from Master");

        // Obtenemos el nuevo tiempo de reposo del maestro
        if (doc.containsKey("time")) {
            sleepTime = doc["time"].as<unsigned long>() * 1000000;
            Serial.printf("Slave: New sleep time received: %lu seconds\n", sleepTime / 1000000);
        }

        waitingForResponse = false; // Dejamos de esperar la respuesta ya que la recibimos
        enterDeepSleep();
    } else {
        Serial.println("Slave: Ignoring request from unrecognized sender");
    }
}
