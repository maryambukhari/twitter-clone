<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$tweet_id = $_POST['tweet_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if ($tweet_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid tweet ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO likes (user_id, tweet_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE user_id = user_id");
    $stmt->execute([$user_id, $tweet_id]);
    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
