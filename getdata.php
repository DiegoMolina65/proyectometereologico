<?php
$servername = "localhost";
$username = "phpmyadmin";
$password = "admin";
$dbname = "sensor_data";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT distance FROM sensor_data ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    echo "Distance: " . $row["distance"]. "<br>";
  }
} else {
  echo "0 results";
}
$conn->close();
?>
