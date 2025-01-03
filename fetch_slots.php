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

if ($role == 'admin' || $role == 'view') {
    $sql = "SELECT bookings.id, rooms.name AS room_name, DATE_FORMAT(bookings.date, '%d %M %Y') as date, bookings.divisi, bookings.time_start, bookings.time_end, bookings.description, bookings.status 
            FROM bookings 
            JOIN rooms ON bookings.room_id = rooms.id 
            WHERE bookings.date >= CURDATE()";
} else {
    $sql = "SELECT bookings.id, rooms.name AS room_name, DATE_FORMAT(bookings.date, '%d %M %Y') as date, bookings.divisi, bookings.time_start, bookings.time_end, bookings.description, bookings.status 
            FROM bookings 
            JOIN rooms ON bookings.room_id = rooms.id 
            WHERE bookings.date >= CURDATE() AND bookings.user_id = (SELECT id FROM users WHERE user_name = '$username')";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $counter = 1;
    while ($booking = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$counter}</td>
                <td>{$booking['room_name']}</td>
                <td>{$booking['date']}</td>
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
