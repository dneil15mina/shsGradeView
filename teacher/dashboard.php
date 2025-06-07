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
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> You have 0 classes assigned this semester.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
