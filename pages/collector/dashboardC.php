<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'collector') {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../includes/db_connect.php';

$collector_id = $_SESSION['user_id'];

$query = $conn->prepare("
    SELECT t.task_id, wr.waste_type, wr.description, wr.location, wr.timestamp, wr.status AS report_status
    FROM tasks t
    JOIN waste_reports wr ON t.report_id = wr.report_id
    WHERE t.collector_id = ?
    ORDER BY wr.timestamp DESC
");
$query->bind_param("i", $collector_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collector Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h3>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?> (Collector)</h3>
        <a href="../../auth/logout.php" class="btn btn-danger float-end mb-3">Logout</a>

        <h4 class="mb-3">Assigned Waste Collection Tasks</h4>
        <?php
if (isset($_SESSION['task_success'])) {
    echo '<div class="alert alert-success">'.$_SESSION['task_success'].'</div>';
    unset($_SESSION['task_success']);
}
if (isset($_SESSION['task_error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['task_error'].'</div>';
    unset($_SESSION['task_error']);
}
?>


        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Waste Type</th>
                            <th>Description</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Reported On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['waste_type']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars($row['location']) ?></td>
                                <td>
                                    <span class="badge bg-<?= match($row['report_status']) {
                                        'pending' => 'warning',
                                        'in_progress' => 'info',
                                        'resolved' => 'success',
                                        default => 'secondary'
                                    } ?>">
                                        <?= ucfirst($row['report_status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y, h:i A', strtotime($row['timestamp'])) ?></td>
                                <td>
                                    <a href="update_task.php?task_id=<?= $row['task_id'] ?>" class="btn btn-sm btn-primary">Update Status</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">You have no assigned tasks at the moment.</div>
        <?php endif; ?>
    </div>
</body>
</html>
