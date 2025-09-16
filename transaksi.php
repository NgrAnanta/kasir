<?php
include 'service/database.php';
session_start();

if(!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

if(isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('location: index.php');
    exit;
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// ================== PROSES TRANSAKSI ==================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $produk_ids = $_POST['produk_id'] ?? [];
    $jumlahs = $_POST['jumlah'] ?? [];

    if (!is_array($produk_ids)) $produk_ids = [$produk_ids];
    if (!is_array($jumlahs)) $jumlahs = [$jumlahs];

    $grand_total = 0;
    $items = [];
    $error_msg = '';
    $success = true;

    $username = $_SESSION['username'] ?? 'guest';

    // Validasi dan hitung total
    for ($i=0; $i<count($produk_ids); $i++) {
        $produk_id = (int)$produk_ids[$i];
        $jumlah = (int)$jumlahs[$i];
        if ($produk_id < 1 || $jumlah < 1) continue;

        $produk = $db->query("SELECT * FROM produk WHERE id=$produk_id")->fetch_assoc();
        if (!$produk) {
            $success = false;
            $error_msg .= "Produk ID $produk_id tidak ditemukan!<br>";
            continue;
        }
        if ($produk['stock'] < $jumlah) {
            $success = false;
            $error_msg .= "Stok produk '" . htmlspecialchars($produk['nama']) . "' tidak mencukupi!<br>";
            continue;
        }

        $total = $produk['harga_jual'] * $jumlah;
        $grand_total += $total;
        $items[] = [
            'produk_id' => $produk_id,
            'jumlah' => $jumlah,
            'total' => $total
        ];
    }

    if ($success && count($items) > 0) {
        // Insert header
        $db->query("INSERT INTO transaksi_header (user, total) VALUES ('".$db->real_escape_string($username)."', $grand_total)");
        $transaksi_id = $db->insert_id;

        // Insert detail & update stok
        foreach ($items as $item) {
            $db->query("INSERT INTO transaksi_detail (transaksi_id, produk_id, jumlah, total)
                        VALUES ({$transaksi_id}, {$item['produk_id']}, {$item['jumlah']}, {$item['total']})");
            $db->query("UPDATE produk SET stock = stock - {$item['jumlah']} WHERE id={$item['produk_id']}");
        }

        $_SESSION['message'] = "✅ Transaksi berhasil! Total: Rp " . number_format($grand_total,0,',','.');
    } else {
        $_SESSION['message'] = "❌ " . $error_msg;
    }

    header("Location: transaksi.php");
    exit;
}

// ================== AMBIL DATA ==================
$produk_list = $db->query("SELECT * FROM produk ORDER BY nama ASC");
$riwayat = $db->query("
    SELECT h.id, h.user, h.total, h.tanggal, 
           GROUP_CONCAT(CONCAT(p.nama,' x', d.jumlah) SEPARATOR ', ') as items
    FROM transaksi_header h
    JOIN transaksi_detail d ON h.id = d.transaksi_id
    JOIN produk p ON d.produk_id = p.id
    GROUP BY h.id
    ORDER BY h.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-cart-check text-success"></i> Transaksi</h3>
        <a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <!-- Pesan -->
    <?php if ($message): ?>
        <div class="alert <?= strpos($message,'✅')!==false ? 'alert-success' : 'alert-danger' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <!-- Form Transaksi -->
    <div class="card shadow-sm mb-4 border-0 rounded-3">
        <div class="card-header bg-success text-white">
            <strong><i class="bi bi-plus-circle"></i> Form Transaksi</strong>
        </div>
        <div class="card-body">
            <form action="transaksi.php" method="POST" id="formTransaksi">
                <div id="barang-container">
                    <div class="row g-3 barang-row align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Produk</label>
                            <select name="produk_id[]" class="form-select produk_id" onchange="updateHarga(this)" required>
                                <option value="">-- Pilih Produk --</option>
                                <?php while ($row = $produk_list->fetch_assoc()): ?>
                                    <option value="<?= $row['id'] ?>" 
                                            data-harga="<?= $row['harga_jual'] ?>" 
                                            data-stok="<?= $row['stock'] ?>">
                                        <?= $row['nama'] ?> (Stok: <?= $row['stock'] ?>) - 
                                        Rp <?= number_format($row['harga_jual'],0,',','.') ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah[]" class="form-control jumlah" placeholder="Jumlah beli" required min="1" oninput="updateTotal()">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger w-100" onclick="hapusBaris(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-outline-primary" onclick="tambahBaris()">
                        <i class="bi bi-plus-circle"></i> Tambah Barang
                    </button>
                </div>
                <div class="mt-3 fw-bold fs-5 text-end" id="total_preview">Total: Rp 0</div>
                <div class="mt-3 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Proses Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Riwayat Transaksi -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-primary text-white">
            <strong><i class="bi bi-clock-history"></i> Riwayat Transaksi</strong>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Barang</th>
                        <th>User</th>
                        <th>Total</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($trx = $riwayat->fetch_assoc()): ?>
                    <tr>
                        <td><?= $trx['id'] ?></td>
                        <td><?= htmlspecialchars($trx['items']) ?></td>
                        <td><?= htmlspecialchars($trx['user']) ?></td>
                        <td>Rp <?= number_format($trx['total'],0,',','.') ?></td>
                        <td><?= $trx['tanggal'] ?></td>
                        <td>
                            <a href="cetak_struk.php?id=<?= $trx['id'] ?>" target="_blank" class="btn btn-sm btn-info">
                                <i class="bi bi-printer"></i> Cetak Struk
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function tambahBaris() {
    let container = document.getElementById('barang-container');
    let baris = container.querySelector('.barang-row');
    let clone = baris.cloneNode(true);
    clone.querySelector('.produk_id').selectedIndex = 0;
    clone.querySelector('.jumlah').value = '';
    container.appendChild(clone);
}
function hapusBaris(btn) {
    let container = document.getElementById('barang-container');
    if (container.querySelectorAll('.barang-row').length > 1) {
        btn.closest('.barang-row').remove();
        updateTotal();
    }
}
function updateHarga(select) {
    let stok = select.options[select.selectedIndex]?.getAttribute('data-stok') || '';
    let jumlahInput = select.closest('.barang-row').querySelector('.jumlah');
    if (stok) jumlahInput.setAttribute('max', stok);
    else jumlahInput.removeAttribute('max');
    updateTotal();
}
function updateTotal() {
    let total = 0;
    document.querySelectorAll('.barang-row').forEach(row => {
        let select = row.querySelector('.produk_id');
        let jumlah = row.querySelector('.jumlah').value;
        let harga = select.options[select.selectedIndex]?.getAttribute('data-harga') || 0;
        if (jumlah && harga) total += (parseInt(jumlah) * parseInt(harga));
    });
    document.getElementById('total_preview').innerText = 'Total: Rp ' + total.toLocaleString('id-ID');
}
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.produk_id').forEach(select => updateHarga(select));
});
</script>

</body>
</html>
