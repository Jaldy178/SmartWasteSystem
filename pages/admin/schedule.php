<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../includes/db_connect.php';

// Handle messages
$success = $_SESSION['schedule_success'] ?? "";
$error = $_SESSION['schedule_error'] ?? "";
unset($_SESSION['schedule_success'], $_SESSION['schedule_error']);

// Fetch all schedules
$area = $_GET['area'] ?? '';
$day = $_GET['day'] ?? '';

$sql = "
    SELECT cs.schedule_id, cs.area, cs.day, cs.time, u.full_name AS admin_name
    FROM collection_schedule cs
    LEFT JOIN users u ON cs.created_by = u.user_id
    WHERE 1 = 1
";

$params = [];
$types = "";

// Apply area filter if set
if (!empty($area)) {
    $sql .= " AND cs.area LIKE ?";
    $params[] = "%$area%";
    $types .= "s";
}

// Apply day filter if set
if (!empty($day)) {
    $sql .= " AND cs.day = ?";
    $params[] = $day;
    $types .= "s";
}

$sql .= " ORDER BY FIELD(cs.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), cs.time";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$schedules = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Collection Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h3>Manage Collection Schedule</h3>
        <form method="GET" class="row g-2 mb-4">
    <div class="col-md-4">
        <input type="text" name="area" class="form-control" placeholder="Search by area" value="<?= htmlspecialchars($_GET['area'] ?? '') ?>">
    </div>
    <div class="col-md-3">
        <select name="day" class="form-select">
            <option value="">All Days</option>
            <?php
            foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d) {
                $selected = (isset($_GET['day']) && $_GET['day'] === $d) ? "selected" : "";
                echo "<option value='$d' $selected>$d</option>";
            }
            ?>
        </select>
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
    </div>
    <div class="col-md-2">
        <a href="schedule.php" class="btn btn-outline-secondary w-100">Reset</a>
    </div>
</form>

        <a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Schedule Table -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">Current Schedule</div>
            <div class="card-body">
                <?php if ($schedules->num_rows > 0): ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Area</th>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $schedules->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['area']) ?></td>
                                    <td><?= $row['day'] ?></td>
                                    <td><?= date('g:i A', strtotime($row['time'])) ?></td>
                                    <td><?= htmlspecialchars($row['admin_name']) ?></td>
                                    <td>
                                        <a href="edit_schedule.php?schedule_id=<?= $row['schedule_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="../../forms/delete_schedule_handler.php?schedule_id=<?= $row['schedule_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this schedule?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No schedule found.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add New Schedule Form -->
        <div class="card">
            <div class="card-header bg-success text-white">Add New Schedule</div>
            <div class="card-body">
                <form method="POST" action="../../forms/add_schedule_handler.php">
                    <div class="mb-3">
                        <label for="area" class="form-label">Area</label>
                        <input type="text" name="area" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="day" class="form-label">Day</label>
                        <select name="day" class="form-select" required>
                            <option value="" disabled selected>Select a day</option>
                            <?php
                            foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day) {
                                echo "<option value='$day'>$day</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="time" class="form-label">Time</label>
                        <input type="time" name="time" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Add Schedule</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
