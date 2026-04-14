<?php
session_start();
require_once 'koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Email dan password wajib diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            // Redirect sesuai role
            if ($user['role'] === 'admin') {
                header('Location: tiket_harian.php');
            } else {
                header('Location: tiket.php');
            }
            exit;
        } else {
            $error = 'Email atau password salah!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BromoTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Lato:wght@300;400;700&display=swap');
        * { box-sizing: border-box; }
        body {
            font-family: 'Lato', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('https://i.pinimg.com/736x/d5/fa/66/d5fa660d2e02cb8b5f2c3e1489919426.jpg') center/cover fixed;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(10,8,5,0.75);
            backdrop-filter: blur(6px);
            z-index: 0;
        }
        .card-login {
            position: relative;
            z-index: 1;
            background: rgba(255,255,255,0.07);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 16px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }
        .logo-title {
            font-family: 'Cinzel', serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            text-align: center;
            margin-bottom: 0.25rem;
        }
        .logo-title span { color: #E8621A; }
        .subtitle { text-align: center; color: rgba(255,255,255,0.5); font-size: 0.88rem; margin-bottom: 2rem; }
        .form-label { color: rgba(255,255,255,0.8); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 6px; }
        .form-control {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 0.95rem;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.15);
            border-color: #E8621A;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(232,98,26,0.25);
        }
        .form-control::placeholder { color: rgba(255,255,255,0.3); }
        .btn-login {
            background: #E8621A;
            border: none;
            color: #fff;
            padding: 13px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 1px;
            width: 100%;
            margin-top: 0.5rem;
            transition: all 0.3s;
            cursor: pointer;
        }
        .btn-login:hover { background: #c7531a; transform: translateY(-1px); }
        .divider { border-color: rgba(255,255,255,0.12); margin: 1.2rem 0; }
        .reg-link { text-align: center; color: rgba(255,255,255,0.5); font-size: 0.88rem; }
        .reg-link a { color: #E8621A; text-decoration: none; font-weight: 700; }
        .reg-link a:hover { color: #ff7a35; }
        .alert-custom {
            background: rgba(220,53,69,0.15);
            border: 1px solid rgba(220,53,69,0.4);
            color: #ff6b78;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .back-link {
            position: fixed;
            top: 1.5rem; left: 1.5rem;
            z-index: 2;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-size: 0.88rem;
            display: flex; align-items: center; gap: 6px;
            transition: color 0.2s;
        }
        .back-link:hover { color: #fff; }
    </style>
</head>
<body>

<a href="index.php" class="back-link">
    <i class="bi bi-arrow-left"></i> Kembali
</a>

<div class="card-login">
    <div class="logo-title">BROMO<span>TRACK</span></div>
    <p class="subtitle">Masuk ke sistem tracking pengunjung</p>

    <?php if ($error): ?>
        <div class="alert-custom">
            <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control"
            placeholder="Masukkan email" required autocomplete="off">
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control"
            placeholder="Masukkan password" required autocomplete="new-password">
    </div>
    <button type="submit" class="btn-login">
        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
    </button>
</form>


    <hr class="divider">
    <p class="reg-link">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
</div>
</body>
</html>