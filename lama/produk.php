<?php
include 'service/database.php';
session_start();

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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

// ================== AMBIL DATA PRODUK ==================
$result = $db->query("SELECT * FROM produk ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Produk</title>
</head>
<body>
    <h3>Form Produk</h3>
    <?php if ($message): ?>
        <p><i><?= $message ?></i></p>
    <?php endif; ?>

    <form action="produk.php" method="POST">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        <?php endif; ?>

        <input type="text" name="nama" placeholder="Nama Produk" 
               value="<?= $edit_data ? $edit_data['nama'] : '' ?>" required>
        <input type="number" name="stock" placeholder="Stok" 
               value="<?= $edit_data ? $edit_data['stock'] : '' ?>" required>
        <input type="number" step="0.01" name="harga_beli" placeholder="Harga Beli" 
               value="<?= $edit_data ? $edit_data['harga_beli'] : '' ?>" required>
        <input type="number" step="0.01" name="harga_jual" placeholder="Harga Jual" 
               value="<?= $edit_data ? $edit_data['harga_jual'] : '' ?>" required>
        <button type="submit"><?= $edit_data ? "Update" : "Simpan" ?></button>
    </form>

    <h3>Daftar Produk</h3>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Stock</th>
            <th>Harga Beli</th>
            <th>Harga Jual</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['nama'] ?></td>
            <td><?= $row['stock'] ?></td>
            <td>Rp <?= number_format($row['harga_beli'], 0, ',', '.') ?></td>
            <td>Rp <?= number_format($row['harga_jual'], 0, ',', '.') ?></td>
            <td>
                <a href="produk.php?edit=<?= $row['id'] ?>">✏️ Edit</a> | 
                <a href="produk.php?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus produk ini?')">🗑️ Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
