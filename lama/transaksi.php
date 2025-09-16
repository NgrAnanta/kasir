<?php
include 'service/database.php';
session_start();

$message = "";

// Ambil pesan dari session (jika ada)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// ================== PROSES TRANSAKSI ==================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $produk_ids = isset($_POST['produk_id']) ? $_POST['produk_id'] : [];
    $jumlahs = isset($_POST['jumlah']) ? $_POST['jumlah'] : [];

    if (!is_array($produk_ids)) $produk_ids = [$produk_ids];
    if (!is_array($jumlahs)) $jumlahs = [$jumlahs];

    $grand_total = 0;
    $success = true;
    $error_msg = '';

    // Ambil user yang sedang login
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'guest';

    for ($i = 0; $i < count($produk_ids); $i++) {
        $produk_id = (int)$produk_ids[$i];
        $jumlah = (int)$jumlahs[$i];
        if ($produk_id < 1 || $jumlah < 1) continue;

        $produk = $db->query("SELECT * FROM produk WHERE id=$produk_id")->fetch_assoc();
        if ($produk) {
            if ($produk['stock'] >= $jumlah) {
                $total = $produk['harga_jual'] * $jumlah;
                $grand_total += $total;
                // Kurangi stok
                $db->query("UPDATE produk SET stock = stock - $jumlah WHERE id=$produk_id");
                // Simpan ke tabel transaksi, tambahkan kolom user
                $db->query("INSERT INTO transaksi (produk_id, jumlah, total, user) 
                            VALUES ($produk_id, $jumlah, $total, '" . $db->real_escape_string($username) . "')");
            } else {
                $success = false;
                $error_msg .= "Stok produk '" . htmlspecialchars($produk['nama']) . "' tidak mencukupi!<br>";
            }
        } else {
            $success = false;
            $error_msg .= "Produk dengan ID $produk_id tidak ditemukan!<br>";
        }
    }

    // Simpan pesan ke session lalu redirect (PRG pattern)
    if ($success) {
        $_SESSION['message'] = "✅ Transaksi berhasil! Total: Rp " . number_format($grand_total, 0, ',', '.');
    } else {
        $_SESSION['message'] = "❌ " . $error_msg;
    }
    header("Location: transaksi.php");
    exit;
}

// ================== AMBIL DATA ==================
$produk_list = $db->query("SELECT * FROM produk ORDER BY nama ASC");
$riwayat = $db->query("SELECT t.*, p.nama 
                       FROM transaksi t 
                       JOIN produk p ON t.produk_id = p.id 
                       ORDER BY t.id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi</title>
</head>
<body>
    <h3>Form Transaksi</h3>
    <?php if ($message): ?>
        <p><i><?= $message ?></i></p>
    <?php endif; ?>

    <form action="transaksi.php" method="POST" id="formTransaksi">
        <div id="barang-container">
            <div class="barang-row">
                <label>Pilih Produk:</label>
                <select name="produk_id[]" class="produk_id" onchange="updateHarga(this)" required>
                    <option value="">-- Pilih --</option>
                    <?php 
                    $produk_list2 = $db->query("SELECT * FROM produk");
                    while ($row = $produk_list2->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" 
                                data-harga="<?= $row['harga_jual'] ?>" 
                                data-stok="<?= $row['stock'] ?>">
                            <?= $row['nama'] ?> (Stok: <?= $row['stock'] ?>) - 
                            Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="number" name="jumlah[]" class="jumlah" placeholder="Jumlah beli" required min="1" oninput="updateTotal()" max="">
                <button type="button" onclick="hapusBaris(this)">Hapus</button>
            </div>
        </div>
        <button type="button" onclick="tambahBaris()">Tambah Barang</button>
        <p id="total_preview">Total: Rp 0</p>
        <button type="submit">Beli</button>
    </form>

    <h3>Riwayat Transaksi</h3>
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

<script>
function tambahBaris() {
    var container = document.getElementById('barang-container');
    var baris = container.querySelector('.barang-row');
    var clone = baris.cloneNode(true);
    clone.querySelector('.produk_id').selectedIndex = 0;
    clone.querySelector('.jumlah').value = '';
    clone.querySelector('.jumlah').removeAttribute('max');
    container.appendChild(clone);
}
function hapusBaris(btn) {
    var container = document.getElementById('barang-container');
    if (container.querySelectorAll('.barang-row').length > 1) {
        btn.parentNode.remove();
        updateTotal();
    }
}
function updateHarga(select) {
    var stok = select.options[select.selectedIndex]?.getAttribute('data-stok') || '';
    var jumlahInput = select.closest('.barang-row').querySelector('.jumlah');
    if (stok) {
        jumlahInput.setAttribute('max', stok);
    } else {
        jumlahInput.removeAttribute('max');
    }
    updateTotal();
}
function updateTotal() {
    var total = 0;
    var rows = document.querySelectorAll('.barang-row');
    rows.forEach(function(row) {
        var select = row.querySelector('.produk_id');
        var jumlah = row.querySelector('.jumlah').value;
        var harga = select.options[select.selectedIndex]?.getAttribute('data-harga') || 0;
        if (jumlah && harga) {
            total += (parseInt(jumlah) * parseInt(harga));
        }
    });
    document.getElementById('total_preview').innerText = 'Total: Rp ' + total.toLocaleString('id-ID');
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.produk_id').forEach(function(select) {
        updateHarga(select);
    });
});
</script>
</body>
</html>














