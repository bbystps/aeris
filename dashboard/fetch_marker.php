<?php
include("db_conn.php");
$sensorId = isset($_GET['sensorId']) ? $_GET['sensorId'] : 'Lamp 1';
try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $stmt = $pdo->prepare('SELECT latitude, longitude FROM lamp_list WHERE name = :sensorId');
  $stmt->execute(['sensorId' => $sensorId]);
  $location = $stmt->fetchAll(PDO::FETCH_ASSOC);
  // Return as JSON
    echo json_encode($location);
} catch (PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage();
}
?>
