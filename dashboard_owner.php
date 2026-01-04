<?php
require 'auth_owner.php'; // 🔐 KHUSUS OWNER
include 'koneksi.php';

$ownerId = (int) ($_SESSION['user_id'] ?? 0);

/* ================== VALIDASI OWNER ================== */
if ($ownerId <= 0) {
    header("Location: login_owner.php");
    exit;
}

/* ================== RINGKASAN ================== */

$totalCabang = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM laundries 
    WHERE owner_id = $ownerId
"))['total'] ?? 0;

$totalAdmin = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS total 
    FROM users 
    WHERE role = 'admin' 
    AND owner_id = $ownerId
"))['total'] ?? 0;

$totalTransaksi = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(t.id) AS total
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    WHERE u.owner_id = $ownerId
"))['total'] ?? 0;

$totalPendapatan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(t.total_harga),0) AS total
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    WHERE u.owner_id = $ownerId 
    AND t.status_id = 4
"))['total'] ?? 0;

// Total Pelanggan (semua cabang owner)
$totalPelanggan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(DISTINCT c.id) AS total
    FROM customers c
    JOIN transactions t ON c.id = t.customer_id
    JOIN users u ON t.user_id = u.id
    WHERE u.owner_id = $ownerId
"));



/* ================== GRAFIK PENDAPATAN PER CABANG ================== */
$qGrafik = mysqli_query($conn, "
    SELECT 
        l.nama_laundry,
        COALESCE(SUM(t.total_harga), 0) AS pendapatan
    FROM laundries l
    LEFT JOIN users u 
        ON u.cabang_id = l.id
        AND u.role = 'admin'
    LEFT JOIN transactions t 
        ON t.user_id = u.id
        AND t.status_id = 4
    WHERE l.owner_id = $ownerId
    GROUP BY l.id
");


$labels = [];
$data = [];

while ($g = mysqli_fetch_assoc($qGrafik)) {
    $labels[] = $g['nama_laundry'];
    $data[] = (int) $g['pendapatan'];
}
?>



<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Laundry</title>

    <style>
        /* ===== RESET & BASE STYLE ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        ::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        /* ===== NAVBAR (Konsisten dengan Transaksi) ===== */
        .navbar {
            background: #2c3e50;
            padding: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            position: relative;
            min-height: 56px;
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

        /* Burger Menu Button */
        .burger-menu {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            position: absolute;
            left: 15px;
            z-index: 1000;
        }

        .burger-menu span {
            display: block;
            width: 25px;
            height: 3px;
            background: white;
            margin: 5px 0;
            transition: 0.3s;
            border-radius: 2px;
        }

        /* Nav Links Container */
        .nav-links {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* ===== CONTAINER (Disesuaikan lebarnya dengan style Transaksi) ===== */
        .container {
            width: 100%;
            max-width: 100%;
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }

        h3 {
            margin-bottom: 15px;
            color: #555;
            font-size: 18px;
        }

        /* ===== CARDS SECTION ===== */
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 40px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            flex: 1;
            min-width: 200px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: 0.3s;
        }

        .card h3 {
            margin: 0;
            font-size: 16px;
            color: #7f8c8d;
            font-weight: bold;
        }

        .card p {
            font-size: 28px;
            font-weight: bold;
            margin-top: 10px;
            color: #2c3e50;
        }

        .card-link {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex: 1;
        }

        .card-link .card:hover {
            transform: translateY(-5px);
            background: #fdfdfd;
            border: 1px solid #1abc9c;
        }

        /* ===== STATUS TABLE (Meniru style Card di halaman Transaksi) ===== */
        .status-section {
            margin-top: 20px;
        }

        .status-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #2c3e50;
            color: white;
            padding: 12px;
            text-align: center;
            font-size: 14px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: center;
            font-size: 14px;
            color: #444;
        }

        /* ===== BUTTONS (Warna konsisten dengan tombol Simpan) ===== */
        .status-btn {
            padding: 8px 15px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            color: #fff;
            background: #1abc9c;
            transition: 0.3s;
            font-size: 12px;
            text-transform: uppercase;
        }

        .status-btn:hover {
            background: #16a085;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .badge-selesai {
            color: #27ae60;
            font-weight: bold;
            background: #eafaf1;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
        }

        /* ===== RESPONSIVE DESIGN ===== */

        /* Desktop/Layar Besar (1025px+) */
        @media (min-width: 1025px) {
            .container {
                padding: 30px 50px;
            }

            .cards {
                gap: 25px;
            }
        }

        /* Tablet Lanskap/Laptop Kecil (769px - 1024px) */
        @media (min-width: 769px) and (max-width: 1024px) {
            .container {
                padding: 25px 30px;
            }

            .cards {
                gap: 18px;
            }

            .card {
                min-width: 180px;
            }

            .card p {
                font-size: 24px;
            }
        }

        /* Ponsel Besar/Tablet (481px - 768px) */
        @media (min-width: 481px) and (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 22px;
            }

            h3 {
                font-size: 16px;
            }

            .cards {
                gap: 15px;
            }

            .card {
                min-width: 150px;
                padding: 15px;
            }

            .card h3 {
                font-size: 14px;
            }

            .card p {
                font-size: 22px;
            }

            .status-card {
                padding: 15px;
            }

            th,
            td {
                padding: 10px;
                font-size: 13px;
            }

            .status-btn {
                padding: 6px 12px;
                font-size: 11px;
            }
        }

        /* Ponsel Kecil (320px - 480px) */
        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            h2 {
                font-size: 20px;
                margin-bottom: 15px;
            }

            h3 {
                font-size: 15px;
                margin-bottom: 10px;
            }

            .cards {
                gap: 12px;
            }

            .card {
                min-width: 100%;
                padding: 15px;
            }

            .card h3 {
                font-size: 13px;
            }

            .card p {
                font-size: 20px;
                margin-top: 8px;
            }

            .status-card {
                padding: 12px;
            }

            th,
            td {
                padding: 8px 5px;
                font-size: 12px;
            }

            .status-btn {
                padding: 5px 10px;
                font-size: 10px;
            }

            table {
                font-size: 11px;
            }
        }

        /* Burger Menu untuk layar < 600px */
        @media (max-width: 600px) {
            .burger-menu {
                display: block;
            }

            .navbar {
                justify-content: center;
                padding: 15px;
                min-height: 56px;
            }

            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #2c3e50;
                padding: 0;
                gap: 0;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                z-index: 999;
            }

            .nav-links.active {
                display: flex;
            }

            .navbar a {
                width: 100%;
                text-align: center;
                padding: 15px 18px;
                border-radius: 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .navbar a:last-child {
                border-bottom: none;
            }

            .navbar a:hover {
                background: #1abc9c;
            }

            /* Animasi Burger Menu */
            .burger-menu.active span:nth-child(1) {
                transform: rotate(-45deg) translate(-5px, 6px);
            }

            .burger-menu.active span:nth-child(2) {
                opacity: 0;
            }

            .burger-menu.active span:nth-child(3) {
                transform: rotate(45deg) translate(-5px, -6px);
            }
        }
    </style>

    <script>
        function konfirmasi(status) {
            return confirm('Ubah status menjadi "' + status + '" ?');
        }
    </script>
