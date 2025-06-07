<?php
require_once '../config/db_connect.php';

// Get all enrollments with class and student info
$stmt = $pdo->query("
    SELECT 
        e.enrollment_id,
        c.class_id,
        sub.subject_name,
        gl.level_name,
        sec.section_name,
        u.user_id AS student_id,
        u.first_name,
        u.last_name,
        t.user_id AS teacher_id,
        t.first_name AS teacher_first,
        t.last_name AS teacher_last
    FROM enrollment e
    JOIN classes c ON e.class_id = c.class_id
    JOIN subjects sub ON c.subject_id = sub.subject_id
    JOIN sections sec ON c.section_id = sec.section_id
    JOIN grade_levels gl ON sec.level_id = gl.level_id
    JOIN users u ON e.student_id = u.user_id
    JOIN users t ON c.teacher_id = t.user_id
    ORDER BY c.class_id, u.last_name
");
$enrollments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enrollment Verification</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Enrollment Verification</h1>
    <table>
        <tr>
            <th>Class ID</th>
            <th>Subject</th>
            <th>Level</th>
            <th>Section</th>
            <th>Teacher</th>
            <th>Student ID</th>
            <th>Student Name</th>
        </tr>
        <?php foreach ($enrollments as $e): ?>
        <tr>
            <td><?= $e['class_id'] ?></td>
            <td><?= htmlspecialchars($e['subject_name']) ?></td>
            <td><?= htmlspecialchars($e['level_name']) ?></td>
            <td><?= htmlspecialchars($e['section_name']) ?></td>
            <td><?= htmlspecialchars($e['teacher_last'] . ', ' . $e['teacher_first']) ?></td>
            <td><?= $e['student_id'] ?></td>
            <td><?= htmlspecialchars($e['last_name'] . ', ' . $e['first_name']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
