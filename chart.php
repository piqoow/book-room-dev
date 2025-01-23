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
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
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
                <?php if ($role != 'view') {
                    echo "<li><a href='book.php'><i class='fas fa-calendar-plus'></i> Book Room</a></li>";
                } ?>
                <?php if ($role != 'view' && $role != 'user') {
                    echo "<li><a href='chart.php'><i class='fas fa-chart-pie'></i> Chart</a></li>";
                } ?>
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
        <?php if ($role == 'view') {
            echo "<h1>Meeting Room <span>" . htmlspecialchars($rooms) . "</span></h1>";
        } else {
            echo "<h1> <span>" . htmlspecialchars($username) . "</span></h1>";
        } ?>

        <!-- Date Range Filter -->
        <div class="date-range-filter">
            <label for="startDate">Start Date:</label>
            <input type="date" id="startDate" name="startDate">
            <label for="endDate">End Date:</label>
            <input type="date" id="endDate" name="endDate">
            <button onclick="applyDateFilter()">Apply Filter</button>
            <button onclick="refreshPage()">All Time</button>
        </div>

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
            var timeChartInstance = null;
            var roomChartInstance = null;

            function fetchChartData(startDate = '', endDate = '') {
                $.ajax({
                    url: 'fetch_chart_data.php',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        startDate: startDate,
                        endDate: endDate
                    },
                    success: function(data) {
                        console.log('Received data:', data);

                        // Destroy the old charts if they exist
                        if (timeChartInstance) {
                            timeChartInstance.destroy();
                            timeChartInstance = null; // Reset instance
                        }
                        if (roomChartInstance) {
                            roomChartInstance.destroy();
                            roomChartInstance = null; // Reset instance
                        }

                        // If no data, display message and clear summary
                        if (data.timeRoomData.labels.length === 0 && data.roomData.labels.length === 0) {
                            document.getElementById('chartContainer').innerHTML = `
                                <p style="text-align: center; font-weight: bold; font-size: 18px;">No Data Available</p>`;
                            document.getElementById('summary').innerHTML = ''; // Clear summary section
                            return; // Stop further processing
                        }

                        // Ensure chart container is reset
                        document.getElementById('chartContainer').innerHTML = `
                            <div class="chart-item">
                                <canvas id="timeChart"></canvas>
                            </div>
                            <div class="dough-item">
                                <canvas id="roomChart"></canvas>
                            </div>`;

                        // Show charts and update them
                        createTimeChart(data.timeRoomData);
                        if (data.roomData.labels.length !== 0) {
                            createRoomChart(data.roomData);
                        }
                        displaySummary(data.summary);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching chart data:', error);

                        // Destroy any existing charts if AJAX fails
                        if (timeChartInstance) {
                            timeChartInstance.destroy();
                            timeChartInstance = null;
                        }
                        if (roomChartInstance) {
                            roomChartInstance.destroy();
                            roomChartInstance = null;
                        }

                        // Remove chart content and show error message
                        document.getElementById('chartContainer').innerHTML = `
                            <p style="text-align: center; font-weight: bold; font-size: 18px;">Tidak ada data Bookings di rentang tanggal ini.</p>`;
                        document.getElementById('summary').innerHTML = ''; // Clear summary section
                    }
                });
            }

            // Apply date filter
            function applyDateFilter() {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                fetchChartData(startDate, endDate);
            }

            // Refresh page to initial state
            function refreshPage() {
                fetchChartData(); // Call fetchChartData without parameters to reset to initial state
                document.getElementById('startDate').value = ''; // Clear start date input
                document.getElementById('endDate').value = ''; // Clear end date input
            }

            function createTimeChart(data) {
                console.log('Creating time chart with data:', data); // Debug: Log data used for time chart
                const ctx = document.getElementById('timeChart').getContext('2d');

                timeChartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: data.datasets
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            },
                            x: {
                                ticks: {
                                    maxRotation: 90,
                                    minRotation: 45
                                }
                            }
                        },
                        plugins: {
                            datalabels: {
                                display: true,
                                color: 'white',
                                backgroundColor: 'rgba(0, 0, 0, 0.7)',
                                borderRadius: 3,
                                font: {
                                    size: 10 // Adjust the font size to make labels smaller
                                },
                                anchor: 'end',
                                align: 'center',
                                offset: 6, // Add offset to move the labels away from the bars
                                clamp: true, // Ensure labels do not go outside the chart area
                                formatter: function(value, context) {
                                    if (value === 0) {
                                        return null; // Do not display labels for data points with a value of 0
                                    }
                                    // Construct the label with the desired format
                                    const time = context.chart.data.labels[context.dataIndex];
                                    return `${value}x`;
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            }

            // Create room chart using Doughnut Chart
            function createRoomChart(roomData) {
                console.log('Creating room chart with data:', roomData); // Debug: Log data used for room chart
                const ctx = document.getElementById('roomChart').getContext('2d');

                // Calculate the total number of bookings displayed in the chart
                const totalDisplayedBookings = roomData.counts.reduce((sum, count) => sum + count, 0);

                // Create the doughnut chart
                roomChartInstance = new Chart(ctx, {
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
                    },
                    options: {
                        plugins: {
                            datalabels: {
                                display: true,
                                color: 'white',
                                backgroundColor: 'rgba(0, 0, 0, 0.7)',
                                borderRadius: 3,
                                formatter: function(value, context) {
                                    if (value === 0) {
                                        return null; // Do not display labels for data points with a value of 0
                                    }
                                    // Calculate the percentage for each data point based on the total displayed bookings
                                    let percentage = (value / totalDisplayedBookings * 100).toFixed(1);
                                    // Return the percentage string
                                    return `${percentage}%`;
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            }

            // Display summary data
            function displaySummary(summary) {
                const summarySection = document.getElementById('summary');
                summarySection.innerHTML = `
                    <h3>Summary</h3>
                    <p><strong>Ruangan Yang Sering Digunakan:</strong> ${summary.mostUsedRoom ? summary.mostUsedRoom : 'N/A'}</p>
                    <p><strong>Waktu Yang Sering Digunakan:</strong> ${summary.mostUsedTimeSlot ? summary.mostUsedTimeSlot : 'N/A'}</p>
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
                margin: 10px;
            }

            .dough-item {
                flex: 1;
                max-width: 25%;
                margin: 10px;
            }

            canvas {
                width: 100% !important;
                height: auto !important;
            }
        </style>
    </div>
</body>

</html>