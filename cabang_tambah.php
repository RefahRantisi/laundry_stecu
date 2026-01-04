<?php
require 'auth_owner.php';
include 'koneksi.php';

$owner_id = $_SESSION['owner_id'];

if (isset($_POST['nama'])) {
    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    mysqli_query($conn, "
        INSERT INTO laundries (owner_id, nama_laundry, alamat)
        VALUES ($owner_id, '$nama', '$alamat')
    ");

    header("Location: cabang.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Cabang</title>

    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
        }

        .container {
            width: 600px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 6px;
        }

        /* ===== BUTTON BACK ===== */
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

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input, textarea {
            padding: 8px;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        form button[type="submit"] {
            padding: 10px 20px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        form button[type="submit"]:hover {
            background: #27ae60;
        }

    </style>
</head>

<body>

<div class="container">

    <!-- BACK -->
    <a href="cabang.php" class="btn-back">← Kembali ke Data Cabang</a>

    <h2>Tambah Cabang</h2>

    <form method="post">
        <label>Nama Cabang</label>
        <input type="text" name="nama" required placeholder="Contoh: Laundry STECU Cabang 2">
        <br><br>

        <label>Alamat</label>
        <textarea name="alamat" placeholder="Alamat lengkap cabang"></textarea>
        <br><br>

        <button type="submit">Simpan Cabang</button>
    </form>

</div>

</body>
</html>
