<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Koneksi database
$conn = new mysqli('localhost', 'root', '', 'laundry_stecu');

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password != $confirm) {
        $error = "Password tidak cocok!";
    } else {
        // Cek username
        $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check->num_rows > 0) {
            $error = "Username sudah digunakan!";
        } else {
            // Insert user
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed')";
            
            if ($conn->query($sql)) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Gagal mendaftar: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
</head>
<body>
    <h2>Sign Up - Laundry STECU</h2>
    
    <?php if ($error): ?>
        <p style="color: red;"><b><?php echo $error; ?></b></p>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <p style="color: green;"><b><?php echo $success; ?></b></p>
    <?php endif; ?>
    
    <form method="POST">
        <table>
            <tr>
                <td>Username:</td>
                <td><input type="text" name="username" required></td>
            </tr>
            <tr>
                <td>Password:</td>
                <td><input type="password" name="password" required></td>
            </tr>
            <tr>
                <td>Konfirmasi:</td>
                <td><input type="password" name="confirm_password" required></td>
            </tr>
            <tr>
                <td></td>
                <td><button type="submit">Daftar</button></td>
            </tr>
        </table>
    </form>
    
    <p>Sudah punya akun? <a href="login.php">Login</a></p>
</body>
</html>