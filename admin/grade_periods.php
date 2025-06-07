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
<html>
<head>
    <title>Grade Period Management</title>
    <style>
        .form-group { margin-bottom: 15px; }
        .checkbox-label { display: flex; align-items: center; }
        .checkbox-label input { margin-right: 8px; }
    </style>
</head>
<body>
    <?php include 'dashboard.php'; ?>
    
    <h1>Grade Period Settings</h1>
    
    <?php if (isset($success)): ?>
        <div style="color:green; margin-bottom:15px;"><?= $success ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="midterm_active" <?= ($settings['midterm_active'] ?? 0) ? 'checked' : '' ?>>
                Enable Midterm Grade Encoding
            </label>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="finals_active" <?= ($settings['finals_active'] ?? 0) ? 'checked' : '' ?>>
                Enable Final Grade Encoding
            </label>
        </div>
        
        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="show_computed_grades" <?= ($settings['show_computed_grades'] ?? 1) ? 'checked' : '' ?>>
                Show Computed Final Grades
            </label>
        </div>
        
        <button type="submit">Save Settings</button>
    </form>
</body>
</html>
