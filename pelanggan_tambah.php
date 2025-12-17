<?php include 'koneksi.php'; ?>

<h2>Tambah Pelanggan</h2>

<form method="POST">
    <label>Nama</label><br>
    <input type="text" name="nama" required><br><br>

    <label>No. Telp</label><br>
    <input type="text" name="no_telp" required><br><br>

    <label>Alamat</label><br>
    <textarea name="alamat" required></textarea><br><br>

    <button type="submit" name="simpan">Simpan</button>
    <a href="pelanggan.php">Kembali</a>
</form>

<?php
if (isset($_POST['simpan'])) {

    $nama = $_POST['nama'];
    $telp = $_POST['no_telp'];
    $alamat = $_POST['alamat'];

    mysqli_query($conn, "
        INSERT INTO customers (nama, no_telp, alamat)
        VALUES ('$nama','$telp','$alamat')
    ");

    // AMBIL ID CUSTOMER BARU
    $customer_id = mysqli_insert_id($conn);

    // KEMBALI KE TRANSAKSI BAWA ID + NAMA
    header("Location: transaksi.php?customer_id=$customer_id&nama=" . urlencode($nama));
    exit;
}

$nama_awal = isset($_GET['nama']) ? $_GET['nama'] : '';
?>
