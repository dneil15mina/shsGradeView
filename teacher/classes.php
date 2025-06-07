<?php
require_once '../includes/auth.php';
requireRole('teacher');

// Get teacher's assigned classes
$teacherId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT c.class_id, sub.subject_name, gl.level_name, s.section_name, c.school_year, c.semester
    FROM classes c
    JOIN subjects sub ON c.subject_id = sub.subject_id
    JOIN sections s ON c.section_id = s.section_id
    JOIN grade_levels gl ON s.level_id = gl.level_id
    WHERE c.teacher_id = ?
");
$stmt->execute([$teacherId]);
$classes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Classes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .header { background: #333; color: white; padding: 15px; }
        .content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; text-decoration: none; border-radius: 3px; }
        .btn-primary { background: #337ab7; color: white; }
    </style>
</head>
<body>
    <?php include 'dashboard.php'; ?>
    
    <div class="content">
        <h3>My Classes</h3>
        
        <?php if (empty($classes)): ?>
            <p>You are not currently assigned to any classes.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Grade Level</th>
                        <th>Section</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?= htmlspecialchars($class['subject_name']) ?></td>
                            <td><?= htmlspecialchars($class['level_name']) ?></td>
                            <td><?= htmlspecialchars($class['section_name']) ?></td>
                            <td><?= htmlspecialchars($class['school_year']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($class['semester'])) ?></td>
                            <td>
                                <a href="grades.php?class_id=<?= $class['class_id'] ?>" class="btn btn-primary">View Grades</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
