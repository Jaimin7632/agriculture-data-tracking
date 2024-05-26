#include <Wire.h>
#include <ArduinoJson.h>

const int CHUNK_SIZE = 32;

void setup() {
  Wire.begin(); // join I2C bus as master
  Serial.begin(9600);
}

String receiveJsonString() {
  String jsonResponse = "";
  Wire.requestFrom(8, 500); // Request more than necessary
  bool endReached = false;

  while (Wire.available() && !endReached) {
    char c = Wire.read();
    if (c == '\0') {
      endReached = true; // Termination character received
    } else if (c != '') { // Exclude null characters
        jsonResponse += c;
    }
  }
  return jsonResponse;
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
