<?php

require 'auth.php';
include 'koneksi.php';

$user_id = $_SESSION['user_id'];        

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
   AMBIL DATA PAKET LAUNDRY (AKTIF)
=============================== */
$pakets = mysqli_query(
    $conn,
    "SELECT id, nama_paket, harga_per_kg
     FROM laundry_packages
     WHERE is_active = 1
     ORDER BY nama_paket ASC"
);

/* ===============================
   SIMPAN TRANSAKSI
=============================== */
if (isset($_POST['simpan'])) {

    $tanggal = date('Y-m-d H:i:s');
    $berat = floatval($_POST['berat_kg']);
    $package_id = intval($_POST['package_id']);
    $customer_id = intval($_POST['customer_id']);
    $user_id = $_SESSION['user_id']; // 🔥 PENTING

    /* ===============================
       VALIDASI CUSTOMER
    =============================== */
    $cek_customer = $conn->prepare(
        "SELECT id FROM customers WHERE id = ?"
    );
    $cek_customer->bind_param("i", $customer_id);
    $cek_customer->execute();
    $cek_customer->store_result();

    if ($cek_customer->num_rows == 0) {
        echo "<script>alert('Pelanggan tidak valid');history.back();</script>";
        exit;
    }

    /* ===============================
       VALIDASI PAKET
    =============================== */
    $cek_paket = $conn->prepare(
        "SELECT harga_per_kg
         FROM laundry_packages
         WHERE id = ? AND is_active = 1
         LIMIT 1"
    );
    $cek_paket->bind_param("i", $package_id);
    $cek_paket->execute();
    $result_paket = $cek_paket->get_result();

    if ($result_paket->num_rows == 0) {
        echo "<script>alert('Paket tidak aktif');history.back();</script>";
        exit;
    }

    $row_paket = $result_paket->fetch_assoc();
    $harga_paket = $row_paket['harga_per_kg'];
    $total = $harga_paket * $berat;

    /* ===============================
       AMBIL STATUS AWAL
    =============================== */
    $q_status = $conn->prepare("
        SELECT psf.status_id
        FROM package_status_flow psf
        JOIN laundry_status ls ON psf.status_id = ls.id
        WHERE psf.package_id = ?
          AND ls.is_active = 1
        ORDER BY psf.urutan ASC
        LIMIT 1
    ");
    $q_status->bind_param("i", $package_id);
    $q_status->execute();
    $res_status = $q_status->get_result();

    if ($res_status->num_rows == 0) {
        echo "<script>alert('Alur status belum tersedia');history.back();</script>";
        exit;
    }

    $status_id = $res_status->fetch_assoc()['status_id'];

    /* ===============================
       SIMPAN KE transactions (BENAR)
    =============================== */
    $stmt = $conn->prepare("
        INSERT INTO transactions (
            tanggal,
            customer_id,
            package_id,
            berat_kg,
            total_harga,
            status_id,
            user_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "siiddii",
        $tanggal,
        $customer_id,
        $package_id,
        $berat,
        $total,
        $status_id,
        $user_id
    );

    $stmt->execute();

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
        body {
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        ::-webkit-scrollbar {
            width: 0;
            height: 0;
        }

        body {
            background-color: #f4f6f9;
            color: #333;
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
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
        }

        /* ===== CARD ===== */
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
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
            background: #1abc9c;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        form button[type="button"] {
            margin-top: 15px;
            padding: 10px 20px;
            background: #1abc9c;
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
        <a href="laporan.php">Laporan</a>
        <a href="pengaturan.php">Pengaturan</a>
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
                    $pakets = mysqli_query(
                        $conn,
                        "SELECT id, nama_paket, harga_per_kg
                        FROM laundry_packages
                        WHERE is_active = 1
                        ORDER BY nama_paket ASC"
                    );
                    while ($p = mysqli_fetch_assoc($pakets)):
                        ?>
                        <option value="<?= $p['id']; ?>" data-harga="<?= $p['harga_per_kg']; ?>">
                            <?= $p['nama_paket']; ?>
                            (Rp <?= number_format($p['harga_per_kg']); ?>/kg)
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