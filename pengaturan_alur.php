<?php
require 'auth.php';
include 'koneksi.php';

$paket_id = $_GET['paket_id'] ?? null;

/* =====================
   TAMBAH STATUS KE PAKET
===================== */
if (isset($_GET['tambah'])) {
    $paket_id  = (int) $_GET['paket_id'];
    $status_id = (int) $_GET['tambah'];

    $q = mysqli_query($conn, "
        SELECT IFNULL(MAX(urutan),0)+1 AS urut
        FROM package_status_flow
        WHERE package_id='$paket_id'
    ");
    $urut = mysqli_fetch_assoc($q)['urut'];

    mysqli_query($conn, "
        INSERT INTO package_status_flow (package_id, status_id, urutan)
        VALUES ('$paket_id','$status_id','$urut')
    ");

    header("Location: pengaturan_alur.php?paket_id=$paket_id");
    exit;
}

/* =====================
   HAPUS STATUS DARI PAKET
===================== */
if (isset($_GET['hapus'])) {
    $flow_id = (int) $_GET['hapus'];

    mysqli_query($conn, "
        DELETE FROM package_status_flow WHERE id='$flow_id'
    ");

    header("Location: pengaturan_alur.php?paket_id=$paket_id");
    exit;
}

/* =====================
   DATA PAKET
===================== */
$paket = mysqli_query($conn, "
    SELECT id, nama_paket
    FROM laundry_packages
    WHERE is_active=1
    ORDER BY nama_paket
");

/* =====================
   DATA STATUS
===================== */
$semua_status = mysqli_query($conn, "
    SELECT id, nama_status
    FROM laundry_status
    WHERE is_active=1
    ORDER BY nama_status
");

/* =====================
   STATUS AKTIF DI PAKET
===================== */
$status_aktif = [];
if ($paket_id) {
    $q = mysqli_query($conn, "
        SELECT psf.id AS flow_id, s.id, s.nama_status
        FROM package_status_flow psf
        JOIN laundry_status s ON psf.status_id = s.id
        WHERE psf.package_id='$paket_id'
          AND s.is_active = 1
        ORDER BY psf.urutan ASC
    ");
    while ($r = mysqli_fetch_assoc($q)) {
        $status_aktif[] = $r;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Alur Status</title>

    <!-- RWD -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
        }

        .container {
            max-width: 700px;
            width: 95%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,.08);
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

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .section {
            margin-top: 25px;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
        }

        .item:hover {
            background: #f9f9f9;
        }

        .btn {
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #fff;
            font-weight: bold;
            text-decoration: none;
            font-size: 18px;
        }

        .btn-hapus { background: #e67e22; }
        .btn-tambah { background: #2ecc71; }

        .info {
            color: #888;
            font-style: italic;
        }

        /* ===== MOBILE ===== */
        @media (max-width: 600px) {
            h2, h3 {
                font-size: 18px;
            }

            .item {
                padding: 14px 8px;
            }

            .btn-back {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <a href="pengaturan.php" class="btn-back">← Kembali ke Pengaturan</a>

    <h2>Pengaturan Alur Status</h2>

    <!-- PILIH PAKET -->
    <form method="get">
        <label><strong>Pilih Paket</strong></label>
        <select name="paket_id" onchange="this.form.submit()">
            <option value="">-- Pilih Paket --</option>
            <?php while ($p = mysqli_fetch_assoc($paket)) { ?>
                <option value="<?= $p['id'] ?>" <?= $paket_id == $p['id'] ? 'selected' : '' ?>>
                    <?= $p['nama_paket'] ?>
                </option>
            <?php } ?>
        </select>
    </form>

    <?php if ($paket_id): ?>

        <!-- STATUS AKTIF -->
        <div class="section">
            <h3>Status Aktif (Urutan Proses)</h3>

            <?php if (empty($status_aktif)): ?>
                <p class="info">Belum ada status untuk paket ini</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($status_aktif as $s): ?>
                        <li class="item">
                            <span><?= $s['nama_status'] ?></span>
                            <a class="btn btn-hapus"
                               href="?paket_id=<?= $paket_id ?>&hapus=<?= $s['flow_id'] ?>"
                               onclick="return confirm('Nonaktifkan status ini?')">−</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- STATUS TERSEDIA -->
        <div class="section">
            <h3>Status Tersedia</h3>

            <?php
            mysqli_data_seek($semua_status, 0);
            $ada = false;

            while ($s = mysqli_fetch_assoc($semua_status)) {
                $dipakai = false;
                foreach ($status_aktif as $a) {
                    if ($a['id'] == $s['id']) $dipakai = true;
                }
                if (!$dipakai) {
                    $ada = true;
            ?>
                <div class="item">
                    <span><?= $s['nama_status'] ?></span>
                    <a class="btn btn-tambah"
                       href="?paket_id=<?= $paket_id ?>&tambah=<?= $s['id'] ?>">+</a>
                </div>
            <?php }} ?>

            <?php if (!$ada): ?>
                <p class="info">Semua status sudah digunakan</p>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
