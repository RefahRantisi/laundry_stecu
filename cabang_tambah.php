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
        /* ===== RESET ===== */
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
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
        }

        h2 {
            margin-bottom: 20px;
        }

        /* ===== CARD FORM ===== */
        .form-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 14px;
        }

        input, textarea {
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: #1abc9c;
        }

        /* ===== BUTTON ===== */
        .btn {
            padding: 10px 18px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
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
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background: #95a5a6;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 480px) {
            .container {
                margin: 20px auto;
                padding: 15px;
            }

            .form-card {
                padding: 20px;
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
    <h2>Tambah Cabang</h2>

    <div class="form-card">
        <form method="post">
            <div class="form-group">
                <label>Nama Cabang</label>
                <input type="text" name="nama" required>
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="cabang.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

</body>
</html>
