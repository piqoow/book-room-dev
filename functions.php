<?php
include 'config.php';

// Redirect to login if not logged in
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// functions.php
function checkRole($requiredRole) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] != $requiredRole) {
        echo "<script>alert('Access denied!'); window.location='dashboard.php';</script>";
        exit();
    }
}


function sanitizeInput($data) {
    // Hapus spasi di awal dan akhir, dan karakter-karakter yang tidak diinginkan
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>
