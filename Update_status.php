<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];

    $sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $booking_id);

    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Failed to update status.";
    }
}
?>
