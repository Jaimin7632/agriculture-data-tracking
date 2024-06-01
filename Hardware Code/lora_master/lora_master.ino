#include "LoRaWan_APP.h"
#include "Arduino.h"
#include <Wire.h>
#include <set>
#include <map>
#include <ArduinoJson.h>

#define RF_FREQUENCY               868000000 // Hz
#define TX_OUTPUT_POWER            20 // dBm
#define LORA_BANDWIDTH             0 // [0: 125 kHz, 1: 250 kHz, 2: 500 kHz, 3: Reserved]
#define LORA_SPREADING_FACTOR      7 // [SF7..SF12]
#define LORA_CODINGRATE            1 // [1: 4/5, 2: 4/6, 3: 4/7, 4: 4/8]
#define LORA_PREAMBLE_LENGTH       8 // Same for Tx and Rx
#define LORA_SYMBOL_TIMEOUT        0 // Symbols
#define LORA_FIX_LENGTH_PAYLOAD_ON false
#define LORA_IQ_INVERSION_ON       false

#define RX_TIMEOUT_VALUE           0 // Continuous listening
#define RESPONSE_TIMEOUT           5000 // Timeout for response from slave in milliseconds
#define BUFFER_SIZE                400 // Define the payload size here
const int CHUNK_SIZE = 32;
#define I2C_SLAVE_ADDR             8 // Dirección del dispositivo Master en el bus I2C
#define NUM_SLAVES                 20 // Número total de esclavos admitidos

const char* MASTER_ID = "Master1"; // Definir el identificador del maestro
const char* slaveIDs[NUM_SLAVES] = {
    "Slave0", "Slave1", "Slave2", "Slave3", "Slave4",
    "Slave5", "Slave6", "Slave7", "Slave8", "Slave9",
    "Slave10", "Slave11", "Slave12", "Slave13", "Slave14",
    "Slave15", "Slave16", "Slave17", "Slave18", "Slave19"
};

char txpacket[BUFFER_SIZE];
char rxpacket[BUFFER_SIZE];
char i2cData[BUFFER_SIZE];

static RadioEvents_t RadioEvents;
int16_t txNumber;
int16_t rssi, rxSize;
bool lora_idle = true;
bool waitingForResponse = false;
unsigned long responseStartTime = 0;
unsigned long receivedTime = 10; // Variable global para almacenar el tiempo recibido por I2C

std::set<String> allowedSlaveIDs;
StaticJsonDocument<BUFFER_SIZE> lastReceivedDoc;
StaticJsonDocument<BUFFER_SIZE> serverJson;
std::map<String, StaticJsonDocument<BUFFER_SIZE>> slaveData; // Mapa para almacenar datos de cada esclavo
bool dataChanged = false; // Flag to track if data has changed

bool isAnyDataToSend() {
    for (auto& pair : slaveData) {
        if (!pair.second.isNull()) {
            // The document has data, clear it
            return true;  // Return true as soon as we find and clear a document with data
        }
    }
    return false;  // No documents with data were found
}

bool isChannelFree() {
    if (deviceState == DEVICE_STATE_SEND || deviceState == DEVICE_STATE_CYCLE) {
        return false;
    }
    return true;
}

void performBackoff() {
    unsigned long backoffTime = random(0, 100); // Ajustar los límites según sea necesario
    delay(backoffTime);
}

void setup() {
    Serial.begin(115200);
    Mcu.begin(HELTEC_BOARD, SLOW_CLK_TPYE);
    txNumber = 0;
    rssi = 0;

    for (int i = 0; i < NUM_SLAVES; ++i) {
        allowedSlaveIDs.insert(String(slaveIDs[i]));
    }
    //edit
    Wire.begin(); // join I2C bus as master

//     Wire.onReceive(receiveEvent);
//     Wire.onRequest(requestEvent);

    RadioEvents.RxDone = OnRxDone;
    RadioEvents.TxDone = OnTxDone; // Handle Tx Done
    Radio.Init(&RadioEvents);
    Radio.SetChannel(RF_FREQUENCY);
    Radio.SetRxConfig(MODEM_LORA, LORA_BANDWIDTH, LORA_SPREADING_FACTOR,
                      LORA_CODINGRATE, 0, LORA_PREAMBLE_LENGTH,
                      LORA_SYMBOL_TIMEOUT, LORA_FIX_LENGTH_PAYLOAD_ON,
                      0, true, 0, 0, LORA_IQ_INVERSION_ON, true);
    Radio.SetTxConfig(MODEM_LORA, TX_OUTPUT_POWER, 0, LORA_BANDWIDTH,
                      LORA_SPREADING_FACTOR, LORA_CODINGRATE,
                      LORA_PREAMBLE_LENGTH, LORA_FIX_LENGTH_PAYLOAD_ON,
                      true, 0, 0, LORA_IQ_INVERSION_ON, 3000);

    // Start listening
    Serial.println("Master: Starting to listen");
    Radio.Rx(RX_TIMEOUT_VALUE);
}

