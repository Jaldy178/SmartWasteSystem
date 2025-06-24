<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'collector') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $task_id = $_POST['task_id'] ?? null;
    $report_id = $_POST['report_id'] ?? null;
    $status_update = $_POST['status_update'] ?? '';
    $notes = trim($_POST['notes']);

    if (!$task_id || !$report_id || empty($status_update)) {
        $_SESSION['task_error'] = "Missing required fields.";
        header("Location: ../pages/collector/dashboard.php");
        exit;
    }

    // Update task status and notes
    $updateTask = $conn->prepare("
        UPDATE tasks SET status_update = ?, notes = ?, updated_at = NOW()
        WHERE task_id = ?
    ");
    $updateTask->bind_param("ssi", $status_update, $notes, $task_id);
    $updateTask->execute();

    // Update report status
    $updateReport = $conn->prepare("UPDATE waste_reports SET status = ? WHERE report_id = ?");
    $updateReport->bind_param("si", $status_update, $report_id);
    $updateReport->execute();

    $_SESSION['task_success'] = "Task updated successfully.";
    header("Location: ../pages/collector/dashboard.php");
    exit;
}
