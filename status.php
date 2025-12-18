<?php
session_start();
include 'koneksi.php';

/* ================= UPDATE STATUS TRANSAKSI ================= */
if (isset($_POST['status_id']) && isset($_POST['transaksi_id'])) {
    $transaksi_id = $_POST['transaksi_id'];
    $status_id    = $_POST['status_id'];

    mysqli_query($conn, "
        UPDATE transactions 
        SET status_id = '$status_id'
        WHERE id = '$transaksi_id'
    ");
}

/* ================= AMBIL DATA TRANSAKSI ================= */
$data = mysqli_query($conn, "
    SELECT 
        t.id AS transaksi_id,
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
        body { margin:0; font-family: Arial, sans-serif; background:#f4f4f4; }
        .navbar { background:#2c3e50; padding:15px; display:flex; justify-content:center; gap:12px; }
        .navbar a { color:white; text-decoration:none; font-weight:bold; padding:10px 18px; border-radius:6px; transition:0.3s; }
        .navbar a:hover { background:#1abc9c; }

        .container { max-width:1100px; margin:auto; padding:30px; }
        h2 { margin-bottom:20px; }
        .card { background:white; padding:25px; border-radius:10px; box-shadow:0 4px 10px rgba(0,0,0,0.1); }

        table { width:100%; border-collapse:collapse; }
        table th { background:#2c3e50; color:white; padding:12px; }
        table td { padding:10px; background:white; border-bottom:1px solid #ddd; text-align:center; }

        .status-btn { padding:6px 12px; margin:2px; border:none; border-radius:6px; cursor:pointer; font-weight:bold; color:white; transition:0.3s; }
        .btn-diterima { background:#3498db; } .btn-diterima:hover { background:#2980b9; }
        .btn-dicuci { background:#f39c12; } .btn-dicuci:hover { background:#d35400; }
        .btn-disetrika { background:#9b59b6; } .btn-disetrika:hover { background:#8e44ad; }
        .btn-selesai { background:#2ecc71; } .btn-selesai:hover { background:#27ae60; }

        form.inline { display:inline-block; margin:0; }
    </style>
    <script>
        // Fungsi konfirmasi sesuai tombol
        function konfirmasi(statusLabel) {
            return confirm("Apakah Anda ingin mengubah status menjadi " + statusLabel + "?");
        }
    </script>
</head>
<body>

<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="pelanggan.php">Data Pelanggan</a>
    <a href="transaksi.php">Transaksi</a>
    <a href="status.php">Status Laundry</a>
    <a href="laporan.php">Laporan</a>
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
                $status_saat_ini = $row['status_id'];
                $nama_paket = $row['nama_paket'];

                // Tentukan tombol aksi sesuai paket
                $status_paket = [];
                switch($nama_paket) {
                    case 'Cuci Kering Reguler':
                    case 'Cuci Kering Express':
                        $status_paket = [1 => 'Diterima', 2 => 'Dicuci', 4 => 'Selesai'];
                        break;
                    case 'Cuci Setrika Reguler':
                    case 'Cuci Setrika Express':
                        $status_paket = [1 => 'Diterima', 2 => 'Dicuci', 3 => 'Disetrika', 4 => 'Selesai'];
                        break;
                    case 'Setrika Reguler':
                    case 'Setrika Express':
                        $status_paket = [1 => 'Diterima', 3 => 'Disetrika', 4 => 'Selesai'];
                        break;
                }
            ?>
            <tr>
                <td><?= $row['transaksi_id']; ?></td>
                <td><?= $row['pelanggan']; ?></td>
                <td><?= $row['nama_paket']; ?></td>
                <td><?= $row['nama_status']; ?></td>
                <td>
                    <form method="post" class="inline">
                        <input type="hidden" name="transaksi_id" value="<?= $row['transaksi_id']; ?>">
                        <?php foreach($status_paket as $id => $label): ?>
                            <?php if($id > $status_saat_ini): ?>
                                <button type="submit" 
                                        name="status_id" 
                                        value="<?= $id ?>" 
                                        class="status-btn btn-<?= strtolower($label) ?>" 
                                        onclick="return konfirmasi('<?= $label ?>')">
                                    <?= $label ?>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>

</body>
</html>
