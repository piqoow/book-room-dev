<?php
include 'config.php';

// Fetch booking counts by time for all time
$timeQuery = "SELECT DATE_FORMAT(time_start, '%H:%i') as time_slot, COUNT(*) as count 
              FROM bookings 
              GROUP BY time_slot 
              ORDER BY time_slot";
$timeResult = $conn->query($timeQuery);

$timeData = [];
while ($row = $timeResult->fetch_assoc()) {
    $timeData['labels'][] = $row['time_slot'];
    $timeData['counts'][] = $row['count'];
}

// Fetch booking counts by room for all time
$roomQuery = "SELECT rooms.name, COUNT(*) as count 
              FROM bookings 
              JOIN rooms ON bookings.room_id = rooms.id 
              GROUP BY rooms.name";
$roomResult = $conn->query($roomQuery);

$roomData = [];
while ($row = $roomResult->fetch_assoc()) {
    $roomData['labels'][] = $row['name'];
    $roomData['counts'][] = $row['count'];
}

// Return data as JSON
echo json_encode(['timeData' => $timeData, 'roomData' => $roomData]);

$conn->close();
?>