<?php

require 'auth.php';
include 'koneksi.php';

if (isset($_POST['simpan'])) {

    $nama   = $_POST['nama'];
    $telp   = $_POST['no_telp'];
    $alamat = $_POST['alamat'];

    mysqli_query($conn, "
        INSERT INTO customers (nama, no_telp, alamat, cabang_id)
        VALUES ('$nama','$telp','$alamat', '$cabang_id')
    ");

    // Ambil ID customer yang baru ditambahkan
    $customer_id = mysqli_insert_id($conn);

    // Redirect ke halaman transaksi
    header("Location: transaksi.php?customer_id=$customer_id&nama=" . urlencode($nama));
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pelanggan</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        /* ===== CONTENT ===== */
        .container {
            max-width: 600px;
            margin: 25px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* tombol kembali di atas */
        .top-bar {
            margin-bottom: 15px;
        }

        .btn-back {
            display: inline-block;
            margin-bottom: 15px;
            padding: 8px 14px;
            background: #2c3e50;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-back:hover {
            background: #1abc9c;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            font-weight: bold;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        textarea {
            height: 80px;
            resize: none;
        }

        .btn-group {
            margin-top: 20px;
            text-align: right;
        }

        button {
            background: #1abc9c;
            border: none;
            color: white;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #16a085;
        }
    </style>
</head>
<body>


<!-- ===== CONTENT ===== -->
<div class="container">

    <!-- TOMBOL KEMBALI DI ATAS -->
    <div class="top-bar">
        <a href="pelanggan.php" class="btn-back">← Kembali</a>
    </div>

    <h2>Tambah Pelanggan</h2>

    <form method="POST">
        <label>Nama</label>
        <input type="text" name="nama" required>

        <br><br>

        <label>No. Telp</label>
        <input type="text" name="no_telp" required>

        <br><br>

        <label>Alamat</label>
        <textarea name="alamat" required></textarea>

        <div class="btn-group">
            <button type="submit" name="simpan">Simpan</button>
        </div>
    </form>
</div>

</body>
</html>
