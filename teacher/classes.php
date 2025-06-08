<?php
require_once '../includes/auth.php';
requireRole('teacher');

// Get teacher's assigned classes
$teacherId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT c.class_id, sub.subject_name, 
           s.section_name,
           c.school_year, c.semester
    FROM classes c
    JOIN subjects sub ON c.subject_id = sub.subject_id
    JOIN sections s ON c.section_id = s.section_id
    JOIN grade_levels g ON s.level_id = g.level_id
    WHERE c.teacher_id = ?
");
$stmt->execute([$teacherId]);
$classes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Classes</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'dashboard.php'; ?>
    
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">My Classes</h4>
            </div>
            <div class="card-body">
                <?php if (empty($classes)): ?>
                    <div class="alert alert-info">
                        You are not currently assigned to any classes.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Subject</th>
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
                                        <td><?= htmlspecialchars($class['section_name']) ?></td>
                                        <td><?= htmlspecialchars($class['school_year']) ?></td>
                                        <td><?= htmlspecialchars(ucfirst($class['semester'])) ?></td>
                                        <td>
                                            <a href="grades.php?class_id=<?= $class['class_id'] ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-journal-text"></i> View Grades
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
