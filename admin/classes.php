<?php
require_once '../includes/auth.php';
requireRole('admin');

// Get all classes with teacher and section info
$stmt = $pdo->query("
    SELECT c.class_id, c.school_year, c.semester,
           s.subject_name, 
           CONCAT(t.first_name, ' ', t.last_name) AS teacher_name,
           CONCAT(gl.level_name, ' - ', sec.section_name) AS class_info
    FROM classes c
    JOIN subjects s ON c.subject_id = s.subject_id
    JOIN users t ON c.teacher_id = t.user_id
    JOIN sections sec ON c.section_id = sec.section_id
    JOIN grade_levels gl ON sec.level_id = gl.level_id
    ORDER BY c.school_year DESC, s.subject_name
");
$classes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Classes</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; text-decoration: none; }
        .btn-primary { background: #337ab7; color: white; }
    </style>
</head>
<body>
    <?php include 'dashboard.php'; ?>
    
    <div class="content">
        <h3>Manage Classes</h3>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Class</th>
                    <th>School Year</th>
                    <th>Semester</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                <tr>
                    <td><?= $class['class_id'] ?></td>
                    <td><?= htmlspecialchars($class['subject_name']) ?></td>
                    <td><?= htmlspecialchars($class['teacher_name']) ?></td>
                    <td><?= htmlspecialchars($class['class_info']) ?></td>
                    <td><?= htmlspecialchars($class['school_year']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($class['semester'])) ?></td>
                    <td>
                        <a href="edit_class.php?id=<?= $class['class_id'] ?>" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <a href="add_class.php" class="btn btn-primary">Add New Class</a>
    </div>
</body>
</html>
