<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../includes/db_connect.php';

$schedule_id = $_GET['schedule_id'] ?? null;

if ($schedule_id && is_numeric($schedule_id)) {
    $stmt = $conn->prepare("DELETE FROM collection_schedule WHERE schedule_id = ?");
    $stmt->bind_param("i", $schedule_id);

    if ($stmt->execute()) {
        $_SESSION['schedule_success'] = "Schedule deleted successfully.";
    } else {
        $_SESSION['schedule_error'] = "Failed to delete schedule.";
    }
} else {
    $_SESSION['schedule_error'] = "Invalid schedule ID.";
}

header("Location: ../pages/admin/schedule.php");
exit;
