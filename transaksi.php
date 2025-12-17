<?php
include 'koneksi.php';

// SIMPAN TRANSAKSI
if (isset($_POST['simpan'])) {
    $customer_id = $_POST['customer_id'];
    $package_id = $_POST['package_id'];
    $berat_kg = $_POST['berat_kg'];
    $total_harga = $_POST['total_harga'];
    $status_id = 1; // Diterima

    $query = mysqli_query($koneksi, "
        INSERT INTO transactions
        (customer_id, package_id, berat_kg, total_harga, status_id)
        VALUES
        ('$customer_id', '$package_id', '$berat_kg', '$total_harga', '$status_id')
    ");

    if ($query) {
        echo "<script>alert('Transaksi berhasil ditambahkan');</script>";
    } else {
        echo "<script>alert('Gagal menambahkan transaksi');</script>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Transaksi Laundry</title>
    <style>
        label {
            display: block;
            margin-top: 10px;
        }

        input,
        select {
            width: 300px;
            padding: 6px;
        }
    </style>
</head>

<body>

    <h2>Input Transaksi Laundry</h2>

    <form method="POST">

        <label>Tanggal</label>
        <input type="text" value="<?= date('d-m-Y H:i'); ?>" readonly>

        <label>Pelanggan</label>
        <select name="customer_id" required>
            <option value="">-- Pilih Pelanggan --</option>
            <?php
            $cust = mysqli_query($koneksi, "SELECT * FROM customers");
            while ($c = mysqli_fetch_assoc($cust)) {
                echo "<option value='{$c['id']}'>{$c['nama']} - {$c['no_telp']}</option>";
            }
            ?>
        </select>

        <label>Paket Laundry</label>
        <select name="package_id" id="package" onchange="hitungTotal()" required>
            <option value="">-- Pilih Paket --</option>
            <?php
            $paket = mysqli_query($koneksi, "SELECT * FROM packages");
            while ($p = mysqli_fetch_assoc($paket)) {
                echo "<option value='{$p['id']}' data-harga='{$p['harga_per_kg']}'>
                    {$p['nama_paket']} (Rp {$p['harga_per_kg']}/kg)
                  </option>";
            }
            ?>
        </select>

        <label>Berat (Kg)</label>
        <input type="number" name="berat_kg" id="berat_kg" step="0.01" onkeyup="hitungTotal()" required>

        <label>Total Harga</label>
        <input type="text" id="total_view" readonly>
        <input type="hidden" name="total_harga" id="total_harga">

        <br><br>
        <button type="submit" name="simpan">Simpan Transaksi</button>

    </form>

    <script>
        function hitungTotal() {
            let paket = document.getElementById("package");
            let harga = paket.options[paket.selectedIndex]?.getAttribute("data-harga") || 0;
            let berat = document.getElementById("berat_kg").value || 0;

            let total = harga * berat;

            document.getElementById("total_view").value =
                "Rp " + Number(total).toLocaleString('id-ID');
            document.getElementById("total_harga").value = total;
        }
    </script>

</body>

</html>