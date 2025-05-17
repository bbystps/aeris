<?php
include("db_conn.php");

$lamp_location = isset($_GET['lamp_location']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['lamp_location']) : 'lamp';
$lamp_db = $lamp_location . "_data";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM $lamp_db ORDER BY id DESC");
    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
}
?>