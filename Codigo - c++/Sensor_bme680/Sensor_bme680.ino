#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME680.h>

#define BME_SDA 21
#define BME_SCL 22

Adafruit_BME680 bme;

void setup() {
  Serial.begin(9600);
  Wire.begin(BME_SDA, BME_SCL);
  
  if (!bme.begin()) {
    Serial.println("No se pudo encontrar el sensor BME680. Verifique las conexiones.");
    while (1);
  }
  
  // Configurar el sensor BME680
  bme.setTemperatureOversampling(BME680_OS_8X);
  bme.setHumidityOversampling(BME680_OS_2X);
  bme.setPressureOversampling(BME680_OS_4X);
  bme.setIIRFilterSize(BME680_FILTER_SIZE_3);
  bme.setGasHeater(320, 150); // Duración del calentador en ms (320 ms) y temperatura en grados Celsius (150 °C)
}

void loop() {
  if (bme.performReading()) {
    float temperature = bme.temperature;
    float humidity = bme.humidity;
    float pressure = bme.pressure / 100.0; // Dividir por 100 para convertir de Pa a hPa
    
    Serial.print("Temperatura: ");
    Serial.print(temperature);
    Serial.println(" °C");
    
    Serial.print("Humedad: ");
    Serial.print(humidity);
    Serial.println(" %");
    
    Serial.print("Presión: ");
    Serial.print(pressure);
    Serial.println(" hPa");
  } else {
    Serial.println("Error al leer el sensor BME680");
  }
  
  delay(2000);
}
