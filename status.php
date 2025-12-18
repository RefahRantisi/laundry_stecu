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

    header("Location: status.php");
    exit;
}

/* ================= AMBIL DATA TRANSAKSI (BELUM SELESAI) ================= */
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
    WHERE s.nama_status != 'Selesai'
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
            font-family: Arial;
            background: #f4f4f4;
        }

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

        .container {
            max-width: 1000px;
            margin: auto;
            padding: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
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
            padding: 6px 14px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            color: white;
        }

        .status-btn[data-status="Diterima"] {
            background: #e74c3c;
        }

        .status-btn[data-status="Dicuci"] {
            background: #f1c40f;
            color: black;
        }

        .status-btn[data-status="Disetrika"] {
            background: #2ecc71;
        }

        .status-btn[data-status="Selesai"] {
            background: #2c3e50;
        }
    </style>

    <script>
        function konfirmasi(status) {
            return confirm("Apakah Anda ingin mengubah status menjadi " + status + "?");
        }
    </script>
</head>

<body>

<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="pelanggan.php">Pelanggan</a>
    <a href="transaksi.php">Transaksi</a>
    <a href="status.php">Status</a>
    <a href="laporan.php">Laporan</a>
</div>

<div class="container">
    <h2>Status Laundry</h2>

    <div class="card">
        <table>
            <tr>
                <th>ID</th>
                <th>Pelanggan</th>
                <th>Paket</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>

            <?php while ($row = mysqli_fetch_assoc($data)) {

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
                                name="status_id"
                                value="<?= $next['status_id'] ?>"
                                class="status-btn"
                                data-status="<?= $next['nama_status'] ?>"
                                onclick="return konfirmasi('<?= $next['nama_status'] ?>')">
                                <?= $next['nama_status'] ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>

            <?php } ?>
        </table>
    </div>
</div>

</body>
</html>
