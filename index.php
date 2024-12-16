

<?php
session_start(); // Memulai sesi

// Jika pengguna sudah login, langsung ke dashboard
if (isset($_SESSION['email'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Room Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Welcome to the Meeting Room Booking System</h2>
        <a href="login.php">Login</a> | <a href="register.php">Register</a>
    </div>
</body>
</html>