</head>

<body>

    <!-- ===== NAVBAR ===== -->
    <!-- ===== NAVBAR dengan BURGER MENU ===== -->
    <div class="navbar">
        <!-- Burger Menu Button -->
        <button class="burger-menu" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Navigation Links -->
        <div class="nav-links" id="navLinks">
            <a href="dashboard_owner.php">Dashboard</a>
            <a href="data_cabang.php">Data Cabang</a>
            <a href="laporan_owner.php">Laporan</a>
            <a href="logout.php">Keluar</a>
        </div>
    </div>

    <script>
        // Fungsi untuk toggle burger menu
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            const burgerMenu = document.querySelector('.burger-menu');

            navLinks.classList.toggle('active');
            burgerMenu.classList.toggle('active');
        }

        // Menutup menu saat link diklik (opsional)
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                const navLinks = document.getElementById('navLinks');
                const burgerMenu = document.querySelector('.burger-menu');

                navLinks.classList.remove('active');
                burgerMenu.classList.remove('active');
            });
        });

        // Fungsi konfirmasi (sudah ada sebelumnya)
        function konfirmasi(status) {
            return confirm('Ubah status menjadi "' + status + '" ?');
        }
    </script>

    <div class="container">
        <h2>Dashboard</h2>
        <h3>Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?></h3>

        <!-- CARDS -->
        <div class="cards">

            <div class="card">
                <h3>Total Cabang</h3>
                <p><?= $totalCabang ?></p>
            </div>

            <div class="card">
                <h3>Total Admin</h3>
                <p><?= $totalAdmin ?></p>
            </div>
            <div class="card">
                <h3>Total Pelanggan</h3>
                <p><?= $totalPelanggan['total'] ?>
                </p>
            </div>
            <div class="card">
                <h3>Total Transaksi</h3>
                <p><?= $totalTransaksi ?></p>
            </div>

            <div class="card">
                <h3>Total Pendapatan</h3>
                <p>Rp <?= number_format($totalPendapatan ?? 0, 0, ',', '.') ?></p>
            </div>

        </div>



        <div class="status-card">
            <h3>Pendapatan per Cabang</h3>
            <canvas id="grafikCabang"></canvas>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const ctx = document.getElementById('grafikCabang');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: <?= json_encode($data) ?>,
                        borderWidth: 1
                    }]
                }
            });
        </script>

    </div>
</body>

</html>