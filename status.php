<?php
session_start();
include 'koneksi.php';

/* ================= UPDATE STATUS TRANSAKSI ================= */
if (isset($_POST['status_id'], $_POST['transaksi_id'])) {

    $transaksi_id = (int) $_POST['transaksi_id'];
    $status_id = (int) $_POST['status_id'];

    mysqli_query($conn, "
        UPDATE transactions 
        SET status_id = $status_id
        WHERE id = $transaksi_id
    ");

    // redirect agar tidak double submit & jelas hasilnya
    header("Location: status.php?updated=1");
    exit;
}

/* ================= AMBIL DATA TRANSAKSI ================= */
$data = mysqli_query($conn, "
    SELECT 
        t.id AS transaksi_id,
        t.package_id,
        c.nama AS pelanggan,
        p.nama_paket,
        s.nama_status,
        t.status_id
    FROM transactions t
    JOIN customers c ON t.customer_id = c.id
    JOIN laundry_packages p ON t.package_id = p.id
    JOIN laundry_status s ON t.status_id = s.id
    ORDER BY t.id DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Status Laundry</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
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
            transition: 0.3s;
        }

        .navbar a:hover {
            background: #1abc9c;
        }

        /* ===== CONTAINER ===== */
        .container {
            max-width: 1000px;
            margin: auto;
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #2c3e50;
            color: white;
            padding: 12px;
        }

        table td {
            padding: 10px;
            background: white;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        /* ===== BUTTON STATUS ===== */
        .status-btn {
            padding: 6px 12px;
            margin: 2px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            color: white;
            transition: 0.3s;
        }

        .btn-diterima {
            background: #3498db;
        }

        .btn-diterima:hover {
            background: #2980b9;
        }

        .btn-dicuci {
            background: #f39c12;
        }

        .btn-dicuci:hover {
            background: #d35400;
        }

        .btn-disetrika {
            background: #9b59b6;
        }

        .btn-disetrika:hover {
            background: #8e44ad;
        }

        .btn-selesai {
            background: #2ecc71;
        }

        .btn-selesai:hover {
            background: #27ae60;
        }

        form.inline {
            display: inline-block;
            margin: 0;
        }
    </style>
    <script>
        function konfirmasi(statusLabel) {
            return confirm("Apakah Anda ingin mengubah status menjadi " + statusLabel + "?");
        }
    </script>
</head>

<body>

    <!-- ===== NAVBAR ===== -->
    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="pelanggan.php">Data Pelanggan</a>
        <a href="transaksi.php">Transaksi</a>
        <a href="status.php">Status Laundry</a>
        <a href="laporan.php">Laporan</a>
        <a href="pengaturan.php">Pengaturan</a>
    </div>

    <div class="container">
        <h2>Status Laundry</h2>

        <div class="card">
            <table>
                <tr>
                    <th>ID Transaksi</th>
                    <th>Pelanggan</th>
                    <th>Paket</th>
                    <th>Status Saat Ini</th>
                    <th>Ubah Status</th>
                </tr>

                <?php while ($row = mysqli_fetch_assoc($data)) {

                    $transaksi_id = $row['transaksi_id'];
                    $package_id = $row['package_id'];
                    $status_saat_ini = (int) $row['status_id'];

                    // Ambil SEMUA alur status paket
                    $alur = mysqli_query($conn, "
        SELECT psf.id AS flow_id, psf.status_id, s.nama_status
        FROM package_status_flow psf
        JOIN laundry_status s ON psf.status_id = s.id
        WHERE psf.package_id = '$package_id'
        ORDER BY psf.urutan
    ");

                    $status_list = [];
                    while ($a = mysqli_fetch_assoc($alur)) {
                        $status_list[] = $a;
                    }

                    // Cari status saat ini di alur
                    $next_status = null;
                    for ($i = 0; $i < count($status_list); $i++) {
                        if ($status_list[$i]['status_id'] == $status_saat_ini) {
                            $next_status = $status_list[$i + 1] ?? null;
                            break;
                        }
                    }
                    ?>

                    <tr>
                        <td><?= $row['transaksi_id']; ?></td>
                        <td><?= $row['pelanggan']; ?></td>
                        <td><?= $row['nama_paket']; ?></td>
                        <td><?= $row['nama_status']; ?></td>
                        <td>
                            <?php if ($next_status): ?>
                                <form method="post" class="inline">
                                    <input type="hidden" name="transaksi_id" value="<?= $transaksi_id ?>">
                                    <button type="submit" name="status_id" value="<?= $next_status['status_id'] ?>"
                                        class="status-btn" onclick="return konfirmasi('<?= $next_status['nama_status'] ?>')">
                                        <?= $next_status['nama_status'] ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span>Selesai</span>
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php } ?>

            </table>
        </div>
    </div>

</body>

</html>