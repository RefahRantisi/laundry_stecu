<?php
require 'auth.php';
include 'koneksi.php';

/* =========================
   DEFAULT
========================= */
$id = null;
$nama_status = '';
$is_fixed = 0;

/* =========================
   MODE EDIT
========================= */
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $id = (int) $_GET['id'];

    $q = mysqli_query($conn, "
        SELECT nama_status, is_fixed
        FROM laundry_status
        WHERE id = $id AND is_active = 1
        LIMIT 1
    ");

    if ($row = mysqli_fetch_assoc($q)) {
        $nama_status = $row['nama_status'];
        $is_fixed = (int) $row['is_fixed'];
    } else {
        header("Location: pengaturan_status.php");
        exit;
    }
}

/* =========================
   CEK STATUS AWAL / AKHIR
========================= */
$awal_sudah_ada = false;
$akhir_sudah_ada = false;

$cek = mysqli_query($conn, "
    SELECT is_fixed, id FROM laundry_status
    WHERE is_active = 1
");

while ($r = mysqli_fetch_assoc($cek)) {
    if ($r['is_fixed'] == 1 && $r['id'] != $id)
        $awal_sudah_ada = true;
    if ($r['is_fixed'] == 2 && $r['id'] != $id)
        $akhir_sudah_ada = true;
}

/* =========================
   SIMPAN
========================= */
if (isset($_POST['simpan'])) {
    $nama_status = mysqli_real_escape_string($conn, $_POST['nama_status']);
    $is_fixed = (int) $_POST['is_fixed'];
    $edit_id = isset($_POST['id']) ? (int) $_POST['id'] : null;

    if ($is_fixed === 1) {
        mysqli_query($conn, "
            UPDATE laundry_status 
            SET is_fixed = 0 
            WHERE is_fixed = 1 AND id != " . ($edit_id ?? 0)
        );
    }

    if ($is_fixed === 2) {
        mysqli_query($conn, "
            UPDATE laundry_status 
            SET is_fixed = 0 
            WHERE is_fixed = 2 AND id != " . ($edit_id ?? 0)
        );
    }

    if ($edit_id) {
        mysqli_query($conn, "
            UPDATE laundry_status SET
                nama_status = '$nama_status',
                is_fixed = $is_fixed
            WHERE id = $edit_id
        ");
    } else {
        mysqli_query($conn, "
            INSERT INTO laundry_status (nama_status, is_fixed, is_active)
            VALUES ('$nama_status', $is_fixed, 1)
        ");
    }

    header("Location: pengaturan_status.php");
    exit;
}

/* =========================
   NONAKTIFKAN
========================= */
if (isset($_GET['hapus']) && ctype_digit($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    mysqli_query($conn, "
        UPDATE laundry_status
        SET is_active = 0, is_fixed = 0
        WHERE id = $id
    ");

    mysqli_query($conn, "
        DELETE FROM package_status_flow
        WHERE status_id = $id
    ");

    header("Location: pengaturan_status.php");
    exit;
}

/* =========================
   DATA LIST
========================= */
$data = mysqli_query($conn, "
    SELECT *
    FROM laundry_status
    WHERE is_active = 1
    ORDER BY
        CASE
            WHEN is_fixed = 1 THEN 0
            WHEN is_fixed = 2 THEN 9999
            ELSE 1
        END,
        id ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Status</title>

    <!-- RWD -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            width: 95%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }

        h2, h3 {
            margin-top: 0;
        }

        /* ===== BUTTON ===== */
        .btn-back {
            display: inline-block;
            margin-bottom: 15px;
            padding: 10px 16px;
            background: #2c3e50;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-back:hover {
            background: #1abc9c;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            color: #fff;
        }

        .btn-delete {
            background: #e74c3c;
        }

        /* ===== FORM ===== */
        label {
            font-weight: bold;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button[type="submit"] {
            padding: 10px 20px;
            background: #2ecc71;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        /* ===== TABLE ===== */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            min-width: 400px;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            white-space: nowrap;
        }

        th {
            background: #f7f7f7;
        }

        /* ===== MOBILE ===== */
        @media (max-width: 600px) {
            h2, h3 {
                font-size: 18px;
            }

            th, td {
                font-size: 14px;
                padding: 8px;
            }

            .btn,
            .btn-back,
            button[type="submit"] {
                width: 100%;
                text-align: center;
                margin-bottom: 6px;
            }
        }
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
                <?= $awal_sudah_ada ? 'disabled' : '' ?>>
            Status awal
        </label><br>

        <label>
            <input type="radio" name="is_fixed" value="2"
                <?= $is_fixed === 2 ? 'checked' : '' ?>
                <?= $akhir_sudah_ada ? 'disabled' : '' ?>>
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

    <div class="table-responsive">
        <table>
            <tr>
                <th>Nama Status</th>
                <th>Aksi</th>
            </tr>

            <?php while ($row = mysqli_fetch_assoc($data)) { ?>
                <tr>
                    <td><?= $row['nama_status'] ?></td>
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

</div>

</body>
</html>
