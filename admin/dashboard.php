<?php
require_once '../includes/auth.php';
requireRole('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
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
        <h2>Admin Dashboard</h2>
        <div class="admin-links" style="margin: 15px 0;">
            <a href="manage_grade_levels.php" class="btn">Manage Grade Levels</a>
            <a href="manage_sections.php" class="btn">Manage Sections</a>
            <a href="manage_subjects.php" class="btn">Manage Subjects</a>
            <a href="add_class.php" class="btn">Add Class</a>
            <a href="grade_periods.php" class="btn">Grade Periods</a>
        </div>
        <div>
            <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="../logout.php" style="color: white; margin-left: 15px;">Logout</a>
        </div>
    </div>
    
    <div class="sidebar">
        <div class="menu-item"><a href="users.php">Manage Users</a></div>
        <div class="menu-item"><a href="classes.php">Manage Classes</a></div>
        <div class="menu-item"><a href="grades.php">Grade Settings</a></div>
        <div class="menu-item"><a href="reports.php">Reports</a></div>
    </div>
    
    <div class="content">
        <h3>System Overview</h3>
        <p>Welcome to the administration panel. Use the menu to manage different aspects of the system.</p>
    </div>
</body>
</html>
