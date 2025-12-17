<?php
session_start();
include 'koneksi.php';


$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM transactions"));
?>
<h2>Dashboard</h2>
<p>Total Order: <?= $total['total']; ?></p>
<a href="customers.php">Data Pelanggan</a> |
<a href="transaksi.php">Transaksi</a> |
<a href="status.php">Status Laundry</a> |
<a href="laporan.php">Laporan</a>