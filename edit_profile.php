<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo '<script>location.href = "login.php";</script>';
    exit;
}
include 'db.php';
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = $_POST['bio'];
    $profile_pic = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $profile_pic = $target_file;
        }
    }
    $sql = "UPDATE users SET bio = ?";
    $params = [$bio];
    if ($profile_pic) {
        $sql .= ", profile_pic = ?";
        $params[] = $profile_pic;
    }
    $sql .= " WHERE id = ?";
    $params[] = $user_id;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo '<script>location.href = "profile.php?user=' . $_SESSION['username'] . '";</script>';
}
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Profile - Twitter Clone</title>
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
        input[type="file"] {
            margin: 10px 0;
            width: 100%;
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
            min-height: 100px;
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
        <h2>Edit Profile</h2>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="profile_pic" accept="image/*">
            <textarea name="bio" placeholder="Bio"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</body>
</html>