void loop() {
    unsigned long currentTime = millis();

    if (waitingForResponse && (currentTime - responseStartTime >= RESPONSE_TIMEOUT)) {
        Serial.println("Master: Response timeout, no response from slave");
        waitingForResponse = false;
        lora_idle = true;
        Radio.Rx(RX_TIMEOUT_VALUE); // Volver a modo de escucha después del timeout
    }
    if(isAnyDataToSend()){
      sendToMain();
    }
    String jsonResponse = receiveJsonString();
    Serial.println("Received JSON: " + jsonResponse);

    Radio.IrqProcess(); // Procesa las interrupciones de la radio
    //todo
    delay(5000);
}

void sendRequest() {
    if (!isChannelFree()) {
        Serial.println("Master: Channel not clear, performing backoff...");
        performBackoff();
    }

    sprintf(txpacket, "{\"id\":\"%s\"}", MASTER_ID);
    Serial.print("Master: Sending request: ");
    Serial.println(txpacket);
    Radio.Send((uint8_t *)txpacket, strlen(txpacket));
    lora_idle = false;
    waitingForResponse = true;
    responseStartTime = millis();
}

void sendToMain() {
    StaticJsonDocument<BUFFER_SIZE> doc;

    // Iterar a través de los datos de cada esclavo
    for (const auto& slave : slaveData) {
        const StaticJsonDocument<BUFFER_SIZE>& lastData = slave.second;
        doc[slave.first] = lastData;
    }

    char jsonString[BUFFER_SIZE];
    size_t jsonLength = serializeJson(doc, jsonString, sizeof(jsonString));

    // Send JSON string in chunks
    int numChunks = jsonLength / CHUNK_SIZE;
    int remainder = jsonLength % CHUNK_SIZE;

      // transmit to device #8
//     Wire.write((uint8_t)numChunks);      // send the number of chunks
//     Wire.write((uint8_t)remainder);      // send the remainder
    Wire.beginTransmission(8);
    Wire.write('\0');
    Wire.endTransmission();
    Wire.beginTransmission(8);
    Wire.write('\0');
    Wire.endTransmission();
    for (int i = 0; i < numChunks; i++) {
      Wire.beginTransmission(8);
      for (int j = 0; j < CHUNK_SIZE; j++) {
        Wire.write((uint8_t)jsonString[i * CHUNK_SIZE + j]);
      }
      Wire.endTransmission();
      delay(100);
    }
    if (remainder > 0) {
      Wire.beginTransmission(8);
      for (int i = 0; i < remainder; i++) {
        Wire.write((uint8_t)jsonString[numChunks * CHUNK_SIZE + i]);
      }
      Wire.endTransmission();
      delay(100);
    }
    Wire.beginTransmission(8);
    Wire.write('\0');
    Wire.endTransmission();
       // stop transmitting
    Serial.println("Master: Sent data to Main via I2C");

}

