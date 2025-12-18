<?php
include 'koneksi.php';

/* =========================
   TAMBAH STATUS
========================= */
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_status']);

    mysqli_query($conn, "
        INSERT INTO laundry_status (nama_status, is_active)
        VALUES ('$nama', 1)
    ");

    header("Location: pengaturan_status.php");
    exit;
}

/* =========================
   NONAKTIFKAN STATUS (AMAN FK)
========================= */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    // Cek dipakai transaksi atau tidak
    $cek = mysqli_query($conn, "
        SELECT id FROM transactions WHERE status_id='$id' LIMIT 1
    ");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
            alert('Status sudah dipakai transaksi, tidak bisa dihapus.');
            window.location='pengaturan_status.php';
        </script>";
        exit;
    }

    // nonaktifkan
    mysqli_query($conn, "
        UPDATE laundry_status 
        SET is_active = 0 
        WHERE id='$id'
    ");

    // hapus dari alur paket
    mysqli_query($conn, "
        DELETE FROM package_status_flow WHERE status_id='$id'
    ");

    header("Location: pengaturan_status.php");
    exit;
}

/* =========================
   DATA LIST (AKTIF SAJA)
========================= */
$data = mysqli_query($conn, "
    SELECT * FROM laundry_status
    WHERE is_active = 1
    ORDER BY id DESC
");
?>


<!DOCTYPE html>
<html>

<head>
    <title>Pengaturan Status</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
        }

        .container {
            width: 700px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 6px;
        }

        /* ===== TOMBOL KEMBALI (SAMA FORMAT) ===== */
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        input,
        button {
            padding: 8px;
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

        .aksi a {
            margin-right: 8px;
            text-decoration: none;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            color: white;
        }

        .btn-delete {
            background: #e74c3c;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- TOMBOL KEMBALI -->
    <a href="pengaturan.php" class="btn-back">← Kembali ke Pengaturan</a>

    <h2>Tambah Status</h2>

    <form method="post">
        <label>Nama Status</label><br>
        <input type="text" name="nama_status" required><br><br>

        <button type="submit" name="tambah">Simpan Status</button>
    </form>

    <h3>Daftar Status</h3>
    <table>
        <tr>
            <th>Nama Status</th>
            <th>Aksi</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?= $row['nama_status'] ?></td>
                <td class="aksi">
                    <a href="?hapus=<?= $row['id'] ?>"
                       class="btn btn-delete"
                       onclick="return confirm('Hapus status ini?')">
                        Hapus
                    </a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
