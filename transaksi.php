<?php
require 'auth.php';
include 'koneksi.php';

$user_id   = $_SESSION['user_id'];
$cabang_id = $_SESSION['cabang_id'];

/* ===============================
   AMBIL DATA CUSTOMER (AUTOCOMPLETE)
=============================== */
$customers = [];
$q = mysqli_query(
    $conn,
    "SELECT id, nama, no_telp
     FROM customers
     WHERE cabang_id = '$cabang_id'
     ORDER BY nama ASC"
);
while ($r = mysqli_fetch_assoc($q)) {
    $customers[] = $r;
}

/* ===============================
   DATA DARI URL (SETELAH TAMBAH CUSTOMER)
=============================== */
$customer_id_url = $_GET['customer_id'] ?? '';
$nama_url        = $_GET['nama'] ?? '';
$telp_url        = $_GET['no_telp'] ?? '';

$display_value = ($nama_url && $telp_url)
    ? "$nama_url - $telp_url"
    : '';

/* ===============================
   AMBIL DATA KATEGORI AKTIF (PER CABANG)
=============================== */
$kategori_list = mysqli_query(
    $conn,
    "SELECT DISTINCT kategori_barang
     FROM laundry_units
     WHERE is_active = 1
       AND cabang_id = '$cabang_id'
     ORDER BY kategori_barang ASC"
);

/* ===============================
   AMBIL DATA PAKET (PER CABANG)
=============================== */
$pakets = [];
$pakets_query = mysqli_query(
    $conn,
    "SELECT 
        lp.id,
        lp.nama_paket,
        lp.harga,
        lu.kategori_barang,
        lu.nama_satuan
     FROM laundry_packages lp
     JOIN laundry_units lu ON lp.unit_id = lu.id
     WHERE lp.is_active = 1
       AND lu.is_active = 1
       AND lp.cabang_id = '$cabang_id'
       AND lu.cabang_id = '$cabang_id'
     ORDER BY lu.kategori_barang ASC, lp.nama_paket ASC"
);
while ($p = mysqli_fetch_assoc($pakets_query)) {
    $pakets[] = $p;
}

