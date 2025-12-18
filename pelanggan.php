<?php
session_start();
if (!isset($_SESSION['login'])) {
    echo "<script>
        alert('Silakan login terlebih dahulu');
        window.location='index.php';
    </script>";
    exit;
}
include 'koneksi.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Pelanggan</title>
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

        /* ===== CARD ===== */
        .content-card {
            background: white;
            padding: 25px;
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: #2c3e50;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f1f1f1;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="pelanggan.php">Data Pelanggan</a>
    <a href="transaksi.php">Transaksi</a>
    <a href="status.php">Status Laundry</a>
    <a href="laporan.php">Laporan</a>
</div>

<!-- CONTENT -->
<div class="container">
    <h2>Data Pelanggan</h2>
    <p>Kelola data pelanggan laundry</p>

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

            <?php
            $no = 1;
            $data = mysqli_query($conn,"SELECT * FROM customers ORDER BY id DESC");
            while($p = mysqli_fetch_assoc($data)){
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $p['nama'] ?></td>
                <td><?= $p['no_telp'] ?></td>
                <td><?= $p['alamat'] ?></td>
                <td>
                    <a class="btn btn-edit" href="pelanggan_edit.php?id=<?= $p['id'] ?>">Edit</a>
                    <a class="btn btn-delete"
                       href="pelanggan_hapus.php?id=<?= $p['id'] ?>"
                       onclick="return confirm('Yakin hapus pelanggan?')">Hapus</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>

</body>
</html>
