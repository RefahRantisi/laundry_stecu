<?php
include 'koneksi.php';

/* =========================
   HANDLE TAMBAH
========================= */
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_status'];

    mysqli_query($conn, "
        INSERT INTO laundry_status (nama_status)
        VALUES ('$nama')
    ");

    header("Location: pengaturan_status.php");
    exit;
}

/* =========================
   HANDLE HAPUS
========================= */
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    // hapus relasi alur dulu
    mysqli_query($conn, "DELETE FROM package_status_flow WHERE status_id='$id'");
    mysqli_query($conn, "DELETE FROM laundry_status WHERE id='$id'");

    header("Location: pengaturan_status.php");
    exit;
}

/* =========================
   DATA LIST
========================= */
$data = mysqli_query($conn, "SELECT * FROM laundry_status ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Pengaturan Status</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
        }

        .container {
            width: 700px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        input,
        button {
            padding: 8px;
        }

        .aksi a {
            margin-right: 8px;
            text-decoration: none;
        }

        .hapus {
            color: red;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Tambah Status</h2>

        <form method="post">
            <label>Nama Status</label><br>
            <input type="text" name="nama_status" required><br><br>

            <button type="submit" name="tambah">Simpan Status</button>
        </form>

        <h3>Daftar Status</h3>
        <table>
            <tr>
                <th>Nama Status</th>
                <th>Aksi</th>
            </tr>

            <?php while ($row = mysqli_fetch_assoc($data)) { ?>
                <tr>
                    <td><?= $row['nama_status'] ?></td>
                    <td class="aksi">
                        <a href="?hapus=<?= $row['id'] ?>" class="hapus" onclick="return confirm('Hapus status ini?')">
                            Hapus
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

</body>

</html>