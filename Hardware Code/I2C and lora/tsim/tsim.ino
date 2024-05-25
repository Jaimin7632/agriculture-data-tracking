#include <Wire.h>
#include <ArduinoJson.h>

const int CHUNK_SIZE = 32;

void setup() {
  Wire.begin(); // join I2C bus as master
  Serial.begin(9600);
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

  delay(500);

  // Request the size of the incoming JSON string from the slave
  Wire.requestFrom(8, 1);
  int responseNumChunks = Wire.read(); // read the number of chunks
  int responseRemainder = Wire.read(); // read the remainder

  // Request the actual JSON string chunks based on the received information
  String jsonResponse;
  for (int i = 0; i < responseNumChunks; i++) {
    Wire.requestFrom(8, CHUNK_SIZE);
    while (Wire.available()) {
      char c = Wire.read();
      if (c != '\0') { // Exclude null characters
        jsonResponse += c;
      }
    }
  }
  if (responseRemainder > 0) {
    Wire.requestFrom(8, responseRemainder);
    while (Wire.available()) {
      char c = Wire.read();
      if (c != '\0') { // Exclude null characters
        jsonResponse += c;
      }
    }
  }

  Serial.println("Received JSON: " + jsonResponse);
  delay(1000); // wait for a second
}
