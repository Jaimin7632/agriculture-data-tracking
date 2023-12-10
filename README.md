# Tsim-A7670 Setup Guide

## Arduino Setup
1. Documentation for Tsim-A7670 [T-A76XX repository](https://github.com/Xinyuan-LilyGO/LilyGO-T-A76XX/tree/main).
2. Open Arduino IDE.
3. Navigate to Arduino -> Preferences.
4. Add the following URLs to the "Additional Boards Manager URLs":
   - http://arduino.esp8266.com/stable/package_esp8266com_index.json
   - https://raw.githubusercontent.com/espressif/arduino-esp32/gh-pages/package_esp32_index.json
5. Click OK to close the Preferences window.
6. Go to Tools -> Board -> Boards Manager.
7. Search for "esp8266" and install the package.
8. Search for "esp32" and install the package.
9. Select "ESP32 Wrover Module" as the target board for code upload.
10. Upload the code from Hardware Code Folder

## Server Code Setup
1. Clone the Repo.
2. Update the necessary configurations in the server code.
3. Run the server code to initiate the communication.


---

*Note: This guide assumes you have the Arduino IDE and necessary dependencies installed.*

