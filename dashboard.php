<?php
session_start();
include 'koneksi.php';

$totalOrder   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions"));
$proses       = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions WHERE status_id != 4"));
$selesai      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions WHERE status_id = 4"));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Laundry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
        }
        .navbar {
            background: #2c3e50;
            padding: 15px;
        }
        .navbar a {
            color: white;
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
        }
        .container {
            padding: 20px;
        }
        .cards {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 200px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin: 0;
            font-size: 16px;
            color: #555;
        }
        .card p {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0 0;
        }
    </style>
</head>
<body>

<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="pelanggan.php">Data Pelanggan</a>
    <a href="transaksi.php">Transaksi</a>
    <a href="status.php">Status Laundry</a>
    <a href="laporan.php">Laporan</a>
</div>

<div class="container">
    <h2>Dashboard</h2>
    <p>Selamat datang di sistem informasi laundry</p>

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
</div>

</body>
</html>
