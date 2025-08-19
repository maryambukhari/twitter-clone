<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<script>location.href = "login.php";</script>';
    exit;
}
include 'db.php';
$username = $_GET['user'] ?? $_SESSION['username'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();
if (!$user) {
    die('User not found');
}
$profile_user_id = $user['id'];
$current_user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM follows WHERE follower_id = ? AND followed_id = ?");
$stmt->execute([$current_user_id, $profile_user_id]);
$following = $stmt->fetch() ? true : false;
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM follows WHERE followed_id = ?");
$stmt->execute([$profile_user_id]);
$followers = $stmt->fetch()['count'];
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM follows WHERE follower_id = ?");
$stmt->execute([$profile_user_id]);
$following_count = $stmt->fetch()['count'];
$stmt = $pdo->prepare("SELECT t.*, u.username, u.profile_pic FROM tweets t JOIN users u ON t.user_id = u.id WHERE t.user_id = ? ORDER BY t.created_at DESC LIMIT 50");
$stmt->execute([$profile_user_id]);
$tweets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>@<?php echo $username; ?> - Twitter Clone</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #fff;
            color: #14171a;
            margin: 0;
            line-height: 1.5;
        }
        header {
            background: #fff;
            border-bottom: 1px solid #e6ecf0;
            padding: 10px 20px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #1da1f2;
            font-weight: bold;
            font-size: 16px;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #0c84d1;
        }
        .main {
            max-width: 600px;
            margin: 60px auto 0;
            padding: 0 10px;
        }
        .profile-header {
            text-align: center;
            padding: 30px 20px;
            background: #f5f8fa;
            border-bottom: 1px solid #e6ecf0;
        }
        .profile-header img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .profile-header h2 {
            margin: 10px 0 5px;
            font-size: 24px;
            font-weight: bold;
        }
        .profile-header p {
            color: #657786;
            margin: 0 0 15px;
        }
        .stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 15px;
        }
        .stats span {
            font-size: 16px;
        }
        .stats strong {
            display: block;
            font-size: 18px;
            font-weight: bold;
        }
        .follow-btn, .edit-btn {
            background: #1da1f2;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .follow-btn:hover, .edit-btn:hover {
            background: #0c84d1;
        }
        /* Reuse tweet styles from index.php */
        .tweet {
            border-bottom: 1px solid #e6ecf0;
            padding: 15px;
            display: flex;
            transition: background 0.3s;
        }
        .tweet:hover {
            background: #f5f8fa;
        }
        .tweet img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .tweet-content {
            flex: 1;
        }
        .user {
            font-weight: bold;
            color: #14171a;
        }
        .time {
            color: #657786;
            font-size: 14px;
            margin-left: 5px;
        }
        .tweet-content p {
            margin: 5px 0;
            font-size: 16px;
        }
        .interactions {
            display: flex;
            justify-content: space-between;
            max-width: 300px;
            margin-top: 10px;
        }
        .interactions span {
            cursor: pointer;
            color: #657786;
            font-size: 14px;
            display: flex;
            align-items: center;
            transition: color 0.3s;
        }
        .interactions span:hover {
            color: #1da1f2;
        }
        .comments-div {
            margin-top: 10px;
            padding-left: 58px;
        }
        .comment-form {
            margin-top: 10px;
            display: flex;
            padding-left: 58px;
        }
        .comment-form input {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccd6dd;
            border-radius: 20px 0 0 20px;
            outline: none;
        }
        .comment-form button {
            background: #1da1f2;
            color: #fff;
            border: none;
            padding: 8px 15px;
            border-radius: 0 20px 20px 0;
            cursor: pointer;
        }
        @media (max-width: 600px) {
            .main {
                margin-top: 50px;
                padding: 0;
            }
            .profile-header {
                padding: 20px 10px;
            }
            .profile-header img {
                width: 100px;
                height: 100px;
            }
            .stats {
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Home</a>
            <a href="profile.php?user=<?php echo $_SESSION['username']; ?>">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="main">
        <div class="profile-header">
            <img src="<?php echo $user['profile_pic']; ?>" alt="profile">
            <h2>@<?php echo $user['username']; ?></h2>
            <p><?php echo htmlspecialchars($user['bio'] ?? ''); ?></p>
            <div class="stats">
                <span><strong><?php echo $following_count; ?></strong> Following</span>
                <span><strong><?php echo $followers; ?></strong> Followers</span>
            </div>
            <?php if ($profile_user_id == $current_user_id): ?>
                <a class="edit-btn" href="edit_profile.php">Edit Profile</a>
            <?php else: ?>
                <button class="follow-btn" onclick="follow(<?php echo $profile_user_id; ?>)"><?php echo $following ? 'Unfollow' : 'Follow'; ?></button>
            <?php endif; ?>
        </div>
        <div id="tweets">
            <?php foreach ($tweets as $tweet): ?>
            <div class="tweet" id="tweet-<?php echo $tweet['id']; ?>">
                <img src="<?php echo $tweet['profile_pic']; ?>" alt="profile">
                <div class="tweet-content">
                    <span class="user">@<?php echo $tweet['username']; ?></span>
                    <span class="time"><?php echo $tweet['created_at']; ?></span>
                    <p><?php echo htmlspecialchars($tweet['content']); ?></p>
                    <div class="interactions">
                        <span onclick="like(<?php echo $tweet['id']; ?>)">Like</span>
                        <span onclick="toggleComments(<?php echo $tweet['id']; ?>)">Comment</span>
                        <?php if ($tweet['user_id'] == $current_user_id): ?>
                            <span onclick="editTweet(<?php echo $tweet['id']; ?>)">Edit</span>
                            <span onclick="deleteTweet(<?php echo $tweet['id']; ?>)">Delete</span>
                        <?php endif; ?>
                    </div>
                    <div id="comments-<?php echo $tweet['id']; ?>" class="comments-div" style="display:none;"></div>
                    <form id="comment-form-<?php echo $tweet['id']; ?>" class="comment-form" style="display:none;">
                        <input type="text" placeholder="Add a comment">
                        <button type="button" onclick="postComment(<?php echo $tweet['id']; ?>)">Post</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function follow(userId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'follow.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200 && xhr.responseText === 'success') {
                    location.reload();
                }
            };
            xhr.send('user_id=' + userId);
        }
        function like(tweetId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'like.php');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send('tweet_id=' + tweetId);
        }
        function toggleComments(tweetId) {
            const commentsDiv = document.getElementById('comments-' + tweetId);
            const form = document.getElementById('comment-form-' + tweetId);
            if (commentsDiv.style.display === 'none') {
                commentsDiv.style.display = 'block';
                form.style.display = 'flex';
                fetchComments(tweetId);
            } else {
                commentsDiv.style.display = 'none';
                form.style.display = 'none';
            }
        }
        function fetchComments(tweetId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_comments.php?tweet_id=' + tweetId);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('comments-' + tweetId).innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
        function postComment(tweetId) {
            const input = document.querySelector('#comment-form-' + tweetId + ' input');
            const content = input.value.trim();
            if (content) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'comment.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200 && xhr.responseText === 'success') {
                        input.value = '';
                        fetchComments(tweetId);
                    }
                };
                xhr.send('tweet_id=' + tweetId + '&content=' + encodeURIComponent(content));
            }
        }
        function deleteTweet(tweetId) {
            if (confirm('Are you sure you want to delete this tweet?')) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'delete_tweet.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200 && xhr.responseText === 'success') {
                        document.getElementById('tweet-' + tweetId).remove();
                    }
                };
                xhr.send('tweet_id=' + tweetId);
            }
        }
        function editTweet(tweetId) {
            location.href = 'edit_tweet.php?id=' + tweetId;
        }
    </script>
</body>
</html>
