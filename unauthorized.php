<?php
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unauthorized Access</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #d9534f; }
        .container { max-width: 600px; margin: 0 auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #337ab7; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Access Denied</h1>
        <p>You don't have permission to access this page.</p>
        <?php if (isLoggedIn()): ?>
            <a href="<?= $_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : ($_SESSION['role'] === 'teacher' ? 'teacher/dashboard.php' : 'student/dashboard.php') ?>" class="btn">Return to Dashboard</a>
        <?php else: ?>
            <a href="login.php" class="btn">Login</a>
        <?php endif; ?>
    </div>
</body>
</html>
