<?php
include 'koneksi.php';

/* ===============================
   SIMPAN TRANSAKSI
================================ */
if (isset($_POST['simpan'])) {

    $tanggal = date('Y-m-d H:i:s');
    $customer_id = $_POST['customer_id'];
    $berat = $_POST['berat_kg'];
    $harga_paket = $_POST['harga_paket']; // harga per kg (hidden)

    // HITUNG TOTAL (SERVER SIDE)
    $total = $harga_paket * $berat;

    $package_id = 1; // sementara
    $status_id = 1;

    mysqli_query($conn, "INSERT INTO transactions
        (tanggal, customer_id, package_id, berat_kg, total_harga, status_id)
        VALUES
        ('$tanggal','$customer_id','$package_id','$berat','$total','$status_id')
    ");

    echo "<script>alert('Transaksi berhasil disimpan');</script>";
}

/* ===============================
   DATA CUSTOMER
================================ */
$customers = [];
$q = mysqli_query($conn, "SELECT id, nama FROM customers");
while ($r = mysqli_fetch_assoc($q)) {
    $customers[] = $r;
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

        .list {
            border: 1px solid #ccc;
            max-height: 150px;
            overflow-y: auto;
            display: none;
            position: absolute;
            background: white;
            width: 200px;
        }

        .item {
            padding: 5px;
            cursor: pointer;
        }

        .item:hover {
            background: #eee;
        }
    </style>
</head>

<body>

    <h2>Transaksi Laundry</h2>

    <form method="POST">

        <label>Tanggal & Waktu</label>
        <input type="text" value="<?= date('Y-m-d H:i:s'); ?>" readonly>

        <!-- AUTOCOMPLETE CUSTOMER -->
        <label>Nama Pelanggan</label>
        <input type="text" id="nama" autocomplete="off" required>
        <input type="hidden" name="customer_id" id="customer_id">

        <div id="list" class="list"></div>

        <br>
        <a href="pelanggan_tambah.php" id="btnTambah" style="display:none;">
            <button type="button">+ Tambah Pelanggan</button>
        </a>

        <!-- PAKET -->
        <label>Paket Laundry</label>
        <select id="paket" required onchange="hitung()">
            <option value="">-- Pilih Paket --</option>
            <option value="5000">Cuci Kering - Reguler</option>
            <option value="8000">Cuci Kering - Express</option>
            <option value="7000">Cuci Setrika - Reguler</option>
            <option value="10000">Cuci Setrika - Express</option>
            <option value="6000">Setrika - Reguler</option>
            <option value="9000">Setrika - Express</option>
        </select>

        <!-- harga paket (hidden, untuk backend) -->
        <input type="hidden" name="harga_paket" id="harga_paket">

        <label>Berat (Kg)</label>
        <input type="number" step="0.1" id="berat" name="berat_kg" required oninput="hitung()">

        <!-- TOTAL OTOMATIS -->
        <label>Total Harga</label>
        <input type="text" id="total" readonly>

        <br><br>
        <button type="submit" name="simpan">Simpan Transaksi</button>

    </form>

    <script>
        const customers = <?= json_encode($customers); ?>;
        const input = document.getElementById('nama');
        const list = document.getElementById('list');
        const hiddenId = document.getElementById('customer_id');
        const btnTambah = document.getElementById('btnTambah');

        input.addEventListener('keyup', function () {
            let key = this.value.toLowerCase();
            list.innerHTML = '';
            let found = false;

            if (key === '') {
                list.style.display = 'none';
                btnTambah.style.display = 'none';
                return;
            }

            customers.forEach(c => {
                if (c.nama.toLowerCase().includes(key)) {
                    found = true;
                    let div = document.createElement('div');
                    div.className = 'item';
                    div.textContent = c.nama;
                    div.onclick = () => {
                        input.value = c.nama;
                        hiddenId.value = c.id;
                        list.style.display = 'none';
                        btnTambah.style.display = 'none';
                    };
                    list.appendChild(div);
                }
            });

            list.style.display = found ? 'block' : 'none';
            btnTambah.style.display = found ? 'none' : 'inline';
        });

        function hitung() {
            let harga = document.getElementById('paket').value;
            let berat = document.getElementById('berat').value;

            document.getElementById('harga_paket').value = harga;

            if (harga && berat) {
                document.getElementById('total').value = harga * berat;
            } else {
                document.getElementById('total').value = '';
            }
        }

        input.addEventListener('keyup', function () {
            let key = this.value.toLowerCase();
            list.innerHTML = '';
            let found = false;

            if (key === '') {
                list.style.display = 'none';
                btnTambah.style.display = 'none';
                return;
            }

            customers.forEach(c => {
                if (c.nama.toLowerCase().includes(key)) {
                    found = true;
                    let div = document.createElement('div');
                    div.className = 'item';
                    div.textContent = c.nama;
                    div.onclick = () => {
                        input.value = c.nama;
                        hiddenId.value = c.id;
                        list.style.display = 'none';
                        btnTambah.style.display = 'none';
                    };
                    list.appendChild(div);
                }
            });

            if (!found) {
                btnTambah.style.display = 'inline';
                btnTambah.href = "pelanggan_tambah.php?nama=" + encodeURIComponent(input.value);
            } else {
                btnTambah.style.display = 'none';
            }

            list.style.display = found ? 'block' : 'none';
        });

    </script>

</body>

</html>