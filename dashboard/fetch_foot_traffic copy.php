<?php
include("db_conn.php");

$sensorId = isset($_POST['sensorId']) ? $_POST['sensorId'] : '';
$sensorTable = $sensorId . "_foot_traffic";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the latest record from the specified sensor table
    $stmt = $pdo->query("SELECT * FROM `$sensorTable` ORDER BY id DESC LIMIT 1");
    $siteLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if data is empty and return default values
    if (empty($siteLocations)) {
        $siteLocations = [[
            "id" => 0,
            "value" => 0,
            "timestamp" => "0000-00-00 00:00:00"
        ]];
    }

    // Return as JSON
    echo json_encode($siteLocations);

} catch (PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
}
?>
