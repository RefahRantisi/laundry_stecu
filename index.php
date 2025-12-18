<?php
session_start();
include 'koneksi.php';

// cek status login
$login = isset($_SESSION['admin_id']);

// ambil data HANYA kalau sudah login
if ($login) {
    $totalOrder = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions")
    );

    $proses = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions WHERE status_id != 4")
    );

    $selesai = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions WHERE status_id = 4")
    );
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Laundry</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }
        .navbar {
            background: #2c3e50;
            padding: 15px;
            display: flex;
            justify-content: center;
            gap: 12px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 18px;
            border-radius: 6px;
        }
        .navbar a:hover {
            background: #1abc9c;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            padding: 30px;
        }
        .cards {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            justify-content: center;
        }
        .card {
            background: white;
            padding: 25px;
            width: 220px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .notice {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <a href="index.php">Dashboard</a>

    <?php if ($login): ?>
        <a href="pelanggan.php">Data Pelanggan</a>
        <a href="transaksi.php">Transaksi</a>
        <a href="status.php">Status Laundry</a>
        <a href="laporan.php">Laporan</a>
        <a href="login.php" style="background:#2c3e50">Login</a>  
    <?php else: ?>
        <span>Data Pelanggan</span>
        <span>Transaksi</span>
        <span>Status Laundry</span>
        <span>Laporan</span>
        <a href="login.php" style="background:#1abc9c">Login</a>
        <a href="signup.php" style="background:#16a085">Sign Up</a>
    <?php endif; ?>
</div>

<!-- CONTENT -->
<div class="container">
    <h2>Dashboard</h2>

    <?php if (!$login): ?>
        <div class="notice">
            <b>Silakan login untuk melihat data dashboard.</b>
        </div>
    <?php else: ?>
        <div class="cards">
            <div class="card">
                <h3>Total Order</h3>
                <p><?= $totalOrder['total']; ?></p>
            </div>
            <div class="card">
                <h3>Laundry Proses</h3>
                <p><?= $proses['total']; ?></p>
            </div>
            <div class="card">
                <h3>Laundry Selesai</h3>
                <p><?= $selesai['total']; ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
