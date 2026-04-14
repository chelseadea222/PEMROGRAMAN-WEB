<?php
require_once 'koneksi.php';

// ambil semua data tiket
$stmt = $pdo->query("SELECT * FROM tiket_harian ORDER BY tanggal ASC");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// kelompokkan per bulan
$monthlyData = [];
foreach ($rows as $row) {
    $bulan = date('F Y', strtotime($row['tanggal']));
    $monthlyData[$bulan][] = $row;
}

// export JSON
if (isset($_GET['backup']) && $_GET['backup'] === 'json') {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="backup_tiket_perbulan.json"');
    echo json_encode($monthlyData, JSON_PRETTY_PRINT);
    exit;
}

// export CSV
if (isset($_GET['backup']) && $_GET['backup'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="backup_tiket_perbulan.csv"');
    $output = fopen("php://output", "w");
    fputcsv($output, ["Bulan","Nama","Tanggal","Tiket","Status"]);
    foreach ($monthlyData as $bulan => $items) {
        foreach ($items as $item) {
            fputcsv($output, [$bulan, $item['nama'], $item['tanggal'], $item['jumlah'], $item['status']]);
        }
    }
    fclose($output);
    exit;
}


?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Backup Tiket Harian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
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

        /* Judul */
        h2 {
        font-family:'Cinzel',serif;
        font-size:2rem; font-weight:700;
        color:#fff;
        text-shadow:2px 2px 4px rgba(0,0,0,.5);
        margin-bottom:20px;
        }

        /* Card transparan */
        .glass-card {
        background:rgba(255,255,255,.05);
        border:1px solid rgba(255,255,255,.1);
        border-radius:12px;
        padding:1.5rem;
        margin-bottom:1.5rem;
        }

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

/* Variasi tombol sesuai tema */
.btn-csv { color:var(--gold); }
.btn-json { color:var(--orange); }
.btn-dashboard { color:var(--gray); }
.btn-tiket { color:var(--red); }

    </style>
</head>
<body class="container mt-5">

    <h2><i class="bi bi-archive"></i> Backup Data Tiket Harian</h2>

<!-- Tabel Data -->
<div class="glass-card">
    <div class="sec-title">
        <i class="bi bi-table"></i> Data Tiket Harian per Bulan
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Tanggal</th>
                <th>Tiket</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($monthlyData as $bulan => $items): ?>
                <tr>
                    <td colspan="4" style="color:var(--gold); font-weight:700; text-align:center;">
                        Bulan: <?= htmlspecialchars($bulan) ?>
                    </td>
                </tr>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nama']) ?></td>
                        <td><?= htmlspecialchars($item['tanggal']) ?></td>
                        <td><?= htmlspecialchars($item['jumlah']) ?></td>
                        <td>
                            <?php if ($item['status'] === 'Lunas'): ?>
                                <span class="badge-lunas">Lunas</span>
                            <?php else: ?>
                                <span class="badge-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Tombol Backup & Navigasi (hanya di bawah tabel) -->
<div class="mt-3 d-flex flex-wrap gap-2">
    <a href="backup_tiket.php?backup=csv" class="btn-glass btn-csv">
        <i class="bi bi-file-earmark-spreadsheet"></i> Backup ke CSV
    </a>
    <a href="backup_tiket.php?backup=json" class="btn-glass btn-json">
        <i class="bi bi-calendar-check"></i> Backup Per Bulan (JSON)
    </a>
    <a href="dashboard.php" class="btn-glass btn-dashboard">
        <i class="bi bi-speedometer2"></i> Balik ke Dashboard
    </a>
    <a href="tiket_harian.php" class="btn-glass btn-tiket">
        <i class="bi bi-calendar-day"></i> Data Tiket Harian
    </a>
</div>

</body>
</html>