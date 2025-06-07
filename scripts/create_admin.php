<?php
require_once '../config/db_connect.php';

try {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn();

    if (!$adminExists) {
        // Create admin user
        $stmt = $pdo->prepare("
            INSERT INTO users 
            (username, password, first_name, last_name, role) 
            VALUES (?, ?, ?, ?, 'admin')
        ");
        $stmt->execute([
            'admin',
            password_hash('admin123', PASSWORD_DEFAULT),
            'Admin',
            'User'
        ]);
        echo "Admin user created successfully!";
    } else {
        echo "Admin user already exists";
    }
} catch (PDOException $e) {
    die("Error creating admin user: " . $e->getMessage());
}
?>
