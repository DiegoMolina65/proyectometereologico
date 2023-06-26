<?php
use Bluerhinos\phpMQTT;
require("phpMQTT.php");

$host = "34.171.0.123"; 
$port = 1883;
$username = "diegomolina"; 
$password = "diegomolina"; 
$mqtt = new phpMQTT($host, $port, "PHP MQTT Client"); 

if(!$mqtt->connect(true, NULL, $username, $password)) {
  exit(1);
}

$topics = array(
  'bme680' => array("qos" => 0, "function" => "procMsgBME680"),
  'mq135' => array("qos" => 0, "function" => "procMsgMQ135"),
  'lluvia' => array("qos" => 0, "function" => "procMsgLluvia"),
  'viento' => array("qos" => 0, "function" => "procMsgViento")
);

$mqtt->subscribe($topics, 0);

$topicOrder = ['bme680', 'mq135', 'lluvia', 'viento'];
$topicIndex = 0;

while($mqtt->proc()){
  if ($topicIndex === count($topicOrder)) {
    $topicIndex = 0;
  }
  
  $currentTopic = $topicOrder[$topicIndex];
  $mqtt->publish($currentTopic, 'get-data');
  
  $topicIndex++;
}

$mqtt->close();

function procMsgBME680($topic, $msg){
  echo "BME680 Msg Received: $msg\n";
  $json = json_decode($msg, true);

  $temperature = $json['temperature'];
  $humidity = $json['humidity'];
  $pressure = $json['pressure'];

  $conn = new mysqli('localhost', 'phpmyadmin', 'admin', 'sensor_data');
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $stmt = $conn->prepare("INSERT INTO sensor_values (temperature, humidity, pressure) VALUES (?, ?, ?)");
  $stmt->bind_param("ddd", $temperature, $humidity, $pressure);
  
  if ($stmt->execute()) {
    echo "Datos BME680 almacenados correctamente\n";
  } else {
    echo "Error al almacenar los datos BME680: " . $stmt->error . "\n";
  }

  $stmt->close();
  $conn->close();
}

function procMsgMQ135($topic, $msg){
  echo "MQ135 Msg Received: $msg\n";
  $json = json_decode($msg, true);

  $airQuality = $json['air_quality'];
  
  $conn = new mysqli('localhost', 'phpmyadmin', 'admin', 'sensor_data');
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $stmt = $conn->prepare("INSERT INTO sensor_values (air_quality) VALUES (?)");
  $stmt->bind_param("d", $airQuality);
  
  if ($stmt->execute()) {
    echo "Datos MQ135 almacenados correctamente\n";
  } else {
    echo "Error al almacenar los datos MQ135: " . $stmt->error . "\n";
  }

  $stmt->close();
  $conn->close();
}

function procMsgLluvia($topic, $msg){
  echo "Lluvia Msg Received: $msg\n";
  $json = json_decode($msg, true);

  $lluviaDetectada = $json['lluvia_detectada'];
  $acumuladoLluvia = $json['acumulado_lluvia'];
  
  $conn = new mysqli('localhost', 'phpmyadmin', 'admin', 'sensor_data');
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $stmt = $conn->prepare("INSERT INTO sensor_values (lluvia_detectada, acumulado_lluvia) VALUES (?, ?)");
  $stmt->bind_param("dd", $lluviaDetectada, $acumuladoLluvia);
  
  if ($stmt->execute()) {
    echo "Datos de lluvia almacenados correctamente\n";
  } else {
    echo "Error al almacenar los datos de lluvia: " . $stmt->error . "\n";
  }

  $stmt->close();
  $conn->close();
}

function procMsgViento($topic, $msg){
  echo "Viento Msg Received: $msg\n";
  $json = json_decode($msg, true);

  $velocidadViento = $json['velocidad_viento'];

  $conn = new mysqli('localhost', 'phpmyadmin', 'admin', 'sensor_data');
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $stmt = $conn->prepare("INSERT INTO sensor_values (velocidad_viento) VALUES (?)");
  $stmt->bind_param("d", $velocidadViento);
  
  if ($stmt->execute()) {
    echo "Datos de viento almacenados correctamente\n";
  } else {
    echo "Error al almacenar los datos de viento: " . $stmt->error . "\n";
  }

  $stmt->close();
  $conn->close();
}
?>
