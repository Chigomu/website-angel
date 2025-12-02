<?php
session_start();
require_once __DIR__ . '/../app/db.php';

$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username']);
    $p = trim($_POST['password']);

    $st = $pdo->prepare("SELECT * FROM admins WHERE username=?");
    $st->execute([$u]);
    $a = $st->fetch();

    if ($a && password_verify($p, $a['password_hash'])) {
        $_SESSION['admin_logged_in'] = 1;
        header("Location: dashboard.php");
        exit;
    } else {
        $err = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Login — Ibuké Enjel Bakery</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">

    <style>
        /* === BACKGROUND IMAGE === */
        body {
            background: url('https://images.unsplash.com/photo-1535141192574-5d4897c12636?q=80&w=1600&auto=format&fit=crop') center/cover no-repeat fixed;
            padding: 0;
            margin: 0;
            font-family: var(--font-body);
        }

        /* === FADE-IN ANIMATION === */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-center {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            backdrop-filter: blur(3px);
        }

        .login-box {
            background: #ffffffee;
            width: 100%;
            max-width: 420px;
            padding: 40px;
            text-align: center;
            border: 1px solid var(--line-color);
            box-shadow: 0 10px 40px rgba(0,0,0,0.25);
            animation: fadeIn 0.6s ease-out both;
        }

        .login-box h3 {
            font-family: var(--font-heading);
            font-size: 2rem;
            color: var(--text-dark);
            margin-bottom: 25px;
        }

        .login-box input {
            width: 100%;
            padding: 14px;
            border: 1px solid var(--line-color);
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .password-wrapper {
            position: relative;
        }

        .show-pass {
            position: absolute;
            right: 18px;          /* lebih longgar */
            top: 35%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.25rem;   /* DIPERBESAR */
            color: var(--accent);
            opacity: 0.75;        /* lebih soft */
             transition: 0.25s ease;
            }

        .show-pass:hover {
            opacity: 1;
            transform: translateY(-50%) scale(1.15); /* animasi kecil */
        }

        /* === LOGIN BUTTON === */
        .login-box button {
            width: 100%;
            padding: 14px;
            cursor: pointer;
            background: var(--accent);
            color: #fff;
            border: none;
            text-transform: uppercase;
            font-weight: 600;
            transition: 0.25s ease;
            letter-spacing: 1px;
        }

        /* Tidak putih saat hover */
        .login-box button:hover {
            background: #c86445; /* warna lebih gelap dan tetap oranye */
        }

        .error-msg {
            color: #C0392B;
            margin-bottom: 15px;
        }

        .back-link {
            margin-top: 15px;
            display: inline-block;
            color: var(--accent);
            text-decoration: none;
        }

        .back-link:hover {
            color: var(--accent-dark);
        }
    </style>
</head>
<body>

<div class="login-center">
    <div class="login-box">

        <h3>Admin Login</h3>
        <p style="margin-bottom: 25px;">Silakan masukkan username dan password admin.</p>

        <?php if (!empty($err)): ?>
            <div class="error-msg"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <form method="POST">

            <input name="username" placeholder="Username" required>

            <div class="password-wrapper">
                <input id="passwordInput" name="password" type="password" placeholder="Password" required>
                <span class="show-pass" onclick="togglePassword()">👁</span>
            </div>

            <button class="btn-primary" type="submit">Login</button>
        </form>

        <a class="back-link" href="../index.php">← Kembali ke Beranda</a>

    </div>
</div>

<script>
function togglePassword() {
    const field = document.getElementById("passwordInput");
    field.type = field.type === "password" ? "text" : "password";
}
</script>

</body>
</html>
