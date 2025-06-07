<?php
require_once '../includes/auth.php';
require_once '../config/db_connect.php';
requireRole('admin');

$errors = [];
$success = '';

// Get available options
$subjects = $pdo->query("SELECT subject_id, subject_name FROM subjects")->fetchAll();
$sections = $pdo->query("SELECT section_id, level_name, section_name 
                        FROM sections JOIN grade_levels USING(level_id)")->fetchAll();
$teachers = $pdo->query("SELECT user_id, first_name, last_name 
                        FROM users WHERE role = 'teacher' AND is_active = 1")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId = $_POST['subject_id'] ?? '';
    $sectionId = $_POST['section_id'] ?? '';
    $teacherId = $_POST['teacher_id'] ?? '';
    $schoolYear = $_POST['school_year'] ?? '';
    $semester = $_POST['semester'] ?? '';

    // Validate inputs
    if (empty($subjectId) || empty($sectionId) || empty($teacherId) || 
        empty($schoolYear) || empty($semester)) {
        $errors[] = 'All fields are required';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO classes (subject_id, section_id, teacher_id, school_year, semester)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$subjectId, $sectionId, $teacherId, $schoolYear, $semester]);
            
            $pdo->commit();
            $success = 'Class added successfully!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            if ($e->errorInfo[1] == 1062) {
                $errors[] = 'This class already exists for the selected section, school year and semester';
            } else {
                $errors[] = 'Failed to add class: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Class</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        select, input { width: 100%; padding: 8px; }
        .error { color: red; margin-bottom: 15px; }
        .success { color: green; margin-bottom: 15px; }
        .btn { background: #337ab7; color: white; border: none; padding: 10px 15px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include 'dashboard.php'; ?>
    
    <div class="container">
        <h2>Add New Class</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Subject</label>
                <select name="subject_id" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['subject_id'] ?>">
                            <?= htmlspecialchars($subject['subject_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Section</label>
                <select name="section_id" required>
                    <option value="">Select Section</option>
                    <?php foreach ($sections as $section): ?>
                        <option value="<?= $section['section_id'] ?>">
                            <?= htmlspecialchars($section['level_name'] . ' - ' . $section['section_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Teacher</label>
                <select name="teacher_id" required>
                    <option value="">Select Teacher</option>
                    <?php foreach ($teachers as $teacher): ?>
                        <option value="<?= $teacher['user_id'] ?>">
                            <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>School Year</label>
                <input type="text" name="school_year" required 
                       placeholder="e.g. 2025-2026" value="<?= htmlspecialchars($_POST['school_year'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Semester</label>
                <select name="semester" required>
                    <option value="">Select Semester</option>
                    <option value="1st">1st Semester</option>
                    <option value="2nd">2nd Semester</option>
                    <option value="summer">Summer</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Add Class</button>
        </form>
    </div>
</body>
</html>
