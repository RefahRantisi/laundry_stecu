<?php
include 'koneksi.php';

$id   = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM customers WHERE id='$id'");
$p    = mysqli_fetch_assoc($data);

if (isset($_POST['update'])) {
    $nama   = $_POST['nama'];
    $telp   = $_POST['no_telp'];
    $alamat = $_POST['alamat'];

    mysqli_query($conn, "
        UPDATE customers SET
        nama='$nama',
        no_telp='$telp',
        alamat='$alamat'
        WHERE id='$id'
    ");

    header("Location: pelanggan.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pelanggan</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        /* ===== CONTENT ===== */
        .container {
            max-width: 600px;
            margin: 40px auto;
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
            background: #7f8c8d;
            padding: 8px 14px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            display: inline-block;
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

<div class="container">

    <!-- TOMBOL KEMBALI DI ATAS -->
    <div class="top-bar">
        <a href="pelanggan.php" class="btn-back">← Kembali</a>
    </div>

    <h2>Edit Pelanggan</h2>

    <form method="POST">
        <label>Nama</label>
        <input type="text" name="nama" value="<?= $p['nama']; ?>" required>

        <br><br>

        <label>No. Telp</label>
        <input type="text" name="no_telp" value="<?= $p['no_telp']; ?>" required>

        <br><br>

        <label>Alamat</label>
        <textarea name="alamat" required><?= $p['alamat']; ?></textarea>

        <div class="btn-group">
            <button type="submit" name="update">Update</button>
        </div>
    </form>
</div>

</body>
</html>
