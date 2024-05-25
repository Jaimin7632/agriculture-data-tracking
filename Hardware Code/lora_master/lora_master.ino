#include "LoRaWan_APP.h"
#include "Arduino.h"
#include <Wire.h>
#include <set>
#include <ArduinoJson.h>

// heltec_wireless_stick_lite_v3
// static const uint8_t SDA = 2;
// static const uint8_t SCL = 3;

#define RF_FREQUENCY                                868000000 // Hz
#define TX_OUTPUT_POWER                             14        // dBm
#define LORA_BANDWIDTH                              0         // [0: 125 kHz, 1: 250 kHz, 2: 500 kHz, 3: Reserved]
#define LORA_SPREADING_FACTOR                       7         // [SF7..SF12]
#define LORA_CODINGRATE                             1         // [1: 4/5, 2: 4/6, 3: 4/7, 4: 4/8]
#define LORA_PREAMBLE_LENGTH                        8         // Same for Tx and Rx
#define LORA_SYMBOL_TIMEOUT                         0         // Symbols
#define LORA_FIX_LENGTH_PAYLOAD_ON                  false
#define LORA_IQ_INVERSION_ON                        false

#define RX_TIMEOUT_VALUE                            1000
#define BUFFER_SIZE                                 30 // Define the payload size here

#define NUM_SLAVES 2  // Número total de esclavos admitidos
const char* MASTER_ID = "Master1";
DynamicJsonDocument jsonDoc(400); // Crear un documento JSON dinámico
char rxBuffer[400]; // Buffer para almacenar los datos recibidos por I2C
int rxIndex = 0;
// Definir los ID únicos de los esclavos admitidos aquí
const char* slaveIDs[NUM_SLAVES] = {"Slave1", "Slave2"};

char txpacket[BUFFER_SIZE];
char rxpacket[BUFFER_SIZE];

static RadioEvents_t RadioEvents;
int16_t txNumber;
int16_t rssi, rxSize;
bool lora_idle = true;

// Utilizamos un std::set para almacenar los ID de los esclavos admitidos
std::set<const char*> allowedSlaveIDs;

// Función para verificar si un ID está en la lista de IDs admitidos
bool isValidSlaveID(const char* id) {
    for (int i = 0; i < NUM_SLAVES; ++i) {
        if (strcmp(id, slaveIDs[i]) == 0) {
            return true;
        }
    }
    return false;
}

void setup() {
    Serial.begin(115200);
    Mcu.begin(HELTEC_BOARD, SLOW_CLK_TPYE);
    txNumber = 0;
    rssi = 0;

    // Inicializamos el conjunto de ID admitidos
    for (int i = 0; i < NUM_SLAVES; ++i) {
        allowedSlaveIDs.insert(slaveIDs[i]);
    }

    Wire.begin(); // Inicializar I2C
    Wire.onReceive(receiveEvent);

    RadioEvents.RxDone = OnRxDone;
    Radio.Init(&RadioEvents);
    Radio.SetChannel(RF_FREQUENCY);
    Radio.SetRxConfig(MODEM_LORA, LORA_BANDWIDTH, LORA_SPREADING_FACTOR,
                      LORA_CODINGRATE, 0, LORA_PREAMBLE_LENGTH,
                      LORA_SYMBOL_TIMEOUT, LORA_FIX_LENGTH_PAYLOAD_ON,
                      0, true, 0, 0, LORA_IQ_INVERSION_ON, true);
}

void loop() {
    if (lora_idle) {
        lora_idle = false;
        Serial.println("into RX mode");
        Radio.Rx(0);
    }
    Radio.IrqProcess();
}

void receiveEvent(int numBytes) {
  // Reiniciar el buffer y el índice
  memset(rxBuffer, 0, BUFFER_SIZE);
  rxIndex = 0;

  // Leer los datos recibidos por I2C y almacenarlos en el buffer
  while (Wire.available() > 0 && rxIndex < BUFFER_SIZE) {
    char c = Wire.read(); // Leer un byte del bus I2C
    rxBuffer[rxIndex++] = c; // Almacenar el byte en el buffer
  }
  DeserializationError error = deserializeJson(jsonDoc, rxBuffer);
  if (error) {
        Serial.println("Error al parsear el JSON recibido");
  } else {
    // Obtener el ID del JSON recibido
    //const char* id = jsonDoc["id"];
     serializeJsonPretty(jsonDoc, Serial);
   }

}

void OnRxDone(uint8_t *payload, uint16_t size, int16_t rssi, int8_t snr) {
    rssi = rssi;
    rxSize = size;
    memcpy(rxpacket, payload, size);
    rxpacket[size] = '\0';
    Radio.Sleep();

    // Obtener el ID del remitente del paquete recibido
    char senderID[10];
    sscanf(rxpacket, "{\"id\":\"%[^\"]\"", senderID);

    // Verificar si el ID del remitente está en la lista de ID admitidos
    bool validSender = isValidSlaveID(senderID);

    if (validSender) {
        // El paquete proviene de un esclavo admitido
        Serial.printf("\r\nreceived packet \"%s\" with rssi %d , length %d\r\n", rxpacket, rssi, rxSize);

        // Enviar los datos por I2C
        Wire.beginTransmission(8); // Dirección del dispositivo I2C
        Wire.write((const uint8_t *)rxpacket, strlen(rxpacket));
        Wire.endTransmission();

        // Imprimir los datos enviados por I2C
        Serial.print("Sent packet via I2C: ");
        Serial.println(rxpacket);

        sendTimeToSlave(5);
    } else {
        // El paquete proviene de un remitente no admitido
        Serial.println("Received packet from an unauthorized sender");
    }

    lora_idle = true;
}


void sendTimeToSlave(unsigned long time) {
    StaticJsonDocument<BUFFER_SIZE> doc;
    doc["id"] = MASTER_ID;
    doc["time"] = time;

    char jsonBuffer[BUFFER_SIZE];
    serializeJson(doc, jsonBuffer);
    Serial.print("Master: Sending new time to Slave: ");
    Serial.println(jsonBuffer);

    Radio.Sleep();
    Radio.Send((uint8_t *)jsonBuffer, strlen(jsonBuffer));
    lora_idle = false;

}



