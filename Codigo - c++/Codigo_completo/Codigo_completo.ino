#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME680.h>
#include <MQ135.h>
#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>

// Configuración de red WiFi
const char* ssid = "diegoelmastoroo";
const char* password = "123456789";

// Configuración del broker MQTT
const char* mqttServer = "34.171.0.123";
const int mqttPort = 1883;
const char* mqttUsername = "diegomolina";
const char* mqttPassword = "diegomolina";

// Tópicos MQTT
const char* bme680Topic = "bme680";
const char* mq135Topic = "mq135";
const char* distanceTopic = "distance";

// Configuración BME680
#define BME_SDA 21
#define BME_SCL 22
Adafruit_BME680 bme;

// Configuración MQ135
#define SENSOR_PIN A0
#define RLOAD_VALUE 10
MQ135 gasSensor = MQ135(SENSOR_PIN, RLOAD_VALUE);

// Configuración HC-SR04
const int trigPin = 4;
const int echoPin = 2;

WiFiClient wifiClient;
PubSubClient mqttClient(wifiClient);

void connectToMqtt() {
  while (!mqttClient.connected()) {
    Serial.println("Conectando a MQTT...");

    if (mqttClient.connect("ESP32Client", mqttUsername, mqttPassword)) {
      Serial.println("Conexión exitosa a MQTT");
    } else {
      Serial.print("Error al conectar a MQTT, código de estado: ");
      Serial.print(mqttClient.state());
      Serial.println(" Retrying in 5 seconds...");
      delay(5000);
    }
  }
}

void setup() {
  Serial.begin(115200);

  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Conectando a WiFi...");
  }
  Serial.println("Conexión WiFi exitosa");
  Serial.print("Dirección IP: ");
  Serial.println(WiFi.localIP());

  mqttClient.setServer(mqttServer, mqttPort);

  // Iniciar BME680
  if (!bme.begin()) {
    Serial.println("No se pudo encontrar el sensor BME680. Verifique las conexiones.");
    while (1);
  }
  bme.setTemperatureOversampling(BME680_OS_8X);
  bme.setHumidityOversampling(BME680_OS_2X);
  bme.setPressureOversampling(BME680_OS_4X);
  bme.setIIRFilterSize(BME680_FILTER_SIZE_3);
  bme.setGasHeater(320, 150); // Duración del calentador en ms (320 ms) y temperatura en grados Celsius (150 °C)
}

float measureDistance() {
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  unsigned long duration = pulseIn(echoPin, HIGH);
  float distance = duration * 0.034 / 2;
  return distance;
}

void loop() {
  if (!mqttClient.connected()) {
    connectToMqtt();
  }
  mqttClient.loop();

  if (bme.performReading()) {
    DynamicJsonDocument bme680Json(128);
    bme680Json["temperature"] = bme.temperature;
    bme680Json["humidity"] = bme.humidity;
    bme680Json["pressure"] = bme.pressure / 100.0;
    String bme680Str;
    serializeJson(bme680Json, bme680Str);
    mqttClient.publish(bme680Topic, bme680Str.c_str());
  }

  float airQuality = gasSensor.getPPM();
  DynamicJsonDocument mq135Json(128);
  mq135Json["air_quality"] = airQuality;
  String mq135Str;
  serializeJson(mq135Json, mq135Str);
  mqttClient.publish(mq135Topic, mq135Str.c_str());

  float distance = measureDistance();
  DynamicJsonDocument distanceJson(128);
  distanceJson["distance"] = distance;
  String distanceStr;
  serializeJson(distanceJson, distanceStr);
  mqttClient.publish(distanceTopic, distanceStr.c_str());
  
  delay(2000); // Esperar 2 segundos antes de realizar la siguiente medición
}
