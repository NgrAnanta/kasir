<?php
include 'service/database.php';
require 'vendor/autoload.php'; // pastikan sudah install dompdf via composer

use Dompdf\Dompdf;

// Ambil filter tanggal
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');

// Ringkasan total
$sql_summary = "
    SELECT 
        COALESCE(SUM(d.jumlah),0) AS total_barang,
        COALESCE(SUM(d.total),0) AS total_penjualan,
        COALESCE(SUM((p.harga_jual - p.harga_beli) * d.jumlah),0) AS total_untung
    FROM transaksi_header h
    LEFT JOIN transaksi_detail d ON h.id = d.transaksi_id
    LEFT JOIN produk p ON d.produk_id = p.id
    WHERE DATE(h.tanggal) BETWEEN '$start_date' AND '$end_date'
";
$summary = $db->query($sql_summary)->fetch_assoc();

// Detail transaksi
$sql_detail = "
    SELECT 
        h.id AS header_id, 
        h.user, 
        h.tanggal, 
        d.jumlah, 
        d.total, 
        COALESCE(p.nama,'-') AS produk,
        COALESCE((p.harga_jual - p.harga_beli) * d.jumlah,0) AS untung_bersih
    FROM transaksi_header h
    LEFT JOIN transaksi_detail d ON h.id = d.transaksi_id
    LEFT JOIN produk p ON d.produk_id = p.id
    WHERE DATE(h.tanggal) BETWEEN '$start_date' AND '$end_date'
    ORDER BY h.tanggal DESC
";
$riwayat = $db->query($sql_detail);

// Buat HTML laporan
$html = '<h2 style="text-align:center;">Laporan Penjualan</h2>';
$html .= "<p>Periode: $start_date s/d $end_date</p>";
$html .= '<table border="1" width="100%" cellpadding="5" cellspacing="0">';
$html .= '
<tr style="background-color:#f2f2f2;">
    <th>ID</th>
    <th>Produk</th>
    <th>Jumlah</th>
    <th>Total</th>
    <th>Untung Bersih</th>
    <th>User</th>
    <th>Tanggal</th>
</tr>
';

if($riwayat->num_rows > 0){
    while($trx = $riwayat->fetch_assoc()){
        $html .= '<tr>';
        $html .= '<td>'.$trx['header_id'].'</td>';
        $html .= '<td>'.htmlspecialchars($trx['produk']).'</td>';
        $html .= '<td>'.$trx['jumlah'].'</td>';
        $html .= '<td>Rp '.number_format($trx['total'],0,',','.').'</td>';
        $html .= '<td>Rp '.number_format($trx['untung_bersih'],0,',','.').'</td>';
        $html .= '<td>'.htmlspecialchars($trx['user']).'</td>';
        $html .= '<td>'.$trx['tanggal'].'</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="7" style="text-align:center;">Tidak ada transaksi</td></tr>';
}
$html .= '</table>';

// Tambahkan ringkasan di bawah tabel
$html .= '<h4>Ringkasan</h4>';
$html .= '<p>Total Barang Terjual: '.$summary['total_barang'].'</p>';
$html .= '<p>Total Penjualan: Rp '.number_format($summary['total_penjualan'],0,',','.').'</p>';
$html .= '<p>Total Untung Bersih: Rp '.number_format($summary['total_untung'],0,',','.').'</p>';

// Buat PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Output langsung sebagai file download
$dompdf->stream("laporan_penjualan_{$start_date}_sd_{$end_date}.pdf", ["Attachment" => true]);
