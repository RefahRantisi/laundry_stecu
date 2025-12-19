<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Pengaturan Laundry</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        /* ===== NAVBAR (SAMA DENGAN DASHBOARD) ===== */
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

        /* ===== CONTAINER ===== */
        .container {
            max-width: 1000px;
            margin: auto;
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
        }

        /* ===== CARD ===== */
        .card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* ===== MENU LINK ===== */
        .menu-link {
            display: block;
            padding: 14px;
            background: #2c3e50;
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 12px;
            font-weight: bold;
            transition: 0.3s;
        }

        .menu-link:hover {
            background: #1abc9c;
        }
    </style>
</head>

<body>

<!-- ===== NAVBAR ===== -->
<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="pelanggan.php">Data Pelanggan</a>
    <a href="transaksi.php">Transaksi</a>
    <a href="laporan.php">Laporan</a>
    <a href="pengaturan.php">Pengaturan</a>
</div>

<!-- ===== CONTENT ===== -->
<div class="container">
    <h2>Pengaturan Laundry</h2>

    <div class="card">
        <a href="pengaturan_paket.php" class="menu-link">⚙️ Pengaturan Paket</a>
        <a href="pengaturan_status.php" class="menu-link">⚙️ Pengaturan Status</a>
        <a href="pengaturan_alur.php" class="menu-link">⚙️ Pengaturan Alur Paket</a>
    </div>
</div>

</body>
</html>
