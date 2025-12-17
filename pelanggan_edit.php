<?php
include 'koneksi.php';
$id = $_GET['id'];
$data = mysqli_query($conn,"SELECT * FROM customers WHERE id='$id'");
$p = mysqli_fetch_assoc($data);
?>

<h2>Edit Pelanggan</h2>

<form method="POST">
    <label>Nama</label><br>
    <input type="text" name="nama" value="<?= $p['nama'] ?>" required><br><br>

    <label>No. Telp</label><br>
    <input type="text" name="no_telp" value="<?= $p['no_telp'] ?>" required><br><br>

    <label>Alamat</label><br>
    <textarea name="alamat" required><?= $p['alamat'] ?></textarea><br><br>

    <button type="submit" name="update">Update</button>
    <a href="pelanggan.php">Kembali</a>
</form>

<?php
if(isset($_POST['update'])){
    $nama   = $_POST['nama'];
    $telp   = $_POST['no_telp'];
    $alamat = $_POST['alamat'];

    mysqli_query($conn,"
        UPDATE customers SET
        nama='$nama',
        no_telp='$telp',
        alamat='$alamat'
        WHERE id='$id'
    ");

    header("location:pelanggan.php");
}
?>
