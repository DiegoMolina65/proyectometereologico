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
  'distance' => array("qos" => 0, "function" => "procMsg"),
  'bme680' => array("qos" => 0, "function" => "procMsg"),
  'mq135' => array("qos" => 0, "function" => "procMsg")
);

$mqtt->subscribe($topics, 0);

while($mqtt->proc()){
}

$mqtt->close();

function procMsg($topic, $msg){
  echo "Msg Recieved: $msg\n";
  $json = json_decode($msg, true);
  
  // Assumed to have $conn as mysqli connection
  // Please setup your database connection
  $conn = new mysqli('localhost', 'phpmyadmin', 'admin', 'sensor_data');

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  
  if ($topic == 'distance') {
    $distance = $json['distance'];
    $stmt = $conn->prepare("INSERT INTO sensor_values (distance) VALUES (?)");
    $stmt->bind_param("f", $distance);
  } else if ($topic == 'bme680') {
    $temperature = $json['temperature'];
    $humidity = $json['humidity'];
    $pressure = $json['pressure'];
    $stmt = $conn->prepare("INSERT INTO sensor_values (temperature, humidity, pressure) VALUES (?, ?, ?)");
    $stmt->bind_param("fff", $temperature, $humidity, $pressure);
  } else if ($topic == 'mq135') {
    $airQuality = $json['air_quality'];
    $stmt = $conn->prepare("INSERT INTO sensor_values (air_quality) VALUES (?)");
    $stmt->bind_param("f", $airQuality);
  }
  
  if ($conn->error) {
    die("SQL error: " . $conn->error);
  }
  
  $stmt->execute();

  echo "New records created successfully";

  $stmt->close();
  $conn->close();
}
?>
