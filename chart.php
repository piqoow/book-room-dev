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
                WHERE bookings.date >= CURDATE() and rooms.name = '$rooms' ORDER BY bookings.date ASC";
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
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        echo "<li><a href='#'><i class='fas fa-chart-pie'></i> Chart</a></li>";
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
            <!-- Charts Section -->
            <h2>Chart</h2>
            <div class="charts-section">
                <div id="chartContainer">
                    <div class="chart-item">
                        <canvas id="timeChart"></canvas>
                    </div>
                    <div class="dough-item">
                        <canvas id="roomChart"></canvas>
                    </div>
                </div>
            </div>
            <!-- Summary Section -->
            <div id="summary" class="summary-section">
                <!-- Summary content will be added here by JavaScript -->
            </div>
            <script>
                // Fetch chart data
                function fetchChartData() {
                    $.ajax({
                        url: 'fetch_chart_data.php',
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            console.log('Received data:', data); // Debug: Log received data
                            createTimeChart(data.timeRoomData);
                            createRoomChart(data.roomData);
                            displaySummary(data.summary);
                        },
                        error: function() {
                            alert('Failed to load chart data.');
                        }
                    });
                }

                // Create time chart
                function createTimeChart(data) {
                    console.log('Creating time chart with data:', data); // Debug: Log data used for time chart
                    const ctx = document.getElementById('timeChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: data.datasets
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }

                // Create room chart using Doughnut Chart
                function createRoomChart(roomData) {
                    console.log('Creating room chart with data:', roomData); // Debug: Log data used for room chart
                    const ctx = document.getElementById('roomChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: roomData.labels,
                            datasets: [{
                                label: 'Count of Bookings by Room',
                                data: roomData.counts,
                                backgroundColor: roomData.backgroundColors,
                                borderColor: roomData.borderColors,
                                borderWidth: 1
                            }]
                        }
                    });
                }

                // Display summary data
                function displaySummary(summary) {
                    const summarySection = document.getElementById('summary');
                    summarySection.innerHTML = `
                    <h3>Summary</h3>
                    <p><strong>Ruangan Yang Sering Digunakan:</strong> ${summary.mostUsedRoom}</p>
                    <p><strong>Waktu Yang Sering Digunakan:</strong> ${summary.mostUsedTimeSlot}</p>
                `;
                }

                // Function to update the clock and date
                function updateClock() {
                    const now = new Date();
                    const hours = now.getHours().toString().padStart(2, '0');
                    const minutes = now.getMinutes().toString().padStart(2, '0');
                    const seconds = now.getSeconds().toString().padStart(2, '0');
                    const currentTime = `${hours}:${minutes}:${seconds}`;
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

                // Initial call to fetch chart data
                fetchChartData();
            </script>
            <style>
                .charts-section {
                    margin-top: 20px;
                    display: flex;
                    justify-content: space-between;
                }

                #chartContainer {
                    display: flex;
                    justify-content: space-around;
                    width: 100%;
                }

                .chart-item {
                    flex: 1;
                    max-width: 50%;
                    /* Mengatur ukuran maksimal untuk setiap chart */
                    margin: 10px;
                }

                .dough-item {
                    flex: 1;
                    max-width: 25%;
                    /* Mengatur ukuran maksimal untuk setiap chart */
                    margin: 10px;
                }

                canvas {
                    width: 100% !important;
                    height: auto !important;
                }
            </style>
    </body>

    </html>