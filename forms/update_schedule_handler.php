<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $schedule_id = $_POST['schedule_id'];
    $area = trim($_POST['area']);
    $day = $_POST['day'];
    $time = $_POST['time'];

    if (empty($area) || empty($day) || empty($time)) {
        $_SESSION['schedule_error'] = "All fields are required.";
        header("Location: ../pages/admin/schedule.php");
        exit;
    }

    $stmt = $conn->prepare("UPDATE collection_schedule SET area = ?, day = ?, time = ? WHERE schedule_id = ?");
    $stmt->bind_param("sssi", $area, $day, $time, $schedule_id);

    if ($stmt->execute()) {
        $_SESSION['schedule_success'] = "Schedule updated successfully.";
    } else {
        $_SESSION['schedule_error'] = "Failed to update schedule.";
    }

    header("Location: ../pages/admin/schedule.php");
    exit;
}
