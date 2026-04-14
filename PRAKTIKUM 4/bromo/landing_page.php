<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Landing Page User - Bromo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Lato:wght@300;400;700&display=swap');

        :root {
        --orange:#E8621A;
        --gold:#D4A017;
        --gray:#ccc;
        }

        body {
        font-family:'Lato',sans-serif;
        background:url('https://i.pinimg.com/736x/d5/fa/66/d5fa660d2e02cb8b5f2c3e1489919426.jpg') center/cover fixed;
        color:#fff;
        min-height:100vh;
        display:flex;
        align-items:center;
        justify-content:center;
        flex-direction:column;
        text-align:center;
        }
        body::before {
        content:""; position:fixed; inset:0;
        background:rgba(10,8,5,.82); backdrop-filter:blur(4px);
        z-index:-1;
        }

        h1 {
        font-family:'Cinzel',serif;
        font-size:3rem;
        font-weight:700;
        color:var(--gold);
        text-shadow:2px 2px 6px rgba(0,0,0,.6);
        margin-bottom:40px;
        }

        .btn-glass {
        background:rgba(255,255,255,.05);
        border:1px solid rgba(255,255,255,.15);
        color:#fff;
        padding:12px 28px;
        border-radius:8px;
        font-weight:600;
        font-size:1.1rem;
        transition:.3s;
        display:inline-flex; align-items:center; gap:8px;
        text-decoration:none;
        margin:10px;
        }
        .btn-glass:hover { transform:translateY(-2px); }

        .btn-login { color:var(--orange); }
        .btn-register { color:var(--gold); }
    </style>
    </head>
    <body>
    <h1>Selamat Datang di Gunung Bromo</h1>
    <div>
        <a href="login.php" class="btn-glass btn-login">
        <i class="bi bi-box-arrow-in-right"></i> Login
        </a>
        <a href="register.php" class="btn-glass btn-register">
        <i class="bi bi-person-plus"></i> Register
        </a>
    </div>
</body>
</html>
