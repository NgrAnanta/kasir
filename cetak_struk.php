<?php
include 'service/database.php';

if (!isset($_GET['id'])) {
    echo "ID transaksi tidak ditemukan.";
    exit;
}

$transaksi_id = (int)$_GET['id'];

// Ambil header transaksi
$header = $db->query("SELECT * FROM transaksi_header WHERE id = $transaksi_id")->fetch_assoc();
if (!$header) {
    echo "Transaksi tidak ditemukan.";
    exit;
}

// Ambil detail transaksi
$details = $db->query("
    SELECT d.*, p.nama, p.harga_jual
    FROM transaksi_detail d
    JOIN produk p ON d.produk_id = p.id
    WHERE d.transaksi_id = $transaksi_id
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Transaksi #<?= $header['id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: monospace; }
        .struk { max-width: 480px; margin: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 4px; text-align: left; }
        th { border-bottom: 1px solid #000; }
        td.total { text-align: right; }
    </style>
</head>
<body onload="window.print()">

<div class="struk border p-3 mt-3">
    <h4 class="text-center">Toko Anda</h4>
    <p class="text-center mb-0">Struk Transaksi #<?= $header['id'] ?></p>
    <p class="text-center mb-2"><?= date('d-m-Y H:i', strtotime($header['tanggal'])) ?></p>
    <p>User: <?= htmlspecialchars($header['user']) ?></p>

    <table>
        <thead>
            <tr>
                <th>Barang</th>
                <th>Harga</th>
                <th>Jumlah</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($d = $details->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($d['nama']) ?></td>
                <td>Rp <?= number_format($d['harga_jual'],0,',','.') ?></td>
                <td><?= $d['jumlah'] ?></td>
                <td>Rp <?= number_format($d['total'],0,',','.') ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="total">Grand Total</th>
                <th>Rp <?= number_format($header['total'],0,',','.') ?></th>
            </tr>
        </tfoot>
    </table>

    <p class="text-center mt-3">Terima kasih atas kunjungan Anda!</p>
</div>

</body>
</html>
