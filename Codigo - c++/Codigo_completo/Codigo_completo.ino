#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME680.h>
#include <MQ135.h>
#include <WiFi.h>
#include <PubSubClient.h>
#include <ArduinoJson.h>
#include <NewPing.h>

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
const char* lluviaTopic = "lluvia";
const char* vientoTopic = "viento";

// Configuración BME680
#define BME_SDA 21
#define BME_SCL 22
Adafruit_BME680 bme;

// Configuración MQ135
#define SENSOR_PIN A0
#define RLOAD_VALUE 10
MQ135 gasSensor = MQ135(SENSOR_PIN, RLOAD_VALUE);

// Configuración del sensor de lluvia
#define TRIGGER_PIN 4
#define ECHO_PIN 2
#define UMBRAL_COBERTURA 10
NewPing sonar(TRIGGER_PIN, ECHO_PIN);
float acumuladoLluvia = 0;

// Configuración del sensor de viento
#define SENSOR_PIN 5 // Sensor Hall conectado al pin D5

volatile int numRevoluciones = 0; // Contador de revoluciones del anemómetro
unsigned long tiempoAnterior = 0; // Variable para guardar el tiempo del último cálculo

// Interrupt service routine para incrementar el contador de revoluciones
void ISR_revolucion() {
  numRevoluciones++;
}

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

  pinMode(SENSOR_PIN, INPUT_PULLUP);
  // Configura el pin del sensor como interrupción y llama a la ISR en cada flanco de bajada
  attachInterrupt(digitalPinToInterrupt(SENSOR_PIN), ISR_revolucion, FALLING);
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

  delay(50);

  unsigned int distance = sonar.ping_cm();
  if (distance < UMBRAL_COBERTURA) {
    float lluvia = UMBRAL_COBERTURA - distance;
    acumuladoLluvia += lluvia;
    DynamicJsonDocument lluviaJson(128);
    lluviaJson["lluvia_detectada"] = lluvia;
    lluviaJson["acumulado_lluvia"] = acumuladoLluvia;
    String lluviaStr;
    serializeJson(lluviaJson, lluviaStr);
    mqttClient.publish(lluviaTopic, lluviaStr.c_str());
  }

  unsigned long tiempoAhora = millis();

  // Actualiza la velocidad del viento cada segundo
  if (tiempoAhora - tiempoAnterior >= 1000) {
    detachInterrupt(digitalPinToInterrupt(SENSOR_PIN));

    // Asumiendo que el anemómetro genera 1 pulso por revolución y tiene un factor de calibración de 2.4 km/h por revolución
    float velocidadViento = (numRevoluciones * 2.4);
    
    DynamicJsonDocument vientoJson(128);
    vientoJson["velocidad_viento"] = velocidadViento;
    String vientoStr;
    serializeJson(vientoJson, vientoStr);
    mqttClient.publish(vientoTopic, vientoStr.c_str());

    numRevoluciones = 0;
    tiempoAnterior = tiempoAhora;

    attachInterrupt(digitalPinToInterrupt(SENSOR_PIN), ISR_revolucion, FALLING);
  }

  delay(1000);
}
