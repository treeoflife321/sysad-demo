<?php
require 'db.php';

$alert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $alert = "<script>alert('Username already taken.');</script>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $insert = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if ($insert->execute([$username, $hashed_password])) {
                echo "<script>
                        alert('New user added successfully!');
                        window.location.href = 'login.php';
                      </script>";
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?= $alert ?>
    <div class="container">
        <h2>Register User</h2>
        <form action="create.php" method="POST">
            <input type="text" name="username" placeholder="New Username" required>
            <input type="password" name="password" placeholder="New Password" required>
            <button type="submit" class="btn">Create User</button>
        </form>
        <div style="margin-top: 15px;">
            <a href="login.php" class="btn btn-secondary">Back to Login</a>
        </div>
    </div>
</body>
</html>