<?php
session_start();

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Data produk
$barang = [
    ["B001", "Sabun", 5000],
    ["B002", "Sampo", 12000],
    ["B003", "Pasta Gigi", 8000],
    ["B004", "Tisu", 7000],
    ["B005", "Detergen", 15000],
];

$beli = [];
$jumlah = [];
$total = [];

// Loop acak pembelian
foreach ($barang as $i => $data) {
    $kode  = $data[0];
    $nama  = $data[1];
    $harga = $data[2];

    $beli[$i] = rand(0, 1);
    if ($beli[$i] == 1) {
        $jumlah[$i] = rand(1, 5);
        $total[$i]  = $harga * $jumlah[$i];
    } else {
        $jumlah[$i] = 0;
        $total[$i]  = 0;
    }
}

// Jika semua 0 → paksa beli 1 barang
if (array_sum($beli) == 0) {
    $i = array_rand($barang);
    $beli[$i] = 1;
    $jumlah[$i] = rand(1, 5);
    $total[$i] = $barang[$i][2] * $jumlah[$i];
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>POLGAN MART</title>

    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f1f4f9;
            margin: 0;
            padding: 0;
        }

        .header-top {
            display: flex;
            justify-content: flex-end;
            padding: 20px;
        }

        .header-top p {
            margin: 0 20px 0 0;
            color: #333;
            font-size: 15px;
            font-weight: 600;
        }

        .logout-btn {
            padding: 10px 15px;
            background: #eee;
            border-radius: 10px;
            border: 1px solid #ccc;
            text-decoration: none;
            color: #333;
        }

        .logout-btn:hover {
            background: #ddd;
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: 20px auto;
            background: #fff;
            padding: 30px;
            border-radius: 18px;
            box-shadow: 0px 4px 18px rgba(0,0,0,0.07);
        }

        h2 {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        h3 {
            margin-top: 30px;
            text-align: center;
            font-size: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            font-size: 15px;
        }

        table th {
            background: #f7f9fc;
            font-weight: 700;
            padding: 12px;
            border-bottom: 2px solid #ececec;
            text-align: left;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #efefef;
        }

        table tr:hover {
            background-color: #fafafa;
        }

        .total-row td {
            font-weight: bold;
            padding: 12px;
        }
    </style>
</head>

<body>

<div class="header-top">
    <p>Selamat datang, <?= $_SESSION['username']; ?>!</p>
    <a class="logout-btn" href="logout.php">Logout</a>
</div>

<div class="container">

    <h2>--POLGAN MART--</h2>
    <hr>

    <h3>Daftar Pembelian</h3>

    <table>
        <tr>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>Harga</th>
            <th>Jumlah</th>
            <th>Total</th>
        </tr>

        <?php
        $grandtotal = 0;

        foreach ($barang as $i => $data) {
            if ($beli[$i] == 1) {
                $kode  = $data[0];
                $nama  = $data[1];
                $harga = $data[2];
                ?>

                <tr>
                    <td><?= $kode ?></td>
                    <td><?= $nama ?></td>
                    <td>Rp <?= number_format($harga, 0, ',', '.') ?></td>
                    <td><?= $jumlah[$i] ?></td>
                    <td>Rp <?= number_format($total[$i], 0, ',', '.') ?></td>
                </tr>

                <?php
                $grandtotal += $total[$i];
            }
        }
        ?>

        <tr class="total-row">
            <td colspan="4" align="right">Total Belanja :</td>
            <td>Rp <?= number_format($grandtotal, 0, ',', '.') ?></td>
        </tr>

    </table>

</div>

</body>
</html>
