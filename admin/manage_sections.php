<?php
require_once '../includes/auth.php';
require_once '../config/db_connect.php';
requireRole('admin');

$errors = [];
$success = '';

// Get all grade levels for dropdown
$levels = $pdo->query("SELECT * FROM grade_levels ORDER BY level_order")->fetchAll();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_section'])) {
        $levelId = (int)($_POST['level_id'] ?? 0);
        $sectionName = trim($_POST['section_name'] ?? '');

        if ($levelId <= 0 || empty($sectionName)) {
            $errors[] = 'Grade level and section name are required';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO sections (level_id, section_name) VALUES (?, ?)");
                $stmt->execute([$levelId, $sectionName]);
                $success = 'Section added successfully!';
            } catch (PDOException $e) {
                $errors[] = 'Failed to add section: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['delete_section'])) {
        $sectionId = (int)($_POST['section_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("DELETE FROM sections WHERE section_id = ?");
            $stmt->execute([$sectionId]);
            $success = 'Section deleted successfully!';
        } catch (PDOException $e) {
            $errors[] = 'Failed to delete section: ' . $e->getMessage();
        }
    }
}

// Get all sections with grade level info
$sections = $pdo->query("
    SELECT s.section_id, s.section_name, g.level_id, g.level_name 
    FROM sections s JOIN grade_levels g ON s.level_id = g.level_id
    ORDER BY g.level_order, s.section_name
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Sections</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'dashboard.php'; ?>
    
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Manage Sections</h4>
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
                        <h5 class="mb-0">Add New Section</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Grade Level</label>
                                    <select class="form-select" name="level_id" required>
                                        <option value="">Select Grade Level</option>
                                        <?php foreach ($levels as $level): ?>
                                            <option value="<?= $level['level_id'] ?>">
                                                <?= htmlspecialchars($level['level_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Section Name</label>
                                    <input type="text" class="form-control" name="section_name" required placeholder="e.g. Section A">
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="add_section" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Add Section
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Existing Sections</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Grade Level</th>
                                        <th>Section Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sections as $section): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($section['level_name']) ?></td>
                                        <td><?= htmlspecialchars($section['section_name']) ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="section_id" value="<?= $section['section_id'] ?>">
                                                <button type="submit" name="delete_section" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure? This will remove all class assignments for this section!')">
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
