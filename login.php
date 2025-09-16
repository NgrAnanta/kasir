<?php
include 'service/database.php';
session_start();

$loggin_message = "";

if (isset($_SESSION["is_login"])) {
    header("Location: dashboard.php");
    exit;
}

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $db->query($sql);

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        if (password_verify($password, $data['password'])) {
            $_SESSION["username"] = $data['username'];
            $_SESSION["is_login"] = true;
            header("Location: dashboard.php");
            exit;
        } else {
            $loggin_message = "❌ Password salah!";
        }
    } else {
        $loggin_message = "❌ Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Bebex Shop</title>
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

.card-login {
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(5px);
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 400px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card-login:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.3);
}
.btn-login {
    transition: all 0.3s ease;
}
.btn-login:hover {
    transform: scale(1.05);
    background-color: #198754;
}
.alert-custom {
    background-color: #f8d7da;
    color: #842029;
    border: none;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
}
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
    <div class="card-login text-center">
        <h4 class="mb-4">Login Akun</h4>
        <?php if($loggin_message): ?>
            <div class="alert alert-custom"><?= $loggin_message ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="mb-3 text-start">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="mb-3 text-start">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-success w-100 btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </button>
        </form>
        <div class="mt-3">
            Belum punya akun? <a href="register.php">Daftar</a>
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
