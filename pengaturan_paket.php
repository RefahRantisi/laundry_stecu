<?php
include 'koneksi.php';

/* =========================
   HANDLE TAMBAH / EDIT
========================= */
if (isset($_POST['simpan'])) {
    $nama = $_POST['nama_paket'];
    $harga = $_POST['harga_per_kg'];

    if (!empty($_POST['id'])) {
        // EDIT
        $id = $_POST['id'];
        mysqli_query($conn, "
            UPDATE laundry_packages 
            SET nama_paket='$nama', harga_per_kg='$harga' 
            WHERE id='$id'
        ");
    } else {
        // TAMBAH
        mysqli_query($conn, "
            INSERT INTO laundry_packages (nama_paket, harga_per_kg)
            VALUES ('$nama', '$harga')
        ");
    }

    header("Location: pengaturan_paket.php");
    exit;
}

/* =========================
   HANDLE HAPUS
========================= */
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    // hapus alur paket dulu (jaga konsistensi)
    mysqli_query($conn, "DELETE FROM package_status_flow WHERE package_id='$id'");
    mysqli_query($conn, "DELETE FROM laundry_packages WHERE id='$id'");

    header("Location: pengaturan_paket.php");
    exit;
}

/* =========================
   DATA UNTUK EDIT
========================= */
$edit = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $q = mysqli_query($conn, "SELECT * FROM laundry_packages WHERE id='$id'");
    $edit = mysqli_fetch_assoc($q);
}

/* =========================
   DATA LIST
========================= */
$data = mysqli_query($conn, "SELECT * FROM laundry_packages ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Pengaturan Paket</title>
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

        /* ===== TOMBOL KEMBALI (DITAMBAHKAN) ===== */
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

        .aksi a {
            margin-right: 8px;
            text-decoration: none;
        }

        .hapus {
            color: red;
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- TOMBOL KEMBALI KE PENGATURAN -->
        <a href="pengaturan.php" class="btn-back">← Kembali ke Pengaturan</a>

        <h2><?= $edit ? 'Edit Paket' : 'Tambah Paket' ?></h2>

        <form method="post">
            <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">

            <label>Nama Paket</label><br>
            <input type="text" name="nama_paket" required value="<?= $edit['nama_paket'] ?? '' ?>"><br><br>

            <label>Harga per Kg</label><br>
            <input type="number" name="harga_per_kg" required value="<?= $edit['harga_per_kg'] ?? '' ?>"><br><br>

            <button type="submit" name="simpan">
                <?= $edit ? 'Update Paket' : 'Simpan Paket' ?>
            </button>

            <?php if ($edit): ?>
                <a href="pengaturan_paket.php">Batal</a>
            <?php endif; ?>
        </form>

        <h3>Daftar Paket</h3>
        <table>
            <tr>
                <th>Nama Paket</th>
                <th>Harga / Kg</th>
                <th>Aksi</th>
            </tr>

            <?php while ($row = mysqli_fetch_assoc($data)) { ?>
                <tr>
                    <td><?= $row['nama_paket'] ?></td>
                    <td>Rp <?= number_format($row['harga_per_kg']) ?></td>
                    <td class="aksi">
                        <a href="?edit=<?= $row['id'] ?>">Edit</a>
                        <a href="?hapus=<?= $row['id'] ?>" class="hapus"
                           onclick="return confirm('Hapus paket ini?')">
                            Hapus
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

</body>

</html>
