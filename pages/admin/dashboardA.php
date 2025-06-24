<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../includes/db_connect.php';

// Fetch all reports
$waste_type = $_GET['waste_type'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "
    SELECT wr.report_id, wr.waste_type, wr.description, wr.location, wr.status, wr.timestamp, u.full_name AS resident_name
    FROM waste_reports wr
    JOIN users u ON wr.user_id = u.user_id
    WHERE 1=1
";

$params = [];
$types = "";

// Waste type filter
if (!empty($waste_type)) {
    $sql .= " AND wr.waste_type LIKE ?";
    $params[] = "%$waste_type%";
    $types .= "s";
}

// Status filter
if (!empty($status)) {
    $sql .= " AND wr.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY wr.timestamp DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h3>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?> (Admin)</h3>
        <a href="../../auth/logout.php" class="btn btn-danger float-end mb-3">Logout</a>

        <h4 class="mb-3">All Waste Reports</h4>
        <form method="GET" class="row g-2 mb-4">
    <div class="col-md-3">
        <input type="text" name="waste_type" class="form-control" placeholder="Filter by waste type" value="<?= htmlspecialchars($_GET['waste_type'] ?? '') ?>">
    </div>
    <div class="col-md-3">
        <select name="status" class="form-select">
            <option value="">All Statuses</option>
            <?php
            foreach (['pending','in_progress','resolved'] as $s) {
                $selected = (isset($_GET['status']) && $_GET['status'] === $s) ? "selected" : "";
                echo "<option value='$s' $selected>" . ucfirst($s) . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
    </div>
    <div class="col-md-2">
        <a href="dashboard.php" class="btn btn-outline-secondary w-100">Reset</a>
    </div>
</form>

        <a href="schedule.php" class="btn btn-outline-success mb-3">Manage Collection Schedule</a>

        <?php
if (isset($_SESSION['assign_success'])) {
    echo '<div class="alert alert-success">'.$_SESSION['assign_success'].'</div>';
    unset($_SESSION['assign_success']);
}
if (isset($_SESSION['assign_error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['assign_error'].'</div>';
    unset($_SESSION['assign_error']);
}
?>


        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Reported By</th>
                            <th>Waste Type</th>
                            <th>Description</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['resident_name']) ?></td>
                                <td><?= htmlspecialchars($row['waste_type']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars($row['location']) ?></td>
                                <td>
                                    <span class="badge bg-<?= match($row['status']) {
                                        'pending' => 'warning',
                                        'in_progress' => 'info',
                                        'resolved' => 'success',
                                        default => 'secondary'
                                    } ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y, h:i A', strtotime($row['timestamp'])) ?></td>
                                <td>
                                    <a href="assign_task.php?report_id=<?= $row['report_id'] ?>" class="btn btn-sm btn-primary">Assign Task</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No waste reports available.</div>
        <?php endif; ?>
    </div>
</body>
</html>
