<?php
session_start();


$inputKode = isset($_POST['kode']) ? strtoupper($_POST['kode']) : '';
$inputNama = isset($_POST['nama']) ? $_POST['nama'] : '';
$inputHarga = isset($_POST['harga']) ? str_replace('.', '', $_POST['harga']) : ''; // Menghapus format ribuan
$inputJumlah = isset($_POST['jumlah']) ? intval($_POST['jumlah']) : '';
$statusMessage = '';

if (isset($_POST['clear'])) {
    unset($_SESSION['cart']);
}

// Proses Tambah Barang
if (isset($_POST['tambah'])) {
    $kode = strtoupper($_POST['kode']);
    $nama = $_POST['nama'];
    // Hapus format Rupiah atau titik ribuan untuk perhitungan
    $harga = intval(str_replace('.', '', $_POST['harga'])); 
    $jumlah = intval($_POST['jumlah']);

    // Validasi dasar
    if (empty($kode) || empty($nama) || empty($harga) || empty($jumlah) || $harga <= 0 || $jumlah <= 0) {
        $statusMessage = 'Harap isi semua kolom (Kode, Nama, Harga, Jumlah) dengan nilai yang valid.';
    } else {
        // Data valid, proses penambahan
        $subtotal = $harga * $jumlah;
        $found = false;

        // Cek jika barang sudah ada di keranjang (didasarkan pada kode)
        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['kode'] === $kode) {
                    // Jika kode sama, hanya update jumlah dan subtotal
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
        
        // Bersihkan input setelah berhasil
        $inputKode = '';
        $inputNama = '';
        $inputHarga = '';
        $inputJumlah = '';
        $statusMessage = 'Barang berhasil ditambahkan ke daftar pembelian.';
    }
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
if ($total > 0 && $total < 50000) {
    $persenDiskon = 5;
} elseif ($total >= 50000 && $total <= 100000) {
    $persenDiskon = 10;
} elseif ($total > 100000) {
    $persenDiskon = 15;
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
    <title>POLGAN MART - Input Manual</title>
    <style>
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
        .button-row { display: flex; gap: 10px; margin-top: 15px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .button-row button { padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; transition: background-color 0.2s; }
        .btn-tambahkan-small { background: #0d6efd; color: white; border: none; width: 120px; }
        .btn-batal { background: #f8f9fa; color: #333; border: 1px solid #ccc; width: 80px; }
        .btn-tambahkan-large { flex-grow: 1; background: #0d6efd; color: white; border: none; height: 42px; margin: 0; }
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
            <button class="logout-btn">Logout</button>
        </div>
    </div>
    
    <div class="input-area">
        <form method="post">
            
            <?php if ($statusMessage): ?>
                <p style="color: <?= (strpos($statusMessage, 'berhasil') !== false) ? 'green' : 'red' ?>; font-weight: bold;"><?= $statusMessage ?></p>
            <?php endif; ?>

            <div class="input-group">
                <label>Kode Barang</label>
                <input type="text" name="kode" class="input-field" placeholder="Masukkan Kode Barang (cth: A01)" 
                       required value="<?= htmlspecialchars($inputKode) ?>">
            </div>
            
            <div class="input-group">
                <label>Nama Barang</label>
                <input type="text" name="nama" class="input-field" placeholder="Masukkan Nama Barang" 
                       required value="<?= htmlspecialchars($inputNama) ?>">
            </div>

            <div class="form-row">
                <div class="input-group">
                    <label>Harga</label>
                    <input type="text" name="harga" class="input-field" placeholder="Masukkan Harga (cth: 5000)" 
                           required value="<?= htmlspecialchars($inputHarga) ?>">
                </div>
                <div class="input-group">
                    <label>Jumlah</label>
                    <input type="number" name="jumlah" class="input-field" min="1" placeholder="Masukkan Jumlah" 
                           required value="<?= htmlspecialchars($inputJumlah) ?>">
                </div>
            </div>

            <div class="button-row">
                <button class="btn-tambahkan-small" name="tambah">Tambahkan</button>
                <button type="button" class="btn-batal" onclick="window.location.href=window.location.href">Batal</button>
                <button type="button" class="btn-tambahkan-large" disabled>Tambahkan</button>
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
</body>
</html>