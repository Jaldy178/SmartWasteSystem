<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../includes/db_connect.php';

$schedule_id = $_GET['schedule_id'] ?? null;
if (!$schedule_id) {
    header("Location: schedule.php");
    exit;
}

// Get schedule
$stmt = $conn->prepare("SELECT * FROM collection_schedule WHERE schedule_id = ?");
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();

if (!$schedule) {
    echo "Schedule not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <h3>Edit Schedule</h3>
    <a href="schedule.php" class="btn btn-secondary mb-3">Back</a>

    <form method="POST" action="../../forms/update_schedule_handler.php">
        <input type="hidden" name="schedule_id" value="<?= $schedule['schedule_id'] ?>">

        <div class="mb-3">
            <label for="area" class="form-label">Area</label>
            <input type="text" name="area" class="form-control" required value="<?= htmlspecialchars($schedule['area']) ?>">
        </div>
        <div class="mb-3">
            <label for="day" class="form-label">Day</label>
            <select name="day" class="form-select" required>
                <?php
                foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day) {
                    $selected = $day === $schedule['day'] ? "selected" : "";
                    echo "<option value='$day' $selected>$day</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="time" class="form-label">Time</label>
            <input type="time" name="time" class="form-control" required value="<?= $schedule['time'] ?>">
        </div>
        <button type="submit" class="btn btn-success">Update Schedule</button>
    </form>
</div>
</body>
</html>
