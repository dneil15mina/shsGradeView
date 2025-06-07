<?php
require_once '../config/db_connect.php';

try {
    // Create sample users
    $users = [
        ['admin', password_hash('admin123', PASSWORD_DEFAULT), 'Admin', 'User', 'admin'],
        ['teacher1', password_hash('teacher123', PASSWORD_DEFAULT), 'John', 'Doe', 'teacher'],
        ['teacher2', password_hash('teacher123', PASSWORD_DEFAULT), 'Jane', 'Smith', 'teacher'],
        ['student1', password_hash('student123', PASSWORD_DEFAULT), 'Alice', 'Johnson', 'student'],
        ['student2', password_hash('student123', PASSWORD_DEFAULT), 'Bob', 'Williams', 'student']
    ];

    foreach ($users as $user) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute($user);
    }

    // Create sample enrollments and grades
    $pdo->exec("INSERT INTO enrollment (student_id, class_id, date_enrolled) VALUES (4, 1, CURDATE())");
    $pdo->exec("INSERT INTO enrollment (student_id, class_id, date_enrolled) VALUES (5, 1, CURDATE())");
    $pdo->exec("INSERT INTO enrollment (student_id, class_id, date_enrolled) VALUES (4, 2, CURDATE())");
    
    $pdo->exec("INSERT INTO grades (enrollment_id, midterm_grade, final_grade, updated_by) VALUES (1, 88, 92, 2)");
    $pdo->exec("INSERT INTO grades (enrollment_id, midterm_grade, final_grade, updated_by) VALUES (2, 85, 89, 2)");
    $pdo->exec("INSERT INTO grades (enrollment_id, midterm_grade, final_grade, updated_by) VALUES (3, 90, 94, 3)");

    echo "Database seeded successfully with sample data!\n";
} catch (PDOException $e) {
    die("Database seeding failed: " . $e->getMessage());
}
?>
