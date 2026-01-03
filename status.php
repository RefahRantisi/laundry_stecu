<?php
require 'auth.php';
include 'koneksi.php';

$user_id = $_SESSION['user_id']; // 🔥 ID user login

/* ================= UPDATE STATUS ================= */
if (isset($_POST['transaksi_id'], $_POST['status_id'])) {
    $transaksi_id = (int) $_POST['transaksi_id'];
    $status_id = (int) $_POST['status_id'];

    mysqli_query($conn, "
        UPDATE transactions 
        SET status_id = $status_id
        WHERE id = $transaksi_id 
        AND user_id = $user_id
    ");

    header("Location: index.php");
    exit;
}

/* ================= AMBIL TRANSAKSI (KECUALI SELESAI) ================= */
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
    AND t.user_id = $user_id
    ORDER BY t.id DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Status Laundry</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            background-color: #f4f6f9;
            color: #333;
            font-family: Arial, Helvetica, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        ::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            background: #2c3e50;
            padding: 15px;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            /* agar menyesuaikan layar sempit */
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 18px;
            border-radius: 6px;
            white-space: nowrap;
        }

        .navbar a:hover {
            background: #1abc9c;
        }

        /* ===== CONTAINER ===== */
        .container {
            padding: 30px 15px;
            /* padding horizontal lebih kecil untuk mobile */
        }

        .status-section {
            margin-top: 50px;
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow-x: auto;
            /* scroll horizontal jika tabel terlalu lebar */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
            /* agar tidak terlalu sempit di mobile */
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

        /* ===== BUTTON FLEKSIBEL ===== */
        .status-btn {
            padding: 7px 16px;
            border-radius: 6px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(135deg, #1abc9c, #16a085);
        }

        .status-btn:hover {
            opacity: 0.85;
        }

        form {
            margin: 0;
        }

        /* ===== MEDIA QUERIES ===== */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 8px;
            }

            table {
                min-width: 100%;
            }

            th,
            td {
                padding: 8px;
            }
        }

        @media (max-width: 480px) {
            body {
                font-size: 14px;
            }

            h2 {
                font-size: 18px;
            }

            .status-btn {
                padding: 6px 12px;
                font-size: 13px;
            }

            th,
            td {
                padding: 6px;
            }
        }

        h2 {
            margin-bottom: 5px;
        }
    </style>

    <script>
        function konfirmasi(status) {
            return confirm("Ubah status menjadi \"" + status + "\" ?");
        }
    </script>
</head>

<body>

    <div class="container">
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
                                        <button type="submit" name="status_id" value="<?= $next['status_id'] ?>"
                                            class="status-btn" onclick="return konfirmasi('<?= $next['nama_status'] ?>')">
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