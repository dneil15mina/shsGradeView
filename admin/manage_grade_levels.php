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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Grade Levels</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'dashboard.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex gap-2 mb-3">
            <a href="manage_sections.php" class="btn btn-outline-primary">
                <i class="bi bi-collection"></i> Manage Sections
            </a>
            <a href="manage_subjects.php" class="btn btn-outline-primary">
                <i class="bi bi-book"></i> Manage Subjects
            </a>
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Manage Grade Levels</h4>
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
                        <h5 class="mb-0">Add New Grade Level</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label class="form-label">Grade Level Name</label>
                                <input type="text" class="form-control" name="level_name" required placeholder="e.g. Grade 11">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Order (for sorting)</label>
                                <input type="number" class="form-control" name="level_order" required min="1">
                            </div>
                            <button type="submit" name="add_level" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Grade Level
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Existing Grade Levels</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
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
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="level_id" value="<?= $level['level_id'] ?>">
                                                <button type="submit" name="delete_level" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure? This will also delete all sections under this grade level!')">
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
