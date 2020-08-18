///////////////////////////////////////////////////////////////////////////////////////
// Ninebot BLE Scanner
// by Joey Babcock - https://joeybabcock.me/blog/
// ------------------------------------------------------------------------------------
// This sketch searches for ninebot scooters and reports their mac and associated
// location to a server defined in the header.
///////////////////////////////////////////////////////////////////////////////////////

#include "BLEDevice.h"
#include <WiFi.h>
#include <WiFiMulti.h>
#include <HTTPClient.h>

#ifndef NULL
#define NULL 0
#endif

WiFiMulti wifiMulti;

static BLEUUID serviceUUID("6E400001-B5A3-F393-E0A9-E50E24DCCA9E");

int loops = 0;

static BLEAdvertisedDevice* myDevice;
char ssid[23];
String url = F("http://your_website.com/put.php");
String key = F("3mpu9vhzo8427sq8"); // random key to make up for the current lack of security

void sendPing()
{
    HTTPClient http;   
    
    snprintf(ssid, 23, "%08X", (uint32_t)ESP.getEfuseMac());
    
    String httpRequestData = "";
    httpRequestData += url;
    httpRequestData += "?station=";
    httpRequestData += ssid;
    httpRequestData += "?key=";
    httpRequestData += key;

    http.begin(httpRequestData);  //Specify destination for HTTP request
    http.addHeader("Content-Type", "text/plain");             //Specify content-type header

    Serial.print("Posting station: ");
    Serial.println((uint32_t)ESP.getEfuseMac());
    Serial.println();
    
    int httpResponseCode = http.GET();   //Send the actual POST request
    
    if(httpResponseCode > 0)
    {
        String response = http.getString();                       //Get the response to the request
    
        Serial.println(httpResponseCode);   //Print return code
        Serial.println(response);           //Print request answer
    }
    else
    {
        Serial.print("Error on sending POST: ");
        Serial.println(httpResponseCode);
    }
    
    http.end();  //Free resources
}

void updateLocation(String mac, String scootName)
{
    HTTPClient http;   
    
    snprintf(ssid, 23, "%08X", (uint32_t)ESP.getEfuseMac());
    
    String httpRequestData;
    strcpy(httpRequestData, url);
    httpRequestData += "?mac=";
    httpRequestData += mac;
    httpRequestData += "&station=";
    httpRequestData += ssid;
    httpRequestData += "&name=";
    httpRequestData += scootName;

    http.begin(httpRequestData);  //Specify destination for HTTP request
    http.addHeader("Content-Type", "text/plain");             //Specify content-type header

    Serial.print("Posting device update for: ");
    Serial.println(mac);
    Serial.print("Named: ");
    Serial.println(scootName);
    Serial.print("Coming From station: ");
    Serial.println((uint32_t)ESP.getEfuseMac());
    Serial.println();
    
    int httpResponseCode = http.GET();   //Send the actual POST request
    
    if(httpResponseCode > 0)
    {
        String response = http.getString();                       //Get the response to the request
    
        Serial.println(httpResponseCode);   //Print return code
        Serial.println(response);           //Print request answer
    }
    else
    {
        Serial.print("Error on sending POST: ");
        Serial.println(httpResponseCode);
    }
    
    http.end();
}

class MyAdvertisedDeviceCallbacks: public BLEAdvertisedDeviceCallbacks {
    void onResult(BLEAdvertisedDevice advertisedDevice) {
        Serial.print("Advertised Device found: ");
        Serial.println(advertisedDevice.toString().c_str());
    
        if (advertisedDevice.haveServiceUUID() && advertisedDevice.isAdvertisingService(serviceUUID)) {
            Serial.println("UART Device found!");
            updateLocation(advertisedDevice.getAddress().toString().c_str(), advertisedDevice.getName().c_str());
        }
    }
};


void setup() {
    Serial.begin(115200);
    Serial.println("Starting Arduino Ninebot...");

    wifiMulti.addAP("Network1", "P@ssword1");
    wifiMulti.addAP("Network2", "P@ssword2");
    wifiMulti.addAP("Network3", "P@ssword3");

    Serial.println("Connecting Wifi...");
    if(wifiMulti.run() == WL_CONNECTED) {
        Serial.println("");
        Serial.println("WiFi connected");
        Serial.println("IP address: ");
        Serial.println(WiFi.localIP());
    }
    
    BLEDevice::init("");
    BLEDevice::getScan()->stop();
    BLEScan* pBLEScan = BLEDevice::getScan();
    pBLEScan->setAdvertisedDeviceCallbacks(new MyAdvertisedDeviceCallbacks());
    pBLEScan->setInterval(1349);
    pBLEScan->setWindow(449);
    pBLEScan->setActiveScan(true);
    pBLEScan->start(5, false);
    delay(5000);
}

void loop() {
    Serial.print("Entering Loop ");
    Serial.println(loops);
    if(loops < 5)
    {
        if(loops == 1)
        {
            sendPing();
        }
        BLEDevice::getScan()->start(5, false);
        delay(5000);
        BLEDevice::getScan()->stop();
        loops++;
    }
    else
    {
        Serial.println("Restarting...");
        ESP.restart();
    }
}
