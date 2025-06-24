<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $waste_type = trim($_POST['waste_type']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $user_id = $_SESSION['user_id'];

    if (empty($waste_type) || empty($description) || empty($location)) {
        $_SESSION['report_error'] = "All fields are required.";
        header("Location: ../pages/resident/submit_report.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO waste_reports (user_id, waste_type, description, location) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $waste_type, $description, $location);

    if ($stmt->execute()) {
        $_SESSION['report_success'] = "Waste report submitted successfully!";
    } else {
        $_SESSION['report_error'] = "Database error: " . $conn->error;
    }

    header("Location: ../pages/resident/submit_report.php");
    exit;
}
?>
