<?php
include 'config.php';

// Get start and end dates from request
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

// Define colors for rooms
$roomColors = [
    'Centrepark 1' => ['rgba(255, 206, 86, 0.2)', 'rgba(255, 206, 86, 1)'],
    'Centrepark 2' => ['rgba(54, 162, 235, 0.2)', 'rgba(54, 162, 235, 1)'],
    'Alfabeta' => ['rgba(75, 192, 192, 0.2)', 'rgba(75, 192, 192, 1)'],
    'Parkee' => ['rgba(153, 102, 255, 0.2)', 'rgba(153, 102, 255, 1)'],
    'EV' => ['rgba(255, 159, 64, 0.2)', 'rgba(255, 159, 64, 1)'],
    'Wuzz' => ['rgba(255, 99, 132, 0.2)', 'rgba(255, 99, 132, 1)']
];

// Define date range condition
$dateCondition = "";
if ($startDate && $endDate) {
    $dateCondition = "AND bookings.date BETWEEN '$startDate' AND '$endDate'";
}

// Fetch booking counts by time and room
$timeRoomQuery = "SELECT DATE_FORMAT(time_start, '%H:%i') as time_slot, rooms.name as room_name, COUNT(*) as count 
                  FROM bookings 
                  JOIN rooms ON bookings.room_id = rooms.id 
                  WHERE 1=1 $dateCondition
                  GROUP BY time_slot, room_name 
                  ORDER BY time_slot, room_name";
$timeRoomResult = $conn->query($timeRoomQuery);

$timeRoomData = [];
$rooms = [];
$timeSlotCounts = [];
while ($row = $timeRoomResult->fetch_assoc()) {
    $timeRoomData['labels'][] = $row['time_slot'];
    if (!isset($rooms[$row['room_name']])) {
        $rooms[$row['room_name']] = [];
    }
    $rooms[$row['room_name']][$row['time_slot']] = $row['count'];
    
    if (!isset($timeSlotCounts[$row['time_slot']])) {
        $timeSlotCounts[$row['time_slot']] = 0;
    }
    $timeSlotCounts[$row['time_slot']] += $row['count'];
}

// Fill missing time slots with 0 counts
$uniqueLabels = array_unique($timeRoomData['labels']);
foreach ($rooms as $room => $timeCounts) {
    foreach ($uniqueLabels as $label) {
        if (!isset($rooms[$room][$label])) {
            $rooms[$room][$label] = 0;
        }
    }
    // Sort by time slot
    ksort($rooms[$room]);
}

// Prepare data for chart
$labels = array_values($uniqueLabels);
$datasets = [];
foreach ($rooms as $room => $counts) {
    $datasets[] = [
        'label' => $room,
        'data' => array_values($counts),
        'backgroundColor' => $roomColors[$room][0],
        'borderColor' => $roomColors[$room][1],
        'borderWidth' => 1
    ];
}

// Fetch booking counts by room for all time
$roomQuery = "SELECT rooms.name, COUNT(*) as count 
              FROM bookings 
              JOIN rooms ON bookings.room_id = rooms.id 
              WHERE 1=1 $dateCondition
              GROUP BY rooms.name";
$roomResult = $conn->query($roomQuery);

$roomData = [];
$roomCounts = [];
while ($row = $roomResult->fetch_assoc()) {
    $roomData['labels'][] = $row['name'];
    $roomData['counts'][] = $row['count'];
    $roomData['backgroundColors'][] = $roomColors[$row['name']][0];
    $roomData['borderColors'][] = $roomColors[$row['name']][1];
    
    $roomCounts[$row['name']] = $row['count'];
}

// Determine the most frequently used room and time slot
$mostUsedRoom = array_keys($roomCounts, max($roomCounts))[0];
$mostUsedTimeSlot = array_keys($timeSlotCounts, max($timeSlotCounts))[0];

// Return data as JSON
echo json_encode(['timeRoomData' => ['labels' => $labels, 'datasets' => $datasets], 'roomData' => $roomData, 'summary' => ['mostUsedRoom' => $mostUsedRoom, 'mostUsedTimeSlot' => $mostUsedTimeSlot]]);

$conn->close();
?>