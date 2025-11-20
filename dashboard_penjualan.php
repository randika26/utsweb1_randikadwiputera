<?php
session_start();

// Daftar barang (contoh)
$barangList = [
    "A01" => ["nama" => "Milku", "harga" => 5000],
    "A02" => ["nama" => "Fanta", "harga" => 6000],
    "A03" => ["nama" => "Oreo", "harga" => 12000],
    "A04" => ["nama" => "Chitato", "harga" => 12000],
    "K001" => ["nama" => "Teh Pucuk", "harga" => 3000], 
];

// Tombol Kosongkan Keranjang
if (isset($_POST['clear'])) {
    unset($_SESSION['cart']);
    // Redirect untuk membersihkan POST dan query string
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Proses Tambah Barang
if (isset($_POST['tambah'])) {
    $kode = strtoupper($_POST['kode']);
    $jumlah = intval($_POST['jumlah']);

    // Validasi
    if (isset($barangList[$kode]) && $jumlah > 0) {
        $nama = $barangList[$kode]["nama"];
        $harga = $barangList[$kode]["harga"];
        $subtotal = $harga * $jumlah;
        $found = false;

        // Cek jika barang sudah ada di keranjang
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['kode'] === $kode) {
                    $_SESSION['cart'][$key]['jumlah'] += $jumlah;
                    $_SESSION['cart'][$key]['subtotal'] += $subtotal;
                    $found = true;
                    break;
                }
            }
        }
        
        // Jika barang belum ada, tambahkan sebagai item baru
        if (!$found) {
            $_SESSION['cart'][] = [
                "kode" => $kode,
                "nama" => $nama,
                "harga" => $harga,
                "jumlah" => $jumlah,
                "subtotal" => $subtotal
            ];
        }
    }
    // Redirect setelah penambahan berhasil untuk mencegah resubmission form
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Hitung total, diskon, dan grand total
$total = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['subtotal'];
    }
}

// Logika Diskon
$persenDiskon = 0;
if ($total > 100000) {
    $persenDiskon = 10;
}

$diskon = ($persenDiskon / 100) * $total;
$grandTotal = $total - $diskon;

