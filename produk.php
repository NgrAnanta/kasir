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

// ================== HAPUS PRODUK ==================
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $db->query("DELETE FROM produk WHERE id=$id");
    $message = "🗑️ Produk berhasil dihapus!";
}

// ================== EDIT PRODUK ==================
$edit_id = 0;
$edit_data = null;

if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $result_edit = $db->query("SELECT * FROM produk WHERE id=$edit_id");
    if ($result_edit->num_rows > 0) {
        $edit_data = $result_edit->fetch_assoc();
    }
}

// ================== SIMPAN PRODUK (INSERT / UPDATE) ==================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nama'])) {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $nama = mysqli_real_escape_string($db, $_POST['nama']);
    $stock = (int) $_POST['stock'];
    $harga_beli = (float) $_POST['harga_beli'];
    $harga_jual = (float) $_POST['harga_jual'];

    if ($id > 0) {
        // UPDATE produk
        $db->query("UPDATE produk 
                    SET nama='$nama', stock=$stock, harga_beli=$harga_beli, harga_jual=$harga_jual
                    WHERE id=$id");
        $message = "✏️ Produk berhasil diperbarui!";
    } else {
        // INSERT produk (hindari duplikat nama)
        $cek = $db->query("SELECT * FROM produk WHERE nama='$nama'");
        if ($cek->num_rows > 0) {
            $db->query("UPDATE produk 
                        SET stock = stock + $stock,
                            harga_beli = $harga_beli,
                            harga_jual = $harga_jual
                        WHERE nama='$nama'");
            $message = "✅ Stok produk '$nama' berhasil ditambahkan!";
        } else {
            $db->query("INSERT INTO produk (nama, stock, harga_beli, harga_jual) 
                        VALUES ('$nama', $stock, $harga_beli, $harga_jual)");
            $message = "✅ Produk baru '$nama' berhasil ditambahkan!";
        }
    }
}

// ================== PENCARIAN & FILTER ==================
$search = isset($_GET['search']) ? mysqli_real_escape_string($db, $_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$query = "SELECT * FROM produk WHERE 1=1";

// cari berdasarkan nama
if ($search != '') {
    $query .= " AND nama LIKE '%$search%'";
}

// filter stok
if ($filter == 'habis') {
    $query .= " AND stock = 0";
} elseif ($filter == 'tersedia') {
    $query .= " AND stock > 0";
}

$query .= " ORDER BY id DESC";
$result = $db->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-box-seam text-primary"></i> Manajemen Produk</h3>
        <a href="dashboard.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Pesan -->
    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <!-- Form Produk -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <strong><?= $edit_data ? "Edit Produk" : "Tambah Produk" ?></strong>
        </div>
        <div class="card-body">
            <form action="produk.php" method="POST" class="row g-3">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
                <?php endif; ?>

                <div class="col-md-4">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" name="nama" class="form-control" 
                           value="<?= $edit_data ? $edit_data['nama'] : '' ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Stok</label>
                    <input type="number" name="stock" class="form-control" 
                           value="<?= $edit_data ? $edit_data['stock'] : '' ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Harga Beli</label>
                    <input type="number" step="0.01" name="harga_beli" class="form-control" 
                           value="<?= $edit_data ? $edit_data['harga_beli'] : '' ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Harga Jual</label>
                    <input type="number" step="0.01" name="harga_jual" class="form-control" 
                           value="<?= $edit_data ? $edit_data['harga_jual'] : '' ?>" required>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> <?= $edit_data ? "Update" : "Simpan" ?>
                    </button>
                    <?php if ($edit_data): ?>
                        <a href="produk.php" class="btn btn-warning">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Cari nama produk..." value="<?= $search ?>">
                </div>
                <div class="col-md-3">
                    <select name="filter" class="form-select">
                        <option value="">-- Semua Stok --</option>
                        <option value="tersedia" <?= $filter == 'tersedia' ? 'selected' : '' ?>>Stok Tersedia</option>
                        <option value="habis" <?= $filter == 'habis' ? 'selected' : '' ?>>Stok Habis</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Cari
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="produk.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Daftar Produk -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <strong>Daftar Produk</strong>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Stok</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['nama'] ?></td>
                            <td>
                                <?= $row['stock'] ?> 
                                <?php if ($row['stock'] == 0): ?>
                                    <span class="badge bg-danger">Habis</span>
                                <?php elseif ($row['stock'] < 5): ?>
                                    <span class="badge bg-warning text-dark">Hampir Habis</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Tersedia</span>
                                <?php endif; ?>
                            </td>
                            <td>Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
                            <td>
                                <a href="produk.php?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="produk.php?hapus=<?= $row['id'] ?>" 
                                   onclick="return confirm('Yakin hapus produk ini?')" 
                                   class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">⚠️ Tidak ada produk ditemukan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
