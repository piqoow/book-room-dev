<?php
session_start();
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    // Debugging: Cek apakah status dikirim
    var_dump($_POST);

    // Pastikan booking_id dan status terisi
    if (!empty($booking_id) && !empty($status)) {
        // Sanitasi input untuk mencegah SQL Injection
        $booking_id = $conn->real_escape_string($booking_id);
        $status = $conn->real_escape_string($status);

        // Query untuk memperbarui status booking
        $sql = "UPDATE bookings SET status = '$status' WHERE id = '$booking_id'";

        if ($conn->query($sql) === TRUE) {
            // Jika update berhasil, tampilkan pesan dan arahkan kembali ke dashboard
            echo "<script>alert('Booking status updated successfully.'); window.location='dashboard.php';</script>";
        } else {
            // Jika terjadi kesalahan
            echo "Error: " . $conn->error;
        }
    } else {
        // Jika data tidak lengkap
        echo "Please fill out all fields.";
    }
}
?>
