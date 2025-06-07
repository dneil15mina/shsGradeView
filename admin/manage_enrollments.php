<?php
require_once '../includes/auth.php';
requireRole('admin');

// Handle enrollment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_enrollment'])) {
        $studentId = (int)$_POST['student_id'];
        $classId = (int)$_POST['class_id'];
        
        $stmt = $pdo->prepare("INSERT INTO enrollment (student_id, class_id, date_enrolled) VALUES (?, ?, CURDATE())");
        $stmt->execute([$studentId, $classId]);
    } elseif (isset($_POST['remove_enrollment'])) {
        $enrollmentId = (int)$_POST['enrollment_id'];
        $stmt = $pdo->prepare("DELETE FROM enrollment WHERE enrollment_id = ?");
        $stmt->execute([$enrollmentId]);
    } elseif (isset($_POST['bulk_enroll'])) {
        $classId = (int)$_POST['class_id'];
        $sectionId = (int)$_POST['section_id'];
        
        // Get all active students in the section
        $students = $pdo->prepare("
            SELECT u.user_id
            FROM users u
            JOIN enrollment e ON u.user_id = e.student_id
            JOIN classes c ON e.class_id = c.class_id
            WHERE c.section_id = ? AND u.role = 'student' AND u.is_active = TRUE
        ")->execute([$sectionId])->fetchAll(PDO::FETCH_COLUMN);
        
        // Enroll each student
        foreach ($students as $studentId) {
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO enrollment (student_id, class_id, date_enrolled)
                VALUES (?, ?, CURDATE())
            ");
            $stmt->execute([$studentId, $classId]);
        }
    }
}

// Get all classes
$classes = $pdo->query("
    SELECT c.class_id, sub.subject_name, gl.level_name, sec.section_name, 
           CONCAT(u.first_name, ' ', u.last_name) AS teacher_name
    FROM classes c
    JOIN subjects sub ON c.subject_id = sub.subject_id
    JOIN sections sec ON c.section_id = sec.section_id
    JOIN grade_levels gl ON sec.level_id = gl.level_id
    JOIN users u ON c.teacher_id = u.user_id
    ORDER BY gl.level_order, sec.section_name, sub.subject_name
")->fetchAll();

// Get all students
$students = $pdo->query("
    SELECT user_id, first_name, last_name 
    FROM users 
    WHERE role = 'student' AND is_active = TRUE
    ORDER BY last_name, first_name
")->fetchAll();

// Get current enrollments
$enrollments = $pdo->query("
    SELECT e.enrollment_id, e.class_id, e.student_id,
           CONCAT(u.first_name, ' ', u.last_name) AS student_name,
           sub.subject_name, gl.level_name, sec.section_name
    FROM enrollment e
    JOIN users u ON e.student_id = u.user_id
    JOIN classes c ON e.class_id = c.class_id
    JOIN subjects sub ON c.subject_id = sub.subject_id
    JOIN sections sec ON c.section_id = sec.section_id
    JOIN grade_levels gl ON sec.level_id = gl.level_id
    ORDER BY gl.level_name, sec.section_name, sub.subject_name, u.last_name
")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Enrollments</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        .form-group { margin-bottom: 15px; }
    </style>
</head>
<body>
    <?php include 'dashboard.php'; ?>
    
    <h1>Manage Student Enrollments</h1>
    
    <!-- Enrollment Options -->
    <h2>Enrollment Actions</h2>
    
    <!-- Bulk Enrollment by Section -->
    <h3>Bulk Enroll Section</h3>
    <form method="POST">
        <div class="form-group">
            <label>Class:
                <select name="class_id" required>
                    <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['class_id'] ?>">
                        <?= htmlspecialchars($c['level_name']) ?> - <?= htmlspecialchars($c['section_name']) ?> - <?= htmlspecialchars($c['subject_name']) ?> (<?= htmlspecialchars($c['teacher_name']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="form-group">
            <label>Section to Enroll:
                <select name="section_id" required>
                    <?php 
                    $sections = $pdo->query("SELECT section_id, level_name, section_name FROM sections JOIN grade_levels ON sections.level_id = grade_levels.level_id ORDER BY level_order, section_name")->fetchAll();
                    foreach ($sections as $sec): ?>
                    <option value="<?= $sec['section_id'] ?>">
                        <?= htmlspecialchars($sec['level_name']) ?> - <?= htmlspecialchars($sec['section_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <button type="submit" name="bulk_enroll">Enroll Entire Section</button>
    </form>

    <!-- Single Enrollment -->
    <h3>Add Single Enrollment</h3>
    <form method="POST">
        <div class="form-group">
            <label>Student:
                <select name="student_id" required>
                    <?php foreach ($students as $s): ?>
                    <option value="<?= $s['user_id'] ?>"><?= htmlspecialchars($s['last_name']) ?>, <?= htmlspecialchars($s['first_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="form-group">
            <label>Class:
                <select name="class_id" required>
                    <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['class_id'] ?>">
                        <?= htmlspecialchars($c['level_name']) ?> - <?= htmlspecialchars($c['section_name']) ?> - <?= htmlspecialchars($c['subject_name']) ?> (<?= htmlspecialchars($c['teacher_name']) ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <button type="submit" name="add_enrollment">Enroll Student</button>
    </form>

    <!-- Current Enrollments -->
    <h2>Current Enrollments</h2>
    <table>
        <tr>
            <th>Student</th>
            <th>Class</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($enrollments as $e): ?>
        <tr>
            <td><?= htmlspecialchars($e['student_name']) ?></td>
            <td><?= htmlspecialchars($e['level_name']) ?> - <?= htmlspecialchars($e['section_name']) ?> - <?= htmlspecialchars($e['subject_name']) ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="enrollment_id" value="<?= $e['enrollment_id'] ?>">
                    <button type="submit" name="remove_enrollment">Remove</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
