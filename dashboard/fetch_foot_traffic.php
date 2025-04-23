<?php
include("db_conn.php");

$sensorId = isset($_POST['sensorId']) ? $_POST['sensorId'] : 'lamp1';
$sensorTable = $sensorId . "_foot_traffic";

// Set timezone to Manila
date_default_timezone_set('Asia/Manila');

// Get current date in Manila timezone
$currentDate = date('Y-m-d');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get sum of today's values
    $stmt = $pdo->prepare("SELECT SUM(CAST(`value` AS UNSIGNED)) AS total FROM `$sensorTable` WHERE DATE(`timestamp`) = :today");
    $stmt->execute(['today' => $currentDate]);
    $totalRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $total = $totalRow['total'] ?? 0;

    // Return both latest and total sum
    echo json_encode([
        "total_today" => $total
    ]);

} catch (PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
}
?>
