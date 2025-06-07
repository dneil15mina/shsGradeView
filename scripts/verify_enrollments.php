<?php
require_once '../config/db_connect.php';

// Get teacher's classes and enrollments
$teacherId = 2; // teacher1's ID from seed data
$stmt = $pdo->prepare("
    SELECT c.class_id, sub.subject_name, gl.level_name, s.section_name,
           COUNT(e.enrollment_id) AS student_count
    FROM classes c
    JOIN subjects sub ON c.subject_id = sub.subject_id
    JOIN sections s ON c.section_id = s.section_id
    JOIN grade_levels gl ON s.level_id = gl.level_id
    LEFT JOIN enrollment e ON c.class_id = e.class_id
    WHERE c.teacher_id = ?
    GROUP BY c.class_id
");
$stmt->execute([$teacherId]);
$classes = $stmt->fetchAll();

echo "Teacher's Classes and Enrollment Count:\n";
print_r($classes);

// Get detailed enrollment for first class
if (!empty($classes)) {
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.first_name, u.last_name
        FROM enrollment e
        JOIN users u ON e.student_id = u.user_id
        WHERE e.class_id = ?
    ");
    $stmt->execute([$classes[0]['class_id']]);
    echo "\nStudents in first class:\n";
    print_r($stmt->fetchAll());
}
?>
