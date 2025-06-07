<?php
require_once '../includes/auth.php';
requireRole('admin');

$errors = [];
$success = '';

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $role = $_POST['role'] ?? '';
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 4) {
        $errors[] = 'Username must be at least 4 characters';
    }

    if (!empty($password) && strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    } elseif (!empty($password) && $password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    if (empty($firstName) || empty($lastName)) {
        $errors[] = 'First and last name are required';
    }

    if (!in_array($role, ['admin', 'teacher', 'student'])) {
        $errors[] = 'Invalid role selected';
    }

    // Check if username exists (excluding current user)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND user_id != ?");
    $stmt->execute([$username, $userId]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Username already exists';
    }

    if (empty($errors)) {
        // Prepare update query
        $query = "UPDATE users SET username = ?, first_name = ?, last_name = ?, role = ?, is_active = ?";
        $params = [$username, $firstName, $lastName, $role, $isActive];

        // Add password update if provided
        if (!empty($password)) {
            $query .= ", password = ?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }

        $query .= " WHERE user_id = ?";
        $params[] = $userId;

        $stmt = $pdo->prepare($query);
        if ($stmt->execute($params)) {
            $success = 'User updated successfully!';
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
        } else {
            $errors[] = 'Failed to update user';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .header { background: #333; color: white; padding: 15px; }
        .content { padding: 20px; max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"], select { width: 100%; padding: 8px; box-sizing: border-box; }
        .error { color: red; margin-bottom: 15px; }
        .success { color: green; margin-bottom: 15px; }
        .btn { padding: 8px 15px; background: #337ab7; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include 'dashboard.php'; ?>
    
    <div class="content">
        <h3>Edit User: <?= htmlspecialchars($user['username']) ?></h3>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">New Password (leave blank to keep current)</label>
                <input type="password" id="password" name="password">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
            
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="teacher" <?= $user['role'] === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                    <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" <?= $user['is_active'] ? 'checked' : '' ?>>
                    Active
                </label>
            </div>
            
            <button type="submit" class="btn">Update User</button>
            <a href="users.php" class="btn" style="background: #6c757d;">Cancel</a>
        </form>
    </div>
</body>
</html>
