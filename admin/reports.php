<?php
require_once '../includes/auth.php';
requireRole('admin');

// Get available school years
$schoolYears = $pdo->query("SELECT DISTINCT school_year FROM classes ORDER BY school_year DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grade Reports</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .report-form { margin-bottom: 20px; padding: 15px; background: #f5f5f5; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
        .btn { padding: 5px 10px; text-decoration: none; }
        .btn-primary { background: #337ab7; color: white; }
    </style>
</head>
<body>
    <?php include 'dashboard.php'; ?>
    
    <div class="content">
        <h3>Grade Reports</h3>
        
        <div class="report-form">
            <form method="GET" action="generate_report.php">
                <label>School Year:
                    <select name="school_year" required>
                        <?php foreach ($schoolYears as $year): ?>
                            <option value="<?= $year['school_year'] ?>">
                                <?= htmlspecialchars($year['school_year']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                
                <label>Report Type:
                    <select name="report_type" required>
                        <option value="summary">Summary Report</option>
                        <option value="detailed">Detailed Report</option>
                        <option value="teacher">Teacher Performance</option>
                    </select>
                </label>
                
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </form>
        </div>
    </div>
</body>
</html>
