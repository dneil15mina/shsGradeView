<?php
require_once '../includes/auth.php';
require_once '../config/db_connect.php';
requireRole('admin');

$errors = [];
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_level'])) {
        $levelName = trim($_POST['level_name'] ?? '');
        $levelOrder = (int)($_POST['level_order'] ?? 0);

        if (empty($levelName) || $levelOrder <= 0) {
            $errors[] = 'Level name and valid order are required';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO grade_levels (level_name, level_order) VALUES (?, ?)");
                $stmt->execute([$levelName, $levelOrder]);
                $success = 'Grade level added successfully!';
            } catch (PDOException $e) {
                $errors[] = 'Failed to add grade level: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_level'])) {
        $levelId = (int)($_POST['level_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM grade_levels WHERE level_id = ?");
            $stmt->execute([$levelId]);
            $success = 'Grade level deleted successfully!';
        } catch (PDOException $e) {
            $errors[] = 'Failed to delete grade level: ' . $e->getMessage();
        }
    }
}

// Get all grade levels with fallback if level_order doesn't exist
try {
    $levels = $pdo->query("SELECT * FROM grade_levels ORDER BY level_order")->fetchAll();
} catch (PDOException $e) {
    // Fallback if level_order column doesn't exist yet
    $levels = $pdo->query("SELECT * FROM grade_levels ORDER BY level_name")->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Grade Levels</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; }
        .error { color: red; margin-bottom: 15px; }
        .success { color: green; margin-bottom: 15px; }
        .btn { background: #337ab7; color: white; border: none; padding: 8px 15px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <?php include 'dashboard.php'; ?>
    <div style="margin: 15px 0;">
        <a href="manage_sections.php" class="btn">Manage Sections</a>
        <a href="manage_subjects.php" class="btn">Manage Subjects</a>
        <a href="add_class.php" class="btn">Add Class</a>
    </div>
    
    <div class="container">
        <h2>Manage Grade Levels</h2>
        
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

        <h3>Add New Grade Level</h3>
        <form method="POST">
            <div class="form-group">
                <label>Grade Level Name</label>
                <input type="text" name="level_name" required placeholder="e.g. Grade 11">
            </div>
            <div class="form-group">
                <label>Order (for sorting)</label>
                <input type="number" name="level_order" required min="1">
            </div>
            <button type="submit" name="add_level" class="btn">Add Grade Level</button>
        </form>

        <h3>Existing Grade Levels</h3>
        <table>
            <thead>
                <tr>
                    <th>Level Name</th>
                    <th>Order</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($levels as $level): ?>
                <tr>
                    <td><?= htmlspecialchars($level['level_name']) ?></td>
                    <td><?= htmlspecialchars($level['level_order']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="level_id" value="<?= $level['level_id'] ?>">
                            <button type="submit" name="delete_level" class="btn" 
                                    onclick="return confirm('Are you sure? This will also delete all sections under this grade level!')">
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
