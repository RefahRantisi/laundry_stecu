<?php
include 'koneksi.php';

/* ===============================
   AMBIL DATA CUSTOMER (AUTOCOMPLETE)
=============================== */
$customers = [];
$q = mysqli_query(
    $conn,
    "SELECT id, nama, no_telp FROM customers ORDER BY nama ASC"
);
while ($r = mysqli_fetch_assoc($q)) {
    $customers[] = $r;
}

/* ===============================
   DATA DARI URL (SETELAH TAMBAH CUSTOMER)
=============================== */
$customer_id_url = $_GET['customer_id'] ?? '';
$nama_url = $_GET['nama'] ?? '';
$telp_url = $_GET['no_telp'] ?? '';

$display_value = ($nama_url && $telp_url)
    ? "$nama_url - $telp_url"
    : '';

/* ===============================
   AMBIL DATA PAKET LAUNDRY
=============================== */
$pakets = mysqli_query($conn, "SELECT * FROM laundry_packages");

/* ===============================
   SIMPAN TRANSAKSI
=============================== */
if (isset($_POST['simpan'])) {

    $tanggal = date('Y-m-d H:i:s');
    $berat = floatval($_POST['berat_kg']);
    $package_id = intval($_POST['package_id']);
    $customer_id = intval($_POST['customer_id']);

    /* ===============================
       VALIDASI CUSTOMER (WAJIB ID)
    =============================== */
    $cek_customer = mysqli_query(
        $conn,
        "SELECT id FROM customers WHERE id = $customer_id"
    );

    if (mysqli_num_rows($cek_customer) == 0) {
        echo "<script>
            alert('Pelanggan tidak valid. Pilih dari daftar.');
            history.back();
        </script>";
        exit;
    }

    /* ===============================
       VALIDASI PAKET (FK)
    =============================== */
    $cek_paket = mysqli_query(
        $conn,
        "SELECT harga_per_kg FROM laundry_packages WHERE id = $package_id"
    );

    if (mysqli_num_rows($cek_paket) == 0) {
        echo "<script>
            alert('Paket laundry tidak valid');
            history.back();
        </script>";
        exit;
    }

    // ambil harga asli dari database (anti manipulasi)
    $row_paket = mysqli_fetch_assoc($cek_paket);
    $harga_paket = $row_paket['harga_per_kg'];
    $total = $harga_paket * $berat;

    /* ===============================
       INSERT TRANSAKSI
    =============================== */
    $status_id = 1; // diterima

    mysqli_query(
        $conn,
        "INSERT INTO transactions
        (tanggal, customer_id, package_id, berat_kg, total_harga, status_id)
        VALUES
        ('$tanggal', '$customer_id', '$package_id', '$berat', '$total', '$status_id')"
    );

    echo "<script>
        alert('Transaksi berhasil disimpan');
        window.location='transaksi.php';
    </script>";
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Transaksi Laundry</title>
    <style>
        ::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            scrollbar-width: none;
            -ms-overflow-style: none;
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

        /* ===== CONTAINER ===== */
        .container {
            max-width: 1000px;
            margin: auto;
            padding: 30px;
        }

        h2 {
            margin-bottom: 15px;
            color: #333;
        }

        /* ===== CARD ===== */
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* ===== FORM ELEMENTS ===== */
        form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #555;
        }

        form input[type="text"],
        form input[type="number"],
        form select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 14px;
        }

        form button[type="submit"] {
            margin-top: 20px;
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

        form button[type="button"] {
            margin-top: 15px;
            padding: 10px 20px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        /* ===== AUTOCOMPLETE LIST ===== */
        .autocomplete {
            position: relative;
        }

        .autocomplete .item {
            background: #fff;
            padding: 8px;
            border: 1px solid #ddd;
            cursor: pointer;
        }

        .autocomplete .item:hover {
            background: #f1f1f1;
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

    <div class="container">
        <h2>Transaksi Laundry</h2>

        <div class="card">
            <form method="POST">
                <label>Tanggal & Waktu</label>
                <input type="text" value="<?= date('Y-m-d H:i:s'); ?>" readonly>

                <label>Nama Pelanggan</label>
                <input type="text" id="customer_display" name="customer_display"
                    value="<?= htmlspecialchars($display_value); ?>" autocomplete="off" required>

                <input type="hidden" name="customer_id" id="customer_id"
                    value="<?= htmlspecialchars($customer_id_url); ?>">
                <div id="list" class="autocomplete"></div>

                <!-- TOMBOL TAMBAH PELANGGAN -->
                <a id="btnTambah" style="display:none; margin-top:15px;">
                    <button type="button">+ Tambah Pelanggan</button>
                </a>

                <label>Paket Laundry</label>
                <select name="package_id" id="paket" required onchange="setHarga()">
                    <option value="">-- Pilih Paket --</option>
                    <?php
                    $pakets = mysqli_query($conn, "SELECT * FROM laundry_packages");
                    while ($p = mysqli_fetch_assoc($pakets)):
                        ?>
                        <option value="<?= $p['id']; ?>" data-harga="<?= $p['harga_per_kg']; ?>">
                            <?= $p['nama_paket']; ?> (Rp <?= number_format($p['harga_per_kg']); ?>/kg)
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="hidden" name="harga_paket" id="harga_paket">

                <label>Berat (Kg)</label>
                <input type="number" step="0.1" name="berat_kg" id="berat" oninput="hitungTotal()" required>

                <label>Total Harga</label>
                <input type="text" id="total" readonly>

                <button type="submit" name="simpan">Simpan Transaksi</button>
            </form>
        </div>
    </div>

    <script>
        /* ===============================
           AUTOCOMPLETE CUSTOMER
        =============================== */
        const customers = <?= json_encode($customers); ?>;
        const input = document.getElementById('customer_display');
        const list = document.getElementById('list');
        const hiddenId = document.getElementById('customer_id');
        const btnTambah = document.getElementById('btnTambah');

        input.addEventListener('keyup', function () {
            const key = this.value.toLowerCase();
            list.innerHTML = '';
            let found = false;

            if (!key) {
                list.style.display = 'none';
                hiddenId.value = '';
                btnTambah.style.display = 'none';
                return;
            }

            customers.forEach(c => {
                const display = `${c.nama} - ${c.no_telp}`;

                if (display.toLowerCase().includes(key)) {
                    found = true;
                    const div = document.createElement('div');
                    div.className = 'item';
                    div.textContent = display;

                    div.onclick = () => {
                        input.value = display;
                        hiddenId.value = c.id;
                        list.style.display = 'none';
                        btnTambah.style.display = 'none';
                    };

                    list.appendChild(div);
                }
            });

            if (!found) {
                btnTambah.style.display = 'inline';
                btnTambah.href =
                    "pelanggan_tambah.php?nama=" + encodeURIComponent(input.value);
                hiddenId.value = '';
            } else {
                btnTambah.style.display = 'none';
            }

            list.style.display = found ? 'block' : 'none';
        });

        /* ===============================
           HITUNG TOTAL
        =============================== */
        function setHarga() {
            const select = document.getElementById('paket');
            const harga = select.options[select.selectedIndex].dataset.harga || 0;
            document.getElementById('harga_paket').value = harga;
            hitungTotal();
        }

        function hitungTotal() {
            const harga = document.getElementById('harga_paket').value || 0;
            const berat = document.getElementById('berat').value || 0;

            document.getElementById('total').value =
                (harga && berat)
                    ? "Rp " + (harga * berat).toLocaleString('id-ID')
                    : '';
        }
    </script>
    </div>


</body>

</html>