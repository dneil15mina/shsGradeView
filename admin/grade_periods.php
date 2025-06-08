<?php
require_once '../includes/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $midtermActive = isset($_POST['midterm_active']) ? 1 : 0;
    $finalsActive = isset($_POST['finals_active']) ? 1 : 0;
    $showComputed = isset($_POST['show_computed_grades']) ? 1 : 0;

    // Update settings
    $pdo->prepare("
        INSERT INTO system_settings (setting_key, setting_value) 
        VALUES ('midterm_active', ?), ('finals_active', ?), ('show_computed_grades', ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ")->execute([$midtermActive, $finalsActive, $showComputed]);

    $success = "Grade period settings updated successfully!";
}

// Get current settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('midterm_active', 'finals_active', 'show_computed_grades')");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Grade Period Management</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'dashboard.php'; ?>
    
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Grade Period Settings</h4>
            </div>
            <div class="card-body">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" 
                               id="midtermActive" name="midterm_active" 
                               <?= ($settings['midterm_active'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="midtermActive">
                            Enable Midterm Grade Encoding
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" 
                               id="finalsActive" name="finals_active" 
                               <?= ($settings['finals_active'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="finalsActive">
                            Enable Final Grade Encoding
                        </label>
                    </div>
                    
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" role="switch" 
                               id="showComputed" name="show_computed_grades" 
                               <?= ($settings['show_computed_grades'] ?? 1) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="showComputed">
                            Show Computed Final Grades
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
