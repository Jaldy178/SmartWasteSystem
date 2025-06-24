<?php
// Database connection settings
$host = "localhost";
$db_user = "root";
$db_pass = "MySQLRootPassword@123"; // leave empty if using default XAMPP settings
$db_name = "smart_waste_system";

// Create connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: You can echo this only during testing
// echo "Connected successfully";
?>
