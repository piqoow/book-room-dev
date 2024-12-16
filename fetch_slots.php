<?php
// Mengambil parameter dari URL
$room_id = $_GET['room_id'];
$date = $_GET['date'];

// Koneksi ke database
include 'config.php';

// Ambil semua waktu yang dibooking untuk room dan date tertentu
$sql = "SELECT time_start, time_end FROM bookings WHERE room_id = '$room_id' AND date = '$date'";
$result = $conn->query($sql);

$booked_slots = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $booked_slots[] = [
            'start' => $row['time_start'],
            'end' => $row['time_end']
        ];
    }
}

// Kirim data dalam format JSON
echo json_encode(['booked_slots' => $booked_slots]);
?>
