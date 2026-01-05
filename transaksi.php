<?php
require 'auth.php';
include 'koneksi.php';


$cabang_id = $_SESSION['cabang_id'];

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
   AMBIL DATA KATEGORI AKTIF 
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
   AMBIL DATA PAKET + KATEGORI + SATUAN 
   =============================== */
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
       AND lu.cabang_id = '$cabang_id'
     ORDER BY lu.kategori_barang ASC, lp.nama_paket ASC"
);

$pakets = [];
while ($p = mysqli_fetch_assoc($pakets_query)) {
    $pakets[] = $p;
}

/* =============================== 
   SIMPAN TRANSAKSI 
   =============================== */
if (isset($_POST['simpan'])) {

    $tanggal = date('Y-m-d H:i:s');
    $qty = floatval($_POST['qty']);
    $package_id = intval($_POST['package_id']);
    $customer_id = intval($_POST['customer_id']);
    $user_id = $_SESSION['user_id'];

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
       VALIDASI PAKET & AMBIL HARGA 
       =============================== */
    $cek_paket = $conn->prepare(
        "SELECT lp.harga
         FROM laundry_packages lp
         JOIN laundry_units lu ON lp.unit_id = lu.id
         WHERE lp.id = ?
           AND lp.is_active = 1
           AND lu.is_active = 1
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
    $harga_paket = $row_paket['harga'];
    $total = $harga_paket * $qty;

    /* =============================== 
       AMBIL STATUS AWAL 
       =============================== */
    $q_status = $conn->prepare(
        "SELECT psf.status_id
         FROM package_status_flow psf
         JOIN laundry_status ls ON psf.status_id = ls.id
         WHERE psf.package_id = ?
           AND ls.is_active = 1
         ORDER BY psf.urutan ASC
         LIMIT 1"
    );
    $q_status->bind_param("i", $package_id);
    $q_status->execute();
    $res_status = $q_status->get_result();

    if ($res_status->num_rows == 0) {
        echo "<script>alert('Alur status belum tersedia');history.back();</script>";
        exit;
    }

    $status_id = $res_status->fetch_assoc()['status_id'];

    /* =============================== 
       SIMPAN KE transactions 
       =============================== */
    $stmt = $conn->prepare(
        "INSERT INTO transactions (
            tanggal,
            customer_id,
            package_id,
            berat_kg,
            total_harga,
            status_id,
            user_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "siiddii",
        $tanggal,
        $customer_id,
        $package_id,
        $qty,
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
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Transaksi Laundry</title>

    <style>
        /* ===============================
           GLOBAL STYLE
           =============================== */
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

        /* ===============================
           NAVBAR
           =============================== */
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

        /* ===============================
           BURGER MENU
           =============================== */
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

        .nav-links {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* ===============================
           CONTAINER & CARD
           =============================== */
        .container {
            padding: 30px;
        }

        h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* ===============================
           FORM
           =============================== */
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
            font-size: 14px;
        }

        form button {
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

        form button:hover {
            background: #16a085;
        }

        /* ===============================
           AUTOCOMPLETE
           =============================== */
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

        /* ===============================
           RESPONSIVE
           =============================== */
        @media (max-width: 600px) {
            .burger-menu {
                display: block;
            }

            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: #2c3e50;
                z-index: 999;
            }

            .nav-links.active {
                display: flex;
            }

            .navbar a {
                width: 100%;
                text-align: center;
                padding: 15px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

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

    <!-- ===============================
     NAVBAR
     =============================== -->
    <div class="navbar">

        <button class="burger-menu" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="nav-links" id="navLinks">
            <a href="dashboard.php">Dashboard</a>
            <a href="pelanggan.php">Data Pelanggan</a>
            <a href="transaksi.php">Transaksi</a>
            <a href="laporan.php">Laporan</a>
            <a href="pengaturan.php">Pengaturan</a>
        </div>
    </div>

    <!-- ===============================
     CONTENT
     =============================== -->
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

                <a id="btnTambah" style="display:none; margin-top:15px;">
                    <button type="button">+ Tambah Pelanggan</button>
                </a>

                <label>Kategori Laundry</label>
                <select name="kategori" id="kategori" required onchange="filterPaket()">
                    <option value="">-- Pilih Kategori --</option>
                    <?php while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                        <option value="<?= $kat['kategori_barang']; ?>">
                            <?= $kat['kategori_barang']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Paket Laundry</label>
                <select name="package_id" id="paket" required onchange="setPaket()" disabled>
                    <option value="">-- Pilih Kategori Terlebih Dahulu --</option>
                </select>

                <input type="hidden" id="harga_paket">
                <input type="hidden" id="satuan_hidden">

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
           DATA DARI PHP
           =============================== */
        const customers = <?= json_encode($customers); ?>;
        const allPakets = <?= json_encode($pakets); ?>;

        /* ===============================
           BURGER MENU
           =============================== */
        function toggleMenu() {
            document.getElementById('navLinks').classList.toggle('active');
            document.querySelector('.burger-menu').classList.toggle('active');
        }

        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('navLinks').classList.remove('active');
                document.querySelector('.burger-menu').classList.remove('active');
            });
        });

        /* ===============================
           AUTOCOMPLETE CUSTOMER
           =============================== */
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
                btnTambah.href = "pelanggan_tambah.php?nama=" + encodeURIComponent(input.value);
                hiddenId.value = '';
            } else {
                btnTambah.style.display = 'none';
            }

            list.style.display = found ? 'block' : 'none';
        });

        /* ===============================
           FILTER PAKET
           =============================== */
        function filterPaket() {
            const kategori = document.getElementById('kategori').value;
            const paketSelect = document.getElementById('paket');

            paketSelect.innerHTML = '<option value="">-- Pilih Paket --</option>';
            paketSelect.disabled = false;

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

            const filteredPakets = allPakets.filter(
                p => p.kategori_barang === kategori
            );

            if (filteredPakets.length === 0) {
                paketSelect.innerHTML = '<option value="">-- Tidak Ada Paket --</option>';
                paketSelect.disabled = true;
                return;
            }

            filteredPakets.forEach(p => {
                const option = document.createElement('option');
                option.value = p.id;
                option.dataset.harga = p.harga;
                option.dataset.satuan = p.nama_satuan;
                option.textContent =
                    `${p.nama_paket} (Rp ${parseFloat(p.harga).toLocaleString('id-ID')}/${p.nama_satuan})`;

                paketSelect.appendChild(option);
            });
        }

        /* ===============================
           SET PAKET & TOTAL
           =============================== */
        function setPaket() {
            const select = document.getElementById('paket');
            const opt = select.options[select.selectedIndex];

            document.getElementById('harga_paket').value = opt.dataset.harga || 0;
            document.getElementById('satuan_hidden').value = opt.dataset.satuan || '';

            document.getElementById('label_qty').textContent =
                opt.dataset.satuan
                    ? `Jumlah (${opt.dataset.satuan})`
                    : 'Jumlah';

            hitungTotal();
        }

        function hitungTotal() {
            const harga = document.getElementById('harga_paket').value || 0;
            const qty = document.getElementById('qty').value || 0;

            document.getElementById('total').value =
                harga && qty
                    ? "Rp " + (harga * qty).toLocaleString('id-ID')
                    : '';
        }
    </script>
</body>

</html>