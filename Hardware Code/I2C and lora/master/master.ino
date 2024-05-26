#include <Wire.h>
#include <ArduinoJson.h>

const int CHUNK_SIZE = 32;

String jsonString = "{\"device\":\"sensor\",\"value\":30}";

void setup() {
  Wire.begin(8);                // join I2C bus with address #8
  Wire.onReceive(receiveEvent); // register event
  Wire.onRequest(requestEvent); // register event
  Serial.begin(9600);           // start serial for output
}

void loop() {
  delay(100); // do nothing, just wait for events
}

// function that executes whenever data is received from master
void receiveEvent(int howMany) {
  if (howMany > 0) {
    int numChunks = Wire.read();  // read the number of chunks
    int remainder = Wire.read();  // read the remainder
    jsonString = "";
    for (int i = 0; i < numChunks; i++) {
      Wire.requestFrom(8, CHUNK_SIZE);
      while (Wire.available()) {
        jsonString += (char)Wire.read();  // receive the actual JSON string chunk
      }
    }
    if (remainder > 0) {
      Wire.requestFrom(8, remainder);
      while (Wire.available()) {
        jsonString += (char)Wire.read();  // receive the remaining JSON string
      }
    }
    Serial.print("Received JSON: ");
    Serial.println(jsonString);
  }
}

// function that executes whenever data is requested by master
void requestEvent() {
  String jsonString = "{\"device\":\"sensor\",\"value\":30}#";
  int jsonLength = jsonString.length();
  int numChunks = jsonLength / CHUNK_SIZE;
  int remainder = jsonLength % CHUNK_SIZE;
  // Send JSON string in chunks
  for (int i = 0; i < numChunks; i++) {
    for (int j = 0; j < CHUNK_SIZE; j++) {
      Wire.write((uint8_t)jsonString[i * CHUNK_SIZE + j]);
    }
  }
  if (remainder > 0) {
    for (int i = 0; i < remainder; i++) {
      Wire.write((uint8_t)jsonString[numChunks * CHUNK_SIZE + i]);
    }
  }
  Wire.write('\0');
}
