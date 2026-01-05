<?php
require 'auth.php';
require 'koneksi.php';

/* =========================
   INIT
========================= */
$id = null;
$nama_status = '';
$is_fixed = 0;

$awal_sudah_ada  = false;
$akhir_sudah_ada = false;

/* =========================
   CEK STATUS AWAL & AKHIR
========================= */
$cek = mysqli_query($conn, "
    SELECT is_fixed
    FROM laundry_status
    WHERE is_active = 1
      AND cabang_id = '$cabang_id'
");

while ($s = mysqli_fetch_assoc($cek)) {
    if ($s['is_fixed'] == 1) $awal_sudah_ada = true;
    if ($s['is_fixed'] == 2) $akhir_sudah_ada = true;
}

/* =========================
   EDIT
========================= */
if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $q = mysqli_query($conn, "
        SELECT *
        FROM laundry_status
        WHERE id='$id'
          AND cabang_id='$cabang_id'
          AND is_active=1
    ");
    if ($row = mysqli_fetch_assoc($q)) {
        $nama_status = $row['nama_status'];
        $is_fixed    = (int) $row['is_fixed'];
    }
}

/* =========================
   SIMPAN
========================= */
if (isset($_POST['simpan'])) {
    $nama_status = mysqli_real_escape_string($conn, $_POST['nama_status']);
    $is_fixed    = (int) $_POST['is_fixed'];
    $edit_id     = $_POST['id'] ?? null;

    if ($edit_id) {
        mysqli_query($conn, "
            UPDATE laundry_status SET
                nama_status='$nama_status',
                is_fixed='$is_fixed'
            WHERE id='$edit_id'
              AND cabang_id='$cabang_id'
        ");
    } else {
        mysqli_query($conn, "
            INSERT INTO laundry_status
            (nama_status, is_fixed, is_active, cabang_id)
            VALUES ('$nama_status', '$is_fixed', 1, '$cabang_id')
        ");
    }

    header("Location: pengaturan_status.php");
    exit;
}

/* =========================
   HAPUS
========================= */
if (isset($_GET['hapus'])) {
    $hapus_id = (int) $_GET['hapus'];
    mysqli_query($conn, "
        UPDATE laundry_status
        SET is_active = 0
        WHERE id='$hapus_id'
          AND cabang_id='$cabang_id'
    ");
    header("Location: pengaturan_status.php");
    exit;
}

/* =========================
   LIST
========================= */
$data = mysqli_query($conn, "
    SELECT *
    FROM laundry_status
    WHERE is_active = 1
      AND cabang_id='$cabang_id'
    ORDER BY id ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            margin: 0;
        }
        .container {
            max-width: 700px;
            width: 95%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }
        .btn-back {
            display: inline-block;
            margin-bottom: 15px;
            padding: 10px 16px;
            background: #2c3e50;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
        }
        .btn-back:hover { background: #1abc9c; }
        .btn { padding: 8px 14px; border-radius: 6px; color: #fff; text-decoration: none; }
        .btn-delete { background: #e74c3c; }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 6px 0 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            padding: 10px 20px;
            background: #2ecc71;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        th { background: #f7f7f7; }
    </style>
</head>

<body>
<div class="container">

    <a href="pengaturan.php" class="btn-back">← Kembali ke Pengaturan</a>

    <h2><?= $id ? 'Edit Status' : 'Tambah Status' ?></h2>

    <form method="post">
        <?php if ($id): ?>
            <input type="hidden" name="id" value="<?= $id ?>">
        <?php endif; ?>

        <label>Nama Status</label>
        <input type="text" name="nama_status" required value="<?= htmlspecialchars($nama_status) ?>">

        <label>
            <input type="radio" name="is_fixed" value="1"
                <?= $is_fixed === 1 ? 'checked' : '' ?>
                <?= ($awal_sudah_ada && $is_fixed !== 1) ? 'disabled' : '' ?>>
            Status awal
        </label><br>

        <label>
            <input type="radio" name="is_fixed" value="2"
                <?= $is_fixed === 2 ? 'checked' : '' ?>
                <?= ($akhir_sudah_ada && $is_fixed !== 2) ? 'disabled' : '' ?>>
            Status akhir
        </label><br>

        <label>
            <input type="radio" name="is_fixed" value="0"
                <?= $is_fixed === 0 ? 'checked' : '' ?>>
            Status proses
        </label><br><br>

        <button type="submit" name="simpan">Simpan</button>
    </form>

    <h3>Daftar Status</h3>

    <table>
        <tr>
            <th>Nama Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_status']) ?></td>
                <td>
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
