<?php

require 'auth.php';

?>
<!DOCTYPE html>
<html>

<head>
    <title>Pengaturan Laundry</title>
    <style>
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

        /* ===== CONTAINER ===== */
        .container {
            width: 100%;
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

        /* ===== CONTENT CARD (Sama seperti Data Pelanggan) ===== */
        .content-card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        /* ===== SECTION ===== */
        .section {
            margin-bottom: 30px;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section h3 {
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
            margin-bottom: 15px;
        }

        /* ===== MENU LINK (Dimodifikasi seperti button di halaman lain) ===== */
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

        .menu-link.danger {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }

        .menu-link.danger:hover {
            background: #1abc9c;
        }

        /* Icon dalam link */
        .menu-link::before {
            content: "⚙️ ";
            margin-right: 8px;
        }

        .menu-link.danger::before {
            content: "🚪 ";
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

            .content-card {
                padding: 22px;
            }

            .menu-link {
                padding: 11px 15px;
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

            .content-card {
                padding: 18px;
                margin-bottom: 20px;
            }

            .menu-link {
                padding: 10px 14px;
                font-size: 14px;
                margin-bottom: 8px;
            }

            .section {
                margin-bottom: 25px;
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
                margin-bottom: 12px;
            }

            .content-card {
                padding: 15px;
                margin-bottom: 15px;
            }

            .menu-link {
                padding: 12px;
                font-size: 13px;
                margin-bottom: 8px;
            }

            .section {
                margin-bottom: 20px;
            }

            .section h3 {
                padding-bottom: 8px;
                margin-bottom: 12px;
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
            <a href="dashboard.php">Dashboard</a>
            <a href="pelanggan.php">Data Pelanggan</a>
            <a href="transaksi.php">Transaksi</a>
            <a href="laporan.php">Laporan</a>
            <a href="pengaturan.php">Pengaturan</a>
        </div>
    </div>

    <!-- ===== CONTENT ===== -->
    <div class="container">
        <h2>Pengaturan Laundry</h2>

        <div class="content-card">
            <!-- Section 1: Paket dan Status -->
            <div class="section">
                <h3>Paket dan Status</h3>
                <a href="pengaturan_kategori.php" class="menu-link">Pengaturan Kategori</a>
                <a href="pengaturan_paket.php" class="menu-link">Pengaturan Paket</a>
                <a href="pengaturan_status.php" class="menu-link">Pengaturan Status</a>
                <a href="pengaturan_alur.php" class="menu-link">Pengaturan Alur Paket</a>
            </div>

            <!-- Section 2: Akun -->
            <div class="section">
                <h3>Akun</h3>
                <a href="ganti_password.php" class="menu-link">Ganti Password</a>
                <a href="logout.php" class="menu-link danger"
                    onclick="return confirm('Yakin mau logout dari sistem?');">Keluar</a>
            </div>
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