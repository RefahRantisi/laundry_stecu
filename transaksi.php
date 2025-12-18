<?php
session_start();
if (!isset($_SESSION['login'])) {
    echo "<script>
        alert('Silakan login terlebih dahulu');
        window.location='index.php';
    </script>";
    exit;
}
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
        label {
            display: block;
            margin-top: 10px;
        }

        .autocomplete {
            border: 1px solid #ccc;
            max-height: 150px;
            overflow-y: auto;
            display: none;
            position: absolute;
            background: white;
            width: 250px;
            z-index: 99;
        }

        .item {
            padding: 6px;
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

        <label>Nama Pelanggan</label>
        <input type="text" id="customer_display" name="customer_display"
            value="<?= htmlspecialchars($display_value); ?>" autocomplete="off" required>

        <input type="hidden" name="customer_id" id="customer_id" value="<?= htmlspecialchars($customer_id_url); ?>">

        <div id="list" class="autocomplete"></div>

        <br>
        <a id="btnTambah" style="display:none;">
            <button type="button">+ Tambah Pelanggan</button>
        </a>


        <!-- PAKET DARI DATABASE -->
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

        <br><br>
        <button type="submit" name="simpan">Simpan Transaksi</button>

    </form>

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

</body>

</html>