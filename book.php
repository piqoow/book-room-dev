<?php
session_start(); // Memulai session

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'config.php';

// Fetch available rooms
$rooms_h = [];
$sql = "SELECT * FROM rooms where unit = 'H'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rooms_h[] = $row;
    }
}

// Fetch available rooms
$rooms_n = [];
$sql = "SELECT * FROM rooms where unit = 'N'";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rooms_n[] = $row;
    }
}

// Fetch available division
$division = [];
$sql = "SELECT * FROM division";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $division[] = $row;
    }
}

// Function to generate time slots
function generateTimeSlots($start, $end, $interval) {
    $times = [];
    $start_time = strtotime($start);
    $end_time = strtotime($end);

    while ($start_time <= $end_time) {
        $times[] = date('H:i', $start_time);
        $start_time = strtotime("+$interval minutes", $start_time);
    }
    return $times;
}

$time_slots = generateTimeSlots('09:00', '17:00', 30); // 30-minute intervals

// Fetch status messages
$status = $_GET['status'] ?? null;
$message = $_GET['message'] ?? null;

// Fetch booked time slots for a specific room and date
$booked_slots = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['room_id'], $_POST['date'])) {
    $room_id = $conn->real_escape_string($_POST['room_id']);
    $date = $conn->real_escape_string($_POST['date']);

    // Query untuk mengambil waktu yang sudah dibooking pada ruang dan tanggal yang dipilih
    $sql_booked = "SELECT time_start, time_end FROM bookings WHERE room_id = '$room_id' AND date = '$date' AND status != 'cancelled'";
    $result_booked = $conn->query($sql_booked);

    // Jika ada waktu yang dibooking, simpan dalam array
    if ($result_booked->num_rows > 0) {
        while ($row = $result_booked->fetch_assoc()) {
            $booked_slots[] = [
                'start' => $row['time_start'],
                'end' => $row['time_end']
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Meeting Room</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .status-message.error {
            color: red;
            background-color: #ffe6e6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .status-message.success {
            color: green;
            background-color: #e6ffe6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">Meeting Room System</div>
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <h1>Book a Meeting Room</h1>

        <!-- Display status message -->
        <?php if ($status): ?>
            <div class="status-message <?php echo htmlspecialchars($status); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Booking Form -->
        <form action="book.php" method="POST">
            <label for="room_id">Select Room:</label>
            <select name="room_id" required>
                <option value="" disabled>--Unit H--</option>
                <?php foreach ($rooms_h as $room_h): ?>
                    <option value="<?php echo $room_h['id']; ?>"><?php echo htmlspecialchars($room_h['name']); ?> - <?php echo htmlspecialchars($room_h['pax']); ?> pax</option>
                <?php endforeach; ?>
                <option value="" disabled>--Unit N---</option>
                <?php foreach ($rooms_n as $room_n): ?>
                    <option value="<?php echo $room_n['id']; ?>"><?php echo htmlspecialchars($room_n['name']); ?> - <?php echo htmlspecialchars($room_n['pax']); ?> pax</option>
                <?php endforeach; ?>
            

            <label for="date">Date:</label>
            <input type="date" name="date" required value="<?php echo htmlspecialchars($_POST['date'] ?? ''); ?>">

            <button type="submit" class="btn-check">Check Availability</button>
        </form>

        <?php if (!empty($booked_slots)): ?>
            <form action="book_now.php" method="POST">
                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($_POST['room_id']); ?>">
                <input type="hidden" name="date" value="<?php echo htmlspecialchars($_POST['date']); ?>">

                <!-- <label for="divisi">Division:</label>
                <input type="text" name="divisi" required> -->

                <label for="divisi">Division:</label>
                <select name="divisi" required>
                    <option value="">--Select Division--</option>
                    <?php foreach ($division as $divisi): ?>
                        <option value="<?php echo $divisi['name']; ?>"><?php echo htmlspecialchars($divisi['name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="meet_with">Meet With:</label>
                <select id="meet_with" name="meet_with" required>
                    <option value="">--Select Item--</option>
                    <option value="internal">Internal</option>
                    <option value="external">External</option>
                </select>

                <label for="description">Description:</label>
                <input type="text" id="description" name="description" required>

                <label for="start_time">Start Time:</label>
                <select name="start_time" required>
                    <option value="">--Select Start Time--</option>
                    <?php foreach ($time_slots as $time): ?>
                        <?php 
                        $disabled = false;
                        // Cek apakah waktu ini sudah diblokir
                        foreach ($booked_slots as $slot) {
                            if (strtotime($time) >= strtotime($slot['start']) && strtotime($time) < strtotime($slot['end'])) {
                                $disabled = true;
                                break;
                            }
                        }
                        ?>
                        <option value="<?php echo $time; ?>" <?php echo $disabled ? 'disabled' : ''; ?>>
                            <?php echo $time; ?> <?php echo $disabled ? '(Booked)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="end_time">End Time:</label>
                <select name="end_time" required>
                    <option value="">--Select End Time--</option>
                    <?php foreach ($time_slots as $time): ?>
                        <?php 
                        $disabled = false;
                        // Cek apakah waktu ini sudah diblokir
                        foreach ($booked_slots as $slot) {
                            if (strtotime($time) > strtotime($slot['start']) && strtotime($time) <= strtotime($slot['end'])) {
                                $disabled = true;
                                break;
                            }
                        }
                        ?>
                        <option value="<?php echo $time; ?>" <?php echo $disabled ? 'disabled' : ''; ?>>
                            <?php echo $time; ?> <?php echo $disabled ? '(Booked)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn-book">Book Now</button>
            </form>
        <?php else: ?>
            <!-- Jika tidak ada waktu yang dibooking, tampilkan seluruh slot waktu -->
            <form action="book_now.php" method="POST">
                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($_POST['room_id']); ?>">
                <input type="hidden" name="date" value="<?php echo htmlspecialchars($_POST['date']); ?>">

                <!-- <label for="divisi">Division:</label>
                <input type="text" name="divisi" required> -->

                
                <!-- <label for="divisi">Division:</label>
                <select name="divisi" required>
                    <option value="">--Select Division--</option>
                    <?php foreach ($division as $divisi): ?>
                        <option value="<?php echo $divisi['name']; ?>"><?php echo htmlspecialchars($divisi['name']); ?></option>
                    <?php endforeach; ?>
                </select> -->

                <label for="meet_with">Meet With:</label>
                <select id="meet_with" name="meet_with" required>
                    <option value="">--Select Item--</option>
                    <option value="internal">Internal</option>
                    <option value="external">External</option>
                </select>

                <label for="description">Description:</label>
                <input type="text" id="description" name="description" required>

                <label for="start_time">Start Time:</label>
                <select name="start_time" required>
                    <option value="">--Select Start Time--</option>
                    <?php foreach ($time_slots as $time): ?>
                        <option value="<?php echo $time; ?>">
                            <?php echo $time; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="end_time">End Time:</label>
                <select name="end_time" required>
                    <option value="">--Select End Time--</option>
                    <?php foreach ($time_slots as $time): ?>
                        <option value="<?php echo $time; ?>">
                            <?php echo $time; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn-book">Book Now</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
