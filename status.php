<?php
include 'config.php';

/* CREATE */
if (isset($_POST['simpan'])) {
    mysqli_query($conn, "INSERT INTO laundry_status VALUES (NULL, '$_POST[nama_status]')");
}

/* UPDATE */
if (isset($_POST['update'])) {
    mysqli_query($conn, "UPDATE laundry_status 
        SET nama_status='$_POST[nama_status]' 
        WHERE id=$_POST[id]");
}

/* DELETE */
if (isset($_GET['hapus'])) {
    mysqli_query($conn, "DELETE FROM laundry_status WHERE id=$_GET[hapus]");
}

/* READ (EDIT MODE) */
$edit = null;
if (isset($_GET['edit'])) {
    $q = mysqli_query($conn, "SELECT * FROM laundry_status WHERE id=$_GET[edit]");
    $edit = mysqli_fetch_assoc($q);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Status Laundry</title>
</head>
<body>

<h2>Manajemen Status Laundry</h2>

<form method="post">
    <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
    
    Nama Status :
    <input type="text" name="nama_status" 
           value="<?= $edit['nama_status'] ?? '' ?>" required>

    <button type="submit" name="<?= $edit ? 'update' : 'simpan' ?>">
        <?= $edit ? 'Update' : 'Simpan' ?>
    </button>
</form>

<br>

<table border="1" cellpadding="5">
<tr>
    <th>No</th>
    <th>Nama Status</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;
$q = mysqli_query($conn, "SELECT * FROM laundry_status");
while ($d = mysqli_fetch_assoc($q)) {
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $d['nama_status'] ?></td>
    <td>
        <a href="?edit=<?= $d['id'] ?>">Edit</a> |
        <a href="?hapus=<?= $d['id'] ?>" 
           onclick="return confirm('Hapus status ini?')">Hapus</a>
    </td>
</tr>
<?php } ?>

</table>

</body>
</html>
