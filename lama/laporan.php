<?php
include 'service/database.php';
session_start();

// Ambil filter tanggal
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // default awal bulan ini
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');      // default hari ini

// Query ringkasan
$sql_summary = "
    SELECT 
        SUM(t.total) AS total_penjualan,
        SUM(t.jumlah) AS total_barang
    FROM transaksi t
    WHERE DATE(t.tanggal) BETWEEN '$start_date' AND '$end_date'
";
$summary = $db->query($sql_summary)->fetch_assoc();

// Query detail transaksi
$sql_detail = "
    SELECT t.*, p.nama 
    FROM transaksi t
    JOIN produk p ON t.produk_id = p.id
    WHERE DATE(t.tanggal) BETWEEN '$start_date' AND '$end_date'
    ORDER BY t.tanggal DESC
";
$riwayat = $db->query($sql_detail);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
</head>
<body>
    <h2>Laporan Penjualan</h2>

    <form method="GET" action="laporan.php">
        <label>Dari Tanggal:</label>
        <input type="date" name="start_date" value="<?= $start_date ?>">
        <label>Sampai:</label>
        <input type="date" name="end_date" value="<?= $end_date ?>">
        <button type="submit">Filter</button>
    </form>

    <h3>Ringkasan</h3>
    <ul>
        <li>Total Barang Terjual: <b><?= $summary['total_barang'] ?: 0 ?></b></li>
        <li>Total Penjualan: <b>Rp <?= number_format($summary['total_penjualan'] ?: 0, 0, ',', '.') ?></b></li>
    </ul>

    <h3>Detail Transaksi</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Produk</th>
            <th>Jumlah</th>
            <th>Total</th>
            <th>User</th>
            <th>Tanggal</th>
        </tr>
        <?php while ($trx = $riwayat->fetch_assoc()): ?>
        <tr>
            <td><?= $trx['id'] ?></td>
            <td><?= $trx['nama'] ?></td>
            <td><?= $trx['jumlah'] ?></td>
            <td>Rp <?= number_format($trx['total'], 0, ',', '.') ?></td>
            <td><?= isset($trx['user']) ? htmlspecialchars($trx['user']) : '-' ?></td>
            <td><?= $trx['tanggal'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
