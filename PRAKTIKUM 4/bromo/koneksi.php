<?php
// Simpan file ini di: D:\XAMPP\htdocs\Pemweb\bromo\config\koneksi.php

$host     = 'localhost';
$db_name  = 'bromo_tracking';
$username = 'root';
$password = '';  // XAMPP default kosong. Kalau kamu set password MySQL, isi di sini.

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE,       PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<b style='color:red'>Koneksi database gagal:</b> " . $e->getMessage());
}
?>