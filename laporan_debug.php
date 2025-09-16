<?php
include 'service/database.php';
session_start();

// Cek login
if(!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Filter tanggal
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// ================= DEBUG =================
// Cek isi transaksi_header
$cek_header = $db->query("SELECT * FROM transaksi_header");
$cek_detail = $db->query("SELECT * FROM transaksi_detail");

echo "<pre>";
echo "=== DEBUG TRANSAKSI_HEADER ===\n";
while($h = $cek_header->fetch_assoc()){
    print_r($h);
}

echo "\n=== DEBUG TRANSAKSI_DETAIL ===\n";
while($d = $cek_detail->fetch_assoc()){
    print_r($d);
}
echo "\n=== END DEBUG ===\n";

// ================= SUMMARY =================
$sql_summary = "
    SELECT 
        COALESCE(SUM(d.jumlah),0) AS total_barang,
        COALESCE(SUM(d.total),0) AS total_penjualan
    FROM transaksi_header h
    LEFT JOIN transaksi_detail d ON h.id = d.transaksi_id
    WHERE DATE(h.tanggal) BETWEEN '$start_date' AND '$end_date'
";
$summary = $db->query($sql_summary);
if(!$summary){
    die("Query summary error: ".$db->error);
}
$summary = $summary->fetch_assoc();

// ================= DETAIL =================
$sql_detail = "
    SELECT 
        h.id AS header_id, 
        h.user, 
        h.tanggal, 
        d.jumlah, 
        d.total, 
        COALESCE(p.nama,'-') AS nama
    FROM transaksi_header h
    LEFT JOIN transaksi_detail d ON h.id = d.transaksi_id
    LEFT JOIN produk p ON d.produk_id = p.id
    WHERE DATE(h.tanggal) BETWEEN '$start_date' AND '$end_date'
    ORDER BY h.tanggal DESC
";
$riwayat = $db->query($sql_detail);
if(!$riwayat){
    die("Query detail error: ".$db->error);
}

// Cek hasil query
echo "\n=== DEBUG DETAIL QUERY ===\n";
while($row = $riwayat->fetch_assoc()){
    print_r($row);
}
echo "</pre>";
?>