/* ===============================
   SIMPAN TRANSAKSI
=============================== */
if (isset($_POST['simpan'])) {

    $tanggal     = date('Y-m-d H:i:s');
    $qty         = floatval($_POST['qty']);
    $package_id  = intval($_POST['package_id']);
    $customer_id = intval($_POST['customer_id']);

    /* ===== VALIDASI CUSTOMER (PER CABANG) ===== */
    $cek_customer = $conn->prepare(
        "SELECT id
         FROM customers
         WHERE id = ?
           AND cabang_id = ?"
    );
    $cek_customer->bind_param("ii", $customer_id, $cabang_id);
    $cek_customer->execute();
    $cek_customer->store_result();

    if ($cek_customer->num_rows == 0) {
        echo "<script>alert('Pelanggan tidak valid');history.back();</script>";
        exit;
    }

    /* ===== VALIDASI PAKET + AMBIL HARGA ===== */
    $cek_paket = $conn->prepare(
        "SELECT lp.harga
         FROM laundry_packages lp
         JOIN laundry_units lu ON lp.unit_id = lu.id
         WHERE lp.id = ?
           AND lp.is_active = 1
           AND lu.is_active = 1
           AND lp.cabang_id = ?
           AND lu.cabang_id = ?
         LIMIT 1"
    );
    $cek_paket->bind_param("iii", $package_id, $cabang_id, $cabang_id);
    $cek_paket->execute();
    $result_paket = $cek_paket->get_result();

    if ($result_paket->num_rows == 0) {
        echo "<script>alert('Paket tidak valid');history.back();</script>";
        exit;
    }

    $row_paket   = $result_paket->fetch_assoc();
    $harga_paket = $row_paket['harga'];
    $total       = $harga_paket * $qty;

    /* ===== AMBIL STATUS AWAL (PER CABANG) ===== */
    $q_status = $conn->prepare(
        "SELECT psf.status_id
         FROM package_status_flow psf
         JOIN laundry_status ls ON psf.status_id = ls.id
         WHERE psf.package_id = ?
           AND ls.is_active = 1
           AND ls.cabang_id = ?
         ORDER BY psf.urutan ASC
         LIMIT 1"
    );
    $q_status->bind_param("ii", $package_id, $cabang_id);
    $q_status->execute();
    $res_status = $q_status->get_result();

    if ($res_status->num_rows == 0) {
        echo "<script>alert('Alur status belum tersedia');history.back();</script>";
        exit;
    }

    $status_id = $res_status->fetch_assoc()['status_id'];

    /* ===== SIMPAN TRANSAKSI ===== */
    $stmt = $conn->prepare(
        "INSERT INTO transactions (
            tanggal,
            customer_id,
            package_id,
            berat_kg,
            total_harga,
            status_id,
            user_id,
            cabang_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "siiddiii",
        $tanggal,
        $customer_id,
        $package_id,
        $qty,
        $total,
        $status_id,
        $user_id,
        $cabang_id
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
            align-items: center;
            gap: 12px;
            position: relative;
            min-height: 56px;
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

        /* Burger Menu Button */
        .burger-menu {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            position: absolute;
            left: 15px;
            z-index: 1000;
        }

        .burger-menu span {
            display: block;
            width: 25px;
            height: 3px;
            background: white;
            margin: 5px 0;
            transition: 0.3s;
            border-radius: 2px;
        }

        /* Nav Links Container */
        .nav-links {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* ===== CONTAINER ===== */
        .container {
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
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

        form button:hover {
            background: #16a085;
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

        /* ===== RESPONSIVE DESIGN ===== */

        /* Desktop/Layar Besar (1025px+) */
        @media (min-width: 1025px) {
            .container {
                padding: 30px 50px;
            }
        }

        /* Tablet Lanskap/Laptop Kecil (769px - 1024px) */
        @media (min-width: 769px) and (max-width: 1024px) {
            .container {
                padding: 25px 30px;
            }

            .card {
                padding: 18px;
            }
        }

        /* Ponsel Besar/Tablet (481px - 768px) */
        @media (min-width: 481px) and (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 22px;
            }

            .card {
                padding: 16px;
            }

            form input[type="text"],
            form input[type="number"],
            form select {
                padding: 9px;
                font-size: 13px;
            }

            form label {
                margin-top: 12px;
                font-size: 14px;
            }

            form button[type="submit"],
            form button[type="button"] {
                padding: 9px 18px;
                font-size: 14px;
            }
        }

        /* Ponsel Kecil (320px - 480px) */
        @media (max-width: 480px) {
            .container {
                padding: 15px;
            }

            h2 {
                font-size: 20px;
                margin-bottom: 15px;
            }

            .card {
                padding: 15px;
            }

            form input[type="text"],
            form input[type="number"],
            form select {
                padding: 8px;
                font-size: 12px;
            }

            form label {
                margin-top: 10px;
                font-size: 13px;
            }

            form button[type="submit"],
            form button[type="button"] {
                width: 100%;
                padding: 10px;
                font-size: 13px;
                margin-top: 15px;
            }

            .autocomplete .item {
                padding: 10px 8px;
                font-size: 13px;
            }
        }

        /* Burger Menu untuk layar < 600px */
        @media (max-width: 600px) {
            .burger-menu {
                display: block;
            }

            .navbar {
                justify-content: center;
                padding: 15px;
                min-height: 56px;
            }

            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #2c3e50;
                padding: 0;
                gap: 0;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                z-index: 999;
            }

            .nav-links.active {
                display: flex;
            }

            .navbar a {
                width: 100%;
                text-align: center;
                padding: 15px 18px;
                border-radius: 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .navbar a:last-child {
                border-bottom: none;
            }

            .navbar a:hover {
                background: #1abc9c;
            }

            /* Animasi Burger Menu */
            .burger-menu.active span:nth-child(1) {
                transform: rotate(-45deg) translate(-5px, 6px);
            }

            .burger-menu.active span:nth-child(2) {
                opacity: 0;
            }

            .burger-menu.active span:nth-child(3) {
                transform: rotate(45deg) translate(-5px, -6px);
            }
        }
    </style>
</head>

<body>

    <!-- NAVBAR -->
    <div class="navbar">
        <!-- Burger Menu Button -->
        <button class="burger-menu" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Navigation Links -->
        <div class="nav-links" id="navLinks">
            <a href="dashboard.php">Dashboard</a>
            <a href="pelanggan.php">Data Pelanggan</a>
            <a href="transaksi.php">Transaksi</a>
            <a href="laporan.php">Laporan</a>
            <a href="pengaturan.php">Pengaturan</a>
        </div>
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

                <!-- DROPDOWN KATEGORI -->
                <label>Kategori Laundry</label>
                <select name="kategori" id="kategori" required onchange="filterPaket()">
                    <option value="">-- Pilih Kategori --</option>
                    <?php while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                        <option value="<?= $kat['kategori_barang']; ?>">
                            <?= $kat['kategori_barang']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <!-- DROPDOWN PAKET LAUNDRY -->
                <label>Paket Laundry</label>
                <select name="package_id" id="paket" required onchange="setPaket()" disabled>
                    <option value="">-- Pilih Kategori Terlebih Dahulu --</option>
                </select>

                <input type="hidden" name="harga_paket" id="harga_paket">
                <input type="hidden" name="satuan" id="satuan_hidden">

                <!-- LABEL DINAMIS UNTUK QTY -->
                <label id="label_qty">Jumlah</label>
                <input type="number" step="0.1" name="qty" id="qty" oninput="hitungTotal()" required>

                <label>Total Harga</label>
                <input type="text" id="total" readonly>

                <button type="submit" name="simpan">Simpan Transaksi</button>
            </form>
        </div>
    </div>

    <script>
        /* ===============================
           DATA PAKET (dari PHP)
        =============================== */
        const allPakets = <?= json_encode($pakets); ?>;

        /* ===============================
           BURGER MENU
        =============================== */
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            const burgerMenu = document.querySelector('.burger-menu');

            navLinks.classList.toggle('active');
            burgerMenu.classList.toggle('active');
        }

        // Menutup menu saat link diklik
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                const navLinks = document.getElementById('navLinks');
                const burgerMenu = document.querySelector('.burger-menu');

                navLinks.classList.remove('active');
                burgerMenu.classList.remove('active');
            });
        });

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
           FILTER PAKET BERDASARKAN KATEGORI
        =============================== */
        function filterPaket() {
            const kategori = document.getElementById('kategori').value;
            const paketSelect = document.getElementById('paket');

            // Reset paket dropdown
            paketSelect.innerHTML = '<option value="">-- Pilih Paket --</option>';
            paketSelect.disabled = false;

            // Reset nilai
            document.getElementById('harga_paket').value = '';
            document.getElementById('satuan_hidden').value = '';
            document.getElementById('qty').value = '';
            document.getElementById('total').value = '';
            document.getElementById('label_qty').textContent = 'Jumlah';

            if (!kategori) {
                paketSelect.disabled = true;
                paketSelect.innerHTML = '<option value="">-- Pilih Kategori Terlebih Dahulu --</option>';
                return;
            }

            // Filter paket berdasarkan kategori
            const filteredPakets = allPakets.filter(p => p.kategori_barang === kategori);

            if (filteredPakets.length === 0) {
                paketSelect.innerHTML = '<option value="">-- Tidak Ada Paket --</option>';
                paketSelect.disabled = true;
                return;
            }

            // Tambahkan paket yang sesuai
            filteredPakets.forEach(p => {
                const option = document.createElement('option');
                option.value = p.id;
                option.setAttribute('data-harga', p.harga);
                option.setAttribute('data-satuan', p.nama_satuan);
                option.textContent = `${p.nama_paket} (Rp ${parseFloat(p.harga).toLocaleString('id-ID')}/${p.nama_satuan})`;
                paketSelect.appendChild(option);
            });
        }

        /* ===============================
           SET PAKET & UPDATE LABEL
        =============================== */
        function setPaket() {
            const select = document.getElementById('paket');
            const selectedOption = select.options[select.selectedIndex];

            const harga = selectedOption.getAttribute('data-harga') || 0;
            const satuan = selectedOption.getAttribute('data-satuan') || '';

            document.getElementById('harga_paket').value = harga;
            document.getElementById('satuan_hidden').value = satuan;

            // Update label qty sesuai satuan
            if (satuan) {
                document.getElementById('label_qty').textContent = `Jumlah (${satuan})`;
            } else {
                document.getElementById('label_qty').textContent = 'Jumlah';
            }

            hitungTotal();
        }

        /* ===============================
           HITUNG TOTAL
        =============================== */
        function hitungTotal() {
            const harga = document.getElementById('harga_paket').value || 0;
            const qty = document.getElementById('qty').value || 0;

            document.getElementById('total').value =
                (harga && qty)
                    ? "Rp " + (parseFloat(harga) * parseFloat(qty)).toLocaleString('id-ID')
                    : '';
        }
    </script>

</body>

</html>