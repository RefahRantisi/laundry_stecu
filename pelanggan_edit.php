<?php
include 'koneksi.php';

$id   = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM customers WHERE id='$id'");
$p    = mysqli_fetch_assoc($data);
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
            max-width: 600px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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
        }

        textarea {
            height: 80px;
            resize: none;
        }

        .btn-group {
            margin-top: 20px;
            display: flex;
            gap: 10px;
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

        .btn-back {
            background: #7f8c8d;
            padding: 10px 18px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }

        .btn-back:hover {
            background: #636e72;
        }
    </style>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<div class="navbar">
    <a href="pelanggan.php">Pelanggan</a>
    <a href="transaksi.php">Transaksi</a>
</div>

<!-- ===== CONTENT ===== -->
<div class="container">
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
            <a href="pelanggan.php" class="btn-back">Kembali</a>
        </div>
    </form>
</div>

<?php
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

</body>
</html>
