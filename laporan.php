<?php
include 'koneksi.php';

/* FILTER TANGGAL */
$where = "";
if (isset($_GET['from']) && isset($_GET['to'])) {
    $from = $_GET['from'];
    $to   = $_GET['to'];
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
<html>
<head>
    <title>Laporan Laundry</title>
</head>
<body>

<h2>Laporan Laundry</h2>

<!-- FILTER -->
<form method="GET">
    Dari: <input type="date" name="from" required>
    Sampai: <input type="date" name="to" required>
    <button type="submit">Filter</button>
</form>

<br>

<table border="1" cellpadding="8">
<tr>
    <th>Nama Pelanggan</th>
    <th>ID Transaksi</th>
    <th>Paket Laundry</th>
    <th>Total Harga</th>
    <th>Tanggal</th>
</tr>

<?php if(mysqli_num_rows($query) > 0) { ?>
    <?php while($row = mysqli_fetch_assoc($query)) { ?>
    <tr>
        <td><?= $row['nama_pelanggan'] ?></td>
        <td><?= $row['id_transaksi'] ?></td>
        <td><?= $row['nama_paket'] ?></td>
        <td>Rp <?= number_format($row['total_harga'],0,',','.') ?></td>
        <td><?= $row['tanggal'] ?></td>
    </tr>
    <?php } ?>
<?php } else { ?>
    <tr>
        <td colspan="5" align="center">Data tidak ditemukan</td>
    </tr>
<?php } ?>
</table>

<br>

<h3>Total Pendapatan:
    Rp <?= number_format($total['total_pendapatan'] ?? 0,0,',','.') ?>
</h3>

<br>
<a href="dashboard.php">⬅ Kembali ke Dashboard</a>

</body>
</html>
