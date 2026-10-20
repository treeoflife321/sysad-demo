<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Handle Delete Request
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // Fetch user to verify existence and check username
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$delete_id]);
    $target_user = $stmt->fetch();

    if ($target_user) {
        if ($target_user['username'] === $_SESSION['user']) {
            echo "<script>alert('You cannot delete your own active session account!'); window.location.href='main.php';</script>";
            exit;
        } else {
            $delete_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $delete_stmt->execute([$delete_id]);
            echo "<script>alert('User deleted successfully!'); window.location.href='main.php';</script>";
            exit;
        }
    }
}

// Handle Update Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $id = intval($_POST['user_id']);
    $new_username = trim($_POST['username']);
    $new_password = trim($_POST['password']);

    if (!empty($new_password)) {
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
        $stmt->execute([$new_username, $hashed, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->execute([$new_username, $id]);
    }
    echo "<script>alert('User updated successfully!'); window.location.href='main.php';</script>";
    exit;
}

// Fetch user to edit if edit parameter exists
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch();
}

// Fetch all users
$stmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container wide">
        <div class="top-bar">
            <a href="logout.php" class="btn btn-danger">Logout</a>
            <span>Logged in as: <strong><?= htmlspecialchars($_SESSION['user']) ?></strong></span>
        </div>

        <h2>Registered Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= $u['created_at'] ?></td>
                        <td>
                            <a href="main.php?edit=<?= $u['id'] ?>" class="btn">Edit</a>
                            <a href="main.php?delete=<?= $u['id'] ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you sure you want to delete user \'<?= htmlspecialchars($u['username']) ?>\'?');">
                               Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($edit_user): ?>
            <div class="edit-box">
                <h3>Edit User (ID: <?= $edit_user['id'] ?>)</h3>
                <form action="main.php" method="POST">
                    <input type="hidden" name="user_id" value="<?= $edit_user['id'] ?>">
                    <input type="text" name="username" value="<?= htmlspecialchars($edit_user['username']) ?>" required>
                    <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
                    <button type="submit" name="update_user" class="btn">Update User</button>
                    <a href="main.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>