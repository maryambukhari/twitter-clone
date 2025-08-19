<?php
session_start();
include 'db.php';
if (isset($_SESSION['user_id']) && isset($_POST['user_id'])) {
    $follower_id = $_SESSION['user_id'];
    $followed_id = $_POST['user_id'];
    if ($follower_id == $followed_id) {
        echo 'error';
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->execute([$follower_id, $followed_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$follower_id, $followed_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
        $stmt->execute([$follower_id, $followed_id]);
    }
    echo 'success';
} else {
    echo 'error';
}
?>
