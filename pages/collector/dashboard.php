<?php
session_start();

require_once '../../includes/db_connect.php';

// Redirect if not logged in or not a collector
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'collector') {
    header("Location: ../../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Count unread notifications
$count_stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE user_id = ? AND is_read = 0");
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$unread = $count_result->fetch_assoc()['unread_count'];

// 🟢 Task Query
$collector_id = $_SESSION['user_id'];

$task_query = $conn->prepare("
    SELECT t.task_id, wr.waste_type, wr.description, wr.location, wr.timestamp, wr.status AS report_status
    FROM tasks t
    JOIN waste_reports wr ON t.report_id = wr.report_id
    WHERE t.collector_id = ?
    ORDER BY wr.timestamp DESC
");
$task_query->bind_param("i", $collector_id);
$task_query->execute();
$task_result = $task_query->get_result();

// Get first report location for map
$firstLatLng = null;
if ($task_result->num_rows > 0) {
    $task_result->data_seek(0);
    $firstRow = $task_result->fetch_assoc();
    if (strpos($firstRow['location'], ',') !== false) {
        $firstLatLng = explode(',', $firstRow['location']);
    }
    $task_result->data_seek(0); // reset again for loop
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collector Dashboard</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Map Libraries -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.min.js"></script>
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?> (Collector)</h3>

    <!-- 🔔 Notification Bell -->
    <a href="#notifications" class="btn btn-outline-secondary btn-sm position-relative me-2">
        🔔
        <?php if ($unread > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unread ?>
            </span>
        <?php endif; ?>
    </a>

    <a href="../../auth/logout.php" class="btn btn-danger float-end mb-3">Logout</a>

    <!-- 🔔 Notifications -->
    <div id="notifications" class="card mt-3 mb-4">
        <div class="card-header bg-info text-white">Notifications</div>
        <ul class="list-group list-group-flush">
            <?php
            $notif_stmt = $conn->prepare("SELECT message, timestamp, is_read FROM notifications WHERE user_id = ? ORDER BY timestamp DESC LIMIT 5");
            $notif_stmt->bind_param("i", $user_id);
            $notif_stmt->execute();
            $notif_result = $notif_stmt->get_result();

            if ($notif_result->num_rows > 0):
                while ($row = $notif_result->fetch_assoc()):
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
    // ✅ Mark notifications as read
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id AND is_read = 0");
    ?>

    <h4 class="mb-3">Assigned Waste Collection Tasks</h4>

    <?php if (isset($_SESSION['task_success'])) {
        echo '<div class="alert alert-success">'.$_SESSION['task_success'].'</div>';
        unset($_SESSION['task_success']);
    }
    if (isset($_SESSION['task_error'])) {
        echo '<div class="alert alert-danger">'.$_SESSION['task_error'].'</div>';
        unset($_SESSION['task_error']);
    } ?>

    <?php if ($task_result->num_rows > 0): ?>
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
                <?php while ($row = $task_result->fetch_assoc()): ?>
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

    <h4 class="mt-5 mb-3">🧭 Route to First Assigned Location</h4>
    <div id="routeMap" style="height: 400px; border-radius: 8px;"></div>
</div>

<script>
    const collectorStation = L.latLng(-1.2921, 36.8219); // Default station
    const map = L.map('routeMap').setView(collectorStation, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    <?php if ($firstLatLng): ?>
    const reportLocation = L.latLng(<?= $firstLatLng[0] ?>, <?= $firstLatLng[1] ?>);
    L.Routing.control({
        waypoints: [collectorStation, reportLocation],
        routeWhileDragging: false,
        createMarker: function(i, wp) {
            return L.marker(wp).bindPopup(i === 0 ? "Your Station" : "Waste Location").openPopup();
        }
    }).addTo(map);
    <?php else: ?>
    document.getElementById('routeMap').innerHTML = "<div class='alert alert-warning'>No valid location available for routing.</div>";
    <?php endif; ?>
</script>
</body>
</html>
