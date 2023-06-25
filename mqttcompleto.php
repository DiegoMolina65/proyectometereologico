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
  'distance' => array("qos" => 0, "function" => "procMsgDistance"),
  'bme680' => array("qos" => 0, "function" => "procMsgBME680"),
  'mq135' => array("qos" => 0, "function" => "procMsgMQ135")
);

$mqtt->subscribe($topics, 0);

while($mqtt->proc()){
}

$mqtt->close();

function procMsgDistance($topic, $msg){
  echo "Distance Msg Received: $msg\n";
  $json = json_decode($msg, true);
  
  // Assumed to have $conn as mysqli connection
  // Please setup your database connection
  $conn = new mysqli('localhost', 'phpmyadmin', 'admin', 'sensor_data');

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  $distance = $json['distance'];
  $stmt = $conn->prepare("INSERT INTO sensor_values (distance) VALUES (?)");
  $stmt->bind_param("d", $distance);
  
  if ($conn->error) {
    die("SQL error: " . $conn->error);
  }
  
  $stmt->execute();

  echo "New distance record created successfully\n";

  $stmt->close();
  $conn->close();
}

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
?>
