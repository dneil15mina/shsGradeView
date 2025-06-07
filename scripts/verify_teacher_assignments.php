<?php
require_once '../config/db_connect.php';

// Get all teachers and their assigned classes
$teachers = $pdo->query("
    SELECT u.user_id, u.first_name, u.last_name, 
           COUNT(c.class_id) AS class_count
    FROM users u
    LEFT JOIN classes c ON u.user_id = c.teacher_id
    WHERE u.role = 'teacher'
    GROUP BY u.user_id
")->fetchAll();

echo "<h2>Teacher Assignments Report</h2>";
echo "<table border='1'>";
echo "<tr><th>Teacher ID</th><th>Name</th><th>Classes Assigned</th></tr>";

foreach ($teachers as $teacher) {
    echo "<tr>";
    echo "<td>{$teacher['user_id']}</td>";
    echo "<td>{$teacher['first_name']} {$teacher['last_name']}</td>";
    echo "<td>{$teacher['class_count']}</td>";
    echo "</tr>";
    
    // Show detailed classes if any
    if ($teacher['class_count'] > 0) {
        $classes = $pdo->prepare("
            SELECT c.class_id, s.subject_name, 
                   CONCAT(gl.level_name, ' - ', sec.section_name) AS class_info,
                   c.school_year, c.semester
            FROM classes c
            JOIN subjects s ON c.subject_id = s.subject_id
            JOIN sections sec ON c.section_id = sec.section_id
            JOIN grade_levels gl ON sec.level_id = gl.level_id
            WHERE c.teacher_id = ?
        ");
        $classes->execute([$teacher['user_id']]);
        
        foreach ($classes->fetchAll() as $class) {
            echo "<tr><td colspan='2'></td>";
            echo "<td>";
            echo "{$class['subject_name']} - {$class['class_info']}";
            echo " ({$class['school_year']}, {$class['semester']})";
            echo "</td></tr>";
        }
    }
}

echo "</table>";
?>
