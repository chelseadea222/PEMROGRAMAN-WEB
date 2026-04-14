<?php
// ─── PROTEKSI: hanya admin ───
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['role'] !== 'admin') {
    header('Location: tiket.php');
    exit;
}

// ─── UPDATE STATUS ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'update_status') {
    $id     = (int)$_POST['tiket_id'];
    $status = $_POST['status'];
    if (in_array($status, ['Lunas', 'Pending'])) {
        $pdo->prepare("UPDATE tiket_harian SET status = ? WHERE id = ?")->execute([$status, $id]);
    }
    header('Location: tiket_harian.php?ok=update');
    exit;
}

// ─── HAPUS ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    $id = (int)$_POST['hapus_id'];
    $pdo->prepare("DELETE FROM tiket_harian WHERE id = ?")->execute([$id]);
    header('Location: tiket_harian.php?ok=hapus');
    exit;
}

// ─── STATISTIK ───
$total_pengunjung = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM tiket_harian")->fetchColumn();
$total_hari_ini   = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM tiket_harian WHERE tanggal = CURDATE()")->fetchColumn();
$total_tiket      = $pdo->query("SELECT COUNT(*) FROM tiket_harian")->fetchColumn();
$total_users      = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();

// ─── SEMUA DATA ───
$semua = $pdo->query("
    SELECT t.*, u.nama AS nama_user, u.email
    FROM tiket_harian t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.tanggal DESC, t.id DESC
")->fetchAll();

// ─── DATA GRAFIK ───
$grafik = $pdo->query("
    SELECT DATE_FORMAT(tanggal,'%b') as bulan, SUM(jumlah) as total
    FROM tiket_harian
    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(tanggal,'%Y-%m')
    ORDER BY tanggal ASC
")->fetchAll();
$g_labels = json_encode(array_column($grafik, 'bulan'));
$g_data   = json_encode(array_column($grafik, 'total'));

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - BromoTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Lato:wght@300;400;700&display=swap');
        :root { --orange:#E8621A; --gold:#D4A017; }
        * { box-sizing:border-box; }
        body {
            font-family:'Lato',sans-serif; min-height:100vh; color:#fff;
            background:url('https://i.pinimg.com/736x/d5/fa/66/d5fa660d2e02cb8b5f2c3e1489919426.jpg') center/cover fixed;
        }
        body::before { content:''; position:fixed; inset:0; background:rgba(10,8,5,.82); backdrop-filter:blur(4px); z-index:0; }
        .wrap { position:relative; z-index:1; min-height:100vh; }
        .topbar { background:rgba(26,18,9,.95); border-bottom:1px solid rgba(232,98,26,.3); padding:.9rem 1.5rem; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; }
        .brand { font-family:'Cinzel',serif; font-size:1.2rem; font-weight:700; color:#fff; }
        .brand span { color:var(--orange); }
        .admin-badge { background:rgba(232,98,26,.2); border:1px solid rgba(232,98,26,.4); color:var(--orange); padding:4px 12px; border-radius:20px; font-size:.78rem; font-weight:700; letter-spacing:1px; }
        .btn-logout { background:transparent; border:1px solid rgba(255,255,255,.25); color:rgba(255,255,255,.6); padding:6px 14px; border-radius:6px; font-size:.82rem; text-decoration:none; transition:all .2s; display:inline-flex; align-items:center; gap:6px; }
        .btn-logout:hover { background:rgba(220,53,69,.2); color:#ff6b78; border-color:rgba(220,53,69,.5); }
        .content { max-width:1100px; margin:0 auto; padding:2rem 1.5rem; }
        .page-title { font-family:'Cinzel',serif; font-size:1.8rem; font-weight:700; }
        .page-title span { color:var(--orange); }
        .stat-card { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:12px; padding:1.4rem; transition:all .3s; }
        .stat-card:hover { background:rgba(232,98,26,.1); border-color:rgba(232,98,26,.35); }
        .stat-num { font-family:'Cinzel',serif; font-size:2rem; font-weight:700; color:var(--gold); }
        .stat-lbl { font-size:.78rem; letter-spacing:1.5px; text-transform:uppercase; color:rgba(255,255,255,.45); margin-top:4px; }
        .stat-icon { font-size:2rem; opacity:.3; float:right; margin-top:-6px; }
        .chart-card { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:14px; padding:1.5rem; margin-bottom:2rem; }
        .sec-title { font-family:'Cinzel',serif; font-size:1rem; font-weight:700; color:var(--gold); margin-bottom:1.2rem; padding-bottom:.7rem; border-bottom:1px solid rgba(255,255,255,.1); display:flex; align-items:center; gap:8px; }
        .table-card { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:14px; overflow:hidden; }
        .table { margin:0; }
        .table thead th { background:rgba(26,18,9,.8); color:rgba(255,255,255,.6); font-size:.78rem; letter-spacing:1.5px; text-transform:uppercase; border-bottom:1px solid rgba(255,255,255,.1); padding:12px 16px; font-weight:600; }
        .table tbody td { background:transparent; color:rgba(255,255,255,.85); border-bottom:1px solid rgba(255,255,255,.06); padding:11px 16px; font-size:.88rem; vertical-align:middle; }
        .table tbody tr:hover td { background:rgba(255,255,255,.04); }
        .badge-lunas { background:rgba(25,135,84,.2); color:#6fff9e; border:1px solid rgba(25,135,84,.4); padding:3px 10px; border-radius:20px; font-size:.78rem; font-weight:700; }
        .badge-pending { background:rgba(255,193,7,.15); color:#ffd654; border:1px solid rgba(255,193,7,.35); padding:3px 10px; border-radius:20px; font-size:.78rem; font-weight:700; }
        .btn-hapus { background:rgba(220,53,69,.15); border:1px solid rgba(220,53,69,.4); color:#ff6b78; padding:5px 14px; border-radius:6px; font-size:.8rem; font-weight:700; transition:all .2s; cursor:pointer; }
        .btn-hapus:hover { background:rgba(220,53,69,.3); }
        select.status-select { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.2); color:#fff; border-radius:6px; padding:4px 8px; font-size:.82rem; cursor:pointer; }
        select.status-select option { background:#1A1209; }
        .alert-ok { background:rgba(25,135,84,.15); border:1px solid rgba(25,135,84,.4); color:#6fff9e; border-radius:10px; padding:12px 16px; font-size:.88rem; margin-bottom:1.5rem; }
    </style>
</head>
<body>
<div class="wrap">

    <nav class="topbar">
        <div class="brand">BROMO<span>TRACK</span> <small style="font-size:.7rem;opacity:.4;font-family:Lato">ADMIN</small></div>
        <div class="d-flex align-items-center gap-2">
            <span class="admin-badge"><i class="bi bi-shield-fill me-1"></i>ADMIN</span>
            <a href="logout.php" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Keluar</a>
        </div>
    </nav>

    <div class="content">

        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <h1 class="page-title">Dashboard <span>Admin</span></h1>
                <p style="color:rgba(255,255,255,.45);font-size:.88rem;">Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?></p>
            </div>
        </div>

        <?php if (isset($_GET['ok'])): ?>
            <div class="alert-ok">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= $_GET['ok']==='update' ? 'Status berhasil diperbarui!' : 'Data berhasil dihapus!' ?>
            </div>
        <?php endif; ?>

        <!-- STAT -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <i class="bi bi-people-fill stat-icon"></i>
                    <div class="stat-num"><?= number_format($total_pengunjung) ?></div>
                    <div class="stat-lbl">Total Pengunjung</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <i class="bi bi-person-check stat-icon"></i>
                    <div class="stat-num"><?= $total_hari_ini ?></div>
                    <div class="stat-lbl">Hari Ini</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <i class="bi bi-ticket-perforated stat-icon"></i>
                    <div class="stat-num"><?= $total_tiket ?></div>
                    <div class="stat-lbl">Total Tiket</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <i class="bi bi-person-fill stat-icon"></i>
                    <div class="stat-num"><?= $total_users ?></div>
                    <div class="stat-lbl">User Terdaftar</div>
                </div>
            </div>
        </div>

        <!-- CHART -->
        <div class="chart-card">
            <div class="sec-title"><i class="bi bi-bar-chart-fill" style="color:var(--orange)"></i> Grafik Pengunjung 6 Bulan Terakhir</div>
            <canvas id="chart" height="80"></canvas>
        </div>

        <!-- tombol backup -->
        <a href="backup_tiket.php" class="btn btn-warning mb-3">
            <i class="bi bi-download me-2"></i> Backup Tiket
        </a>


        <!-- TABLE -->
        <div class="sec-title"><i class="bi bi-table" style="color:var(--orange)"></i> Semua Data Kunjungan</div>
        <div class="table-card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th><th>Nama</th><th>Email</th><th>Tanggal</th><th>Jumlah</th><th>Status</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($semua)): ?>
                        <tr><td colspan="7" style="text-align:center;color:rgba(255,255,255,.35);padding:3rem;">Belum ada data.</td></tr>
                    <?php else: ?>
                        <?php foreach ($semua as $i => $row): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td style="font-size:.82rem;color:rgba(255,255,255,.5)"><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                            <td><?= $row['jumlah'] ?> org</td>
                            <td>
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="aksi" value="update_status">
                                    <input type="hidden" name="tiket_id" value="<?= $row['id'] ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="Lunas"   <?= $row['status']==='Lunas'  ?'selected':'' ?>>Lunas</option>
                                        <option value="Pending" <?= $row['status']==='Pending'?'selected':'' ?>>Pending</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form method="POST" style="display:inline"
                                    onsubmit="return confirm('Yakin hapus data ini?')">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="hapus_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn-hapus">
                                        <i class="bi bi-trash3"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
new Chart(document.getElementById('chart').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?= $g_labels ?>,
        datasets: [{
            label: 'Jumlah Pengunjung',
            data: <?= $g_data ?>,
            borderColor: '#E8621A',
            backgroundColor: 'rgba(232,98,26,0.15)',
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#E8621A',
            pointRadius: 5
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: 'rgba(255,255,255,0.6)' } } },
        scales: {
            x: { ticks: { color: 'rgba(255,255,255,0.5)' }, grid: { color: 'rgba(255,255,255,0.05)' } },
            y: { ticks: { color: 'rgba(255,255,255,0.5)' }, grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true }
        }
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>