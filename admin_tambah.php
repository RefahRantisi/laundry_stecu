<?php
require 'auth_owner.php';
include 'koneksi.php';

$cabang_id = (int) $_GET['cabang_id'];

if (isset($_POST['username'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    mysqli_query($conn, "
        INSERT INTO users (username, password, role, owner_id, cabang_id)
        VALUES ('$username', '$password', 'admin', {$_SESSION['owner_id']}, $cabang_id)
    ");

    header("Location: cabang_detail.php?id=$cabang_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Admin</title>

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
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        /* ===== FORM CARD ===== */
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        label {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
        }

        input {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-bottom: 15px;
            font-size: 14px;
        }

        input:focus {
            border-color: #1abc9c;
            outline: none;
        }

        /* ===== BUTTON SIMPAN ===== */
        .btn {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            background: #1abc9c;
            color: white;
        }

        .btn:hover {
            background: #16a085;
        }

        /* ===== BUTTON BACK (ABU, NO HOVER HIJAU) ===== */
        .btn-back {
            display: inline-block;
            width: 100%;
            padding: 10px;
            margin-top: 15px;
            background: #bdc3c7;
            color: #2c3e50;
            text-align: center;
            text-decoration: none; /* HILANG GARIS BAWAH */
            border-radius: 6px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-back:hover {
            background: #bdc3c7;
            color: #2c3e50;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 480px) {
            .container {
                margin: 30px auto;
                padding: 15px;
            }

            .card {
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

    <h2>Tambah Admin</h2>

    <div class="card">
        <form method="post">
            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button class="btn">Simpan</button>
        </form>

        <a href="cabang_detail.php?id=<?= $cabang_id ?>" class="btn-back">
            ← Kembali ke Detail Cabang
        </a>
    </div>

</div>

</body>
</html>
