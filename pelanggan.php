<?php

require 'auth.php';

include 'koneksi.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>Data Pelanggan</title>

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

        p {
            margin-bottom: 20px;
            color: #666;
        }

        /* ===== CARD ===== */
        .content-card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
        }

        /* ===== BUTTON ===== */
        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            color: white;
            transition: 0.3s;
        }

        .btn-add {
            background: #1abc9c;
            margin-bottom: 15px;
        }

        .btn-edit {
            background: #f39c12;
        }

        .btn-delete {
            background: #e74c3c;
        }

        .btn:hover {
            opacity: 0.85;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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
                padding: 20px;
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

            .content-card {
                padding: 18px;
            }

            table th,
            table td {
                padding: 10px;
                font-size: 13px;
            }

            .btn {
                padding: 7px 12px;
                font-size: 13px;
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

            .content-card {
                padding: 15px;
            }

            table th,
            table td {
                padding: 8px 5px;
                font-size: 12px;
            }

            .btn {
                padding: 6px 10px;
                font-size: 11px;
                margin: 2px 0;
            }

            .btn-add {
                display: block;
                text-align: center;
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

    <!-- NAVBAR -->
    <div class="navbar">
        <!-- Burger Menu Button -->
        <button class="burger-menu" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Navigation Links -->
        <div class="nav-links" id="navLinks">
            <a href="index.php">Dashboard</a>
            <a href="pelanggan.php">Data Pelanggan</a>
            <a href="transaksi.php">Transaksi</a>
            <a href="laporan.php">Laporan</a>
            <a href="pengaturan.php">Pengaturan</a>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="container">
        <h2>Data Pelanggan</h2>

        <div class="content-card">
            <a href="pelanggan_tambah.php" class="btn btn-add">+ Tambah Pelanggan</a>

            <table>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>No. Telp</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>

                <tr>
                    <td>1</td>
                    <td>John Doe</td>
                    <td>081234567890</td>
                    <td>Jl. Merdeka No. 123</td>
                    <td>
                        <a class="btn btn-edit" href="pelanggan_edit.php?id=1">Edit</a>
                        <a class="btn btn-delete" href="pelanggan_hapus.php?id=1"
                            onclick="return confirm('Yakin hapus pelanggan?')">Hapus</a>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Jane Smith</td>
                    <td>082345678901</td>
                    <td>Jl. Sudirman No. 456</td>
                    <td>
                        <a class="btn btn-edit" href="pelanggan_edit.php?id=2">Edit</a>
                        <a class="btn btn-delete" href="pelanggan_hapus.php?id=2"
                            onclick="return confirm('Yakin hapus pelanggan?')">Hapus</a>
                    </td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>Ahmad Rizki</td>
                    <td>083456789012</td>
                    <td>Jl. Gatot Subroto No. 789</td>
                    <td>
                        <a class="btn btn-edit" href="pelanggan_edit.php?id=3">Edit</a>
                        <a class="btn btn-delete" href="pelanggan_hapus.php?id=3"
                            onclick="return confirm('Yakin hapus pelanggan?')">Hapus</a>
                    </td>
                </tr>
            </table>
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