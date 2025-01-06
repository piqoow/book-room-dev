<?php
 // Start the session only once in this file
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'meeting_room_system';

// Create database connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
