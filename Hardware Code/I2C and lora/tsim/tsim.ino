#include <Wire.h>
#include <ArduinoJson.h>

const int CHUNK_SIZE = 32;

void setup() {
  Wire.begin(); // join I2C bus as master
  Serial.begin(9600);
}

String receiveJsonString() {
  const int slaveAddress = 8;
  const int requestBytes = 50;
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
    StaticJsonDocument<jsonBufferSize> jsonDoc;
    DeserializationError error = deserializeJson(jsonDoc, jsonResponse);

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


void loop() {
  // Create a JSON object and populate it
  StaticJsonDocument<200> jsonDocument;
  jsonDocument["sensor"] = "temperature";
  jsonDocument["value"] = 25.5;

  // Serialize JSON to a string
  String jsonString;
  serializeJson(jsonDocument, jsonString);

  // Send JSON string in chunks
  int numChunks = jsonString.length() / CHUNK_SIZE;
  int remainder = jsonString.length() % CHUNK_SIZE;

  Wire.beginTransmission(8);  // transmit to device #8
  Wire.write((uint8_t)numChunks);      // send the number of chunks
  Wire.write((uint8_t)remainder);      // send the remainder
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
  Wire.endTransmission();   // stop transmitting

  // reading part
  delay(500);
  String jsonResponse = receiveJsonString();
  Serial.println("Received JSON: " + jsonResponse);
  delay(1000); // wait for a second
}
