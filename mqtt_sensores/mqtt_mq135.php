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

$topic = 'mq135';
$mqtt->subscribe([$topic => ["qos" => 0, "function" => "procMsg"]], 0);

while($mqtt->proc()){
}

$mqtt->close();

function procMsg($topic, $msg){
  echo "Msg Received: $msg\n";
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

  echo "New record created successfully";

  $stmt->close();
  $conn->close();
}
?>
