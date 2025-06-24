<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: ../../auth/login.php");
    exit;
}

require_once '../../includes/db_connect.php';

$success = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $waste_type = trim($_POST['waste_type']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $user_id = $_SESSION['user_id'];

    // Basic validation
    if (empty($waste_type) || empty($description) || empty($location)) {
        $errors[] = "All fields are required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO waste_reports (user_id, waste_type, description, location) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $waste_type, $description, $location);

        if ($stmt->execute()) {
            $success = "Waste report submitted successfully!";
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Waste Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h3>Submit Waste Report</h3>
        <?php
if (isset($_SESSION['report_success'])) {
    echo '<div class="alert alert-success">'.$_SESSION['report_success'].'</div>';
    unset($_SESSION['report_success']);
}
if (isset($_SESSION['report_error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['report_error'].'</div>';
    unset($_SESSION['report_error']);
}
?>


        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="../../forms/submit_report_handler.php">
            <div class="mb-3">
                <label for="waste_type" class="form-label">Waste Type</label>
                <select name="waste_type" class="form-select" required>
    <option value="" disabled selected>Select Waste Type</option>
    <option value="Organic Waste">Organic Waste</option>
    <option value="Plastic">Plastic</option>
    <option value="Paper/Cardboard">Paper/Cardboard</option>
    <option value="Metal">Metal</option>
    <option value="Glass">Glass</option>
    <option value="E-Waste">E-Waste</option>
    <option value="Hazardous Waste">Hazardous Waste</option>
    <option value="General Waste">General Waste</option>
</select>

            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" name="location" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit Report</button>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>
