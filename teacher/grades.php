<?php
require_once '../includes/auth.php';
requireRole('teacher');

$classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

// Verify teacher is assigned to this class
$teacherId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM classes 
    WHERE class_id = ? AND teacher_id = ?
");
$stmt->execute([$classId, $teacherId]);
if ($stmt->fetchColumn() === 0) {
    header('Location: classes.php');
    exit;
}

// Get class info
$stmt = $pdo->prepare("
    SELECT sub.subject_name, gl.level_name, s.section_name
    FROM classes c
    JOIN subjects sub ON c.subject_id = sub.subject_id
    JOIN sections s ON c.section_id = s.section_id
    JOIN grade_levels gl ON s.level_id = gl.level_id
    WHERE c.class_id = ?
");
$stmt->execute([$classId]);
$class = $stmt->fetch();

// Get grade period settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('midterm_active', 'finals_active', 'show_computed_grades')");
$gradePeriods = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$midtermActive = $gradePeriods['midterm_active'] ?? 0;
$finalsActive = $gradePeriods['finals_active'] ?? 0;
$showComputed = $gradePeriods['show_computed_grades'] ?? 1;

// Get students in this class with their enrollment details
$stmt = $pdo->prepare("
    SELECT u.user_id, u.first_name, u.last_name, 
           e.enrollment_id, e.class_id,
           gr.midterm_grade, gr.final_grade,
           c.subject_id, c.section_id, c.school_year, c.semester,
           sub.subject_name, sec.section_name, gl.level_name
    FROM enrollment e
    JOIN users u ON e.student_id = u.user_id
    JOIN classes c ON e.class_id = c.class_id
    JOIN subjects sub ON c.subject_id = sub.subject_id
    JOIN sections sec ON c.section_id = sec.section_id
    JOIN grade_levels gl ON sec.level_id = gl.level_id
    LEFT JOIN grades gr ON e.enrollment_id = gr.enrollment_id
    WHERE e.class_id = ? AND c.teacher_id = ?
    ORDER BY u.last_name, u.first_name
");
$stmt->execute([$classId, $teacherId]);
$students = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $midtermGrades = $_POST['midterm'] ?? [];
    $finalGrades = $_POST['final'] ?? [];
    
    foreach ($students as $student) {
        $enrollmentId = $student['enrollment_id'];
        
        // Prepare grade data
        $midterm = isset($midtermGrades[$enrollmentId]) ? (int)$midtermGrades[$enrollmentId] : null;
        $final = isset($finalGrades[$enrollmentId]) ? (int)$finalGrades[$enrollmentId] : null;
        
        // Validate grades (0-100)
        if ($midterm !== null && ($midterm < 0 || $midterm > 100)) continue;
        if ($final !== null && ($final < 0 || $final > 100)) continue;
        
        // Update or insert grades
        if ($student['midterm_grade'] !== null || $student['final_grade'] !== null) {
            // Update existing record
            $stmt = $pdo->prepare("
                UPDATE grades 
                SET midterm_grade = ?, final_grade = ?, updated_by = ?
                WHERE enrollment_id = ?
            ");
            $stmt->execute([$midterm, $final, $teacherId, $enrollmentId]);
        } else {
            // Insert new record
            $stmt = $pdo->prepare("
                INSERT INTO grades (enrollment_id, midterm_grade, final_grade, updated_by)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$enrollmentId, $midterm, $final, $teacherId]);
        }
    }
    
    // Refresh student data
    $stmt->execute([$classId, $teacherId]);
    $students = $stmt->fetchAll();
    $success = 'Grades updated successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grade Management - <?= htmlspecialchars($class['subject']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .header { background: #333; color: white; padding: 15px; }
        .content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        input[type="number"] { width: 60px; padding: 5px; }
        .btn { padding: 8px 15px; background: #337ab7; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .success { color: green; margin-bottom: 15px; }
    </style>
</head>
<body>
    <?php include 'dashboard.php'; ?>
    
    <div class="content">
        <h3>Grade Management: <?= htmlspecialchars($class['subject_name']) ?> (<?= htmlspecialchars($class['level_name']) ?>-<?= htmlspecialchars($class['section_name']) ?>)</h3>
        
        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <?php if (!$midtermActive && !$finalsActive): ?>
                <div class="error">Both midterm and final grade encoding periods are currently inactive.</div>
            <?php elseif (!$midtermActive): ?>
                <div class="error">Midterm grade encoding is currently inactive.</div>
            <?php elseif (!$finalsActive): ?>
                <div class="error">Final grade encoding is currently inactive.</div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Midterm Grade</th>
                        <th>Finals Grade</th>
                        <?php if ($showComputed): ?>
                            <th>Final Computed Grade</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?></td>
                            <td>
                                <input type="number" name="midterm[<?= $student['enrollment_id'] ?>]" 
                                       value="<?= $student['midterm_grade'] ?? '' ?>" min="0" max="100"
                                       <?= !$midtermActive ? 'disabled' : '' ?>>
                            </td>
                            <td>
                                <input type="number" name="final[<?= $student['enrollment_id'] ?>]" 
                                       value="<?= $student['final_grade'] ?? '' ?>" min="0" max="100"
                                       <?= !$finalsActive ? 'disabled' : '' ?>>
                            </td>
                            <?php if ($showComputed): ?>
                                <td>
                                    <?php if (isset($student['midterm_grade']) && isset($student['final_grade']) && $student['final_grade'] !== null): ?>
                                        <?= round(($student['midterm_grade'] + $student['final_grade']) / 2) ?>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <button type="submit" class="btn">Save Grades</button>
            <a href="classes.php" class="btn" style="background: #6c757d;">Back to Classes</a>
        </form>
    </div>
</body>
</html>
