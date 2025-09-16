<?php
include 'service/database.php';
session_start();

// Cek login
if(!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Logout
if(isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

// ================= DEBUG: Ambil semua transaksi =================
$sql_detail = "
SELECT 
    h.id AS header_id,
    h.user,
    h.tanggal,
    d.id AS detail_id,
    d.jumlah,
    d.total,
    IFNULL(p.nama,'-') AS nama
FROM transaksi_header h
LEFT JOIN transaksi_detail d ON h.id = d.transaksi_id
LEFT JOIN produk p ON d.produk_id = p.id
ORDER BY h.tanggal DESC
";

$riwayat = $db->query($sql_detail);

// Debug: tampilkan hasil query
echo "<h2>DEBUG QUERY DETAIL</h2><pre>";
if($riwayat) {
    while($row = $riwayat->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Query gagal: " . $db->error;
}
echo "</pre>";
?>
