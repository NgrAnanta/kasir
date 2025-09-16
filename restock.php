<?php
include 'service/database.php';
session_start();

// Ambil list supplier dan produk
$supplier_list = $db->query("SELECT * FROM supplier ORDER BY nama ASC");
$produk_list = $db->query("SELECT * FROM produk ORDER BY nama ASC");

$message = "";

// Proses pembelian
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $supplier_id = (int)$_POST['supplier_id'];
    $produk_ids = $_POST['produk_id'];
    $jumlahs = $_POST['jumlah'];
    $harga_belis = $_POST['harga_beli'];

    $grand_total = 0;

    foreach ($produk_ids as $i => $produk_id) {
        $jumlah = (int)$jumlahs[$i];
        $harga_beli = (float)$harga_belis[$i];
        $grand_total += $jumlah * $harga_beli;
    }

    $db->query("INSERT INTO pembelian (supplier_id, total) VALUES ($supplier_id, $grand_total)");
    $pembelian_id = $db->insert_id;

    foreach ($produk_ids as $i => $produk_id) {
        $jumlah = (int)$jumlahs[$i];
        $harga_beli = (float)$harga_belis[$i];
        $subtotal = $jumlah * $harga_beli;

        $db->query("INSERT INTO pembelian_detail 
            (pembelian_id, produk_id, jumlah, harga_beli, subtotal) 
            VALUES ($pembelian_id, $produk_id, $jumlah, $harga_beli, $subtotal)");

        $db->query("UPDATE produk SET stock = stock + $jumlah, harga_beli = $harga_beli WHERE id = $produk_id");
    }

    $message = "✅ Pembelian berhasil dicatat!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pembelian / Restock</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
body {
    background: #f0f2f5;
    font-family: 'Segoe UI', sans-serif;
}
.card-glass {
    backdrop-filter: blur(12px) saturate(180%);
    background-color: rgba(255, 255, 255, 0.6);
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    transition: transform 0.2s, box-shadow 0.2s;
}
.card-glass:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
}
</style>
</head>
<body>
<div class="container py-5">
    <h3 class="mb-4 text-center">Pembelian / Restock</h3>

    <?php if($message): ?>
        <div class="alert alert-success text-center"><?= $message ?></div>
    <?php endif; ?>

    <div class="card card-glass p-4">
        <form method="POST" id="formPembelian">
            <div class="mb-3">
                <label class="form-label fw-bold">Supplier</label>
                <select name="supplier_id" class="form-select" required>
                    <option value="">-- Pilih Supplier --</option>
                    <?php while($row = $supplier_list->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nama']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div id="produk-container">
                <div class="row g-3 align-items-end mb-3 produk-row">
                    <div class="col-md-5">
                        <label class="form-label">Produk</label>
                        <select name="produk_id[]" class="form-select" required>
                            <option value="">-- Pilih Produk --</option>
                            <?php while($p = $produk_list->fetch_assoc()): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="jumlah[]" class="form-control jumlah" required min="1" oninput="updateTotal()">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Harga Beli</label>
                        <input type="number" name="harga_beli[]" class="form-control harga_beli" required min="0" oninput="updateTotal()">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger w-100" onclick="hapusBaris(this)">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-3">
                <button type="button" class="btn btn-outline-primary" onclick="tambahBaris()">
                    <i class="bi bi-plus-circle"></i> Tambah Produk
                </button>
                <h5 class="text-success fw-bold" id="total_preview">Total: Rp 0</h5>
            </div>

            <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-circle"></i> Simpan Pembelian</button>
        </form>
    </div>
</div>

<script>
function tambahBaris() {
    var container = document.getElementById('produk-container');
    var baris = container.querySelector('.produk-row');
    var clone = baris.cloneNode(true);
    clone.querySelectorAll('select, input').forEach(el => el.value = '');
    container.appendChild(clone);
}

function hapusBaris(btn) {
    var container = document.getElementById('produk-container');
    if(container.querySelectorAll('.produk-row').length > 1) {
        btn.closest('.produk-row').remove();
        updateTotal();
    }
}

function updateTotal() {
    var total = 0;
    document.querySelectorAll('.produk-row').forEach(row => {
        var jumlah = parseInt(row.querySelector('.jumlah').value) || 0;
        var harga = parseFloat(row.querySelector('.harga_beli').value) || 0;
        total += jumlah * harga;
    });
    document.getElementById('total_preview').innerText = 'Total: Rp ' + total.toLocaleString('id-ID');
}

document.addEventListener('input', updateTotal);
</script>
</body>
</html>
