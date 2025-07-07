<?php
session_start();

// Redirect if not logged in or not a resident
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../auth/login.php");
    exit;
}
?>

<?php
require_once '../../includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resident Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Smart Waste System</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">
                    Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>
                </span>
                <a href="../../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h3>Resident Dashboard</h3>

        <!-- Feature Sections -->
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        Submit New Waste Report
                    </div>
                    <div class="card-body">
                        <a href="submit_report.php" class="btn btn-primary">Report Waste</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        View My Reports
                    </div>
                    <div class="card-body">
                        <a href="my_reports.php" class="btn btn-info">View Reports</a>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        Collection Schedule
                    </div>
                    <div class="card-body">
                        <a href="collection_schedule.php" class="btn btn-warning">View Schedule</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
