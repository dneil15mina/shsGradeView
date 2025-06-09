<?php
require_once '../includes/auth.php';
require_once '../config/db_connect.php';
requireRole('admin');

$errors = [];
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_subject'])) {
        // Add new subject
        $subjectName = trim($_POST['subject_name'] ?? '');
        $subjectCode = trim($_POST['subject_code'] ?? '');

        if (empty($subjectName) || empty($subjectCode)) {
            $errors[] = 'Both subject name and code are required';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO subjects (subject_name, subject_code) VALUES (?, ?)");
                $stmt->execute([$subjectName, $subjectCode]);
                $success = 'Subject added successfully!';
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    $errors[] = 'Subject with this code already exists';
                } else {
                    $errors[] = 'Failed to add subject: ' . $e->getMessage();
                }
            }
        }
    } elseif (isset($_POST['delete_subject'])) {
        // Delete subject with all related data
        $subjectId = $_POST['subject_id'] ?? 0;
        try {
            $pdo->beginTransaction();
            
            // First delete all grades for classes of this subject
            $pdo->prepare("DELETE g FROM grades g
                          JOIN enrollment e ON g.enrollment_id = e.enrollment_id
                          JOIN classes c ON e.class_id = c.class_id
                          WHERE c.subject_id = ?")->execute([$subjectId]);
            
            // Then delete all enrollments for those classes
            $pdo->prepare("DELETE e FROM enrollment e
                          JOIN classes c ON e.class_id = c.class_id
                          WHERE c.subject_id = ?")->execute([$subjectId]);
            
            // Then delete all classes for this subject
            $pdo->prepare("DELETE FROM classes WHERE subject_id = ?")->execute([$subjectId]);
            
            // Finally delete the subject itself
            $pdo->prepare("DELETE FROM subjects WHERE subject_id = ?")->execute([$subjectId]);
            
            $pdo->commit();
            $success = 'Subject and all related data deleted successfully!';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Failed to delete subject: ' . $e->getMessage();
        }
    }
}

// Get all subjects
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Subjects</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'dashboard.php'; ?>
    
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Manage Subjects</h4>
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

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Subject</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Subject Name</label>
                                <input type="text" class="form-control" name="subject_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subject Code</label>
                                <input type="text" class="form-control" name="subject_code" required placeholder="e.g. MATH101">
                            </div>
                            <button type="submit" name="add_subject" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Subject
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Existing Subjects</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Subject Name</th>
                                        <th>Subject Code</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($subjects as $subject): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                                        <td><?= htmlspecialchars($subject['subject_code']) ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="subject_id" value="<?= $subject['subject_id'] ?>">
                                                <button type="submit" name="delete_subject" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure you want to delete this subject?')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
