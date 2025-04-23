<?php
include("db_conn.php");

try {
    // Establish database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validate and sanitize input
    // $sensor_id = isset($_GET['sensor_id']) ? $_GET['sensor_id'] : '';
    $sensor_id = isset($_GET['sensor_id']) ? $_GET['sensor_id'] : '';
    $last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

    // If start_date and end_date are not provided, set them to the last 24 hours range
    if (empty($start_date) || empty($end_date)) {
        $end_date = date('Y-m-d H:i:s', strtotime('+1 day'));
        // $end_date = date('Y-m-d H:i:s'); // Current timestamp
        $start_date = date('Y-m-d H:i:s', strtotime('-24 hours'));
    }

    // Construct the SQL query
    $sql = "SELECT water_level, geophone, turbidity, timestamp 
            FROM `$sensor_id` 
            WHERE id > :last_id 
            AND timestamp BETWEEN :start_date AND :end_date
            ORDER BY id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':last_id', $last_id, PDO::PARAM_INT);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);

    $stmt->execute();

    // Fetch data and return as JSON
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
}
?>
