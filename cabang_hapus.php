<?php
require 'auth_owner.php';
include 'koneksi.php';

$id = (int)$_GET['id'];
$owner_id = $_SESSION['owner_id'];

/* VALIDASI: pastikan cabang milik owner */
$cek = mysqli_num_rows(mysqli_query($conn, "
    SELECT id FROM laundries 
    WHERE id = $id AND owner_id = $owner_id
"));

if ($cek == 0) {
    header("Location: cabang.php");
    exit;
}

/* HAPUS CABANG */
mysqli_query($conn, "
    DELETE FROM laundries WHERE id = $id
");

header("Location: cabang.php?hapus=success");
exit;
