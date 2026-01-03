<?php

require 'auth.php';
include 'koneksi.php';

/* =========================
   TAMBAH / EDIT KATEGORI & SATUAN
========================= */
if (isset($_POST['simpan'])) {
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori_barang']);
    $satuan   = mysqli_real_escape_string($conn, $_POST['nama_satuan']);
    $ket      = mysqli_real_escape_string($conn, $_POST['keterangan']);

    if (!empty($_POST['id'])) {
        $id = (int) $_POST['id'];
        mysqli_query($conn, "
            UPDATE laundry_units 
            SET kategori_barang='$kategori', 
                nama_satuan='$satuan', 
                keterangan='$ket'
            WHERE id='$id'
        ");
    } else {
        mysqli_query($conn, "
            INSERT INTO laundry_units (kategori_barang, nama_satuan, keterangan, is_active)
            VALUES ('$kategori', '$satuan', '$ket', 1)
        ");
    }

    header("Location: pengaturan_kategori.php");
    exit;
}

/* =========================
   NONAKTIFKAN KATEGORI (SOFT DELETE)
========================= */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    mysqli_query($conn, "
        UPDATE laundry_units 
        SET is_active = 0 
        WHERE id='$id'
    ");

    header("Location: pengaturan_kategori.php");
    exit;
}

/* =========================
   DATA EDIT
========================= */
$edit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $q = mysqli_query($conn, "
        SELECT * FROM laundry_units 
        WHERE id='$id' AND is_active=1
    ");
    $edit = mysqli_fetch_assoc($q);
}

/* =========================
   DATA LIST (HANYA AKTIF)
========================= */
$data = mysqli_query($conn, "
    SELECT * FROM laundry_units
    WHERE is_active = 1
    ORDER BY id DESC
");
?>


<!DOCTYPE html>
<html>

<head>
    <title>Pengaturan Kategori & Satuan</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
        }

        .container {
            width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 6px;
        }

        /* ===== TOMBOL KEMBALI ===== */
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

        input, textarea {
            padding: 8px;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        textarea {
            resize: vertical;
            min-height: 60px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
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

        .btn-edit {
            background: #f39c12;
        }

        .btn-edit:hover {
            background: #e67e22;
        }

        .btn-delete {
            background: #e74c3c;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .btn-cancel {
            display: inline-block;
            padding: 10px 20px;
            background: #95a5a6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-left: 10px;
        }

        .btn-cancel:hover {
            background: #7f8c8d;
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- TOMBOL KEMBALI KE PENGATURAN -->
        <a href="pengaturan.php" class="btn-back">← Kembali ke Pengaturan</a>

        <h2><?= $edit ? 'Edit Kategori & Satuan' : 'Tambah Kategori & Satuan' ?></h2>

        <form method="post">
            <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">

            <label>Kategori Barang</label>
            <input type="text" name="kategori_barang" required value="<?= $edit['kategori_barang'] ?? '' ?>" placeholder="Contoh: Baju, Sepatu, Selimut">
            <br><br>

            <label>Nama Satuan</label>
            <input type="text" name="nama_satuan" required value="<?= $edit['nama_satuan'] ?? '' ?>" placeholder="Contoh: Kg, Pasang, Pcs">
            <br><br>

            <label>Keterangan</label>
            <textarea name="keterangan" placeholder="Keterangan tambahan (opsional)"><?= $edit['keterangan'] ?? '' ?></textarea>
            <br><br>

            <button type="submit" name="simpan">
                <?= $edit ? 'Update Data' : 'Simpan Data' ?>
            </button>

            <?php if ($edit): ?>
                <a href="pengaturan_kategori.php" class="btn-cancel">Batal</a>
            <?php endif; ?>
        </form>

        <h3>Daftar Kategori & Satuan</h3>
        <table>
            <tr>
                <th>Kategori Barang</th>
                <th>Nama Satuan</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>

            <?php while ($row = mysqli_fetch_assoc($data)) { ?>
                <tr>
                    <td><?= $row['kategori_barang'] ?></td>
                    <td><?= $row['nama_satuan'] ?></td>
                    <td><?= $row['keterangan'] ?: '-' ?></td>
                    <td class="aksi">
                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="?hapus=<?= $row['id'] ?>" class="btn btn-delete"
                           onclick="return confirm('Hapus data ini?')">
                            Hapus
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

</body>

</html>