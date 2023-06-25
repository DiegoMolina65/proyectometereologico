<?php
$servername = "localhost";
$username = "phpmyadmin";
$password = "admin";
$dbname = "sensor_data";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Obtener la última lectura de distancia
$sql_distance = "SELECT distance FROM sensor_values ORDER BY id DESC LIMIT 1";
$result_distance = $conn->query($sql_distance);

if ($result_distance->num_rows > 0) {
  while($row = $result_distance->fetch_assoc()) {
    echo "Distance: " . $row["distance"]. "<br>";
  }
} else {
  echo "No distance results";
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

$conn->close();
?>
