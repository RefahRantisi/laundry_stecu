<?php
session_start();
include 'koneksi.php';

/* ================= UPDATE STATUS ================= */
if (isset($_POST['transaksi_id'], $_POST['status_id'])) {
    $transaksi_id = (int) $_POST['transaksi_id'];
    $status_id = (int) $_POST['status_id'];

    mysqli_query($conn, "
        UPDATE transactions
        SET status_id = $status_id
        WHERE id = $transaksi_id
    ");

    header("Location: dashboard.php");
    exit;
}

/* ================= DATA DASHBOARD ================= */
$totalOrder = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions")
);

$proses = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM transactions t
        JOIN laundry_status s ON t.status_id = s.id
        WHERE s.is_fixed = 0
    ")
);

$selesai = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM transactions t
        JOIN laundry_status s ON t.status_id = s.id
        WHERE s.is_fixed = 2
    ")
);

/* ================= DATA STATUS LAUNDRY ================= */
$dataStatus = mysqli_query($conn, "
    SELECT 
        t.id AS transaksi_id,
        t.package_id,
        t.status_id,
        c.nama AS pelanggan,
        p.nama_paket,
        s.nama_status
    FROM transactions t
    JOIN customers c ON t.customer_id = c.id
    JOIN laundry_packages p ON t.package_id = p.id
    JOIN laundry_status s ON t.status_id = s.id
    WHERE s.nama_status != 'Selesai'
    ORDER BY t.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Laundry</title>

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
        }

        .navbar a:hover {
            background: #1abc9c;
        }

        /* ===== CONTAINER ===== */
        .container {
            max-width: 1100px;
            margin: auto;
            padding: 30px;
        }

        h2 {
            margin-bottom: 5px;
        }

        /* ===== CARDS ===== */
        .cards {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            width: 220px;
            height: 130px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;

            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .card h3 {
            margin: 0;
            color: #555;
            font-size: 18px;
        }

        .card p {
            font-size: 32px;
            font-weight: bold;
            margin-top: 10px;
        }

        .card-link {
            text-decoration: none;
            color: inherit;
        }

        .card-link .card:hover {
            transform: translateY(-5px);
            background: #f9fffc;
            cursor: pointer;
        }

        /* ===== STATUS TABLE ===== */
        .status-section {
            margin-top: 50px;
        }

        .status-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #2c3e50;
            color: white;
            padding: 12px;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        .status-btn {
            padding: 7px 16px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(135deg, #1abc9c, #16a085);
        }
    </style>

    <script>
        function konfirmasi(status) {
            return confirm('Ubah status menjadi "' + status + '" ?');
        }
    </script>
</head>

<body>

<!-- ===== NAVBAR ===== -->
<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="pelanggan.php">Data Pelanggan</a>
    <a href="transaksi.php">Transaksi</a>
    <a href="laporan.php">Laporan</a>
    <a href="pengaturan.php">Pengaturan</a>
</div>

<div class="container">
    <h2>Dashboard</h2>
    <p>Selamat datang di sistem informasi laundry</p>

    <!-- CARDS -->
    <div class="cards">
        <div class="card">
            <h3>Total Order</h3>
            <p><?= $totalOrder['total']; ?></p>
        </div>

        <div class="card">
            <h3>Laundry Proses</h3>
            <p><?= $proses['total']; ?></p>
        </div>

        <div class="card">
            <h3>Laundry Selesai</h3>
            <p><?= $selesai['total']; ?></p>
        </div>

        <a href="transaksi.php" class="card-link">
            <div class="card">
                <h3>Tambah Transaksi</h3>
                <p style="font-size:24px; color:#2ecc71;">+ Order</p>
            </div>
        </a>
    </div>

    <!-- STATUS LAUNDRY -->
    <div class="status-section" id="status">
        <h2>Status Laundry</h2>

        <div class="status-card">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Pelanggan</th>
                    <th>Paket</th>
                    <th>Status Saat Ini</th>
                    <th>Aksi</th>
                </tr>

                <?php while ($row = mysqli_fetch_assoc($dataStatus)) {

                    $alur = mysqli_query($conn, "
                        SELECT psf.status_id, s.nama_status
                        FROM package_status_flow psf
                        JOIN laundry_status s ON psf.status_id = s.id
                        WHERE psf.package_id = '{$row['package_id']}'
                        ORDER BY psf.urutan
                    ");

                    $list = [];
                    while ($a = mysqli_fetch_assoc($alur)) {
                        $list[] = $a;
                    }

                    $next = null;
                    for ($i = 0; $i < count($list); $i++) {
                        if ($list[$i]['status_id'] == $row['status_id']) {
                            $next = $list[$i + 1] ?? null;
                            break;
                        }
                    }
                ?>
                <tr>
                    <td><?= $row['transaksi_id'] ?></td>
                    <td><?= $row['pelanggan'] ?></td>
                    <td><?= $row['nama_paket'] ?></td>
                    <td><?= $row['nama_status'] ?></td>
                    <td>
                        <?php if ($next): ?>
                            <form method="post">
                                <input type="hidden" name="transaksi_id" value="<?= $row['transaksi_id'] ?>">
                                <button
                                    type="submit"
                                    name="status_id"
                                    value="<?= $next['status_id'] ?>"
                                    class="status-btn"
                                    onclick="return konfirmasi('<?= $next['nama_status'] ?>')">
                                    <?= $next['nama_status'] ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <strong>Selesai</strong>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>

</div>

</body>
</html>
