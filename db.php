<?php
$host = 'localhost'; // Update if using remote hosting (e.g., mysql.hostinger.com)
$db = 'dbxvxodg9rmtn7';
$user = 'uxhc7qjwxxfub';
$pass = 'g4t0vezqttq6';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}
?>
