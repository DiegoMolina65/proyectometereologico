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
  'bme680' => array("topic" => "bme680", "qos" => 0, "function" => "procMsgBME680"),
  'mq135' => array("topic" => "mq135", "qos" => 0, "function" => "procMsgMQ135"),
  'lluvia' => array("topic" => "lluvia", "qos" => 0, "function" => "procMsgLluvia"),
  'viento' => array("topic" => "viento", "qos" => 0, "function" => "procMsgViento")
);

$mqtt->subscribe($topics, 0);

while($mqtt->proc()){
  if ($mqtt->topics && is_array($mqtt->topics)) {
    foreach ($mqtt->topics as $topic_key => $topic_val) {
      $topic = $topic_val['topic'];
      $function = $topic_val['function'];
      $mqtt->publish($topic, 'get-data');
      usleep(1000000); // Espera 1 segundo antes de publicar el siguiente tópico
    }
  }
}

$mqtt->close();

function procMsgBME680($topic, $msg){
  echo "BME680 Msg Received: $msg\n";
  $json = json_decode($msg, true);
  
  // Assumed to have $conn as mysqli connection
  // Please setup your database connection
  $conn = new mysqli('localhost', 'phpmyadmin', 'admin', 'sensor_data');

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $temperature = $json['temperature'];
  $humidity = $json['humidity'];
  $pressure = $json['pressure'];
  
  $stmt = $conn->prepare("INSERT INTO sensor_values (temperature, humidity, pressure) VALUES (?, ?, ?)");
  $stmt->bind_param("ddd", $temperature, $humidity, $pressure);
  
  if ($conn->error) {
    die("SQL error: " . $conn->error);
  }
  
  $stmt->execute();

  echo "New BME680 record created successfully\n";

  $stmt->close();
  $conn->close();
}

function procMsgMQ135($topic, $msg){
  echo "MQ135 Msg Received: $msg\n";
  $json = json_decode($msg, true);
  
  // Assumed to have $conn as mysqli connection
  // Please setup your database connection
  $conn = new mysqli('localhost', 'phpmyadmin', 'admin', 'sensor_data');

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $airQuality = $json['air_quality'];
  
  $stmt = $conn->prepare("INSERT INTO sensor_values (air_quality) VALUES (?)");
  $stmt->bind_param("d", $airQuality);
  
  if ($conn->error) {
    die("SQL error: " . $conn->error);
  }
  
  $stmt->execute();

  echo "New MQ135 record created successfully\n";

  $stmt->close();
  $conn->close();
}

function procMsgLluvia($topic, $msg){
  echo "Lluvia Msg Received: $msg\n";
  $json = json_decode($msg, true);
  
  // Assumed to have $conn as mysqli connection
  // Please setup your database connection
  $conn = new mysqli('localhost', 'phpmyadmin', 'admin', 'sensor_data');

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $lluviaDetectada = $json['lluvia_detectada'];
  $acumuladoLluvia = $json['acumulado_lluvia'];
  
  $stmt = $conn->prepare("INSERT INTO sensor_values (lluvia_detectada, acumulado_lluvia) VALUES (?, ?)");
  $stmt->bind_param("dd", $lluviaDetectada, $acumuladoLluvia);
  
  if ($conn->error) {
    die("SQL error: " . $conn->error);
  }
  
  $stmt->execute();

  echo "New Lluvia record created successfully\n";

  $stmt->close();
  $conn->close();
}

function procMsgViento($topic, $msg){
  echo "Viento Msg Received: $msg\n";
  $json = json_decode($msg, true);
  
  // Assumed to have $conn as mysqli connection
  // Please setup your database connection
  $conn = new mysqli('localhost', 'phpmyadmin', 'admin', 'sensor_data');

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $velocidadViento = $json['velocidad_viento'];
  
  $stmt = $conn->prepare("INSERT INTO sensor_values (velocidad_viento) VALUES (?)");
  $stmt->bind_param("d", $velocidadViento);
  
  if ($conn->error) {
    die("SQL error: " . $conn->error);
  }
  
  $stmt->execute();

  echo "New Viento record created successfully\n";

  $stmt->close();
  $conn->close();
}
?>
