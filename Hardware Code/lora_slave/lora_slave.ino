#include "LoRaWan_APP.h"
#include "Arduino.h"
#include <Adafruit_AM2315.h> // Include the Adafruit AM2315 library
#include <ArduinoJson.h>

#define RF_FREQUENCY        868000000 // Hz
#define TX_OUTPUT_POWER     14        // dBm
#define LORA_BANDWIDTH      0         // [0: 125 kHz, 1: 250 kHz, 2: 500 kHz, 3: Reserved]
#define LORA_SPREADING_FACTOR 7       // [SF7..SF12]
#define LORA_CODINGRATE     1         // [1: 4/5, 2: 4/6, 3: 4/7, 4: 4/8]
#define LORA_PREAMBLE_LENGTH 8        // Same for Tx and Rx
#define LORA_SYMBOL_TIMEOUT 0         // Symbols
#define LORA_FIX_LENGTH_PAYLOAD_ON false
#define LORA_IQ_INVERSION_ON false
#define RX_TIMEOUT_VALUE    1000
#define BUFFER_SIZE         100       // Define the payload size here
#define SLAVE_ID            "Slave1" // Unique ID of the slave

char txpacket[BUFFER_SIZE];
char rxpacket[BUFFER_SIZE];

static RadioEvents_t RadioEvents;

void setup() {
    Serial.begin(115200);
    Mcu.begin(HELTEC_BOARD, SLOW_CLK_TPYE);

    RadioEvents.TxDone = OnTxDone;
    RadioEvents.TxTimeout = OnTxTimeout;

    Radio.Init(&RadioEvents);
    Radio.SetChannel(RF_FREQUENCY);
    Radio.SetTxConfig(MODEM_LORA, TX_OUTPUT_POWER, 0, LORA_BANDWIDTH,
                      LORA_SPREADING_FACTOR, LORA_CODINGRATE,
                      LORA_PREAMBLE_LENGTH, LORA_FIX_LENGTH_PAYLOAD_ON,
                      true, 0, 0, LORA_IQ_INVERSION_ON, 3000);
}

void loop() {
    int pressureValue = analogRead(A0);

    // Construct the packet with the slave ID, temperature, and humidity
    DynamicJsonDocument jsonDoc(256);
    jsonDoc["id"] = SLAVE_ID;
    JsonObject sensor1 = jsonDoc.createNestedObject("pressureSensor");
    sensor1["value"] = pressureValue;
    sensor1["unit"] = "C";

    char txpacket[256];
    serializeJson(jsonDoc, txpacket);

    // Send the packet to the master
    Radio.Send((uint8_t *)txpacket, strlen(txpacket));

    // Print the sent packet for debugging
    Serial.print("Sent packet: ");
    Serial.println(txpacket);

    // Wait before sending the next packet
    delay(5000); // Adjust the value according to the desired sending frequency
}

void OnTxDone(void) {
    Serial.println("TX done...");
}

void OnTxTimeout(void) {
    Serial.println("TX Timeout...");
}
