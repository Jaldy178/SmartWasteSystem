<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'collector') {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../includes/db_connect.php';

$collector_id = $_SESSION['user_id'];
$task_id = $_GET['task_id'] ?? null;

if (!$task_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch task + report
$stmt = $conn->prepare("
    SELECT t.task_id, wr.report_id, wr.waste_type, wr.description, wr.location, wr.status AS current_status
    FROM tasks t
    JOIN waste_reports wr ON t.report_id = wr.report_id
    WHERE t.task_id = ? AND t.collector_id = ?
");
$stmt->bind_param("ii", $task_id, $collector_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "Task not found or access denied.";
    exit;
}

$task = $result->fetch_assoc();
$success = $error = "";

// Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $status_update = $_POST['status_update'];
    $notes = trim($_POST['notes']);

    // Update task
    $updateTask = $conn->prepare("
        UPDATE tasks SET status_update = ?, notes = ?, updated_at = NOW() WHERE task_id = ?
    ");
    $updateTask->bind_param("ssi", $status_update, $notes, $task_id);
    $updateTask->execute();

    // Update report status too
    $updateReport = $conn->prepare("UPDATE waste_reports SET status = ? WHERE report_id = ?");
    $updateReport->bind_param("si", $status_update, $task['report_id']);
    $updateReport->execute();

    $success = "Task updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Task Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h3>Update Task for <?= htmlspecialchars($task['waste_type']) ?></h3>
        <a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="../../forms/update_task_handler.php">
        <input type="hidden" name="task_id" value="<?= $task['task_id'] ?>">
        <input type="hidden" name="report_id" value="<?= $task['report_id'] ?>">
            <div class="mb-3">
                <label class="form-label">Waste Type</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($task['waste_type']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" rows="3" disabled><?= htmlspecialchars($task['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($task['location']) ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="status_update" class="form-label">Update Status</label>
                <select name="status_update" class="form-select" required>
                    <option value="" disabled selected>Select status</option>
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="unable_to_resolve">Unable to Resolve</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="notes" class="form-label">Notes (optional)</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Enter any notes..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Task</button>
        </form>
    </div>
</body>
</html>
