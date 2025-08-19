<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<script>location.href = "login.php";</script>';
    exit;
}
include 'db.php';
$tweet_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM tweets WHERE id = ? AND user_id = ?");
$stmt->execute([$tweet_id, $_SESSION['user_id']]);
$tweet = $stmt->fetch();
if (!$tweet) {
    die('Tweet not found or not yours');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];
    $stmt = $pdo->prepare("UPDATE tweets SET content = ? WHERE id = ?");
    $stmt->execute([$content, $tweet_id]);
    echo '<script>location.href = "index.php";</script>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Tweet - Twitter Clone</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(to bottom, #ffffff, #e6f7ff);
            color: #14171a;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 500px;
            width: 100%;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(29, 161, 242, 0.2);
            text-align: center;
        }
        h2 {
            color: #1da1f2;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
        }
        textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccd6dd;
            border-radius: 8px;
            font-size: 16px;
            background: #f5f8fa;
            resize: vertical;
            min-height: 150px;
            transition: border 0.3s;
        }
        textarea:focus {
            border-color: #1da1f2;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #1da1f2;
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #0c84d1;
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Tweet</h2>
        <form method="post">
            <textarea name="content" required><?php echo htmlspecialchars($tweet['content']); ?></textarea>
            <button type="submit">Update Tweet</button>
        </form>
    </div>
</body>
</html>
