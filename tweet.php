<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Handle tweet posting
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = trim($_POST['content']);
    $image = null;
    
    if (!empty($content)) {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($_FILES['image']['type'], $allowed_types)) {
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image = uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $image;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image = null;
                }
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO tweets (user_id, content, image, created_at) VALUES (?, ?, ?, NOW())");
        
        if ($stmt->execute([$user_id, $content, $image])) {
            $success = 'Tweet posted successfully!';
        } else {
            $error = 'Failed to post tweet. Please try again.';
        }
    } else {
        $error = 'Tweet content cannot be empty.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Twitter Clone - Compose Tweet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #000;
            color: #fff;
            line-height: 1.4;
            min-height: 100vh;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            min-height: 100vh;
            border-left: 1px solid #2f3336;
            border-right: 1px solid #2f3336;
        }
        
        .header {
            padding: 16px 20px;
            border-bottom: 1px solid #2f3336;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 10;
            background: rgba(0, 0, 0, 0.8);
        }
        
        .header h1 {
            font-size: 20px;
            font-weight: bold;
        }
        
        .back-btn {
            color: #1d9bf0;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.2s;
        }
        
        .back-btn:hover {
            background: rgba(29, 155, 240, 0.1);
        }
        
        .compose-section {
            padding: 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding: 16px;
            background: #16181c;
            border-radius: 12px;
        }
        
        .avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #1d9bf0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }
        
        .user-details h3 {
            font-size: 16px;
            margin-bottom: 4px;
        }
        
        .user-details p {
            color: #71767b;
            font-size: 14px;
        }
        
        .compose-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .compose-textarea {
            width: 100%;
            background: #16181c;
            border: 2px solid #2f3336;
            border-radius: 12px;
            color: #fff;
            font-size: 20px;
            padding: 20px;
            resize: vertical;
            min-height: 200px;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        
        .compose-textarea:focus {
            outline: none;
            border-color: #1d9bf0;
        }
        
        .compose-textarea::placeholder {
            color: #71767b;
        }
        
        .compose-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: #16181c;
            border-radius: 12px;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }
        
        .file-input {
            position: absolute;
            left: -9999px;
        }
        
        .file-input-label {
            color: #1d9bf0;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 20px;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .file-input-label:hover {
            background: rgba(29, 155, 240, 0.1);
        }
        
        .char-counter {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .char-count {
            font-size: 14px;
            color: #71767b;
        }
        
        .char-count.warning {
            color: #ffd400;
        }
        
        .char-count.danger {
            color: #f4212e;
        }
        
        .tweet-btn {
            background: #1d9bf0;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 24px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s;
            min-width: 80px;
        }
        
        .tweet-btn:hover {
            background: #1a8cd8;
        }
        
        .tweet-btn:disabled {
            background: #0f4e78;
            cursor: not-allowed;
        }
        
        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        
        .alert.success {
            background: rgba(0, 186, 124, 0.1);
            border: 1px solid #00ba7c;
            color: #00ba7c;
        }
        
        .alert.error {
            background: rgba(244, 33, 46, 0.1);
            border: 1px solid #f4212e;
            color: #f4212e;
        }
        
        .image-preview {
            max-width: 100%;
            border-radius: 12px;
            margin-top: 12px;
        }
        
        @media (max-width: 768px) {
            .container {
                border: none;
            }
            
            .header, .compose-section {
                padding: 16px;
            }
            
            .compose-textarea {
                font-size: 18px;
                min-height: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Compose Tweet</h1>
            <a href="home.php" class="back-btn">Cancel</a>
        </header>

        <div class="compose-section">
            <div class="user-info">
                <div class="avatar">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                </div>
            </div>
            
            <?php if (isset($success)): ?>
            <div class="alert success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
            <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form class="compose-form" method="POST" enctype="multipart/form-data">
                <textarea 
                    name="content" 
                    class="compose-textarea" 
                    placeholder="What's happening?"
                    maxlength="280"
                    id="tweetContent"
                    required
                ></textarea>
                
                <div class="compose-options">
                    <div class="file-input-wrapper">
                        <input type="file" name="image" accept="image/*" class="file-input" id="imageInput">
                        <label for="imageInput" class="file-input-label">
                            📷 Add Photo
                        </label>
                    </div>
                    
                    <div class="char-counter">
                        <span class="char-count" id="charCount">0/280</span>
                        <button type="submit" class="tweet-btn" id="tweetBtn" disabled>Tweet</button>
                    </div>
                </div>
                
                <div id="imagePreview"></div>
            </form>
        </div>
    </div>

    <script>
        const textarea = document.getElementById('tweetContent');
        const charCount = document.getElementById('charCount');
        const tweetBtn = document.getElementById('tweetBtn');
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');

        // Character counter
        textarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count + '/280';
            
            // Update button state
            tweetBtn.disabled = count === 0 || count > 280;
            
            // Update counter color
            if (count > 280) {
                charCount.className = 'char-count danger';
            } else if (count > 260) {
                charCount.className = 'char-count warning';
            } else {
                charCount.className = 'char-count';
            }
        });

        // Image preview
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            imagePreview.innerHTML = '';
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    imagePreview.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
        });

        // Auto-resize textarea
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 400) + 'px';
        });
    </script>
</body>
</html>
