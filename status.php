<?php
include 'koneksi.php';

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

/* EDIT MODE */
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

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        /* ===== NAVBAR ===== */
        .navbar {
            background: #2c3e50;
            padding: 15px;
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 18px;
            border-radius: 6px;
            transition: 0.3s;
        }

        .navbar a:hover {
            background: #1abc9c;
        }

        /* ===== CONTENT ===== */
        .container {
            max-width: 1000px;
            margin: auto;
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
        }

        /* ===== CARD ===== */
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        /* ===== FORM ===== */
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
        }

        input[type="text"] {
            width: 300px;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            padding: 8px 18px;
            border: none;
            border-radius: 6px;
            background: #1abc9c;
            color: white;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background: #16a085;
        }

        /* ===== TABLE ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        table th {
            background: #2c3e50;
            color: white;
            padding: 10px;
        }

        table td {
            padding: 10px;
            background: white;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        .action a {
            text-decoration: none;
            font-weight: bold;
            margin: 0 6px;
        }

        .action a.edit {
            color: #2980b9;
        }

        .action a.delete {
            color: #e74c3c;
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <a href="dashboard.php">Dashboard</a>
    <a href="pelanggan.php">Data Pelanggan</a>
    <a href="transaksi.php">Transaksi</a>
    <a href="status.php">Status Laundry</a>
    <a href="laporan.php">Laporan</a>
</div>

<!-- CONTENT -->
<div class="container">
    <h2>Manajemen Status Laundry</h2>

    <div class="card">
        <form method="post">
            <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">

            <label>Nama Status</label>
            <input type="text" name="nama_status"
                   value="<?= $edit['nama_status'] ?? '' ?>" required>

            <br>
            <button type="submit" name="<?= $edit ? 'update' : 'simpan' ?>">
                <?= $edit ? 'Update' : 'Simpan' ?>
            </button>
        </form>

        <table>
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
                <td class="action">
                    <a class="edit" href="?edit=<?= $d['id'] ?>">Edit</a>
                    <a class="delete" href="?hapus=<?= $d['id'] ?>"
                       onclick="return confirm('Hapus status ini?')">Hapus</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>

</body>
</html>
