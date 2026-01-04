<?php

require 'auth_owner.php';

include 'koneksi.php';

$ownerId = (int) $_SESSION['user_id'];

/* LIST CABANG OWNER */
$qCabangList = mysqli_query($conn, "
    SELECT id, nama_laundry
    FROM laundries
    WHERE owner_id = $ownerId
");


/* FILTER */
$where = "";

/* FILTER TANGGAL */
if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $from = mysqli_real_escape_string($conn, $_GET['from']);
    $to = mysqli_real_escape_string($conn, $_GET['to']);
    $where .= " AND DATE(t.tanggal) BETWEEN '$from' AND '$to'";
}

/* FILTER CABANG */
if (!empty($_GET['cabang_id'])) {
    $cabangId = (int) $_GET['cabang_id'];
    $where .= " AND l.id = $cabangId";
}


/* DATA LAPORAN */
$ownerId = (int) $_SESSION['user_id'];

$query = mysqli_query($conn, "
    SELECT
        l.nama_laundry      AS nama_cabang,
        u.username          AS nama_admin,
        p.nama_paket,
        t.total_harga,
        t.tanggal
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    JOIN laundries l ON u.cabang_id = l.id
    JOIN laundry_packages p ON t.package_id = p.id
    JOIN laundry_status s ON t.status_id = s.id
    WHERE 
        u.owner_id = $ownerId
        AND s.is_fixed = 2
        $where
    ORDER BY t.tanggal DESC
");


/* TOTAL PENDAPATAN */
$total = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(SUM(t.total_harga),0) AS total_pendapatan
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    JOIN laundries l ON u.cabang_id = l.id
    JOIN laundry_status s ON t.status_id = s.id
    WHERE 
        u.owner_id = $ownerId
        AND s.is_fixed = 2
        $where
"));

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Laundry</title>

    <style>
        /* RESET */
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

        /* BODY */
        body {
            background-color: #f4f6f9;
            color: #333;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        /* ===== NAVBAR ===== */
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

        /* CONTAINER */
        .container {
            padding: 30px;
        }

        /* JUDUL */
        h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }

        /* FILTER */
        .filter-box {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .filter-box label {
            margin-right: 10px;
            font-weight: bold;
            color: #555;
        }

        .filter-box input[type="date"] {
            padding: 8px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .filter-box button {
            padding: 8px 16px;
            background: #2c3e50;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .filter-box button:hover {
            background: #1abc9c;
        }

        /* TABLE */
        .table-wrapper {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #2c3e50;
            color: #fff;
            padding: 12px;
            text-align: left;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        table tr:hover {
            background: #f1f5f9;
        }

        /* TOTAL */
        .total-box {
            margin-top: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: right;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .total-box strong {
            font-size: 20px;
        }

        /* BACK */
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* ===== RESPONSIVE DESIGN ===== */

        /* Desktop/Layar Besar (1025px+) */
        @media (min-width: 1025px) {
            .container {
                padding: 30px 50px;
            }
        }

        /* Tablet Lanskap/Laptop Kecil (769px - 1024px) */
        @media (min-width: 769px) and (max-width: 1024px) {
            .container {
                padding: 25px 30px;
            }

            .filter-box,
            .table-wrapper,
            .total-box {
                padding: 18px;
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

            .filter-box,
            .table-wrapper,
            .total-box {
                padding: 16px;
            }

            .filter-box form {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
            }

            .filter-box label {
                margin-right: 5px;
            }

            .filter-box input[type="date"] {
                margin-right: 5px;
                padding: 7px;
            }

            .filter-box button {
                padding: 7px 14px;
                font-size: 13px;
            }

            table th,
            table td {
                padding: 10px;
                font-size: 13px;
            }

            .total-box strong {
                font-size: 18px;
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

            .filter-box {
                padding: 15px;
                margin-bottom: 20px;
            }

            .filter-box form {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .filter-box label {
                margin-right: 0;
                display: block;
                margin-bottom: 5px;
            }

            .filter-box input[type="date"] {
                width: 100%;
                margin-right: 0;
                padding: 10px;
            }

            .filter-box button {
                width: 100%;
                padding: 10px;
                font-size: 14px;
            }

            .table-wrapper {
                padding: 12px;
            }

            table th,
            table td {
                padding: 8px 5px;
                font-size: 11px;
            }

            .total-box {
                padding: 15px;
                text-align: center;
            }

            .total-box strong {
                font-size: 18px;
                display: block;
                margin-top: 5px;
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

</head>

<body>
    <!-- ===== NAVBAR ===== -->
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

    <div class="container">
        <h2>Laporan Laundry</h2>

        <!-- FILTER -->
        <div class="filter-box">
    <form method="GET">
        <label>Dari:</label>
        <input type="date" name="from" value="<?= $_GET['from'] ?? '' ?>">
        
                <label>Sampai:</label>
                <input type="date" name="to" value="<?= $_GET['to'] ?? '' ?>">
        
                <label>Cabang:</label>
                <select name="cabang_id">
                    <option value="">Semua Cabang</option>
                    <?php while ($c = mysqli_fetch_assoc($qCabangList)): ?>
                        <option value="<?= $c['id'] ?>" <?= (($_GET['cabang_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nama_laundry']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
        
                <button type="submit">Filter</button>
            </form>
        </div>


        <!-- TABLE -->
        <div class="table-wrapper">
            <table>
                <tr>
                    <th>Cabang</th>
                    <th>Admin</th>
                    <th>Paket Laundry</th>
                    <th>Total Harga</th>
                    <th>Tanggal</th>
                </tr>

                <?php if (mysqli_num_rows($query) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($query)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_cabang']) ?></td>
                            <td><?= htmlspecialchars($row['nama_admin']) ?></td>
                            <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                            <td>Rp <?= number_format($row['total_harga'], 0, ',', '.') ?></td>
                            <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" align="center">Data tidak ditemukan</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- TOTAL -->
        <div class="total-box">
            Total Pendapatan:
            <strong>Rp
                <?= number_format($total['total_pendapatan'] ?? 0, 0, ',', '.') ?>
            </strong>
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

        // Menutup menu saat link diklik
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                const navLinks = document.getElementById('navLinks');
                const burgerMenu = document.querySelector('.burger-menu');

                navLinks.classList.remove('active');
                burgerMenu.classList.remove('active');
            });
        });
    </script>

</body>

</html>