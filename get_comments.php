<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

$tweet_id = $_GET['tweet_id'] ?? 0;
if ($tweet_id <= 0) {
    echo '<p>Invalid tweet ID</p>';
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.tweet_id = ? ORDER BY c.created_at DESC");
    $stmt->execute([$tweet_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($comments as $comment) {
        echo '<div class="comment">';
        echo '<span class="user">@' . htmlspecialchars($comment['username']) . '</span>';
        echo '<span class="time">' . htmlspecialchars($comment['created_at']) . '</span>';
        echo '<p>' . htmlspecialchars($comment['content']) . '</p>';
        echo '</div>';
    }
} catch (PDOException $e) {
    echo '<p>Error fetching comments: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>
