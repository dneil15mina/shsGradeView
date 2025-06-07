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
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 1000px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-container { background: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"] { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #ddd; 
            border-radius: 4px;
            box-sizing: border-box;
        }
        .error { 
            color: #d9534f; 
            background: #fdf7f7; 
            padding: 10px; 
            border-radius: 4px; 
            margin-bottom: 20px;
            border-left: 4px solid #d9534f;
        }
        .success { 
            color: #3c763d; 
            background: #f9f9f9; 
            padding: 10px; 
            border-radius: 4px; 
            margin-bottom: 20px;
            border-left: 4px solid #3c763d;
        }
        .btn { 
            background: #337ab7; 
            color: white; 
            border: none; 
            padding: 10px 20px; 
            cursor: pointer; 
            border-radius: 4px;
            font-size: 14px;
            transition: background 0.3s;
        }
        .btn:hover { background: #286090; }
        .btn-danger { background: #d9534f; }
        .btn-danger:hover { background: #c9302c; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th { 
            background: #337ab7; 
            color: white; 
            padding: 12px; 
            text-align: left; 
        }
        td { 
            padding: 12px; 
            text-align: left; 
            border-bottom: 1px solid #ddd; 
            vertical-align: middle;
        }
        tr:nth-child(even) { background-color: #f9f9f9; }
        tr:hover { background-color: #f1f1f1; }
        .action-buttons { 
            display: flex; 
            gap: 10px; 
            margin: 15px 0;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <?php include 'dashboard.php'; ?>
    <div class="action-buttons">
        <a href="manage_sections.php" class="btn">Manage Sections</a>
        <a href="manage_subjects.php" class="btn">Manage Subjects</a>
        <a href="add_class.php" class="btn">Add Class</a>
        <a href="dashboard.php" class="btn">Back to Dashboard</a>
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

        <div class="form-container">
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
        </div>

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
                    <button type="submit" name="delete_level" class="btn btn-danger" 
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
