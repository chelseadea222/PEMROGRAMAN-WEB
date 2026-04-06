<?php
session_start();
include "config.php";

if (!isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $tanggal = $_POST['tanggal'];
    $jumlah = $_POST['jumlah'];
    $status = $_POST['status'];

    mysqli_query($conn, "INSERT INTO tiket (nama, tanggal, jumlah, status) 
                         VALUES ('$nama','$tanggal','$jumlah','$status')");
}
?>

<h2>Halo, <?php echo $_SESSION['nama']; ?></h2>

<form method="POST">
    <input type="text" name="nama" placeholder="Nama"><br>
    <input type="date" name="tanggal"><br>
    <input type="number" name="jumlah" placeholder="Jumlah Tiket"><br>
    <input type="text" name="status" placeholder="Status"><br>
    <button name="tambah">Tambah</button>
</form>

<h3>Data Tiket</h3>
<table border="1">
<tr>
    <th>Nama</th>
    <th>Tanggal</th>
    <th>Jumlah</th>
    <th>Status</th>
</tr>

<?php
$data = mysqli_query($conn, "SELECT * FROM tiket");
while ($row = mysqli_fetch_assoc($data)) {
    echo "<tr>
            <td>{$row['nama']}</td>
            <td>{$row['tanggal']}</td>
            <td>{$row['jumlah']}</td>
            <td>{$row['status']}</td>
          </tr>";
}
?>
</table>

<a href="logout.php">Logout</a>