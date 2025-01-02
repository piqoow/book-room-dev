<?php
session_start();
include 'config.php';

// Menjalankan query untuk mengkonfirmasi booking secara otomatis
$auto_confirm_query = "UPDATE bookings 
                       SET status = 'Confirmed' 
                       WHERE status = 'Pending' 
                       AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) >= 5";

if ($conn->query($auto_confirm_query) === TRUE) {
    echo "Booking status updated successfully.";
} else {
    echo "Error updating booking status: " . $conn->error;
}