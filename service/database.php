<?php
// Konfigurasi database
$hostname = "localhost";
$username = "root";
$password = "";
$database_name = "latphp";

// Koneksi ke database
$db = new mysqli($hostname, $username, $password, $database_name);

// Cek koneksi
if ($db->connect_error) {
    // Bisa diganti dengan logging error, jangan langsung tampilkan di web live
    die("Koneksi database gagal: " . $db->connect_error);
}
?>
