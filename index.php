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
    <title>Smart Waste System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
  body, html {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: 'Inter', sans-serif;
}

.hero {
    height: 100vh;
    background: url('assets/hero-bg.png') center center / cover no-repeat;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding-top: 6rem; /* Ensures it sits below the navbar */
    padding-bottom: 4rem;
}

/* Navbar over hero image */
.custom-navbar {
    background: transparent;
    position: absolute;
    width: 100%;
    z-index: 1000;
}

.hero-logo {
  position: absolute;
  top: 5px;
  left: 10px;
  z-index: 10;
}


.logo-img {
  height: 200px;
}


.hero-logo img {
  height: 1px;  
  width: auto;
}
    .hero::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 60, 0, 0.4); /* Dark green overlay */
        z-index: 1;
    }

    .hero .container {
        position: relative;
        z-index: 2;
    }

    .hero h1 {
        font-size: 3.5rem;
        font-weight: 700;
    }

    .hero p {
        font-size: 1.25rem;
        margin: 1rem auto 2rem;
        max-width: 700px;
    }

    .hero .btn {
        margin: 0.5rem;
        padding: 0.8rem 2rem;
        font-size: 1.1rem;
        font-weight: 500;
        border-radius: 8px;
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
<!-- 🔰 Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top custom-navbar">
  <div class="container-fluid px-4">
    <!-- 🔵 Logo (Top Left) -->
    <a class="navbar-brand d-flex align-items-center" href="index.php" style="height: 40px;">
  <img src="assets/logo.png" alt="UrbanSweep Logo" class="logo-img">
</a>

<div class="hero-logo">
  <img src="assets/logo.png" alt="UrbanSweep Logo">
</div>

    <!-- 🔘 Navigation Buttons (Top Right) -->
    <div class="ms-auto">
      <a href="auth/login.php" class="btn btn-outline-light me-2">Login</a>
      <a href="auth/register.php" class="btn btn-outline-light me-2">Register</a>
      <a href="#contact" class="btn btn-outline-light">Contact Us</a>
    </div>
  </div>
</nav>


<!-- 🟩 Fullscreen Hero Section -->
<section class="hero d-flex align-items-center justify-content-center text-white text-center">
  <div class="container">
    <h1 class="display-4 fw-bold">UrbanSweep</h1>
    <p class="lead">Smart Waste Management for Cleaner Communities</p>
    <div class="mt-4">
      <a href="auth/login.php" class="btn btn-light btn-lg me-2">Login</a>
      <a href="auth/register.php" class="btn btn-outline-light btn-lg me-2">Register</a>
      <a href="#contact" class="btn btn-success btn-lg">Contact Us</a>
    </div>
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
            <p>Login to get started. If you don't have an account, Kindly Register.</p>
            <a href="auth/login.php" class="btn btn-light btn-lg mt-3">Login Now</a>
            <a href="auth/register.php" class="btn btn-light btn-lg mt-3">Register</a>
        </div>
    </section>

    <!-- 🔻 Footer -->
    <footer class="footer">
        <p>&copy; <?= date("Y") ?> Smart Waste Management System | Built by Hassan&Matthew</p>
    </footer>

</body>
</html>
