<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $report_id = $_POST['report_id'] ?? null;
    $collector_id = $_POST['collector_id'] ?? null;

    // Validate input
    if (!$report_id || !$collector_id) {
        $_SESSION['assign_error'] = "Missing report or collector information.";
        header("Location: ../pages/admin/dashboard.php");
        exit;
    }

    // Check if the report already has a task assigned
    $check = $conn->prepare("SELECT task_id FROM tasks WHERE report_id = ?");
    $check->bind_param("i", $report_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $_SESSION['assign_error'] = "This report has already been assigned.";
        header("Location: ../pages/admin/dashboard.php");
        exit;
    }

    // Insert into tasks table
    $stmt = $conn->prepare("INSERT INTO tasks (report_id, collector_id, status_update, notes) VALUES (?, ?, 'in_progress', '')");
    $stmt->bind_param("ii", $report_id, $collector_id);
    $stmt->execute();

    // Optionally update report status
    $update = $conn->prepare("UPDATE waste_reports SET status = 'in_progress' WHERE report_id = ?");
    $update->bind_param("i", $report_id);
    $update->execute();

    $_SESSION['assign_success'] = "Task assigned successfully.";
    header("Location: ../pages/admin/dashboard.php");
    exit;
}
?>
