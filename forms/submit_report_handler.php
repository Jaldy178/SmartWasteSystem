<?php
session_start();

require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../auth/login.php");
    exit;
}

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

    $status = "pending";
    $stmt = $conn->prepare("INSERT INTO waste_reports (user_id, waste_type, description, location, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $waste_type, $description, $location, $status);

    if ($stmt->execute()) {
        // Get resident name
        $getUser = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
        $getUser->bind_param("i", $user_id);
        $getUser->execute();
        $getUser->bind_result($resident_name);
        $getUser->fetch();
        $getUser->close();

        // Notify all admins
        $notification_msg = "New report submitted: $waste_type by $resident_name";
        $type = "report";
        $admin_query = $conn->query("SELECT user_id FROM users WHERE role = 'admin'");

        while ($admin = $admin_query->fetch_assoc()) {
            $admin_id = $admin['user_id'];
            $notify = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
            $notify->bind_param("iss", $admin_id, $notification_msg, $type);
            $notify->execute();
        }

        $_SESSION['report_success'] = "Waste report submitted successfully!";
    } else {
        $_SESSION['report_error'] = "Database error: " . $conn->error;
    }

    header("Location: ../pages/resident/submit_report.php");
    exit;
}
?>
