<?php
include 'koneksi.php';

$awal_sudah_ada = false;
$akhir_sudah_ada = false;

/* =========================
   DEFAULT VALUE
========================= */
$id = null;
$nama_status = '';
$is_fixed = 0;

/* =========================
   MODE EDIT (HANYA UNTUK LOAD FORM)
========================= */
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
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
        die('Status tidak ditemukan');
    }
}

/* =========================
   SIMPAN STATUS (TAMBAH / EDIT)
========================= */
if (isset($_POST['simpan'])) {
    $nama_status = mysqli_real_escape_string($conn, $_POST['nama_status']);
    $is_fixed = (int) $_POST['is_fixed'];
    $edit_id = isset($_POST['id']) ? (int) $_POST['id'] : null;

    // pastikan hanya satu awal / akhir
    if ($is_fixed === 1) {
        mysqli_query($conn, "UPDATE laundry_status SET is_fixed = 0 WHERE is_fixed = 1");
    }
    if ($is_fixed === 2) {
        mysqli_query($conn, "UPDATE laundry_status SET is_fixed = 0 WHERE is_fixed = 2");
    }

    if ($edit_id) {
        // UPDATE
        mysqli_query($conn, "
            UPDATE laundry_status SET
                nama_status = '$nama_status',
                is_fixed = $is_fixed
            WHERE id = $edit_id
        ");
    } else {
        // INSERT
        mysqli_query($conn, "
            INSERT INTO laundry_status (nama_status, is_fixed, is_active)
            VALUES ('$nama_status', $is_fixed, 1)
        ");
    }

    header("Location: pengaturan_status.php");
    exit;
}

/* =========================
   NONAKTIFKAN STATUS (AMAN)
========================= */
if (isset($_GET['nonaktifkan']) && ctype_digit($_GET['nonaktifkan'])) {

    $id = (int) $_GET['nonaktifkan'];

    // 1. Pastikan status memang ada & aktif
    $cek = mysqli_query($conn, "
        SELECT id FROM laundry_status
        WHERE id = $id AND is_active = 1
        LIMIT 1
    ");

    if (mysqli_num_rows($cek) === 0) {
        header("Location: pengaturan_status.php");
        exit;
    }

    // 2. Nonaktifkan status (tidak hapus → aman FK transaksi)
    mysqli_query($conn, "
        UPDATE laundry_status
        SET
            is_active = 0,
            is_fixed  = 0
        WHERE id = $id
    ");

    // 3. Hapus dari alur paket (tidak ganggu histori)
    mysqli_query($conn, "
        DELETE FROM package_status_flow
        WHERE status_id = $id
    ");

    // 4. Redirect bersih (tanpa parameter)
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
            <?php if (!empty($id)): ?>
                <input type="hidden" name="id" value="<?= $id ?>">
            <?php endif; ?>

            <label>Nama Status</label><br>
            <input type="text" name="nama_status" value="<?= htmlspecialchars($nama_status) ?>" required>
            <br><br>

            <label>
                <input type="radio" name="is_fixed" value="1" <?= ($is_fixed === 1) ? 'checked' : '' ?> <?= $awal_sudah_ada ? 'disabled' : '' ?>>
                Tandai sebagai awal proses
                <?= $awal_sudah_ada ? '<small>(sudah ada)</small>' : '' ?>
            </label>
            <br>

            <label>
                <input type="radio" name="is_fixed" value="2" <?= ($is_fixed === 2) ? 'checked' : '' ?> <?= $akhir_sudah_ada ? 'disabled' : '' ?>>
                Tandai sebagai akhir proses
                <?= $akhir_sudah_ada ? '<small>(sudah ada)</small>' : '' ?>
            </label>
            <br>

            <label>
                <input type="radio" name="is_fixed" value="0" <?= ($is_fixed === 0) ? 'checked' : '' ?>>
                Status proses biasa
            </label>
            <br><br>

            <button type="submit" name="simpan">
                <?= !empty($id) ? 'Update Status' : 'Simpan Status' ?>
            </button>
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
                        <a href="?hapus=<?= $row['id'] ?>" class="btn btn-delete"
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