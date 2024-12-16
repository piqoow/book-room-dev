<?php
 // Start the session only once in this file
$host = '110.0.100.70';
$user = 'root';
$password = 'P@ssw0rdKu!23';
$database = 'meeting_room_system';

// Create database connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
