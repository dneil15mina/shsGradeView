<?php
require_once '../includes/auth.php';
if (!isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit;
}
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../unauthorized.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
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
                        Admin Menu
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="users.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-people"></i> User Management
                        </a>
                        <a href="classes.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-journal-bookmark"></i> Class Management
                        </a>
                        <a href="manage_subjects.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-book"></i> Subject Management
                        </a>
                        <a href="manage_sections.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-layers"></i> Section Management
                        </a>
                        <a href="manage_enrollments.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-person-plus"></i> Manage Enrollments
                        </a>
                        <a href="manage_grade_levels.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-sort-numeric-up"></i> Grade Level Management
                        </a>
                        <a href="grade_periods.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-event"></i> Grade Periods
                        </a>
                        <a href="reports.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-graph-up"></i> Reports
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
                        <h4>System Overview</h4>
                    </div>
                    <div class="card-body">
                        <p>Welcome to the administration panel. Use the menu to manage different aspects of the system.</p>
                        
                        <?php
                        // Get user count
                        $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                        // Get class count
                        $classCount = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
                        // Get student count
                        $studentCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
                        ?>
                        <div class="row mt-3">
                            <div class="col-md-4 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h5><i class="bi bi-people"></i> Users</h5>
                                        <h3><?= $userCount ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5><i class="bi bi-journal-bookmark"></i> Classes</h5>
                                        <h3><?= $classCount ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5><i class="bi bi-mortarboard"></i> Students</h5>
                                        <h3><?= $studentCount ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
