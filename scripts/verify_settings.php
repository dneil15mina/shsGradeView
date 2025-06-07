<?php
require_once '../config/db_connect.php';

try {
    // Verify table structure
    $stmt = $pdo->query("DESCRIBE system_settings");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['setting_key', 'setting_value', 'updated_at'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (!empty($missingColumns)) {
        die("Missing columns in system_settings: " . implode(', ', $missingColumns));
    }

    // Verify required settings exist
    $requiredSettings = ['midterm_active', 'finals_active', 'grade_rounding'];
    $stmt = $pdo->prepare("SELECT setting_key FROM system_settings WHERE setting_key IN (?, ?, ?)");
    $stmt->execute($requiredSettings);
    $existingSettings = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $missingSettings = array_diff($requiredSettings, $existingSettings);
    if (!empty($missingSettings)) {
        die("Missing required settings: " . implode(', ', $missingSettings));
    }

    echo "System settings table is properly configured!";
} catch (PDOException $e) {
    die("Verification failed: " . $e->getMessage());
}
?>
