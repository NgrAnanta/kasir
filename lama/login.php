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

    // Ambil user berdasarkan username saja
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $db->query($sql);

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();

        // Verifikasi password dengan password_verify
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <?php include 'layout/header.html' ?>
    <h3>LOGIN AKUN</h3>
    <i><?= $loggin_message ?></i>
    <form action="login.php" method="POST">
        <input type="text" placeholder="username" name="username" required />
        <input type="password" placeholder="password" name="password" required />
        <button type="submit" name="login">Login</button>
    </form>
</body>
</html>
