<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../includes/db_connect.php';

$report_id = $_GET['report_id'] ?? null;
if (!$report_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch report details
$stmt = $conn->prepare("
    SELECT wr.report_id, wr.waste_type, wr.description, wr.location, wr.status, u.full_name AS resident_name
    FROM waste_reports wr
    JOIN users u ON wr.user_id = u.user_id
    WHERE wr.report_id = ?
");
$stmt->bind_param("i", $report_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if (!$report) {
    echo "Invalid report ID.";
    exit;
}

// Fetch list of collectors
$collectors = $conn->query("SELECT user_id, full_name FROM users WHERE role = 'collector'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h3>Assign Task for Report #<?= $report['report_id'] ?></h3>
        <a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Report Details</div>
            <div class="card-body">
                <p><strong>Reported By:</strong> <?= htmlspecialchars($report['resident_name']) ?></p>
                <p><strong>Waste Type:</strong> <?= htmlspecialchars($report['waste_type']) ?></p>
                <p><strong>Description:</strong> <?= htmlspecialchars($report['description']) ?></p>
                <p><strong>Location:</strong> <?= htmlspecialchars($report['location']) ?></p>
                <p><strong>Status:</strong> <?= ucfirst($report['status']) ?></p>
            </div>
        </div>

        <form method="POST" action="../../forms/assign_task_handler.php">
            <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">

            <div class="mb-3">
                <label for="collector_id" class="form-label">Assign to Collector</label>
                <select name="collector_id" class="form-select" required>
                    <option value="" disabled selected>Select a collector</option>
                    <?php while ($collector = $collectors->fetch_assoc()): ?>
                        <option value="<?= $collector['user_id'] ?>"><?= htmlspecialchars($collector['full_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Assign Task</button>
        </form>
    </div>
</body>
</html>
