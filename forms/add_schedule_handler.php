<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $area = trim($_POST['area']);
    $day = $_POST['day'];
    $time = $_POST['time'];
    $admin_id = $_SESSION['user_id'];

    if (empty($area) || empty($day) || empty($time)) {
        $_SESSION['schedule_error'] = "All fields are required.";
        header("Location: ../pages/admin/schedule.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO collection_schedule (area, day, time, created_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $area, $day, $time, $admin_id);

    if ($stmt->execute()) {
        $_SESSION['schedule_success'] = "Schedule added successfully!";
    } else {
        $_SESSION['schedule_error'] = "Failed to add schedule.";
    }

    header("Location: ../pages/admin/schedule.php");
    exit;
}
