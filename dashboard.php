<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Ambil username dan role dari session
$username = $_SESSION['user_name'];
$rooms = $_SESSION['rooms'];
$role = $_SESSION['role'] ?? 'user';
$user_id = $_SESSION['user_id'];

// Koneksi ke database
include 'config.php';

// Query untuk mengambil daftar pemesanan berdasarkan role
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
            WHERE bookings.date >= CURDATE() AND bookings.user_id = $user_id ORDER BY bookings.date ASC";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Room Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <!-- Header -->
    <header>
        <div class="logo">
            <img src="assets/img/cp.png" alt="Logo" class="logop">
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <?php
                if ($role != 'view') {
                    echo "<li><a href='book.php'><i class='fas fa-calendar-plus'></i> Book Room</a></li>";
                }
                ?>
                <?php
                if ($role != 'view' && $role != 'user') {
                    echo "<li><a href='chart.php'><i class='fas fa-chart-pie'></i> Chart</a></li>";
                }
                ?>

                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <div class="date-clock">
                    <div class="date" id="date"></div>
                    <div class="date" id="clock"></div>
                </div>
            </ul>
        </nav>
    </header>
    <!-- Main Content -->
    <div class="main-container">
        <?php
        if ($role == 'view') {
            echo "<h1>Meeting Room <span>" . htmlspecialchars($rooms) . "</span></h1>";
        } else {
            echo "<h1> <span>" . htmlspecialchars($username) . "</span></h1>";
        }
        ?>
        <div class="booking-section">
            <h2>Booking List</h2>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <?php if ($role != 'view') { ?>
                            <th>Room Name</th>
                        <?php } ?>
                        <th>Date</th>
                        <th>Division</th>
                        <th>Time</th>
                        <th>Description</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="bookingTable">
                    <!-- Isi tabel akan dimuat melalui AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for Updating Status -->
    <?php if ($role == 'admin') { ?>
        <div id="statusModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Update Status</h2>
                <form id="updateStatusForm">
                    <input type="hidden" id="booking_id" name="booking_id">
                    <label for="status">Choose Status:</label>
                    <select name="status" id="status">
                        <option value="confirmed">Confirmed</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button type="submit">Update Status</button>
                </form>
            </div>
        </div>
    <?php } ?>
    <div class="main-container">
        <p>
            <strong>*</strong> Untuk konfirmasi pemesanan, silakan hubungi Admin melalui kontak di bawah ini.
        </p>
        <p><strong>Anissa <a href="https://wa.me/6282110830527" target="_blank"> (+62 821-1083-0527)</a></strong></p>
        <p><strong>Laviana <a href="https://wa.me/628179679993" target="_blank"> (+62 817-9679-993)</a></strong></p>
    </div>


    <script>
        // Function to fetch bookings
        function fetchBookings() {
            $.ajax({
                url: 'fetch_slots.php',
                type: 'GET',
                success: function(response) {
                    $('#bookingTable').html(response);
                },
                error: function() {
                    alert('Failed to load bookings.');
                }
            });
        }

        // Load bookings on page load and refresh every 1 minute
        fetchBookings();
        setInterval(fetchBookings, 60000);

        // Function to show the modal
        function showModal(element) {
            const bookingId = $(element).data('booking-id');
            $('#booking_id').val(bookingId);
            $('#statusModal').fadeIn();
        }

        // Function to close the modal
        function closeModal() {
            $('#statusModal').fadeOut();
        }

        // Handle status update form submission
        $('#updateStatusForm').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.ajax({
                url: 'Update_status.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    alert(response);
                    fetchBookings(); // Refresh booking list
                    closeModal();
                },
                error: function() {
                    alert('Failed to update status.');
                }
            });
        });
        // Function to update the clock and date
        function updateClock() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const second = now.getSeconds().toString().padStart(2, '0');
            const currentTime = `${hours}:${minutes}:${second}`;
            document.getElementById('clock').textContent = currentTime;

            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const currentDate = now.toLocaleDateString('en-US', options);
            document.getElementById('date').textContent = currentDate;
        }

        // Update the clock every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call to set the clock immediately
    </script>

    <style>
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .status {
            cursor: pointer;
        }
    </style>
</body>

</html>