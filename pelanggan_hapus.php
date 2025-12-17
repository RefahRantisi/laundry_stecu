<?php
include 'koneksi.php';

$id = $_GET['id'];
mysqli_query($conn,"DELETE FROM customers WHERE id='$id'");

header("location:pelanggan.php");
?>
