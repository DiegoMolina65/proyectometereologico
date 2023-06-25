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

$distance = null;
$temperature = null;
$humidity = null;
$pressure = null;
$airQuality = null;

while($mqtt->proc()){
  if ($distance !== null && $temperature !== null && $humidity !== null && $pressure !== null && $airQuality !== null) {
    echo "Distance: " . $distance . "\n";
    echo "Temperature: " . $temperature . "\n";
    echo "Humidity: " . $humidity . "\n";
    echo "Pressure: " . $pressure . "\n";
    echo "Air Quality: " . $airQuality . "\n";

    // Reiniciar las variables para la siguiente iteración
    $distance = null;
    $temperature = null;
    $humidity = null;
    $pressure = null;
    $airQuality = null;
  }
}

$mqtt->close();

function procMsg($topic, $msg){
  echo "Msg Received: $msg\n";
  $json = json_decode($msg, true);

  if ($topic == 'distance') {
    $distance = $json['distance'];
  } else if ($topic == 'bme680') {
    $temperature = $json['temperature'];
    $humidity = $json['humidity'];
    $pressure = $json['pressure'];
  } else if ($topic == 'mq135') {
    $airQuality = $json['air_quality'];
  }
}
?>
