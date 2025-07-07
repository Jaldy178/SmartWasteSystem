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
    $location = trim($_POST['location']); // will hold "latitude,longitude"
    $user_id = $_SESSION['user_id'];

    if (empty($waste_type) || empty($description) || empty($location)) {
        $errors[] = "All fields are required, including the map location.";
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
    <link rel="stylesheet" href="../../css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h3 class="mb-4">📍 Submit Waste Report</h3>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
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
                <label for="map" class="form-label">Select Location on Map</label>
                <div id="map" style="height: 300px; border-radius: 8px;"></div>
                <small class="form-text text-muted">Click the map to drop a pin where the waste is located.</small>
                <input type="hidden" name="location" id="location" />
            </div>

            <button type="submit" class="btn btn-primary">Submit Report</button>
            <a href="dashboard.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
        </form>
    </div>

    <script>
        const map = L.map('map').setView([-1.2921, 36.8219], 13); // Nairobi center

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker;

        map.on('click', function(e) {
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }

            document.getElementById('location').value = `${e.latlng.lat},${e.latlng.lng}`;
        });
    </script>
</body>
</html>