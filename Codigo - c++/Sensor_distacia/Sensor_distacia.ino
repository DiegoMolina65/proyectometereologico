
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
const char* distanceTopic = "distance";

// Pin del sensor HC-SR04
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
}

float measureDistance() {
  // Generar un pulso de 10 µs en el pin de trigger
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);

  // Medir el tiempo de eco del pin de echo
  unsigned long duration = pulseIn(echoPin, HIGH);

  // Calcular la distancia en centímetros
  float distance = duration * 0.034 / 2;
  return distance;
}

void loop() {
  if (!mqttClient.connected()) {
    connectToMqtt();
  }
  mqttClient.loop();

  // Medir la distancia
  float distance = measureDistance();

  // Crear un objeto JSON con la distancia
  DynamicJsonDocument jsonDoc(128);
  jsonDoc["distance"] = distance;

  // Convertir el objeto JSON en una cadena
  String jsonStr;
  serializeJson(jsonDoc, jsonStr);

  // Publicar la cadena JSON en el tema MQTT
  mqttClient.publish(distanceTopic, jsonStr.c_str());
  
  delay(2000); // Esperar 2 segundos antes de realizar la siguiente medición
}
