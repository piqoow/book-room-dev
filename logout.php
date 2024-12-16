// logout.php

<?php
session_start(); // Start the session

// Hancurkan semua data session
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit();
?>
