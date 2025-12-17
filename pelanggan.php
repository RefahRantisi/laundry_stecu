<?php include 'koneksi.php'; ?>

<h2>Data Pelanggan</h2>
<a href="pelanggan_tambah.php">+ Tambah Pelanggan</a>

<table border="1" cellpadding="8" cellspacing="0">
<tr>
    <th>No</th>
    <th>Nama</th>
    <th>No. Telp</th>
    <th>Alamat</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;
$data = mysqli_query($conn,"SELECT * FROM customers ORDER BY id DESC");
while($p = mysqli_fetch_assoc($data)){
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $p['nama'] ?></td>
    <td><?= $p['no_telp'] ?></td>
    <td><?= $p['alamat'] ?></td>
    <td>
        <a href="pelanggan_edit.php?id=<?= $p['id'] ?>">Edit</a> |
        <a href="pelanggan_hapus.php?id=<?= $p['id'] ?>"
           onclick="return confirm('Yakin hapus pelanggan?')">Hapus</a>
    </td>
</tr>
<?php } ?>
</table>
