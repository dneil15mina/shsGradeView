<?php
require_once '../includes/auth.php';
require_once '../config/db_connect.php';
requireRole('admin');

$errors = [];
$success = '';

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_class'])) {
    $classId = $_POST['class_id'] ?? 0;
    try {
        // Check for dependent enrollments
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollment WHERE class_id = ?");
        $stmt->execute([$classId]);
        $enrollmentCount = $stmt->fetchColumn();
        
        if ($enrollmentCount > 0) {
            $errors[] = 'Cannot delete class - there are students enrolled in it. Please remove enrollments first.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM classes WHERE class_id = ?");
            $stmt->execute([$classId]);
            $success = 'Class deleted successfully!';
        }
    } catch (PDOException $e) {
        $errors[] = 'Failed to delete class: ' . $e->getMessage();
    }
}

// Get all classes with teacher and section info
$stmt = $pdo->query("
    SELECT c.class_id, c.school_year, c.semester,
           s.subject_name, 
           CONCAT(t.first_name, ' ', t.last_name) AS teacher_name,
           CONCAT(gl.level_name, ' - ', sec.section_name) AS class_info
    FROM classes c
    JOIN subjects s ON c.subject_id = s.subject_id
    JOIN users t ON c.teacher_id = t.user_id
    JOIN sections sec ON c.section_id = sec.section_id
    JOIN grade_levels gl ON sec.level_id = gl.level_id
    ORDER BY c.school_year DESC, s.subject_name
");
$classes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Classes</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'dashboard.php'; ?>
    
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Manage Classes</h4>
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

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Class</th>
                                <th>School Year</th>
                                <th>Semester</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><?= $class['class_id'] ?></td>
                                <td><?= htmlspecialchars($class['subject_name']) ?></td>
                                <td><?= htmlspecialchars($class['teacher_name']) ?></td>
                                <td><?= htmlspecialchars($class['class_info']) ?></td>
                                <td><?= htmlspecialchars($class['school_year']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($class['semester'])) ?></td>
                                <td>
                                    <a href="edit_class.php?id=<?= $class['class_id'] ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="class_id" value="<?= $class['class_id'] ?>">
                                        <button type="submit" name="delete_class" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this class?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <a href="edit_class.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Class
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
