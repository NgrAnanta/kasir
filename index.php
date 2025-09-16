<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bebex Shop</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
/* Body & Layout */
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background: linear-gradient(135deg, #89f7fe, #66a6ff);
    font-family: 'Segoe UI', sans-serif;
}

/* Main Card */
.main-card {
    background: linear-gradient(145deg, #ffffff, #e0f7ff);
    padding: 3rem 2rem;
    border-radius: 1rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    color: #000; /* teks hitam jelas */
    position: relative;
}
.main-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.25);
}

/* Decorative Icon */
.main-card .icon-deco {
    font-size: 4rem;
    color: #0d6efd;
    margin-bottom: 1rem;
    transition: transform 0.3s ease;
}
.main-card:hover .icon-deco {
    transform: scale(1.2) rotate(10deg);
}

/* Navbar */
header a:hover {
    text-decoration: underline;
}

/* Footer */
footer hr {
    border-color: rgba(255, 255, 255, 0.2);
}
</style>
</head>
<body>

<!-- HEADER -->
<header class="bg-primary text-white py-3 shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <h3 class="m-0 fw-bold">Bebex Shop</h3>
        <nav>
            <a href="index.php" class="text-white me-3">Home</a>
            <a href="login.php" class="text-white me-3">Login <i class="bi bi-box-arrow-in-right"></i></a>
            <a href="register.php" class="text-white">Register <i class="bi bi-person-plus"></i></a>
        </nav>
    </div>
</header>

<!-- MAIN CONTENT -->
<main class="container my-5 d-flex justify-content-center">
    <div class="main-card text-center w-100" style="max-width: 600px;">
        <i class="bi bi-shop-window icon-deco"></i>
        <h1 class="mb-3">Halo, Selamat Datang di Bebex Shop!</h1>
        <p class="lead mb-0">Silakan cek barang yang tersedia dan nikmati belanja mudah!</p>
    </div>
</main>

<!-- FOOTER -->
<footer class="bg-dark text-white py-3 mt-auto">
    <div class="container text-center">
        <hr class="border-light">
        <i>Website ini dibuat oleh &copy; 2025 Ngurah Ananta</i>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
