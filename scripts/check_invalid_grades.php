<?php
require_once 'c:/xampp/htdocs/shsGradeView/config/db_connect.php';

// Find grades with invalid enrollment references
$stmt = $pdo->query('
    SELECT g.* 
    FROM grades g 
    LEFT JOIN enrollment e ON g.enrollment_id = e.enrollment_id 
    WHERE e.enrollment_id IS NULL
');
$invalidGrades = $stmt->fetchAll();

echo "Invalid Grade References:\n";
print_r($invalidGrades);

// Count total invalid records
echo "\nTotal invalid grade records: " . count($invalidGrades) . "\n";
?>
