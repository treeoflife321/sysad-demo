<?php
session_start();
require 'db.php';

$alert = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            $alert = "<script>alert('User does not exist.');</script>";
        } elseif (!password_verify($password, $user['password'])) {
            $alert = "<script>alert('Incorrect password.');</script>";
        } else {
            $_SESSION['user'] = $user['username'];
            echo "<script>
                    alert('Login successful!');
                    window.location.href = 'main.php';
                  </script>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?= $alert ?>
    <div class="container">
        <h2>Login</h2>
        <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Login</button>
        </form>
        <div style="margin-top: 15px;">
            <a href="create.php" class="btn btn-secondary">Create Account</a>
        </div>
    </div>
</body>
</html>