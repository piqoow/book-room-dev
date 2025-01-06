<?php
 // Start the session only once in this file
$host = '10.2.1.32';
$user = 'rnd';
$password = 'rahasia123';
$database = 'meeting_room_system';

// Create database connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
