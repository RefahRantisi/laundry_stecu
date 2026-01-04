<?php
require 'auth_owner.php';
include 'koneksi.php';

$owner_id = $_SESSION['owner_id'];

$cabang = mysqli_query($conn, "
    SELECT 
        l.id,
        l.nama_laundry,
        l.alamat,
        COUNT(u.id) AS total_admin
    FROM laundries l
    LEFT JOIN users u
        ON u.cabang_id = l.id
        AND u.role = 'admin'
    WHERE l.owner_id = $owner_id
    GROUP BY l.id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Cabang</title>
    <style>
        /* ================== COPY 100% DARI dashboard_owner.php ================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
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

        /* ===== CONTAINER ===== */
        .container {
            width: 100%;
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
        }

        /* ===== CARD TABLE ===== */
        .status-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow-x: auto;
        }

        /* ===== BUTTON ===== */
        .btn {
            display: inline-block;
            margin-bottom: 15px;
            padding: 10px 18px;
            background: #1abc9c;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
        }

        .btn:hover {
            background: #16a085;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #2c3e50;
            color: white;
            padding: 12px;
            font-size: 14px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: center;
            font-size: 14px;
        }

        .aksi a {
            padding: 6px 12px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .aksi a:hover {
            background: #2980b9;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            th, td {
                font-size: 12px;
                padding: 10px;
            }
        }

        @media (max-width: 480px) {
            th, td {
                font-size: 11px;
                padding: 8px;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <a href="dashboard_owner.php">Dashboard</a>
    <a href="cabang.php">Data Cabang</a>
    <a href="laporan_owner.php">Laporan</a>
    <a href="logout.php">Keluar</a>
</div>

<div class="container">
    <h2>Data Cabang</h2>

    <a href="cabang_tambah.php" class="btn">+ Tambah Cabang</a>

    <div class="status-card">
        <table>
            <tr>
                <th>Nama Cabang</th>
                <th>Alamat</th>
                <th>Jumlah Admin</th>
                <th>Aksi</th>
            </tr>

            <?php while ($c = mysqli_fetch_assoc($cabang)) : ?>
            <tr>
                <td><?= htmlspecialchars($c['nama_laundry']) ?></td>
                <td><?= htmlspecialchars($c['alamat']) ?></td>
                <td><?= $c['total_admin'] ?></td>
                <td class="aksi">
                    <a href="cabang_detail.php?id=<?= $c['id'] ?>" class="btn-detail">Detail</a>
                    <a href="cabang_hapus.php?id=<?= $c['id'] ?>"
                    class="btn-hapus"
                    onclick="return confirm('Yakin hapus cabang ini? Semua data cabang akan hilang!')">
                    Hapus
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

</body>
</html>
