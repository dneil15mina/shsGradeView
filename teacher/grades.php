<?php
// Completely rewritten version to bypass cache issues
require_once '../includes/auth.php';
requireRole('teacher');

// Get class ID from URL
$classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
$teacherId = $_SESSION['user_id'];

// Verify teacher assignment
$stmt = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE class_id = ? AND teacher_id = ?");
if (!$stmt->execute([$classId, $teacherId]) || $stmt->fetchColumn() === 0) {
    header('Location: classes.php');
    exit;
}

// Get class details
$stmt = $pdo->prepare("
    SELECT sub.subject_name, COALESCE(gl.level_name, '') AS level_name, s.section_name
    FROM classes c
    JOIN subjects sub ON c.subject_id = sub.subject_id
    JOIN sections s ON c.section_id = s.section_id
    LEFT JOIN grade_levels gl ON s.level_id = gl.level_id
    WHERE c.class_id = ?
");
$stmt->execute([$classId]);
$class = $stmt->fetch();

// Get grade period settings
$settings = $pdo->query("
    SELECT setting_key, setting_value 
    FROM system_settings 
    WHERE setting_key IN ('midterm_active', 'finals_active', 'show_computed_grades')
")->fetchAll(PDO::FETCH_KEY_PAIR);

$midtermActive = $settings['midterm_active'] ?? 0;
$finalsActive = $settings['finals_active'] ?? 0;
$showComputed = $settings['show_computed_grades'] ?? 1;

// Get students with grades
$studentsQuery = "
    SELECT u.user_id, u.first_name, u.last_name, e.enrollment_id,
           gr.midterm_grade, gr.final_grade
    FROM enrollment e
    JOIN users u ON e.student_id = u.user_id
    JOIN classes c ON e.class_id = c.class_id
    LEFT JOIN grades gr ON e.enrollment_id = gr.enrollment_id
    WHERE e.class_id = ? AND c.teacher_id = ?
    ORDER BY u.last_name, u.first_name
";

$stmt = $pdo->prepare($studentsQuery);
$stmt->execute([$classId, $teacherId]);
$students = $stmt->fetchAll();

// Handle grade submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $midtermGrades = $_POST['midterm'] ?? [];
    $finalGrades = $_POST['final'] ?? [];
    
    foreach ($students as $student) {
        $enrollmentId = $student['enrollment_id'];
        $midterm = isset($midtermGrades[$enrollmentId]) ? (int)$midtermGrades[$enrollmentId] : null;
        $final = isset($finalGrades[$enrollmentId]) ? (int)$finalGrades[$enrollmentId] : null;

        // Validate grades
        if (($midterm !== null && ($midterm < 0 || $midterm > 100)) || 
            ($final !== null && ($final < 0 || $final > 100))) {
            continue;
        }

        // Prepare the appropriate query
        if ($student['midterm_grade'] !== null || $student['final_grade'] !== null) {
            $query = "UPDATE grades SET midterm_grade = ?, final_grade = ?, updated_by = ? WHERE enrollment_id = ?";
        } else {
            $query = "INSERT INTO grades (enrollment_id, midterm_grade, final_grade, updated_by) VALUES (?, ?, ?, ?)";
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute([$midterm, $final, $teacherId, $enrollmentId]);
    }

    // Refresh student data
    $stmt = $pdo->prepare($studentsQuery);
    $stmt->execute([$classId, $teacherId]);
    $students = $stmt->fetchAll();
    $success = 'Grades updated successfully!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grade Management - <?= htmlspecialchars($class['subject_name']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        input[type="number"] { width: 60px; padding: 5px; }
        .btn { padding: 8px 15px; background: #337ab7; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .success { color: green; margin-bottom: 15px; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <?php include 'dashboard.php'; ?>
    
    <div class="content">
        <h3>Grade Management: <?= htmlspecialchars($class['subject_name']) ?> (<?= htmlspecialchars($class['level_name'] ?? '') ?>-<?= htmlspecialchars($class['section_name']) ?>)</h3>
        
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
            
            <div class="d-flex gap-3 mt-3">
                <button type="submit" class="btn btn-success px-4 py-2">
                    <i class="bi bi-save"></i> Save Grades
                </button>
                <a href="classes.php" class="btn btn-secondary px-4 py-2">
                    <i class="bi bi-arrow-left"></i> Back to Classes
                </a>
            </div>
        </form>
    </div>
</body>
</html>
