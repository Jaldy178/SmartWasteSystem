<?php
// === Show errors during development ===
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// === Redirect logged-in users to their dashboards ===
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'resident':
            header("Location: pages/resident/dashboard.php");
            exit;
        case 'collector':
            header("Location: pages/collector/dashboard.php");
            exit;
        case 'admin':
            header("Location: pages/admin/dashboard.php");
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Waste Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body {
            background: #f8f9fa;
        }
        .hero {
            background: linear-gradient(to right, #28a745, #218838);
            color: white;
            padding: 5rem 2rem;
            text-align: center;
        }
        .hero h1 {
            font-size: 3rem;
            font-weight: bold;
        }
        .section {
            padding: 4rem 2rem;
        }
        .footer {
            background-color: #212529;
            color: #ccc;
            text-align: center;
            padding: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <!-- 🔰 Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Smart Waste Management System</h1>
            <p class="lead mt-3">An efficient, digital platform to report, track, and manage waste collection in urban communities.</p>
            <a href="auth/login.php" class="btn btn-light btn-lg mt-4">Login to Your Account</a>
        </div>
    </section>

    <!-- 💡 About Section -->
    <section class="section bg-white">
        <div class="container">
            <h2 class="text-center mb-4">About the System</h2>
            <p class="text-center mx-auto" style="max-width: 800px;">
                The Smart Waste Management System enables residents to report waste issues, allows collectors to receive and update assigned tasks,
                and gives administrators complete oversight of waste reports, task assignments, and collection schedules — all in real time.
                This web-based solution enhances communication and efficiency for cleaner, greener communities.
            </p>
        </div>
    </section>

    <!-- 👥 Who Can Use It -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Who Can Use This Platform?</h2>
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <h5>🏘️ Residents</h5>
                    <p>Submit waste reports, track progress, and view collection schedules in your area.</p>
                </div>
                <div class="col-md-4 text-center">
                    <h5>🚛 Waste Collectors</h5>
                    <p>Access assigned collection tasks, update statuses, and view locations using the system map.</p>
                </div>
                <div class="col-md-4 text-center">
                    <h5>🛠️ Administrators</h5>
                    <p>Monitor all reports, assign tasks, manage collection schedules, and review analytics.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 📬 Call to Action -->
    <section class="section bg-success text-white text-center">
        <div class="container">
            <h2 class="mb-3">Ready to Keep Your City Clean?</h2>
            <p>Login to get started. If you don't have an account, contact your system administrator for access.</p>
            <a href="auth/login.php" class="btn btn-light btn-lg mt-3">Login Now</a>
        </div>
    </section>

    <!-- 🔻 Footer -->
    <footer class="footer">
        <p>&copy; <?= date("Y") ?> Smart Waste Management System | Built by [Your Team Name or School Project Group]</p>
    </footer>

</body>
</html>
