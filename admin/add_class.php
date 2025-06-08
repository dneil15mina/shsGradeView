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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add New Class</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'dashboard.php'; ?>
    
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Add New Class</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-1"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Subject</label>
                            <select class="form-select" name="subject_id" required>
                                <option value="">Select Subject</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject['subject_id'] ?>">
                                        <?= htmlspecialchars($subject['subject_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Section</label>
                            <select class="form-select" name="section_id" required>
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?= $section['section_id'] ?>">
                                        <?= htmlspecialchars($section['level_name'] . ' - ' . $section['section_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Teacher</label>
                            <select class="form-select" name="teacher_id" required>
                                <option value="">Select Teacher</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['user_id'] ?>">
                                        <?= htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">School Year</label>
                            <input type="text" class="form-control" name="school_year" required 
                                   placeholder="e.g. 2025-2026" value="<?= htmlspecialchars($_POST['school_year'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester" required>
                                <option value="">Select Semester</option>
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="summer">Summer</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Class
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
