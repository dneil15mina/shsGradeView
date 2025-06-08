<?php
require_once '../includes/auth.php';
requireRole('teacher');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Dashboard</title>
    <link rel="icon" href="../assets/images/school-logo.png" type="image/png">
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        Teacher Menu
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="classes.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-journal-bookmark"></i> My Classes
                        </a>
                        <a href="grades.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-pencil-square"></i> Enter Grades
                        </a>
                        <a href="profile.php" class="list-group-item list-group-item-action">
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
                        <h4>Teaching Overview</h4>
                    </div>
                    <div class="card-body">
                        <p>Welcome to your teacher portal. Use the menu to manage your classes and student grades.</p>
                        
                        <?php
                        // Get current teacher's assigned classes
                        $teacherId = $_SESSION['user_id'];
                        $stmt = $pdo->prepare("
                            SELECT c.class_id, sub.subject_name, 
                                   CONCAT(gl.level_name, ' - ', s.section_name) AS section_name,
                                   c.school_year, c.semester
                            FROM classes c
                            JOIN subjects sub ON c.subject_id = sub.subject_id
                            JOIN sections s ON c.section_id = s.section_id
                            JOIN grade_levels gl ON s.level_id = gl.level_id
                            WHERE c.teacher_id = ?
                            ORDER BY c.school_year DESC, c.semester
                        ");
                        $stmt->execute([$teacherId]);
                        $classes = $stmt->fetchAll();
                        
                        if (!empty($classes)) {
                            $message = "<div>You are assigned to these classes:</div>";
                            $message .= "<ul class='list-group mt-2'>";
                            foreach ($classes as $class) {
                                $message .= "<li class='list-group-item'>";
                                $message .= htmlspecialchars($class['subject_name']) . " - ";
                                $message .= htmlspecialchars($class['section_name']);
                                $message .= "</li>";
                            }
                            $message .= "</ul>";
                        } else {
                            $message = "You currently have no class assignments this year.";
                        }
                        ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <?= $message ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
