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
        // Delete subject
        $subjectId = $_POST['subject_id'] ?? 0;
        try {
            $stmt = $pdo->prepare("DELETE FROM subjects WHERE subject_id = ?");
            $stmt->execute([$subjectId]);
            $success = 'Subject deleted successfully!';
        } catch (PDOException $e) {
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
    <title>Manage Subjects</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; }
        .error { color: red; margin-bottom: 15px; }
        .success { color: green; margin-bottom: 15px; }
        .btn { background: #337ab7; color: white; border: none; padding: 8px 15px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <?php include 'dashboard.php'; ?>
    
    <div class="container">
        <h2>Manage Subjects</h2>
        
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

        <h3>Add New Subject</h3>
        <form method="POST">
            <div class="form-group">
                <label>Subject Name</label>
                <input type="text" name="subject_name" required>
            </div>
            <div class="form-group">
                <label>Subject Code</label>
                <input type="text" name="subject_code" required placeholder="e.g. MATH101">
            </div>
            <button type="submit" name="add_subject" class="btn">Add Subject</button>
        </form>

        <h3>Existing Subjects</h3>
        <table>
            <thead>
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
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="subject_id" value="<?= $subject['subject_id'] ?>">
                            <button type="submit" name="delete_subject" class="btn" 
                                    onclick="return confirm('Are you sure you want to delete this subject?')">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
