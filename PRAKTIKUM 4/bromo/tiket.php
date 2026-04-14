<?php
session_start();
require_once 'koneksi.php';

// ─── Proteksi login ───
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Kalau admin nyasar ke sini, arahkan ke tiket_harian.php
if ($_SESSION['role'] === 'admin') {
    header('Location: tiket_harian.php');
    exit;
}

$user_id   = $_SESSION['user_id'];
$nama_user = $_SESSION['nama'];
$success   = '';
$error     = '';

// ─── Tambah tiket ───
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $nama    = trim($_POST['nama'] ?? '');
    $tanggal = $_POST['tanggal'] ?? '';
    $jumlah  = (int)($_POST['jumlah'] ?? 0);

    if (!$nama || !$tanggal || $jumlah < 1) {
        $error = 'Semua field wajib diisi dan jumlah minimal 1!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO tiket_harian (user_id, nama, tanggal, jumlah, status) 
                            VALUES (?, ?, ?, ?, 'Pending')");
        $stmt->execute([$user_id, $nama, $tanggal, $jumlah]);
        $success = 'Data kunjungan berhasil dicatat!';
    }
}

// Total input kunjungan
$stmt = $pdo->query("SELECT COUNT(*) FROM tiket_harian WHERE user_id = $user_id");
$count_kunjungan = $stmt->fetchColumn() ?: 0;

// Total orang
$stmt = $pdo->query("SELECT SUM(jumlah) FROM tiket_harian WHERE user_id = $user_id");
$sum_orang = $stmt->fetchColumn() ?: 0;

// Tiket terbayar
$stmt = $pdo->query("SELECT COUNT(*) FROM tiket_harian WHERE user_id = $user_id AND status='Lunas'");
$count_lunas = $stmt->fetchColumn() ?: 0;

