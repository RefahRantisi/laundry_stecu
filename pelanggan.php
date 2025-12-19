<?php
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
            width: 100%;
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
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
            background: #2f4050;
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
    </style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="pelanggan.php">Data Pelanggan</a>
    <a href="transaksi.php">Transaksi</a>
    <a href="laporan.php">Laporan</a>
    <a href="pengaturan.php">Pengaturan</a>
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

            <?php
            $no = 1;
            $data = mysqli_query($conn, "SELECT * FROM customers ORDER BY id DESC");
            while ($p = mysqli_fetch_assoc($data)) {
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
