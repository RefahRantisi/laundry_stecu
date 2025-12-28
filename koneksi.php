<?php
$host     = "db";
$user     = "root";
$password = "root";
$database = "laundry_stecu";

// Membuat koneksi
$conn = mysqli_connect($host, $user, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>