<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../includes/db_connect.php';

$query = $conn->prepare("
    SELECT cs.area, cs.day, cs.time, u.full_name AS admin_name
    FROM collection_schedule cs
    LEFT JOIN users u ON cs.created_by = u.user_id
    ORDER BY FIELD(cs.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), cs.time ASC
");
$query->execute();
$result = $query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collection Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>Waste Collection Schedule</h3>
    <a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                <tr>
                    <th>Area</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Created By</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['area']) ?></td>
                        <td><?= $row['day'] ?></td>
                        <td><?= date('g:i A', strtotime($row['time'])) ?></td>
                        <td><?= htmlspecialchars($row['admin_name']) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No collection schedule available yet.</div>
    <?php endif; ?>
</div>
</body>
</html>
