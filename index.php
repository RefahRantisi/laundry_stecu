<?php
session_start();
include 'koneksi.php';

$totalOrder = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions")
);

$proses = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions WHERE status_id != 4")
);

$selesai = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions WHERE status_id = 4")
);
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

        /* ===== NAVBAR ===== */
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
            transition: 0.3s;
        }

        .navbar a:hover {
            background: #1abc9c;
        }

        /* ===== CONTENT ===== */
        .container {
            max-width: 1000px;
            margin: auto;
            padding: 30px;
        }

        h2 {
            margin-bottom: 5px;
        }

        .cards {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            width: 220px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .card h3 {
            margin: 0;
            color: #555;
            font-size: 18px;
        }

        .card p {
            font-size: 32px;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<div class="navbar">
    <a href="index.php">Dashboard</a>
    <a href="pelanggan.php">Data Pelanggan</a>
    <a href="transaksi.php">Transaksi</a>
    <a href="status.php">Status Laundry</a>
    <a href="laporan.php">Laporan</a>

    <?php if (!$login): ?>
        <a href="login.php">Login</a>
        <a href="signup.php">Sign Up</a>
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
