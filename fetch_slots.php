<?php
session_start();
include 'config.php';

// Menjalankan query untuk mengkonfirmasi booking secara otomatis
$auto_confirm = "UPDATE bookings 
               SET status = 'Confirmed' 
               WHERE status = 'Pending' AND TIMESTAMPDIFF(MINUTE, created_at, NOW()) >= 5";
$conn->query($auto_confirm);

$username = $_SESSION['user_name'];
$role = $_SESSION['role'] ?? 'user'; 
$rooms = $_SESSION['rooms'];
$user_id = $_SESSION['user_id']; 

if ($role == 'admin') {
    $sql = "SELECT bookings.id, rooms.name AS room_name, DATE_FORMAT(bookings.date, '%d %M %Y') as date, bookings.divisi, bookings.time_start, bookings.time_end, bookings.description, bookings.status 
            FROM bookings 
            JOIN rooms ON bookings.room_id = rooms.id 
            WHERE bookings.date >= CURDATE() ORDER BY bookings.date ASC";
} elseif ($role == 'view') {
    $sql = "SELECT bookings.id, DATE_FORMAT(bookings.date, '%d %M %Y') as date, bookings.divisi, bookings.time_start, bookings.time_end, bookings.description, bookings.status 
            FROM bookings 
            JOIN rooms ON bookings.room_id = rooms.id 
            WHERE bookings.date >= CURDATE() AND rooms.name = '$rooms' AND bookings.status IN ('confirmed', 'pending') ORDER BY bookings.date ASC"; // Updated query
} else {
    $sql = "SELECT bookings.id, rooms.name AS room_name, DATE_FORMAT(bookings.date, '%d %M %Y') as date, bookings.divisi, bookings.time_start, bookings.time_end, bookings.description, bookings.status 
            FROM bookings 
            JOIN rooms ON bookings.room_id = rooms.id 
            WHERE bookings.date >= CURDATE() AND bookings.user_id = '$user_id' ORDER BY bookings.date ASC";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $counter = 1;
    while ($booking = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$counter}</td>";
        if ($role != 'view') {
            echo "<td>{$booking['room_name']}</td>";
        }
        echo "<td>{$booking['date']}</td>
                <td>{$booking['divisi']}</td>
                <td>{$booking['time_start']} - {$booking['time_end']}</td>
                <td>{$booking['description']}</td>";
        if ($role == 'admin') {
            echo "<td><span class='status {$booking['status']}' data-booking-id='{$booking['id']}' onclick='showModal(this)'>" . ucfirst($booking['status']) . "</span></td>";
        } else {
            echo "<td><span class='status {$booking['status']}'>" . ucfirst($booking['status']) . "</span></td>";
        }
        echo "</tr>";
        $counter++;
    }
} else {
    echo "<tr><td colspan='6'>No bookings found.</td></tr>";
}
?>