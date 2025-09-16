<?php
  session_start();
  if(isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('location: index.php');
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>

    <h3>Selamat datang di Dashboard <?= $_SESSION['username'] ?> </h3>
    <a href="produk.php">
    <button type="button">produk</button>
    </a>

    <a href="laporan.php">
    <button type="button">laporan</button>
    </a>

    <a href="transaksi.php">
    <button type="button">transaksi</button>
    </a>

    <form action="dashboard.php" method="POST">
    <button type="submit" name="logout">Logout</button>
    </form>
</body>
</html>
