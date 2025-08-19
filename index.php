<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo '<script>alert("Please log in"); location.href = "login.php";</script>';
    exit;
}

if (!file_exists('db.php')) {
    die("Error: db.php file not found. Please ensure it exists in the project directory.");
}

include 'db.php';
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT t.*, u.username, u.profile_pic, 
               (SELECT COUNT(*) FROM likes l WHERE l.tweet_id = t.id) AS like_count,
               (SELECT COUNT(*) FROM likes l WHERE l.tweet_id = t.id AND l.user_id = ?) AS is_liked
        FROM tweets t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.user_id = ? OR t.user_id IN (SELECT followed_id FROM follows WHERE follower_id = ?) 
        ORDER BY t.created_at DESC LIMIT 50
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $tweets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Twitter Clone</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f8f9fa;
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
        .tweet-box {
            background: #fff;
            padding: 15px;
            border-bottom: 1px solid #e6ecf0;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        .tweet-box textarea {
            width: 100%;
            border: none;
            resize: none;
            font-size: 18px;
            background: transparent;
            outline: none;
            min-height: 50px;
            padding: 10px;
        }
        .tweet-box button {
            align-self: flex-end;
            background: #1da1f2;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }
        .tweet-box button:hover {
            background: #0c84d1;
            transform: scale(1.05);
        }
        .tweet {
            background: #fff;
            border-bottom: 1px solid #e6ecf0;
            padding: 15px;
            display: flex;
            border-radius: 8px;
            margin: 10px 0;
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
        .interactions .liked {
            color: #e0245e;
            font-weight: bold;
        }
        .interactions .like-icon::before {
            content: '❤️';
            margin-right: 5px;
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
            transition: background 0.3s;
        }
        .comment-form button:hover {
            background: #0c84d1;
        }
        @media (max-width: 600px) {
            .main {
                margin-top: 50px;
                padding: 0;
            }
            header {
                padding: 10px;
            }
            nav a {
                margin: 0 10px;
                font-size: 14px;
            }
            .tweet {
                padding: 10px;
            }
            .tweet-box {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Home</a>
            <a href="profile.php?user=<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="main">
        <div class="tweet-box">
            <textarea id="tweet-input" placeholder="What's happening?"></textarea>
            <button onclick="postTweet()">Tweet</button>
        </div>
        <div id="feed">
            <?php if (empty($tweets)): ?>
                <p>No tweets to display. Follow users or post a tweet!</p>
            <?php else: ?>
                <?php foreach ($tweets as $tweet): ?>
                <div class="tweet" id="tweet-<?php echo $tweet['id']; ?>">
                    <img src="<?php echo htmlspecialchars($tweet['profile_pic'] ?? 'uploads/default.jpg'); ?>" alt="Profile picture">
                    <div class="tweet-content">
                        <span class="user">@<?php echo htmlspecialchars($tweet['username'] ?? 'Unknown'); ?></span>
                        <span class="time"><?php echo $tweet['created_at'] ?? ''; ?></span>
                        <p><?php echo htmlspecialchars($tweet['content'] ?? ''); ?></p>
                        <div class="interactions">
                            <span onclick="like(<?php echo $tweet['id']; ?>)" class="like-btn <?php echo $tweet['is_liked'] ? 'liked' : ''; ?>">
                                <span class="like-icon"></span>
                                <?php echo $tweet['like_count']; ?> Like<?php echo $tweet['like_count'] == 1 ? '' : 's'; ?>
                            </span>
                            <span onclick="toggleComments(<?php echo $tweet['id']; ?>)">Comment</span>
                            <?php if ($tweet['user_id'] == $user_id): ?>
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
            <?php endif; ?>
        </div>
    </div>
    <script>
        function postTweet() {
            const content = document.getElementById('tweet-input').value.trim();
            if (!content) {
                alert('Tweet cannot be empty');
                return;
            }
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'post_tweet.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    let response;
                    try {
                        response = JSON.parse(xhr.responseText.trim());
                        if (response.status === 'success') {
                            document.getElementById('tweet-input').value = '';
                            location.reload(); // Refresh to show new tweet
                        } else {
                            alert('Failed to post tweet: ' + (response.message || 'Unknown error'));
                        }
                    } catch (e) {
                        alert('Invalid response from server: ' + xhr.responseText);
                    }
                } else {
                    alert('Failed to post tweet: HTTP ' + xhr.status);
                }
            };
            xhr.onerror = function() {
                alert('Request failed. Please check your network and try again.');
            };
            xhr.send('content=' + encodeURIComponent(content));
        }

        function like(tweetId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'like.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText.trim());
                        if (response.status === 'success') {
                            location.reload(); // Refresh to update like count
                        } else {
                            alert('Failed to like tweet: ' + (response.message || 'Unknown error'));
                        }
                    } catch (e) {
                        alert('Invalid response from server: ' + xhr.responseText);
                    }
                } else {
                    alert('Failed to like tweet: HTTP ' + xhr.status);
                }
            };
            xhr.onerror = function() {
                alert('Request failed. Please check your network and try again.');
            };
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
            xhr.open('GET', 'get_comments.php?tweet_id=' + tweetId, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('comments-' + tweetId).innerHTML = xhr.responseText;
                } else {
                    alert('Failed to fetch comments: HTTP ' + xhr.status);
                }
            };
            xhr.onerror = function() {
                alert('Request failed. Please check your network and try again.');
            };
            xhr.send();
        }

        function postComment(tweetId) {
            const input = document.querySelector('#comment-form-' + tweetId + ' input');
            const content = input.value.trim();
            if (!content) {
                alert('Comment cannot be empty');
                return;
            }
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'comment.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText.trim());
                        if (response.status === 'success') {
                            input.value = '';
                            fetchComments(tweetId);
                        } else {
                            alert('Failed to post comment: ' + (response.message || 'Unknown error'));
                        }
                    } catch (e) {
                        alert('Invalid response from server: ' + xhr.responseText);
                    }
                } else {
                    alert('Failed to post comment: HTTP ' + xhr.status);
                }
            };
            xhr.onerror = function() {
                alert('Request failed. Please check your network and try again.');
            };
            xhr.send('tweet_id=' + tweetId + '&content=' + encodeURIComponent(content));
        }

        function deleteTweet(tweetId) {
            if (!confirm('Are you sure you want to delete this tweet?')) return;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'delete_tweet.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText.trim());
                        if (response.status === 'success') {
                            document.getElementById('tweet-' + tweetId).remove();
                        } else {
                            alert('Failed to delete tweet: ' + (response.message || 'Unknown error'));
                        }
                    } catch (e) {
                        alert('Invalid response from server: ' + xhr.responseText);
                    }
                } else {
                    alert('Failed to delete tweet: HTTP ' + xhr.status);
                }
            };
            xhr.onerror = function() {
                alert('Request failed. Please check your network and try again.');
            };
            xhr.send('tweet_id=' + tweetId);
        }

        function editTweet(tweetId) {
            location.href = 'edit_tweet.php?id=' + tweetId;
        }

        // Auto-refresh every 60 seconds to show new tweets
        setInterval(() => {
            location.reload();
        }, 60000);
    </script>
</body>
</html>
