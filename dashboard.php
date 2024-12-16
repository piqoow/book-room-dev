<?php
session_start(); // Memulai session

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php"); // Arahkan ke halaman login jika belum login
    exit();
}

// Ambil username dan role dari session
$username = $_SESSION['user_name'];
$role = $_SESSION['role']; // Ambil role dari sesi

// Koneksi ke database
include 'config.php'; // Pastikan file config.php sudah berisi konfigurasi koneksi database

// Fungsi untuk mengambil data booking
function getBookings($conn, $role, $username) {
    if ($role == 'admin' || $role == 'view') {
        $sql = "SELECT bookings.id, rooms.name as room_name, bookings.date, bookings.divisi, bookings.time_start, bookings.time_end, bookings.status 
                FROM bookings 
                JOIN rooms ON bookings.room_id = rooms.id 
                WHERE bookings.date >= CURDATE()";
    } else {
        $sql = "SELECT bookings.id, rooms.name as room_name, bookings.date, bookings.divisi, bookings.time_start, bookings.time_end, bookings.status 
                FROM bookings 
                JOIN rooms ON bookings.room_id = rooms.id 
                WHERE bookings.date >= CURDATE() AND bookings.user_id = (SELECT id FROM users WHERE user_name = '$username')";
    }

    $result = $conn->query($sql);
    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// Endpoint untuk AJAX
if (isset($_GET['fetch_bookings'])) {
    header('Content-Type: application/json');
    $bookings = getBookings($conn, $role, $username);
    echo json_encode($bookings);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Room Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Tambahkan gaya tambahan jika diperlukan */
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">Meeting Room System</div>
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <?php 
                if ($role != 'view') {
                    echo "<li><a href='book.php'><i class='fas fa-calendar-plus'></i> Book Room</a></li>";
                }
                ?>
                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <h1>Welcome, <span><?php echo htmlspecialchars($username); ?></span></h1>

        <div class="booking-section">
            <h2>Booking List</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Room Name</th>
                        <th>Date</th>
                        <th>Divisi</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="bookingTable">
                    <!-- Data akan dimuat melalui AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Function untuk memuat data booking melalui AJAX
        function loadBookings() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'dashboard.php?fetch_bookings=1', true);
            xhr.onload = function () {
                if (this.status === 200) {
                    const data = JSON.parse(this.responseText);
                    const tbody = document.getElementById('bookingTable');
                    tbody.innerHTML = ''; // Kosongkan tabel sebelum menambahkan data baru

                    if (data.length > 0) {
                        data.forEach((booking, index) => {
                            const row = `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${booking.room_name}</td>
                                    <td>${booking.date}</td>
                                    <td>${booking.divisi}</td>
                                    <td>${booking.time_start} - ${booking.time_end}</td>
                                    <td><span class="status ${booking.status}">${capitalize(booking.status)}</span></td>
                                </tr>`;
                            tbody.innerHTML += row;
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6">No bookings found.</td></tr>';
                    }
                }
            };
            xhr.send();
        }

        // Capitalize function
        function capitalize(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        // Muat data pertama kali saat halaman dibuka
        loadBookings();

        // Refresh data setiap 1 menit (60000 ms)
        setInterval(loadBookings, 60000);
    </script>
</body>
</html>