// Ambil semua tiket milik user
$stmt = $pdo->prepare("SELECT * FROM tiket_harian WHERE user_id = ?");
$stmt->execute([$user_id]);
$data_tiket = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Kunjungan - BromoTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Lato:wght@300;400;700&display=swap');
        
        :root { --orange:#E8621A; --gold:#D4A017; }
        * { box-sizing:border-box; }
        
        body {
            font-family:'Lato',sans-serif; min-height:100vh; color:#fff;
            background:url('https://i.pinimg.com/736x/d5/fa/66/d5fa660d2e02cb8b5f2c3e1489919426.jpg') center/cover fixed;
        }
        
        body::before { 
            content:''; position:fixed; inset:0; 
            background:rgba(10,8,5,0.85); backdrop-filter:blur(6px); z-index:0; 
        }
        
        .wrap { position:relative; z-index:1; min-height:100vh; }

        /* TOPBAR */
        .topbar {
            background:rgba(26,18,9,0.95);
            border-bottom:1px solid rgba(232,98,26,0.3);
            padding:.9rem 1.5rem;
            display:flex; align-items:center; justify-content:space-between;
            position:sticky; top:0; z-index:100;
        }
        .brand { font-family:'Cinzel',serif; font-size:1.2rem; font-weight:700; color:#fff; text-decoration:none; }
        .brand span { color:var(--orange); }
        
        .user-badge {
            background:rgba(232,98,26,0.2); border:1px solid rgba(232,98,26,0.4);
            color:var(--orange); padding:4px 12px; border-radius:20px;
            font-size:.78rem; font-weight:700; letter-spacing:1px;
        }

        .btn-logout {
            background:transparent; border:1px solid rgba(255,255,255,.25);
            color:rgba(255,255,255,.6); padding:6px 14px; border-radius:6px;
            font-size:.82rem; text-decoration:none; transition:all .2s;
            display:inline-flex; align-items:center; gap:6px;
        }
        .btn-logout:hover { background:rgba(220,53,69,.2); color:#ff6b78; border-color:rgba(220,53,69,.5); }

        /* CONTENT */
        .content { max-width:1000px; margin:0 auto; padding:2.5rem 1.5rem; }
        .page-title { font-family:'Cinzel',serif; font-size:2rem; font-weight:700; margin-bottom: 0.5rem; }
        .page-title span { color:var(--orange); }

        /* STAT CARDS - Mengikuti style Admin */
        .stat-card {
            background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
            border-radius:14px; padding:1.4rem; transition:all .3s; height: 100%;
        }
        .stat-card:hover { background:rgba(232,98,26,.1); border-color:rgba(232,98,26,.35); transform: translateY(-3px); }
        .stat-num { font-family:'Cinzel',serif; font-size:1.8rem; font-weight:700; color:var(--gold); }
        .stat-lbl { font-size:.75rem; letter-spacing:1.2px; text-transform:uppercase; color:rgba(255,255,255,.45); margin-top:4px; }
        .stat-icon { font-size:1.8rem; opacity:.2; float:right; }

        /* FORMS & TABLES */
        .glass-card {
            background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
            border-radius:15px; padding:1.8rem; margin-bottom:2rem;
        }
        
        .sec-title {
            font-family:'Cinzel',serif; font-size:0.95rem; font-weight:700; color:var(--gold);
            margin-bottom:1.5rem; padding-bottom:.8rem; border-bottom:1px solid rgba(255,255,255,.1);
            display:flex; align-items:center; gap:10px;
        }

        .form-label { color:rgba(255,255,255,.6); font-size:.85rem; font-weight:600; margin-bottom:8px; }
        .form-control {
            background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.15);
            color:#fff; border-radius:8px; padding:12px; font-size:.9rem;
        }
        .form-control:focus {
            background:rgba(255,255,255,.12); border-color:var(--orange);
            color:#fff; box-shadow:none;
        }

        .btn-submit {
            background:var(--orange); border:none; color:#fff;
            padding:12px 25px; border-radius:8px; font-weight:700;
            font-size:.9rem; letter-spacing:1px; transition:all .3s;
        }
        .btn-submit:hover { background:#c7531a; transform: scale(1.02); }

        /* TABLE STYLE */
        .table { margin:0; }
        .table thead th {
            background:rgba(26,18,9,.6); color:rgba(255,255,255,.5);
            font-size:.75rem; letter-spacing:1.2px; text-transform:uppercase;
            border-bottom:1px solid rgba(255,255,255,.1); padding:15px;
        }
        .table tbody td {
            background:transparent; color:rgba(255,255,255,.8);
            border-bottom:1px solid rgba(255,255,255,.05);
            padding:15px; font-size:.9rem; vertical-align:middle;
        }

        .badge-lunas { background:rgba(25,135,84,.15); color:#6fff9e; border:1px solid rgba(25,135,84,.3); padding:4px 12px; border-radius:20px; font-size:.75rem; }
        .badge-pending { background:rgba(255,193,7,.1); color:#ffd654; border:1px solid rgba(255,193,7,.25); padding:4px 12px; border-radius:20px; font-size:.75rem; }

        .btn-hapus {
            background:rgba(220,53,69,.1); border:1px solid rgba(220,53,69,.3);
            color:#ff6b78; padding:6px 12px; border-radius:6px; font-size:.8rem; transition:.2s;
        }
        .btn-hapus:hover { background:rgba(220,53,69,.25); }

        .alert-ok { background:rgba(25,135,84,.2); border-left:4px solid #198754; color:#6fff9e; padding:15px; border-radius:8px; margin-bottom:20px; }
    </style>
</head>
<body>

<div class="wrap">
    <nav class="topbar">
        <a href="#" class="brand">BROMO<span>TRACK</span></a>
        <div class="d-flex align-items-center gap-3">
            <span class="user-badge"><i class="bi bi-person-circle me-1"></i> USER</span>
            <span class="d-none d-md-block" style="font-size:.9rem; opacity:.8"><?= htmlspecialchars($nama_user) ?></span>
            <a href="logout.php" class="btn-logout"><i class="bi bi-box-arrow-right"></i> Keluar</a>
        </div>
    </nav>

    <div class="content">
        <div class="mb-5">
            <h1 class="page-title">Catat <span>Kunjungan</span></h1>
            <p style="color:rgba(255,255,255,.5)">Manajemen data perjalanan Anda ke kawasan Gunung Bromo.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert-ok"><i class="bi bi-check-circle-fill me-2"></i> <?= $success ?></div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="bi bi-journal-text stat-icon"></i>
                    <div class="stat-num"><?= $count_kunjungan ?></div>
                    <div class="stat-lbl">Total Input</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="bi bi-people-fill stat-icon"></i>
                    <div class="stat-num"><?= $sum_orang ?></div>
                    <div class="stat-lbl">Total Orang</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <i class="bi bi-patch-check-fill stat-icon"></i>
                    <div class="stat-num"><?= $count_lunas ?></div>
                    <div class="stat-lbl">Tiket Terbayar</div>
                </div>
            </div>
        </div>

<div class="col-lg-12">
    <div class="glass-card">
        <div class="sec-title"><i class="bi bi-plus-lg"></i> Tambah Baru</div>
        <form method="POST">
            <input type="hidden" name="aksi" value="tambah">
            <div class="mb-3">
                <label class="form-label">Atas Nama</label>
                <input type="text" name="nama" class="form-control" placeholder="Silahkan isi nama..." required>
            </div>
            <div class="mb-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" required>
            </div>
            <div class="mb-4">
                <label class="form-label">Jumlah Orang</label>
                <input type="number" name="jumlah" class="form-control" min="1" placeholder="Masukkan jumlah orang..." required>
            </div>
            <button type="submit" class="btn-submit w-100">
                <i class="bi bi-send-fill me-2"></i> Simpan Data
            </button>
        </form>
    </div>
</div>


                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>