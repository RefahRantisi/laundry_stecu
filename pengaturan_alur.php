<?php
include 'koneksi.php';

/* =====================
   PILIH PAKET
===================== */
$paket_id = $_GET['paket_id'] ?? null;

/* =====================
   TAMBAH STATUS KE PAKET
===================== */
if (isset($_GET['tambah'])) {
    $paket_id = (int) $_GET['paket_id'];
    $status_id = (int) $_GET['tambah'];

    $q = mysqli_query($conn, "
        SELECT IFNULL(MAX(urutan),0)+1 AS next_urut 
        FROM package_status_flow 
        WHERE package_id='$paket_id'
    ");
    $urut = mysqli_fetch_assoc($q)['next_urut'];

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
    mysqli_query($conn, "DELETE FROM package_status_flow WHERE id='$flow_id'");
    header("Location: pengaturan_alur.php?paket_id=$paket_id");
    exit;
}

/* =====================
   UPDATE URUTAN (AJAX)
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {

    foreach ($_POST['data'] as $d) {
        $id = (int) $d['id'];
        $urut = (int) $d['urutan'];

        mysqli_query($conn, "
            UPDATE package_status_flow
            SET urutan='$urut'
            WHERE id='$id'
        ");
    }

    exit; // PENTING: hentikan proses setelah AJAX
}

/* =====================
   DATA
===================== */
$paket = mysqli_query($conn, "SELECT * FROM laundry_packages ORDER BY nama_paket");
$semua_status = mysqli_query($conn, "SELECT * FROM laundry_status ORDER BY nama_status");

$status_aktif = [];
if ($paket_id) {
    $q = mysqli_query($conn, "
        SELECT psf.id AS flow_id, s.id, s.nama_status
        FROM package_status_flow psf
        JOIN laundry_status s ON psf.status_id = s.id
        WHERE psf.package_id='$paket_id'
        ORDER BY psf.urutan
    ");
    while ($r = mysqli_fetch_assoc($q)) {
        $status_aktif[] = $r;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Pengaturan Alur Status</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
        }

        .container {
            max-width: 700px;
            margin: 30px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .1);
        }

        h2 {
            margin-top: 0;
        }

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
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
            cursor: move;
        }

        .item:hover {
            background: #f9f9f9;
        }

        .btn {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
            font-weight: bold;
            text-decoration: none;
        }

        .btn-hapus {
            background: #f39c12;
        }

        .btn-tambah {
            background: #2ecc71;
        }

        .section {
            margin-top: 30px;
        }

        .info {
            color: #888;
            font-style: italic;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Pengaturan Alur Status</h2>

        <!-- PILIH PAKET -->
        <form method="get">
            <label>Pilih Paket</label>
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
                <h3>Status Aktif (geser untuk ubah urutan)</h3>

                <?php if (empty($status_aktif)): ?>
                    <p class="info">Belum ada status aktif</p>
                <?php else: ?>
                    <ul id="sortable">
                        <?php foreach ($status_aktif as $s): ?>
                            <li class="item" data-id="<?= $s['flow_id'] ?>">
                                <span><?= $s['nama_status'] ?></span>
                                <a class="btn btn-hapus" href="?paket_id=<?= $paket_id ?>&hapus=<?= $s['flow_id'] ?>"
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
                        if ($a['id'] == $s['id'])
                            $dipakai = true;
                    }
                    if (!$dipakai) {
                        $ada = true;
                        ?>
                        <div class="item" style="cursor:default">
                            <span><?= $s['nama_status'] ?></span>
                            <a class="btn btn-tambah" href="?paket_id=<?= $paket_id ?>&tambah=<?= $s['id'] ?>">+</a>
                        </div>
                    <?php }
                } ?>

                <?php if (!$ada): ?>
                    <p class="info">Semua status sudah digunakan</p>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>

    <!-- DRAG & DROP -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <script>
        $("#sortable").sortable({
            update: function () {
                let data = [];
                $("#sortable .item").each(function (index) {
                    data.push({
                        id: $(this).data("id"),
                        urutan: index + 1
                    });
                });

                $.post("update_urutan_status.php", { data: data });
            }
        });
    </script>

</body>

</html>