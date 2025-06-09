<?php
require_once 'c:/xampp/htdocs/shsGradeView/config/db_connect.php';

// Get class ID from command line argument
$classId = isset($argv[1]) ? (int)$argv[1] : 0;
if ($classId === 0) {
    die("Please provide a class ID as argument\n");
}

// Get enrollment records for the class
$stmt = $pdo->prepare("
    SELECT e.enrollment_id, u.user_id, u.first_name, u.last_name
    FROM enrollment e
    JOIN users u ON e.student_id = u.user_id
    WHERE e.class_id = ?
");
$stmt->execute([$classId]);
$enrollments = $stmt->fetchAll();

echo "Enrollment Records for Class $classId:\n";
print_r($enrollments);

// Check for any grades referencing these enrollments
$stmt = $pdo->prepare("
    SELECT g.grade_id, g.enrollment_id, g.midterm_grade, g.final_grade
    FROM grades g
    JOIN enrollment e ON g.enrollment_id = e.enrollment_id
    WHERE e.class_id = ?
");
$stmt->execute([$classId]);
$grades = $stmt->fetchAll();

echo "\nGrade Records for Class $classId:\n";
print_r($grades);
?>