transaksi.ptransaksi.php

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

$message = "";

// Ambil pesan dari session (jika ada)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// ================== PROSES TRANSAKSI ==================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $produk_ids = isset($_POST['produk_id']) ? $_POST['produk_id'] : [];
    $jumlahs = isset($_POST['jumlah']) ? $_POST['jumlah'] : [];

    if (!is_array($produk_ids)) $produk_ids = [$produk_ids];
    if (!is_array($jumlahs)) $jumlahs = [$jumlahs];

    $grand_total = 0;
    $success = true;
    $error_msg = '';

    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'guest';

    for ($i = 0; $i < count($produk_ids); $i++) {
        $produk_id = (int)$produk_ids[$i];
        $jumlah = (int)$jumlahs[$i];
        if ($produk_id < 1 || $jumlah < 1) continue;

        $produk = $db->query("SELECT * FROM produk WHERE id=$produk_id")->fetch_assoc();
        if ($produk) {
            if ($produk['stock'] >= $jumlah) {
                $total = $produk['harga_jual'] * $jumlah;
                $grand_total += $total;
                $db->query("UPDATE produk SET stock = stock - $jumlah WHERE id=$produk_id");
                $db->query("INSERT INTO transaksi (produk_id, jumlah, total, user) 
                            VALUES ($produk_id, $jumlah, $total, '" . $db->real_escape_string($username) . "')");
            } else {
                $success = false;
                $error_msg .= "Stok produk '" . htmlspecialchars($produk['nama']) . "' tidak mencukupi!<br>";
            }
        } else {
            $success = false;
            $error_msg .= "Produk dengan ID $produk_id tidak ditemukan!<br>";
        }
    }

    if ($success) {
        $_SESSION['message'] = "✅ Transaksi berhasil! Total: Rp " . number_format($grand_total, 0, ',', '.');
    } else {
        $_SESSION['message'] = "❌ " . $error_msg;
    }
    header("Location: transaksi.php");
    exit;
}

// ================== AMBIL DATA ==================
$produk_list = $db->query("SELECT * FROM produk ORDER BY nama ASC");
$riwayat = $db->query("SELECT t.*, p.nama 
                       FROM transaksi t 
                       JOIN produk p ON t.produk_id = p.id 
                       ORDER BY t.id DESC");
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
        <a href="dashboard.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Pesan -->
    <?php if ($message): ?>
        <div class="alert <?= strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger' ?>">
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
                                <?php 
                                $produk_list2 = $db->query("SELECT * FROM produk");
                                while ($row = $produk_list2->fetch_assoc()): ?>
                                    <option value="<?= $row['id'] ?>" 
                                            data-harga="<?= $row['harga_jual'] ?>" 
                                            data-stok="<?= $row['stock'] ?>">
                                        <?= $row['nama'] ?> (Stok: <?= $row['stock'] ?>) - 
                                        Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?>
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
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>User</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
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
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Script -->
<script>
function tambahBaris() {
    var container = document.getElementById('barang-container');
    var baris = container.querySelector('.barang-row');
    var clone = baris.cloneNode(true);
    clone.querySelector('.produk_id').selectedIndex = 0;
    clone.querySelector('.jumlah').value = '';
    clone.querySelector('.jumlah').removeAttribute('max');
    container.appendChild(clone);
}
function hapusBaris(btn) {
    var container = document.getElementById('barang-container');
    if (container.querySelectorAll('.barang-row').length > 1) {
        btn.closest('.barang-row').remove();
        updateTotal();
    }
}
function updateHarga(select) {
    var stok = select.options[select.selectedIndex]?.getAttribute('data-stok') || '';
    var jumlahInput = select.closest('.barang-row').querySelector('.jumlah');
    if (stok) {
        jumlahInput.setAttribute('max', stok);
    } else {
        jumlahInput.removeAttribute('max');
    }
    updateTotal();
}
function updateTotal() {
    var total = 0;
    var rows = document.querySelectorAll('.barang-row');
    rows.forEach(function(row) {
        var select = row.querySelector('.produk_id');
        var jumlah = row.querySelector('.jumlah').value;
        var harga = select.options[select.selectedIndex]?.getAttribute('data-harga') || 0;
        if (jumlah && harga) {
            total += (parseInt(jumlah) * parseInt(harga));
        }
    });
    document.getElementById('total_preview').innerText = 'Total: Rp ' + total.toLocaleString('id-ID');
}
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.produk_id').forEach(function(select) {
        updateHarga(select);
    });
});
</script>

</body>
</html>