// Fungsi untuk format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>POLGAN MART</title>
    <style>
        /* CSS Styling */
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; background-color: #f7f7f7; padding: 20px; display: flex; justify-content: center; }
        .main-container { width: 800px; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); margin-top: 30px; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid #eee; }
        .header-left { display: flex; align-items: center; }
        .logo-box { background-color: #0d6efd; color: white; padding: 8px 12px; border-radius: 6px; font-weight: bold; margin-right: 10px; font-size: 14px; }
        .system-info h1 { font-size: 16px; margin: 0; color: #333; }
        .system-info p { font-size: 12px; margin: 0; color: #666; }
        .header-right { text-align: right; font-size: 14px; }
        .header-right .role { font-size: 12px; color: #888; }
        .header-right .logout-btn { background: none; border: none; color: #0d6efd; cursor: pointer; padding: 0; margin-top: 4px; font-size: 12px; }
        .input-area { padding: 20px 0; }
        .input-group { margin-bottom: 15px; }
        label { display: block; font-size: 14px; font-weight: 500; color: #333; margin-bottom: 5px; }
        .input-field { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px; outline: none; transition: border-color 0.2s; }
        .input-field:focus { border-color: #0d6efd; }
        .form-row { display: flex; gap: 15px; }
        .form-row .input-group { flex: 1; }
        
        /* CSS untuk button-row yang sudah disederhanakan */
        .button-row { 
            display: flex; 
            gap: 10px; 
            margin-top: 15px; 
            border-bottom: 1px solid #eee; 
            padding-bottom: 20px; 
            justify-content: flex-start; /* Menyusun tombol dari kiri */
        }
        .button-row button { padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; transition: background-color 0.2s; }
        .btn-tambahkan-small { background: #0d6efd; color: white; border: none; width: 120px; }
        .btn-batal { background: #f8f9fa; color: #333; border: 1px solid #ccc; width: 80px; }

        .purchase-list h3 { text-align: center; font-size: 16px; margin: 20px 0 10px 0; color: #333; font-weight: 600; }
        .table-list { width: 100%; border-collapse: collapse; font-size: 14px; color: #333; }
        .table-list th, .table-list td { padding: 10px 15px; text-align: left; }
        .table-list th { font-weight: 600; color: #555; border-bottom: 1px solid #ddd; }
        .table-list td { border-bottom: 1px solid #eee; }
        .table-list tr:last-child td { border-bottom: none; }
        .summary-area { margin-top: 20px; padding-top: 15px; }
        .summary-row { display: flex; justify-content: flex-end; padding: 5px 0; font-size: 14px; border-bottom: 1px solid #f0f0f0; }
        .summary-row .label { width: 150px; font-weight: 500; color: #555; text-align: right; padding-right: 15px; }
        .summary-row .value { width: 120px; text-align: right; font-weight: 500; }
        .summary-total { font-size: 16px; font-weight: bold; color: #0d6efd; }
        .summary-diskon .value { color: #dc3545; }
        .summary-total-bayar { border-top: 1px solid #ccc; margin-top: 10px; padding-top: 10px; }
        .footer-action { padding-top: 15px; }
        .btn-kosongkan { background: #f8f9fa; color: #333; border: 1px solid #ccc; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 13px; width: auto; margin: 0; }
    </style>
</head>
<body>

<div class="main-container">
    
    <div class="header">
        <div class="header-left">
            <div class="logo-box">PM</div>
            <div class="system-info">
                <h1>--POLGAN MART--</h1>
                <p>Sistem Penjualan Sederhana</p>
            </div>
        </div>
        <div class="header-right">
    <span>Selamat datang, admin!</span>
    <div class="role">Role: Dosen</div>
    
    <a href="logout.php" class="logout-btn" style="text-decoration: underline; color: #dc3545;">Logout</a>
    </div>
    </div>
    
    <div class="input-area">
        <form method="post" id="form-tambah">
            
            <div class="input-group">
                <label>Kode Barang</label>
                <input type="text" name="kode" class="input-field" placeholder="Masukkan Kode Barang atau Pilih" required 
                       list="kode_barang_list" oninput="getBarangData(this.value)"> 
                
                <datalist id="kode_barang_list">
                    <?php foreach ($barangList as $kode => $barang): ?>
                        <option value="<?= htmlspecialchars($kode) ?>"><?= htmlspecialchars($barang['nama']) ?></option>
                    <?php endforeach; ?>
                </datalist>
            </div>
            
            <div class="input-group">
                <label>Nama Barang</label>
                <input type="text" id="nama-barang-display" class="input-field" placeholder="Nama Barang (Otomatis)" readonly>
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>Harga</label>
                    <input type="text" id="harga-display" class="input-field" placeholder="Harga Satuan (Otomatis)" readonly>
                </div>
                <div class="input-group">
                    <label>Jumlah</label>
                    <input type="number" name="jumlah" class="input-field" min="1" placeholder="Masukkan Jumlah" required>
                </div>
            </div>

            <div class="button-row">
                <button class="btn-tambahkan-small" name="tambah">Tambahkan</button>
                <button type="button" class="btn-batal" onclick="document.getElementById('form-tambah').reset(); document.getElementById('nama-barang-display').value = ''; document.getElementById('harga-display').value = '';">Batal</button>
            </div>
            
        </form>
    </div>
    
    <div class="purchase-list">
        <h3>Daftar Pembelian</h3>

        <?php if (!empty($_SESSION['cart'])): ?>
        <table class="table-list">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['kode']) ?></td>
                    <td><?= htmlspecialchars($item['nama']) ?></td>
                    <td><?= formatRupiah($item['harga']) ?></td>
                    <td><?= $item['jumlah'] ?></td>
                    <td style="text-align:right;"><?= formatRupiah($item['subtotal']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-area">
            <div class="summary-row">
                <div class="label">Total Belanja</div>
                <div class="value"><?= formatRupiah($total) ?></div>
            </div>
            <div class="summary-row summary-diskon">
                <div class="label">Diskon</div>
                <div class="value"><?= formatRupiah($diskon) ?> (<?= $persenDiskon ?>%)</div>
            </div>
            <div class="summary-row summary-total-bayar summary-total">
                <div class="label">Total Bayar</div>
                <div class="value"><?= formatRupiah($grandTotal) ?></div>
            </div>
        </div>

        <div class="footer-action">
            <form method="post" style="display:inline-block;">
                <button class="btn-kosongkan" name="clear">Kosongkan Keranjang</button>
            </form>
        </div>

        <?php else: ?>
        <p style="text-align:center; padding:20px; color:#888;">Keranjang masih kosong.</p>
        <?php endif; ?>

    </div>

</div>

<script>
    // Data barang dari PHP diubah ke JavaScript agar bisa diakses real-time oleh browser
    const barangListJS = <?= json_encode($barangList) ?>;

    /**
     * Fungsi yang dipanggil saat ada input (oninput) di kolom Kode Barang.
     */
    function getBarangData(kode) {
        kode = kode.toUpperCase().trim();
        const namaDisplay = document.getElementById('nama-barang-display');
        const hargaDisplay = document.getElementById('harga-display');
        
        // Cek jika kode ditemukan di daftar barang
        if (barangListJS[kode]) {
            namaDisplay.value = barangListJS[kode]['nama'];
            hargaDisplay.value = 'Rp ' + number_format(barangListJS[kode]['harga']);
        } else {
            // Jika kode tidak ditemukan, bersihkan field
            namaDisplay.value = '';
            hargaDisplay.value = '';
        }
    }

    // Fungsi format angka untuk tampilan Rupiah di JavaScript
    function number_format(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }
</script>

</body>
</html>