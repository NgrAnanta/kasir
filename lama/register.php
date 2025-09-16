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
    
    // hash password
    $hash_password = password_hash($password, PASSWORD_DEFAULT);

    // cek dulu apakah username sudah ada
    $cek = $db->query("SELECT * FROM users WHERE username='$username'");

    if ($cek->num_rows > 0) {
        $register_message = "❌ Username sudah digunakan!";
    } else {
        // lakukan insert dengan hash password
        if ($db->query("INSERT INTO users (username, password) VALUES ('$username', '$hash_password')")) {
            $register_message = "✅ Berhasil mendaftar, silakan login!";
        } else {
            // tampilkan error jika gagal insert
            $register_message = "❌ Gagal mendaftar: " . $db->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran</title>
</head>
<body>
    <?php include 'layout/header.html'; ?>

    <h3>PENDAFTARAN AKUN</h3>
    <?php if ($register_message != ""): ?>
        <i><?= $register_message ?></i>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <input type="text" placeholder="username" name="username" required />
        <input type="password" placeholder="password" name="password" required />
        <button type="submit" name="register">Daftar sekarang</button>
    </form>
</body>
</html>
