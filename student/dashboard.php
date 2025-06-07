<?php
require_once '../includes/auth.php';
requireRole('student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .header { background: #333; color: white; padding: 15px; display: flex; justify-content: space-between; }
        .sidebar { width: 200px; background: #f4f4f4; height: 100vh; float: left; }
        .content { margin-left: 200px; padding: 20px; }
        .menu-item { padding: 10px 15px; border-bottom: 1px solid #ddd; }
        .menu-item:hover { background: #ddd; }
        .menu-item a { text-decoration: none; color: #333; display: block; }
        .grade-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .grade-table th, .grade-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .grade-table th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Student Dashboard</h2>
        <div>
            <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="../logout.php" style="color: white; margin-left: 15px;">Logout</a>
        </div>
    </div>
    
    <div class="sidebar">
        <div class="menu-item"><a href="grades.php">View Grades</a></div>
        <div class="menu-item"><a href="profile.php">My Profile</a></div>
        <div class="menu-item"><a href="password.php">Change Password</a></div>
    </div>
    
    <div class="content">
        <h3>My Grades</h3>
        <p>Welcome to your student portal. Here you can view your grades and profile information.</p>
        
        <?php
        // Get student's grades and settings
        $studentId = $_SESSION['user_id'];
        $showComputed = $pdo->query("SELECT setting_value FROM system_settings WHERE setting_key = 'show_computed_grades'")->fetchColumn();
        $showComputed = $showComputed !== false ? (int)$showComputed : 1;
        
        $stmt = $pdo->prepare("
            SELECT sub.subject_name, g.midterm_grade, g.final_grade
            ".($showComputed ? ", FLOOR((COALESCE(g.midterm_grade, 0) + COALESCE(g.final_grade, 0)) / 2) AS average_grade" : "")."
            FROM enrollment e
            JOIN classes c ON e.class_id = c.class_id
            JOIN subjects sub ON c.subject_id = sub.subject_id
            LEFT JOIN grades g ON e.enrollment_id = g.enrollment_id
            WHERE e.student_id = ?
            ORDER BY sub.subject_name
        ");
        $stmt->execute([$studentId]);
        $grades = $stmt->fetchAll();
        ?>
        
        <table class="grade-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Midterm</th>
                    <th>Final</th>
                    <?php if ($showComputed): ?>
                        <th>Final Grade</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grades as $grade): ?>
                    <tr>
                        <td><?= htmlspecialchars($grade['subject_name']) ?></td>
                        <td><?= $grade['midterm_grade'] ?? '-' ?></td>
                        <td><?= $grade['final_grade'] ?? '-' ?></td>
                        <?php if ($showComputed): ?>
                            <td><?= $grade['average_grade'] ?? '-' ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
