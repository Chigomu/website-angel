<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $value) {
        // Update atau Insert jika belum ada
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    header("Location: settings.php?updated=1");
    exit;
}

// === PERBAIKAN DI SINI ===
// Ubah "SELECT *" menjadi "SELECT setting_key, setting_value"
// Agar PDO::FETCH_KEY_PAIR bekerja (hanya boleh 2 kolom: key & value)
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Tampilan Web</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <style>
        body { padding-top: 100px; background: var(--bg-cream); }
        .container { max-width: 800px; margin: 0 auto 50px; background: #fff; padding: 40px; border-radius: 12px; border: 1px solid var(--line-color); }
        h3 { border-bottom: 2px solid var(--line-color); padding-bottom: 10px; margin-top: 30px; margin-bottom: 20px; color: var(--accent); }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: var(--text-dark); }
        input[type="text"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 15px; font-family: inherit; }
        textarea { min-height: 100px; resize: vertical; }
        .btn-save { background: var(--text-dark); color: #fff; padding: 15px 30px; border: none; cursor: pointer; width: 100%; font-size: 1.1rem; transition: 0.3s; }
        .btn-save:hover { background: var(--accent); }
        .alert { background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; }
    </style>
</head>
<body>

  <nav class="navbar">
    <a href="dashboard.php" class="logo">Ibu Angel Admin</a>
    <div class="nav-links">
        <a href="../index.php">Home</a>
        <a href="orders.php">Pesanan</a>
        <a href="products.php">Produk</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="settings.php" style="color: var(--accent);">Tampilan</a>
        <a href="logout.php" style="color: #C0392B;">Logout</a>
    </div>
  </nav>

<div class="container reveal active">
    <h2 style="text-align:center; margin-bottom: 10px;">Pengaturan Tampilan User</h2>
    <p style="text-align:center; color: #666; margin-bottom: 30px;">Ubah teks di halaman utama tanpa menyentuh kode.</p>

    <?php if(isset($_GET['updated'])): ?>
        <div class="alert">Perubahan berhasil disimpan!</div>
    <?php endif; ?>

    <form method="POST">
        
        <h3>🏠 Halaman Utama (Home)</h3>
        
        <label>Judul Besar (Hero Title)</label>
        <textarea name="settings[hero_title]"><?= htmlspecialchars($data['hero_title'] ?? '') ?></textarea>
        <small style="display:block; margin-top:-10px; margin-bottom:15px; color:#888;">Gunakan &lt;br&gt; untuk baris baru.</small>

        <label>Deskripsi Hero</label>
        <textarea name="settings[hero_desc]"><?= htmlspecialchars($data['hero_desc'] ?? '') ?></textarea>

        <label>Teks Berjalan (Marquee)</label>
        <input type="text" name="settings[marquee_text]" value="<?= htmlspecialchars($data['marquee_text'] ?? '') ?>">

        <label>Judul "Tentang Kami"</label>
        <input type="text" name="settings[about_title]" value="<?= htmlspecialchars($data['about_title'] ?? '') ?>">

        <label>Deskripsi "Tentang Kami"</label>
        <textarea name="settings[about_desc]"><?= htmlspecialchars($data['about_desc'] ?? '') ?></textarea>

        <label>URL Gambar "Tentang Kami"</label>
        <input type="text" name="settings[about_img]" value="<?= htmlspecialchars($data['about_img'] ?? '') ?>">

        <h3>🎂 Halaman Custom</h3>

        <label>Judul Header Custom</label>
        <input type="text" name="settings[custom_title]" value="<?= htmlspecialchars($data['custom_title'] ?? '') ?>">

        <label>Deskripsi Header Custom</label>
        <textarea name="settings[custom_desc]"><?= htmlspecialchars($data['custom_desc'] ?? '') ?></textarea>

        <label>Judul Bawah (CTA)</label>
        <input type="text" name="settings[cta_title]" value="<?= htmlspecialchars($data['cta_title'] ?? '') ?>">

        <label>Deskripsi Bawah (CTA)</label>
        <textarea name="settings[cta_desc]"><?= htmlspecialchars($data['cta_desc'] ?? '') ?></textarea>

        <button type="submit" class="btn-save">Simpan Perubahan</button>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelector('.reveal').classList.add('active');
    });
</script>

</body>
</html>