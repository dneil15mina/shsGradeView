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
    <title>Manage Sections</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        select, input { width: 100%; padding: 8px; }
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
        <h2>Manage Sections</h2>
        
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

        <h3>Add New Section</h3>
        <form method="POST">
            <div class="form-group">
                <label>Grade Level</label>
                <select name="level_id" required>
                    <option value="">Select Grade Level</option>
                    <?php foreach ($levels as $level): ?>
                        <option value="<?= $level['level_id'] ?>">
                            <?= htmlspecialchars($level['level_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Section Name</label>
                <input type="text" name="section_name" required placeholder="e.g. Section A">
            </div>
            <button type="submit" name="add_section" class="btn">Add Section</button>
        </form>

        <h3>Existing Sections</h3>
        <table>
            <thead>
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
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="section_id" value="<?= $section['section_id'] ?>">
                            <button type="submit" name="delete_section" class="btn" 
                                    onclick="return confirm('Are you sure? This will remove all class assignments for this section!')">
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
