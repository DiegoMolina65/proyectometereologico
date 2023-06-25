#include <Adafruit_Sensor.h>
#include <MQ135.h>

#define SENSOR_PIN A0
#define RLOAD_VALUE 10 // Valor de la resistencia de carga en ohmios

MQ135 gasSensor = MQ135(SENSOR_PIN, RLOAD_VALUE);

void setup() {
  Serial.begin(9600);
}

void loop() {
  float airQuality = gasSensor.getPPM();
  Serial.print("Calidad del aire: ");
  Serial.print(airQuality);
  Serial.println(" ppm");

  delay(2000);
}
