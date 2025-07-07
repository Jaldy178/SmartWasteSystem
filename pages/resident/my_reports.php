<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../auth/login.php");
    exit;
}
require_once '../../includes/db_connect.php';

// Fetch current user's reports
$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT waste_type, description, location, status, timestamp FROM waste_reports WHERE user_id = ? ORDER BY timestamp DESC");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Waste Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h3>My Waste Reports</h3>
        <a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Waste Type</th>
                            <th>Description</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['waste_type']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars($row['location']) ?></td>
                                <td>
                                    <?php
                                    $status = $row['status'];
                                    $badge = match ($status) {
                                        'pending' => 'warning',
                                        'in_progress' => 'info',
                                        'resolved' => 'success',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $badge ?>"><?= ucfirst($status) ?></span>
                                </td>
                                <td><?= date('d M Y, h:i A', strtotime($row['timestamp'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">You have not submitted any reports yet.</div>
        <?php endif; ?>
    </div>
</body>
</html>
