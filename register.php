<?php
include 'service/database.php';
session_start();

$register_message = "";

if(isset($_SESSION["is_login"])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['register'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);
    
    $hash_password = password_hash($password, PASSWORD_DEFAULT);

    $cek = $db->query("SELECT * FROM users WHERE username='$username'");

    if ($cek->num_rows > 0) {
        $register_message = "❌ Username sudah digunakan!";
    } else {
        if ($db->query("INSERT INTO users (username, password) VALUES ('$username', '$hash_password')")) {
            $register_message = "✅ Berhasil mendaftar, silakan login!";
        } else {
            $register_message = "❌ Gagal mendaftar: " . $db->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Bebex Shop</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
<style>
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background: linear-gradient(135deg, #89f7fe, #66a6ff);
    font-family: 'Segoe UI', sans-serif;
}
main { flex: 1; display: flex; justify-content: center; align-items: center; }

.card-register {
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(5px);
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 400px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card-register:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.3);
}
.btn-register {
    transition: all 0.3s ease;
}
.btn-register:hover {
    transform: scale(1.05);
    background-color: #198754;
}
.alert-custom {
    border: none;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
}
.alert-success { background-color: #d1e7dd; color: #0f5132; }
.alert-danger { background-color: #f8d7da; color: #842029; }

header a:hover {
    text-decoration: underline;
}
footer hr {
    border-color: rgba(255, 255, 255, 0.2);
}
</style>
</head>
<body>

<!-- Header -->
<header class="bg-primary text-white py-3 shadow-sm">
    <div class="container d-flex justify-content-between align-items-center">
        <h3 class="m-0 fw-bold">Bebex Shop</h3>
        <nav>
            <a href="index.php" class="text-white me-3">Home</a>
            <a href="login.php" class="text-white me-3">Login</a>
            <a href="register.php" class="text-white">Register</a>
        </nav>
    </div>
</header>

<!-- Main -->
<main>
    <div class="card-register text-center">
        <h4 class="mb-4">Pendaftaran Akun</h4>
        <?php if($register_message): ?>
            <div class="alert <?= strpos($register_message, '✅') !== false ? 'alert-success alert-custom' : 'alert-danger alert-custom' ?>">
                <?= $register_message ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="register.php">
            <div class="mb-3 text-start">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3 text-start">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="register" class="btn btn-success w-100 btn-register">
                <i class="bi bi-person-plus"></i> Daftar Sekarang
            </button>
        </form>
        <div class="mt-3">
            Sudah punya akun? <a href="login.php">Login</a>
        </div>
    </div>
</main>

<!-- Footer -->
<footer class="bg-dark text-white py-3 mt-auto">
    <div class="container text-center">
        <i>Website ini dibuat oleh &copy; 2025 Ngurah Ananta</i>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
