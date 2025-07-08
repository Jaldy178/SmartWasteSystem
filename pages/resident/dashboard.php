<?php
session_start();

require_once '../../includes/db_connect.php';

$user_id = $_SESSION['user_id'];

// Count unread notifications
$count_stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$unread = $count_result->fetch_assoc()['unread_count'];


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
                <a href="#notifications" class="btn btn-outline-light btn-sm position-relative me-2">
        🔔
        <?php if ($unread > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unread ?>
            </span>
        <?php endif; ?>
    </a>
                <a href="../../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h3>Resident Dashboard</h3>
        <!-- Notifications Section -->
    <div id="notifications" class="card mt-3 mb-4"> 
        <div class="card-header bg-info text-white">
            Notifications
        </div>
        <ul class="list-group list-group-flush">
            <?php
            $stmt = $conn->prepare("SELECT notification_id, message, timestamp, is_read FROM notifications WHERE user_id = ? ORDER BY timestamp DESC LIMIT 5");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    $bg = $row['is_read'] ? '' : 'bg-light fw-bold';
            ?>
                <li class="list-group-item <?= $bg ?>">
                    <div class="d-flex justify-content-between">
                        <span><?= htmlspecialchars($row['message']) ?></span>
                        <small><?= date('M d, Y H:i', strtotime($row['timestamp'])) ?></small>
                    </div>
                </li>
            <?php endwhile;
            else: ?>
                <li class="list-group-item">No notifications found.</li>
            <?php endif; ?>
        </ul>
    </div>
    
    <?php
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id AND is_read = 0");
    ?>


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
