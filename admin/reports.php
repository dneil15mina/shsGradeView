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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grade Reports</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'dashboard.php'; ?>
    
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Grade Reports</h4>
            </div>
            <div class="card-body">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Generate Report</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="generate_report.php" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">School Year</label>
                                <select class="form-select" name="school_year" required>
                                    <?php foreach ($schoolYears as $year): ?>
                                        <option value="<?= $year['school_year'] ?>">
                                            <?= htmlspecialchars($year['school_year']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Report Type</label>
                                <select class="form-select" name="report_type" required>
                                    <option value="summary">Summary Report</option>
                                    <option value="detailed">Detailed Report</option>
                                    <option value="teacher">Teacher Performance</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-file-earmark-text"></i> Generate Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
