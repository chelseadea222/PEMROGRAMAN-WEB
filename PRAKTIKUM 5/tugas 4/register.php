<?php
include "config.php";

if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO users (nama, email, password) 
              VALUES ('$nama','$email','$password')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Registrasi berhasil!'); window.location='login.php';</script>";
    } else {
        echo "Gagal: " . mysqli_error($conn);
    }
}
?>

<form method="POST">
    <h2>Register</h2>
    <input type="text" name="nama" placeholder="Nama" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button name="register">Daftar</button>
</form>