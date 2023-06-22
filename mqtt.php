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

$topics['distance'] = array("qos" => 0, "function" => "procMsg");
$mqtt->subscribe($topics, 0);

while($mqtt->proc()){
}

$mqtt->close();

function procMsg($topic, $msg){
  echo "Msg Recieved: $msg\n";
  $json = json_decode($msg, true);
  $distance = $json['distance'];

  // Assumed to have $conn as mysqli connection
  // Please setup your database connection
  $conn = new mysqli('localhost', 'phpmyadmin', 'admin', 'sensor_data');

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $stmt = $conn->prepare("INSERT INTO sensor_values (distance) VALUES (?)");
  
  if ($conn->error) {
    die("SQL error: " . $conn->error);
  }
  
  $stmt->bind_param("s", $distance);

  $stmt->execute();

  echo "New records created successfully";

  $stmt->close();
  $conn->close();
}
?>
