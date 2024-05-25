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
  Wire.write(numChunks);      // send the number of chunks
  Wire.write(remainder);      // send the remainder
  for (int i = 0; i < numChunks; i++) {
    Wire.write(jsonString.substring(i * CHUNK_SIZE, (i + 1) * CHUNK_SIZE).c_str(), CHUNK_SIZE);
  }
  if (remainder > 0) {
    Wire.write(jsonString.substring(numChunks * CHUNK_SIZE).c_str(), remainder);
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
      jsonResponse += (char)Wire.read();
    }
  }
  if (responseRemainder > 0) {
    Wire.requestFrom(8, responseRemainder);
    while (Wire.available()) {
      jsonResponse += (char)Wire.read();
    }
  }

  Serial.println("Received JSON: " + jsonResponse);
  delay(1000); // wait for a second
}
