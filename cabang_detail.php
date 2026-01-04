<?php
require 'auth_owner.php';
include 'koneksi.php';

$cabang_id = (int) $_GET['id'];

/* ================= TOGGLE STATUS ADMIN ================= */
if (isset($_GET['toggle'])) {
    $admin_id = (int) $_GET['toggle'];

    mysqli_query($conn, "
        UPDATE users
        SET is_active = IF(is_active = 1, 0, 1)
        WHERE id = $admin_id
        AND role = 'admin'
        AND cabang_id = $cabang_id
    ");

    header("Location: cabang_detail.php?id=$cabang_id");
    exit;
}
/* ====================================================== */

$cabang = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM laundries 
    WHERE id = $cabang_id
"));

$admin = mysqli_query($conn, "
    SELECT * FROM users
    WHERE role = 'admin'
    AND cabang_id = $cabang_id
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Cabang</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #333;
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
        }

        .navbar a:hover {
            background: #1abc9c;
        }

        /* ===== CONTAINER ===== */
        .container {
            width: 100%;
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        h2 {
            margin-bottom: 5px;
        }

        .alamat {
            color: #666;
            margin-bottom: 20px;
        }

        /* ===== CARD ===== */
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        /* ===== ACTION BAR ===== */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .btn {
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #1abc9c;
            color: white;
        }

        .btn-primary:hover {
            background: #16a085;
        }

        .btn-secondary {
            background: #bdc3c7;
            color: #2c3e50;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #2c3e50;
            color: white;
            padding: 12px;
            font-size: 14px;
            text-align: center;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            text-align: center;
        }

        /* ===== BADGE STATUS ===== */
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        .badge-active {
            background: #eafaf1;
            color: #27ae60;
        }

        .badge-inactive {
            background: #fdecea;
            color: #e74c3c;
        }

        .badge:hover {
            opacity: 0.85;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            th, td {
                font-size: 13px;
                padding: 10px;
            }
        }

        @media (max-width: 480px) {
            .action-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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

    <h2><?= htmlspecialchars($cabang['nama_laundry']) ?></h2>
    <div class="alamat"><?= htmlspecialchars($cabang['alamat']) ?></div>

    <a href="cabang.php" class="btn btn-secondary" style="margin-bottom:15px;">
        ← Kembali ke Data Cabang
    </a>

    <div class="card">

        <div class="action-bar">
            <h3>Admin Cabang</h3>
            <a href="admin_tambah.php?cabang_id=<?= $cabang_id ?>" class="btn btn-primary">
                + Tambah Admin
            </a>
        </div>

        <table>
            <tr>
                <th>Username</th>
                <th>Status</th>
            </tr>

            <?php while ($a = mysqli_fetch_assoc($admin)) : ?>
            <tr>
                <td><?= htmlspecialchars($a['username']) ?></td>
                <td>
                    <?php if ($a['is_active'] == 1): ?>
                        <a href="cabang_detail.php?id=<?= $cabang_id ?>&toggle=<?= $a['id'] ?>"
                           class="badge badge-active"
                           onclick="return confirm('Nonaktifkan admin ini?')">
                            Aktif
                        </a>
                    <?php else: ?>
                        <a href="cabang_detail.php?id=<?= $cabang_id ?>&toggle=<?= $a['id'] ?>"
                           class="badge badge-inactive"
                           onclick="return confirm('Aktifkan admin ini?')">
                            Nonaktif
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

    </div>
</div>

</body>
</html>
