<?php
session_start();
require_once 'koneksi.php';

// Ambil semua data tiket pakai PDO
$stmt = $pdo->query("SELECT * FROM tiket_harian ORDER BY tanggal DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung statistik
$totalPengunjung = count($data);
$pengunjungHariIni = 0;
$totalTiket = 0;
$totalPembayaran = 0;

// Asumsi harga tiket Rp 60.000 per tiket
$hargaTiket = 60000;
$today = date("Y-m-d");

foreach ($data as $item) {
    $totalTiket += $item['jumlah'];
    $totalPembayaran += $item['jumlah'] * $hargaTiket;
    if ($item['tanggal'] === $today) {
        $pengunjungHariIni++;
    }
}

// Data untuk grafik bulanan
$monthlyStats = [];
foreach ($data as $item) {
    $bulan = substr($item['tanggal'], 0, 7); // YYYY-MM
    if (!isset($monthlyStats[$bulan])) {
        $monthlyStats[$bulan] = 0;
    }
    $monthlyStats[$bulan] += $item['jumlah'];
}


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin Tracking Pengunjung Bromo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Lato:wght@300;400;700&display=swap');

    :root {
    --orange:#E8621A;
    --gold:#D4A017;
    --red:#ff6b78;
    --gray:#ccc;
    }

    body {
    font-family:'Lato',sans-serif;
    background:url('https://i.pinimg.com/736x/d5/fa/66/d5fa660d2e02cb8b5f2c3e1489919426.jpg') center/cover fixed;
    color:#fff;
    min-height:100vh;
    }
    body::before {
    content:""; position:fixed; inset:0;
    background:rgba(10,8,5,.82); backdrop-filter:blur(4px);
    z-index:-1;
    }

    /* Navbar */
    .navbar {
    background:rgba(26,18,9,.9)!important;
    border-bottom:1px solid rgba(232,98,26,.3);
    }
    .navbar-brand {
    font-family:'Cinzel',serif;
    font-weight:700;
    color:#fff;
    }
    .navbar-brand span { color:var(--orange); }

    /* Card statistik */
    .card {
    background:rgba(255,255,255,.05)!important;
    border:1px solid rgba(255,255,255,.1)!important;
    border-radius:15px!important;
    color:#fff;
    }
    .card h6 { color:rgba(255,255,255,.6); font-size:.85rem; }
    .card h4 { font-family:'Cinzel',serif; color:var(--gold); }

    /* Tabel */
    .table thead th {
    background:rgba(26,18,9,.6);
    color:rgba(255,255,255,.7);
    text-transform:uppercase;
    font-size:.8rem;
    }
    .table tbody td {
    background:transparent;
    color:rgba(255,255,255,.85);
    border-bottom:1px solid rgba(255,255,255,.1);
    padding:12px;
    }

    /* Badge status */
    .badge-lunas {
    background:rgba(25,135,84,.15);
    color:#6fff9e;
    border-radius:20px;
    padding:4px 10px;
    font-size:.75rem;
    }
    .badge-pending {
    background:rgba(255,193,7,.1);
    color:var(--gold);
    border-radius:20px;
    padding:4px 10px;
    font-size:.75rem;
    }

/* Tombol */
    .btn-glass {
    background:rgba(255,255,255,.05);
    border:1px solid rgba(255,255,255,.15);
    color:#fff;
    padding:8px 16px;
    border-radius:8px;
    font-weight:600;
    font-size:.85rem;
    transition:.3s;
    display:inline-flex; align-items:center; gap:6px;
    text-decoration:none;
    }
.btn-glass:hover { transform:translateY(-2px); }

/* Variasi tombol */
.btn-csv { color:var(--gold); }
.btn-json { color:var(--orange); }
.btn-dashboard { color:var(--gray); }
.btn-tiket { color:var(--red); }

    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">Bromo Dashboard Admin</a>
            <div class="d-flex align-items-center ms-auto">
                <a href="login.php" class="btn btn-light me-2">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </a>
                <a href="register.php" class="btn btn-warning">
                    <i class="bi bi-person-plus"></i> Daftar
                </a>
            </div>
        </div>
    </nav>

    <!-- Statistik Ringkas -->
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center shadow">
                    <div class="card-body">
                        <i class="bi bi-people fs-2 text-primary"></i>
                        <h6>Total Pengunjung</h6>
                        <h4><?= $totalPengunjung ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow">
                    <div class="card-body">
                        <i class="bi bi-person-check fs-2 text-success"></i>
                        <h6>Pengunjung Hari Ini</h6>
                        <h4><?= $pengunjungHariIni ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow">
                    <div class="card-body">
                        <i class="bi bi-cash-stack fs-2 text-warning"></i>
                        <h6>Total Pembayaran</h6>
                        <h4>Rp <?= number_format($totalPembayaran, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center shadow">
                    <div class="card-body">
                        <i class="bi bi-ticket-perforated fs-2 text-danger"></i>
                        <h6>Tiket Terjual</h6>
                        <h4><?= $totalTiket ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data Pengunjung -->
        <div class="card mb-4 shadow">
            <div class="card-body">
                <h5 class="text-success">Data Pengunjung Terbaru</h5>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Nama</th>
                            <th>Tanggal Kunjungan</th>
                            <th>Tiket</th>
                            <th>Status Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($data, 0, 5) as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nama']) ?></td>
                                <td><?= htmlspecialchars($item['tanggal']) ?></td>
                                <td><?= htmlspecialchars($item['jumlah']) ?></td>
                                <td><?= htmlspecialchars($item['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Grafik Pengunjung Bulanan -->
        <div class="card mb-4 shadow">
            <div class="card-body">
                <h5 class="text-primary">Grafik Pengunjung Bulanan</h5>
                <canvas id="visitorChart"></canvas>
            </div>
        </div>
    </div>

    <script>
    const ctx = document.getElementById('visitorChart').getContext('2d');
    const visitorChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_keys($monthlyStats)) ?>, // bulan
            datasets: [{
                label: 'Jumlah Pengunjung',
                data: <?= json_encode(array_values($monthlyStats)) ?>, // jumlah pengunjung per bulan
                borderColor: 'var(--orange)',        // garis oranye
                backgroundColor: 'rgba(232,98,26,.2)', // fill transparan oranye
                pointBackgroundColor: 'var(--gold)', // titik data warna gold
                pointBorderColor: '#fff',
                pointRadius: 5,
                fill: true,
                tension: 0.4 // bikin garis agak melengkung naik turun
            }]
        },
        options: {
            plugins: {
                legend: {
                    labels: { color: '#fff' } // teks legend putih
                }
            },
            scales: {
                x: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,.1)' }
                },
                y: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,.1)' },
                    beginAtZero: true
                }
            }
        }
    });
</script>


</body>
</html>