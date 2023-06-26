<?php
$servername = "localhost";
$username = "phpmyadmin";
$password = "admin";
$dbname = "sensor_data";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Obtener la última lectura de temperatura, humedad y presión (BME680)
$sql_bme680 = "SELECT temperature, humidity, pressure FROM sensor_values ORDER BY id DESC LIMIT 1";
$result_bme680 = $conn->query($sql_bme680);

if ($result_bme680->num_rows > 0) {
  while($row = $result_bme680->fetch_assoc()) {
    echo "Temperature: " . $row["temperature"]. "<br>";
    echo "Humidity: " . $row["humidity"]. "<br>";
    echo "Pressure: " . $row["pressure"]. "<br>";
  }
} else {
  echo "No BME680 results";
}

// Obtener la última lectura de calidad del aire (MQ135)
$sql_mq135 = "SELECT air_quality FROM sensor_values ORDER BY id DESC LIMIT 1";
$result_mq135 = $conn->query($sql_mq135);

if ($result_mq135->num_rows > 0) {
  while($row = $result_mq135->fetch_assoc()) {
    echo "Air Quality: " . $row["air_quality"]. "<br>";
  }
} else {
  echo "No MQ135 results";
}

// Obtener la última lectura de lluvia
$sql_lluvia = "SELECT lluvia_detectada, acumulado_lluvia FROM sensor_values ORDER BY id DESC LIMIT 1";
$result_lluvia = $conn->query($sql_lluvia);

if ($result_lluvia->num_rows > 0) {
  while($row = $result_lluvia->fetch_assoc()) {
    echo "Lluvia Detectada: " . $row["lluvia_detectada"]. "<br>";
    echo "Acumulado de Lluvia: " . $row["acumulado_lluvia"]. "<br>";
  }
} else {
  echo "No Lluvia results";
}

// Obtener la última lectura de velocidad del viento
$sql_viento = "SELECT velocidad_viento FROM sensor_values ORDER BY id DESC LIMIT 1";
$result_viento = $conn->query($sql_viento);

if ($result_viento->num_rows > 0) {
  while($row = $result_viento->fetch_assoc()) {
    echo "Velocidad del Viento: " . $row["velocidad_viento"]. "<br>";
  }
} else {
  echo "No Viento results";
}

$conn->close();
?>
