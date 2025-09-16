<?php
include 'service/database.php';
session_start();

// Cek login
if(!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Ambil filter tanggal jika ada
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Kondisi filter
$date_condition = '';
if($start_date && $end_date) {
    $date_condition = "WHERE DATE(h.tanggal) BETWEEN '$start_date' AND '$end_date'";
}

// Ringkasan total barang, penjualan, untung bersih
$sql_summary = "
    SELECT 
        COALESCE(SUM(d.jumlah),0) AS total_barang,
        COALESCE(SUM(d.total),0) AS total_penjualan,
        COALESCE(SUM((p.harga_jual - p.harga_beli) * d.jumlah),0) AS total_untung
    FROM transaksi_header h
    LEFT JOIN transaksi_detail d ON h.id = d.transaksi_id
    LEFT JOIN produk p ON d.produk_id = p.id
    $date_condition
";
$summary = $db->query($sql_summary)->fetch_assoc();

// Detail transaksi
$sql_detail = "
    SELECT 
        h.id AS header_id, 
        h.user, 
        h.tanggal, 
        d.id AS detail_id, 
        d.jumlah, 
        d.total, 
        COALESCE(p.nama,'-') AS produk,
        COALESCE((p.harga_jual - p.harga_beli) * d.jumlah,0) AS untung_bersih
    FROM transaksi_header h
    LEFT JOIN transaksi_detail d ON h.id = d.transaksi_id
    LEFT JOIN produk p ON d.produk_id = p.id
    $date_condition
    ORDER BY h.tanggal DESC
";
$riwayat = $db->query($sql_detail);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Penjualan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
    body { background: #f8f9fa; }
    .card-summary { transition: transform 0.2s; cursor: pointer; }
    .card-summary:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
    table thead { background-color: #0d6efd; color: white; position: sticky; top: 0; z-index: 1; }
    .filter-row .form-control, .filter-row .btn { min-height: 38px; }
</style>
</head>
<body>
<div class="container py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 text-primary"><i class="bi bi-bar-chart-line-fill"></i> Laporan Penjualan</h2>
        <a href="dashboard.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <!-- Filter -->
    <form class="row g-2 mb-4 filter-row" method="GET" action="laporan.php">
        <div class="col-md-3">
            <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>" placeholder="Dari Tanggal">
        </div>
        <div class="col-md-3">
            <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>" placeholder="Sampai Tanggal">
        </div>
        <div class="col-md-6 d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
            <a href="laporan.php" class="btn btn-secondary"><i class="bi bi-arrow-repeat"></i> Reset</a>
            <a href="laporan_pdf.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>" target="_blank" class="btn btn-danger"><i class="bi bi-file-earmark-pdf"></i> Export PDF</a>
        </div>
    </form>

    <!-- Ringkasan -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-summary text-white bg-success mb-3 text-center">
                <div class="card-body">
                    <h6>Total Barang Terjual</h6>
                    <h4><?= $summary['total_barang'] ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-summary text-white bg-primary mb-3 text-center">
                <div class="card-body">
                    <h6>Total Penjualan</h6>
                    <h4>Rp <?= number_format($summary['total_penjualan'],0,',','.') ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-summary text-white bg-warning mb-3 text-center">
                <div class="card-body">
                    <h6>Total Untung Bersih</h6>
                    <h4>Rp <?= number_format($summary['total_untung'],0,',','.') ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Transaksi -->
    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <strong>Detail Transaksi</strong>
        </div>
        <div class="card-body table-responsive" style="max-height: 500px;">
            <table class="table table-bordered table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Untung Bersih</th>
                        <th>Penjual</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($riwayat->num_rows > 0): ?>
                        <?php while($trx = $riwayat->fetch_assoc()): ?>
                        <tr>
                            <td><?= $trx['header_id'] ?></td>
                            <td><?= htmlspecialchars($trx['produk']) ?></td>
                            <td><?= $trx['jumlah'] ?></td>
                            <td>Rp <?= number_format($trx['total'],0,',','.') ?></td>
                            <td>Rp <?= number_format($trx['untung_bersih'],0,',','.') ?></td>
                            <td><?= htmlspecialchars($trx['user']) ?></td>
                            <td><?= $trx['tanggal'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada transaksi</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
