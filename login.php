<?php
session_start(); // Memulai sesi

include 'config.php'; // Koneksi database
include 'functions.php'; // Fungsi tambahan (misalnya sanitizeInput)

// Cek apakah form login disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan sanitasi input
    $email = sanitizeInput($_POST['email']);
    $password = sanitizeInput($_POST['password']);

    // Menggunakan prepared statement untuk menghindari SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email); // Mengikat parameter
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['role'] = $user['role']; // Simpan role ke sesi
        header("Location: dashboard.php");
        exit();
    } else {
        echo "<script>alert('Password salah!'); window.location='login.php';</script>";
    }
    } else {
        echo "<script>alert('Email tidak ditemukan!'); window.location='login.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Meeting Room Booking</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<header>
        <div class="logo">Welcome to the Meeting Room Booking System</div>
        
    </header>
<body>
    <div class="container">
        <h2>Login</h2>
        <form action="login.php" method="POST">
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        <p>Belum punya akun? <a href="register.php">Register</a></p>
    </div>
</body>
</html>
