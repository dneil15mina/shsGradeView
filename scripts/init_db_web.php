<?php
require_once '../config/db_connect.php';

$needsCommit = false;

try {
    // Check if system_settings exists
    // Check if semester column exists in classes table
    $stmt = $pdo->query("SHOW COLUMNS FROM classes LIKE 'semester'");
    if ($stmt->rowCount() == 0) {
        try {
            $pdo->exec("ALTER TABLE classes ADD COLUMN semester ENUM('1st', '2nd', 'summer') NOT NULL AFTER school_year");
            echo "Added semester column to classes table successfully!<br>";
            
            // Update existing records with default semester value
            $pdo->exec("UPDATE classes SET semester = '1st' WHERE semester IS NULL");
            echo "Set default semester values for existing classes<br>";
        } catch (PDOException $e) {
            echo "Error adding semester column: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "Semester column already exists in classes table<br>";
    }

    $stmt = $pdo->query("SHOW TABLES LIKE 'system_settings'");
    if ($stmt->rowCount() == 0) {
        $pdo->beginTransaction();
        $needsCommit = true;
        
        $pdo->exec("
            CREATE TABLE system_settings (
                setting_key VARCHAR(50) PRIMARY KEY,
                setting_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        $pdo->exec("
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES 
                ('midterm_active', '1'),
                ('finals_active', '1'),
                ('grade_rounding', 'round')
        ");
        
        if ($needsCommit) {
            $pdo->commit();
        }
        echo "System settings table created and initialized successfully!";
    } else {
        echo "System settings table already exists - no changes made.";
    }
} catch (PDOException $e) {
    if ($needsCommit && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error: " . $e->getMessage());
}
?>