void OnRxDone(uint8_t *payload, uint16_t size, int16_t rssi, int8_t snr) {
    Serial.println("Master: OnRxDone called");
    rssi = rssi;
    rxSize = size;

    if (size > BUFFER_SIZE - 1) {
        Serial.println("Master: Packet size too large!");
        lora_idle = true;
    Radio.Rx(RX_TIMEOUT_VALUE);
        return;
    }

    memcpy(rxpacket, payload, size);
    rxpacket[size] = '\0';
    Radio.Sleep();

    Serial.print("Master: Received packet: ");
    Serial.println(rxpacket);

    StaticJsonDocument<BUFFER_SIZE> doc;
    DeserializationError error = deserializeJson(doc, rxpacket);
    if (error) {
        Serial.println("Master: Failed to parse JSON");
        lora_idle = true;
        Radio.Rx(RX_TIMEOUT_VALUE);
        return;
    }

    const char* senderID = doc["id"];
    if (allowedSlaveIDs.find(String(senderID)) != allowedSlaveIDs.end()) {
        Serial.println("Master: Response from authorized slave");

        // Send time to the slave
        sendTimeToSlave(receivedTime);

        // Introduce a small delay after receiving and before sending
        delay(100);

        // Almacenar los datos del esclavo en el mapa
        // Corregir la forma en que se almacenan los datos
        StaticJsonDocument<BUFFER_SIZE> receivedDoc;
        receivedDoc.set(doc.as<JsonObjectConst>()); // Almacenar el objeto JSON recibido
        slaveData[String(senderID)] = receivedDoc; // Almacenar el documento JSON en el mapa
        dataChanged = true; // Establecer la bandera de cambio de datos
//         String jsonString;
//         serializeJson(jsonDocument, jsonString);
//         sendToMain(jsonString);

        // Copiar los datos al JSON principal para enviar por I2C
        for (const auto& slave : slaveData) {
            lastReceivedDoc[slave.first] = slave.second;
        }
    } else {
        Serial.println("Master: Response from unauthorized sender");
    }

    lora_idle = true;
    Radio.Rx(RX_TIMEOUT_VALUE); // Volver a modo de escucha después de recibir
}

void OnTxDone(void) {
    Serial.println("Master: TX done");
    Radio.Rx(RX_TIMEOUT_VALUE); // Volver a modo de escucha después de la transmisión
}

void receiveEvent(int howMany) {
    static bool receivingData = false;
    static String partialData;

    if (!receivingData) {
        receivingData = true;
        partialData = "";
    }

    while (Wire.available() > 0) { // Leer todos los bytes disponibles
        char receivedChar = Wire.read();
        partialData += receivedChar;
        howMany--;

        if (receivedChar == '\0' || howMany <= 0) {
            // Si recibimos el terminador nulo
            StaticJsonDocument<BUFFER_SIZE> doc;
            DeserializationError error = deserializeJson(doc, partialData);
            if (!error) {
                if (doc.containsKey("time")) {
                    unsigned long newTime = doc["time"].as<unsigned long>();
                    receivedTime = newTime; // Almacenar el tiempo recibido en la variable global
                    Serial.print("Master: Received new time from Main: ");
                    Serial.println(receivedTime);
                } else {
                    Serial.println("Master: JSON does not contain 'time' key");
                }
            } else {
                Serial.println("Master: Failed to parse JSON from I2C");
            }

            receivingData = false;
        }
    }
}

void sendTimeToSlave(unsigned long time) {
    char jsonBuffer[BUFFER_SIZE];
    serializeJson(serverJson, jsonBuffer);
    Serial.print("Master: Sending new time to Slave: ");
    Serial.println(jsonBuffer);

    Radio.Sleep();
    Radio.Send((uint8_t *)jsonBuffer, strlen(jsonBuffer));
    lora_idle = false;
    waitingForResponse = true;
    responseStartTime = millis();
}


String receiveJsonString() {
  const int slaveAddress = 8;
  const int requestBytes = 300;
  const int maxRetries = 5;
  const int delayTime = 500;
  const size_t jsonBufferSize = 1024; // Adjust based on your expected JSON size

  for (int attempts = 0; attempts < maxRetries; attempts++) {
    String jsonResponse = "";
    bool endReached = false;

    // Request 50 bytes from the I2C slave
    Wire.requestFrom(slaveAddress, requestBytes);

    while (Wire.available() && !endReached) {
      char c = Wire.read();
      if (c == '\0') {
        endReached = true; // Termination character received
      } else if (c != '') { // Exclude unwanted characters
        jsonResponse += c;
      }
    }

    // Check if the received string is a valid JSON
    
    DeserializationError error = deserializeJson(serverJson, jsonResponse);

    if (!error) {
      // Valid JSON received
      return jsonResponse;
    }

    // Invalid JSON, retry after delay
    delay(delayTime);
  }

  // If no valid JSON received after retries, return an empty string or handle error
  return "";
}

