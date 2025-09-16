<?php
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Icon -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
body {
  background: #f0f4f8;
  font-family: "Segoe UI", sans-serif;
}

/* Navbar */
.navbar {
  backdrop-filter: blur(8px);
  background: rgba(13, 110, 253, 0.9) !important;
}

/* Hero Section */
.hero {
  background: linear-gradient(135deg, #0d6efd, #6610f2);
  color: white;
  border-radius: 1rem;
  padding: 2.5rem 1rem;
  margin-bottom: 3rem;
  box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

/* Card Menu */
.shadow-hover {
  border: none;
  transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
  background: linear-gradient(135deg, #ffffff, #f9f9f9);
  border-radius: 1rem;
}
.shadow-hover:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 25px rgba(0,0,0,0.18);
  background: linear-gradient(135deg, #eef7ff, #ffffff);
}
.shadow-hover .animate-icon {
  transition: transform 0.3s ease;
}
.shadow-hover:hover .animate-icon {
  transform: scale(1.2) rotate(8deg);
}

/* Icon Pulse */
.animate-icon {
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.1); opacity: 0.85; }
  100% { transform: scale(1); opacity: 1; }
}

/* Badge */
.badge {
  font-size: 0.75rem;
  padding: 0.4em 0.6em;
  border-radius: 0.5rem;
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#"><i class="bi bi-shop-window"></i> Bebex Shop</a>
    <div class="d-flex">
      <form action="dashboard.php" method="POST">
        <button type="submit" name="logout" class="btn btn-outline-light btn-sm rounded-pill">
          <i class="bi bi-box-arrow-right"></i> Logout
        </button>
      </form>
    </div>
  </div>
</nav>

<!-- Content -->
<div class="container" style="padding-top: 5rem;">
  
  <!-- Hero -->
  <div class="hero text-center">
    <h2 class="fw-bold">Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?> 👋</h2>
    <p class="mb-0">Kelola produk, transaksi, dan laporan penjualanmu dengan mudah.</p>
  </div>

  <!-- Menu -->
  <div class="row g-4 justify-content-center">
    <!-- Produk -->
    <div class="col-md-4">
      <a href="produk.php" class="text-decoration-none">
        <div class="card h-100 text-center p-4 shadow-hover position-relative overflow-hidden">
          <span class="badge bg-primary position-absolute top-0 end-0 m-3">NEW</span>
          <i class="bi bi-box-seam display-4 text-primary mb-3 animate-icon"></i>
          <h5 class="fw-bold text-dark">Produk</h5>
          <p class="text-muted">Kelola data produk dan stok barang</p>
        </div>
      </a>
    </div>

    <!-- Transaksi -->
    <div class="col-md-4">
      <a href="transaksi.php" class="text-decoration-none">
        <div class="card h-100 text-center p-4 shadow-hover position-relative overflow-hidden">
          <i class="bi bi-cart-check display-4 text-success mb-3 animate-icon"></i>
          <h5 class="fw-bold text-dark">Transaksi</h5>
          <p class="text-muted">Catat penjualan dan transaksi kasir</p>
        </div>
      </a>
    </div>

    <!-- Laporan -->
    <div class="col-md-4">
      <a href="laporan.php" class="text-decoration-none">
        <div class="card h-100 text-center p-4 shadow-hover position-relative overflow-hidden">
          <i class="bi bi-graph-up display-4 text-danger mb-3 animate-icon"></i>
          <h5 class="fw-bold text-dark">Laporan</h5>
          <p class="text-muted">Lihat laporan penjualan per periode</p>
        </div>
      </a>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
