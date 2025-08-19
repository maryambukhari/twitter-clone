<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        echo '<script>alert("Username and password are required");</script>';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?)");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user) {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    echo '<script>location.href = "index.php";</script>';
                    exit;
                } else {
                    echo '<script>alert("Incorrect password");</script>';
                }
            } else {
                echo '<script>alert("Username not found");</script>';
            }
        } catch (PDOException $e) {
            echo '<script>alert("Database error: ' . addslashes($e->getMessage()) . '");</script>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Twitter Clone</title>
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
            max-width: 400px;
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
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccd6dd;
            border-radius: 25px;
            font-size: 16px;
            background: #f5f8fa;
            transition: border 0.3s;
        }
        input:focus {
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
        p {
            margin-top: 15px;
            font-size: 14px;
        }
        a {
            color: #1da1f2;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
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
        <h2>Login</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>No account? <a href="register.php">Register</a></p>
    </div>
</body>
</html>
