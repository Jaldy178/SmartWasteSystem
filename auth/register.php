<?php
// Start session to show messages
session_start();

// Include database connection
require_once '../includes/db_connect.php';

// Define variables
$full_name = $email = $password = $confirm_password = $role = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // Validate
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $errors[] = "Email already in use.";
    }

    // If no errors, insert user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $hashed_password, $role);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Registration successful! Please log in.";
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}
?>

<!-- HTML FORM -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Smart Waste System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center">User Registration</h2>
        <div class="row justify-content-center">
            <div class="col-md-6 bg-white p-4 shadow rounded">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($full_name) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Register As</label>
                        <select name="role" class="form-select" required>
                            <option value="" disabled selected>Select role</option>
                            <option value="resident" <?= ($role == "resident") ? "selected" : "" ?>>Resident</option>
                            <option value="collector" <?= ($role == "collector") ? "selected" : "" ?>>Collector</option>
                            <option value="admin" <?= ($role == "admin") ? "selected" : "" ?>>Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
                <p class="mt-3 text-center">Already have an account? <a href="login.php">Login here</a>.</p>
            </div>
        </div>
    </div>
</body>
</html>
