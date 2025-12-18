<?php
include 'koneksi.php';


/* FILTER TANGGAL */
$where = "";
if (!empty($_GET['from']) && !empty($_GET['to'])) {
    $from = mysqli_real_escape_string($conn, $_GET['from']);
    $to   = mysqli_real_escape_string($conn, $_GET['to']);
    $where = "AND DATE(t.tanggal) BETWEEN '$from' AND '$to'";
}

/* DATA LAPORAN */
$query = mysqli_query($conn, "
SELECT 
    c.nama AS nama_pelanggan,
    t.id AS id_transaksi,
    p.nama_paket,
    t.total_harga,
    t.tanggal
FROM transactions t
JOIN customers c ON t.customer_id = c.id
JOIN laundry_packages p ON t.package_id = p.id
WHERE t.status_id = 4 $where
ORDER BY t.tanggal DESC
");

/* TOTAL PENDAPATAN */
$total = mysqli_fetch_assoc(
    mysqli_query($conn, "
    SELECT SUM(t.total_harga) AS total_pendapatan
    FROM transactions t
    WHERE t.status_id = 4 $where
    ")
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Laundry</title>

<style>
/* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, Helvetica, sans-serif;
}

/* BODY */
body {
    background-color: #f4f6f9;
    color: #333;
}

/* CONTAINER */
.container {
    padding: 30px;
}

/* JUDUL */
h2 {
    margin-bottom: 20px;
}

/* FILTER */
.filter-box {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.filter-box label {
    margin-right: 10px;
}

.filter-box input[type="date"] {
    padding: 8px;
    margin-right: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.filter-box button {
    padding: 8px 16px;
    background: #2f4050;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.filter-box button:hover {
    background: #1f2d3a;
}

/* TABLE */
.table-wrapper {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th {
    background: #2f4050;
    color: #fff;
    padding: 12px;
    text-align: left;
}

table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

table tr:hover {
    background: #f1f5f9;
}

/* TOTAL */
.total-box {
    margin-top: 20px;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    text-align: right;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.total-box strong {
    font-size: 20px;
}

/* BACK */
.back-link {
    display: inline-block;
    margin-top: 20px;
    text-decoration: none;
    color: #2f4050;
    font-weight: 500;
}

.back-link:hover {
    text-decoration: underline;
}
</style>

</head>
<body>

<div class="container">
    <h2>Laporan Laundry</h2>

    <!-- FILTER -->
    <div class="filter-box">
        <form method="GET">
            <label>Dari:</label>
            <input type="date" name="from" value="<?= $_GET['from'] ?? '' ?>">
            <label>Sampai:</label>
            <input type="date" name="to" value="<?= $_GET['to'] ?? '' ?>">
            <button type="submit">Filter</button>
        </form>
    </div>

    <!-- TABLE -->
    <div class="table-wrapper">
        <table>
            <tr>
                <th>Nama Pelanggan</th>
                <th>ID Transaksi</th>
                <th>Paket Laundry</th>
                <th>Total Harga</th>
                <th>Tanggal</th>
            </tr>

            <?php if(mysqli_num_rows($query) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($query)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_pelanggan']) ?></td>
                    <td><?= $row['id_transaksi'] ?></td>
                    <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                    <td>Rp <?= number_format($row['total_harga'],0,',','.') ?></td>
                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" align="center">Data tidak ditemukan</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- TOTAL -->
    <div class="total-box">
        Total Pendapatan:
        <strong>Rp <?= number_format($total['total_pendapatan'] ?? 0,0,',','.') ?></strong>
    </div>

    <a href="dashboard.php" class="back-link">⬅ Kembali ke Dashboard</a>
</div>

</body>
</html>
