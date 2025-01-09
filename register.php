<?php
include 'config.php';
include 'functions.php';

// Fetch available division
$division = [];
$sql = "SELECT * FROM division";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $division[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = sanitizeInput($_POST['password']);
    $confirm_password = sanitizeInput($_POST['confirm_password']);
    $divisi = sanitizeInput($_POST['divisi']);

    // Validasi: Cek jika password dan confirm password cocok
    if ($password !== $confirm_password) {
        echo "<script>alert('Password dan konfirmasi password tidak cocok!'); window.location='register.php';</script>";
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Masukkan data ke database
    $query = "INSERT INTO users (user_name, email, password, division) VALUES ('$name', '$email', '$hashed_password', '$divisi')";

    if ($conn->query($query)) {
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat registrasi.'); window.location='register.php';</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting Room Booking - Register</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<header>
    <div class="logo">
        <img src="assets/img/logo.svg" alt="Logo" class="logop">
    </div>

</header>

<body>
    <div class="container">
        <h2>Register</h2>
        <form action="register.php" method="POST">
            <!-- Nama -->
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" placeholder="Enter your full name" required>

            <!-- Email -->
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <!-- Password -->
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Create a password" required>

            <!-- Konfirmasi Password -->
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>

            <label for="divisi">Division:</label>
            <select name="divisi" id="divisi" required>
                <option value="">--Select Division--</option>
                <?php foreach ($division as $divisi): ?>
                    <option value="<?php echo $divisi['name']; ?>"><?php echo htmlspecialchars($divisi['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <!-- Submit Button -->
            <button type="submit">Register</button>
        </form>

        <!-- Link ke Login -->
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
</body>

</html>