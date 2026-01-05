<?php
require 'auth.php';
require 'koneksi.php';

/* =========================
   INIT
========================= */
$edit = null;
$satuan_edit = '';

/* =========================
   SIMPAN
========================= */
if (isset($_POST['simpan'])) {
    $nama    = mysqli_real_escape_string($conn, $_POST['nama_paket']);
    $unit_id = (int) $_POST['unit_id'];
    $harga   = (float) $_POST['harga'];

    // 🔐 VALIDASI unit_id harus milik cabang ini
    $cekUnit = mysqli_query($conn, "
        SELECT id FROM laundry_units
        WHERE id='$unit_id' AND cabang_id='$cabang_id' AND is_active=1
    ");
    if (mysqli_num_rows($cekUnit) === 0) {
        die('Kategori tidak valid');
    }

    if (!empty($_POST['id'])) {
        $id = (int) $_POST['id'];
        mysqli_query($conn, "
            UPDATE laundry_packages SET
                nama_paket='$nama',
                unit_id='$unit_id',
                harga='$harga'
            WHERE id='$id' AND cabang_id='$cabang_id'
        ");
    } else {
        mysqli_query($conn, "
            INSERT INTO laundry_packages
            (nama_paket, unit_id, harga, is_active, cabang_id)
            VALUES ('$nama','$unit_id','$harga',1,'$cabang_id')
        ");
    }

    header("Location: pengaturan_paket.php");
    exit;
}

/* =========================
   HAPUS
========================= */
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    mysqli_query($conn, "
        UPDATE laundry_packages
        SET is_active=0
        WHERE id='$id' AND cabang_id='$cabang_id'
    ");
    header("Location: pengaturan_paket.php");
    exit;
}

/* =========================
   EDIT
========================= */
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $q = mysqli_query($conn, "
        SELECT lp.*, lu.nama_satuan
        FROM laundry_packages lp
        LEFT JOIN laundry_units lu 
            ON lp.unit_id = lu.id AND lu.cabang_id='$cabang_id'
        WHERE lp.id='$id'
          AND lp.cabang_id='$cabang_id'
          AND lp.is_active=1
    ");
    $edit = mysqli_fetch_assoc($q);
    $satuan_edit = $edit['nama_satuan'] ?? '';
}

/* =========================
   KATEGORI
========================= */
$kategori_list = mysqli_query($conn, "
    SELECT * FROM laundry_units
    WHERE is_active=1 AND cabang_id='$cabang_id'
    ORDER BY kategori_barang ASC
");

/* =========================
   LIST PAKET
========================= */
$data = mysqli_query($conn, "
    SELECT lp.*, lu.kategori_barang, lu.nama_satuan
    FROM laundry_packages lp
    LEFT JOIN laundry_units lu 
        ON lp.unit_id = lu.id AND lu.cabang_id='$cabang_id'
    WHERE lp.is_active=1
      AND lp.cabang_id='$cabang_id'
    ORDER BY lp.id DESC
");
?>


<!DOCTYPE html>
<html>

<head>
    <title>Pengaturan Paket</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
        }

        .container {
            width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 6px;
        }

        /* ===== TOMBOL KEMBALI ===== */
        .btn-back {
            display: inline-block;
            margin-bottom: 15px;
            padding: 8px 14px;
            background: #2c3e50;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-back:hover {
            background: #1abc9c;
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

        input, select {
            padding: 8px;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        form button[type="submit"] {
            padding: 10px 20px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        form button[type="submit"]:hover {
            background: #27ae60;
        }

        .aksi a {
            margin-right: 8px;
            text-decoration: none;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            color: white;
        }

        .btn-edit {
            background: #f39c12;
        }

        .btn-edit:hover {
            background: #e67e22;
        }

        .btn-delete {
            background: #e74c3c;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .btn-cancel {
            display: inline-block;
            padding: 10px 20px;
            background: #95a5a6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-left: 10px;
        }

        .btn-cancel:hover {
            background: #7f8c8d;
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- TOMBOL KEMBALI KE PENGATURAN -->
        <a href="pengaturan.php" class="btn-back">← Kembali ke Pengaturan</a>

        <h2><?= $edit ? 'Edit Paket' : 'Tambah Paket' ?></h2>

        <form method="post">
            <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">

            <label>Nama Paket</label>
            <input type="text" name="nama_paket" required value="<?= $edit['nama_paket'] ?? '' ?>" placeholder="Contoh: Paket Cuci Kiloan">
            <br><br>

            <label>Kategori Barang</label>
            <select name="unit_id" id="kategori" required>
                <option value="">-- Pilih Kategori --</option>
                <?php 
                mysqli_data_seek($kategori_list, 0); // reset pointer
                while ($kat = mysqli_fetch_assoc($kategori_list)) { 
                    $selected = ($edit && $edit['unit_id'] == $kat['id']) ? 'selected' : '';
                ?>
                    <option value="<?= $kat['id'] ?>" 
                            data-satuan="<?= $kat['nama_satuan'] ?>"
                            <?= $selected ?>>
                        <?= $kat['kategori_barang'] ?>
                    </option>
                <?php } ?>
            </select>
            <br><br>

            <label>Satuan</label>
            <input type="text" id="satuan" readonly placeholder="Otomatis terisi" value="<?= $edit ? mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_satuan FROM laundry_units WHERE id='{$edit['unit_id']}'"))['nama_satuan'] ?? '' : '' ?>" style="background: #f0f0f0;">
            <br><br>

            <label>Harga</label>
            <input type="number" name="harga" step="0.01" required value="<?= $edit['harga'] ?? '' ?>" placeholder="Contoh: 5000">
            <br><br>

            <button type="submit" name="simpan">
                <?= $edit ? 'Update Paket' : 'Simpan Paket' ?>
            </button>

            <?php if ($edit): ?>
                <a href="pengaturan_paket.php" class="btn-cancel">Batal</a>
            <?php endif; ?>
        </form>

        <h3>Daftar Paket</h3>
        <table>
            <tr>
                <th>Nama Paket</th>
                <th>Kategori Barang</th>
                <th>Satuan</th>
                <th>Harga</th>
                <th>Aksi</th>
            </tr>

            <?php while ($row = mysqli_fetch_assoc($data)) { ?>
                <tr>
                    <td><?= $row['nama_paket'] ?></td>
                    <td><?= $row['kategori_barang'] ?? '-' ?></td>
                    <td><?= $row['nama_satuan'] ?? '-' ?></td>
                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                    <td class="aksi">
                        <a href="?edit=<?= $row['id'] ?>" class="btn btn-edit">Edit</a>
                        <a href="?hapus=<?= $row['id'] ?>" class="btn btn-delete"
                           onclick="return confirm('Hapus paket ini?')">
                            Hapus
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>

    <!-- JAVASCRIPT untuk Auto-fill Satuan -->
    <script>
        const kategoriSelect = document.getElementById('kategori');
        const satuanInput = document.getElementById('satuan');

        kategoriSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const satuan = selectedOption.getAttribute('data-satuan');
            
            if (satuan) {
                satuanInput.value = satuan;
            } else {
                satuanInput.value = '';
            }
        });

        // Trigger pada saat halaman load (untuk mode edit)
        window.addEventListener('DOMContentLoaded', function() {
            if (kategoriSelect.value) {
                const selectedOption = kategoriSelect.options[kategoriSelect.selectedIndex];
                const satuan = selectedOption.getAttribute('data-satuan');
                if (satuan) {
                    satuanInput.value = satuan;
                }
            }
        });
    </script>

</body>

</html>