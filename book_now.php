<?php
session_start(); // Memulai session

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'config.php';

// Ambil data dari form
$room_id = $_POST['room_id'] ?? null;
$date = $_POST['date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';
$divisi = $_POST['divisi'] ?? '';
$user_name = $_SESSION['user_name'] ?? '';

// Sanitasi input
$room_id = $conn->real_escape_string($room_id);
$date = $conn->real_escape_string($date);
$start_time = $conn->real_escape_string($start_time);
$end_time = $conn->real_escape_string($end_time);
$divisi = $conn->real_escape_string($divisi);

// Validasi input
if (!$room_id || !$date || !$start_time || !$end_time || !$divisi) {
    header("Location: book.php?status=error&message=" . urlencode('Please fill out all fields.'));
    exit();
}

if (strtotime($end_time) <= strtotime($start_time)) {
    header("Location: book.php?status=error&message=" . urlencode('End time must be later than start time.'));
    exit();
}

// Cek apakah ruangan tersedia di rentang waktu yang dipilih pada tanggal yang sama
$sql_check = "SELECT * FROM bookings 
              WHERE room_id = '$room_id' 
              AND date = '$date' 
              AND ((time_start < '$end_time' AND time_end > '$start_time') 
              OR (time_start >= '$start_time' AND time_start < '$end_time') 
              OR (time_end > '$start_time' AND time_end <= '$end_time'))";

$result_check = $conn->query($sql_check);

if ($result_check->num_rows > 0) {
    // Jika waktu sudah dipesan
    header("Location: book.php?status=error&message=" . urlencode('The room is already booked for the selected time. Please choose another time.'));
    exit();
}

// Ambil user_id dari database
$sql_user = "SELECT id FROM users WHERE user_name = '$user_name'";
$result_user = $conn->query($sql_user);
$user = $result_user->fetch_assoc();
$user_id = $user['id'];

// Jika validasi berhasil, simpan data booking ke database
$sql_insert = "INSERT INTO bookings (room_id, user_id, date, divisi, time_start, time_end) 
               VALUES ('$room_id', '$user_id', '$date', '$divisi', '$start_time', '$end_time')";

if ($conn->query($sql_insert) === TRUE) {
    header("Location: book.php?status=success&message=" . urlencode('Your booking has been successfully made.'));
} else {
    header("Location: book.php?status=error&message=" . urlencode('Please Click Check Availability after selecting the room and date to book now.'));
}

// Tutup koneksi database
$conn->close();
