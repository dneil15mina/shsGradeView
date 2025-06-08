<?php
require_once '../includes/auth.php';
requireRole('student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard</title>
    <link rel="icon" href="../assets/images/school-logo.png" type="image/png">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .grade-table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        Student Menu
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="dashboard.php" class="list-group-item list-group-item-action active">
                            <i class="bi bi-journal-bookmark"></i> My Grades
                        </a>
                        <a href="../profile.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-person"></i> My Profile
                        </a>
                        <a href="../password_change.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-shield-lock"></i> Change Password
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4>My Grades</h4>
                    </div>
                    <div class="card-body">
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
                        
                        <div class="table-responsive">
                            <table class="table table-bordered grade-table">
                                <thead class="table-light">
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
