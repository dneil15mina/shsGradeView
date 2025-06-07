<?php
require_once '../includes/auth.php';
requireRole('teacher');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .header { background: #333; color: white; padding: 15px; display: flex; justify-content: space-between; }
        .sidebar { width: 200px; background: #f4f4f4; height: 100vh; float: left; }
        .content { margin-left: 200px; padding: 20px; }
        .menu-item { padding: 10px 15px; border-bottom: 1px solid #ddd; }
        .menu-item:hover { background: #ddd; }
        .menu-item a { text-decoration: none; color: #333; display: block; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Teacher Dashboard</h2>
        <div>
            <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="../logout.php" style="color: white; margin-left: 15px;">Logout</a>
        </div>
    </div>
    
    <div class="sidebar">
        <div class="menu-item"><a href="classes.php">My Classes</a></div>
        <div class="menu-item"><a href="grades.php">Enter Grades</a></div>
        <div class="menu-item"><a href="password.php">Change Password</a></div>
    </div>
    
    <div class="content">
        <h3>Teaching Overview</h3>
        <p>Welcome to your teacher portal. Use the menu to manage your classes and student grades.</p>
    </div>
</body>
</html>
