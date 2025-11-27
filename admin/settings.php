<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }
    header("Location: settings.php?updated=1");
    exit;
}

$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); 
function val($key, $data) { return htmlspecialchars($data[$key] ?? ''); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Tampilan Web</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <style>
        /* === STYLE SETTINGS MODERN === */
        body { padding-top: 85px; background: var(--bg-cream); }
        
        .container { 
            max-width: 1100px; /* Ukuran ideal */
            width: 95%; margin: 0 auto 50px; 
        }
        
        h2 { text-align:center; margin-bottom: 5px; color: var(--text-dark); }
        p.subtitle { text-align:center; color: #666; margin-bottom: 40px; }

        /* GRID LAYOUT UTAMA */
        .settings-grid { 
            display: grid; 
            grid-template-columns: 240px 1fr; /* Kiri Menu, Kanan Form */
            gap: 30px; 
            align-items: start;
        }

        /* SIDEBAR MENU (STICKY) */
        .settings-menu { 
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid var(--line-color);
            position: sticky; /* KUNCI AGAR MENEMPEL SAAT SCROLL */
            top: 100px;       /* Jarak dari atas layar */
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }

        .settings-menu a { 
            display: block; 
            padding: 12px 15px; 
            text-decoration: none; 
            color: var(--text-light); 
            font-weight: 500; 
            border-radius: 8px; 
            margin-bottom: 5px;
            transition: 0.3s;
        }
        
        .settings-menu a:hover { background: #f5f5f5; color: var(--text-dark); }
        .settings-menu a.active { background: var(--accent); color: #fff; font-weight: 600; }

        /* FORM CONTENT AREA */
        .settings-content {
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid var(--line-color);
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }
        
        .section-title { 
            font-size: 1.4rem; margin-bottom: 25px; color: var(--text-dark); 
            border-bottom: 2px solid var(--line-color); padding-bottom: 10px; 
            margin-top: 0;
        }
        
        /* FORM ELEMENTS */
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 0.9rem; margin-top: 20px; }
        input[type="text"], textarea, select, input[type="number"] { 
            width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; 
            font-family: inherit; background: #fafafa; font-size: 0.95rem; transition: 0.3s;
        }
        input:focus, textarea:focus, select:focus { outline: none; border-color: var(--accent); background: #fff; }
        textarea { min-height: 100px; resize: vertical; }
        
        .color-input-group { display: flex; align-items: center; gap: 10px; background: #fafafa; padding: 5px; border-radius: 6px; border: 1px solid #ccc; }
        input[type="color"] { border: none; width: 40px; height: 40px; cursor: pointer; background: none; padding: 0; border-radius: 4px; }
        .color-input-group input[type="text"] { border: none; background: transparent; padding: 0; font-weight: 500; }
        
        /* TOMBOL SIMPAN MELAYANG DI BAWAH KANAN */
        .btn-save { 
            background: var(--text-dark); color: #fff; padding: 15px 40px; border: none; 
            cursor: pointer; font-size: 1rem; margin-top: 40px; transition: 0.3s; 
            font-weight: 600; text-transform: uppercase; letter-spacing: 1px; width: 100%; border-radius: 6px;
        }
        .btn-save:hover { background: var(--accent); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        
        .alert { background: #d4edda; color: #155724; padding: 15px; margin-bottom: 30px; border-radius: 8px; text-align: center; border: 1px solid #c3e6cb; }

        /* RESPONSIVE: Jika layar kecil, menu jadi di atas */
        @media (max-width: 768px) {
            .settings-grid { grid-template-columns: 1fr; gap: 20px; }
            .settings-menu { position: static; display: flex; overflow-x: auto; padding: 10px; gap: 10px; }
            .settings-menu a { white-space: nowrap; margin-bottom: 0; flex: 1; text-align: center; }
        }
    </style>
</head>
<body>

  <nav class="navbar">
    <a href="dashboard.php" class="logo">Ibu Angel Admin</a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="orders.php">Pesanan</a>
        <a href="products.php">Produk</a>
        <a href="settings.php" style="color: var(--accent);">Tampilan</a>
        <a href="logout.php" style="color: #C0392B;">Logout</a>
    </div>
  </nav>

<div class="container reveal active">
    <h2>Pengaturan Website</h2>
    <p class="subtitle">Kontrol penuh tampilan dan isi website Anda.</p>

    <?php if(isset($_GET['updated'])): ?>
        <div class="alert">Perubahan berhasil disimpan! Refresh halaman utama untuk melihat hasilnya.</div>
    <?php endif; ?>

    <form method="POST">
        <div class="settings-grid">
            
            <div class="settings-menu">
                <a href="#visual" onclick="scrollToSection('visual')">🎨 Tampilan</a>
                <a href="#home" onclick="scrollToSection('home')">🏠 Halaman Home</a>
                <a href="#custom" onclick="scrollToSection('custom')">🎂 Halaman Custom</a>
                <a href="#contact" onclick="scrollToSection('contact')">📞 Kontak</a>
                <button type="submit" class="btn-save" style="margin-top: 20px; font-size: 0.9rem;">Simpan Semua</button>
            </div>

            <div class="settings-content">
                
                <div id="visual">
                    <div class="section-title">Tampilan & Gaya</div>
                    
                    <label>Jenis Font</label>
                    <select name="settings[style_font_preset]">
                        <option value="default" <?= val('style_font_preset', $data) == 'default' ? 'selected' : '' ?>>Default (DM Serif + Outfit)</option>
                        <option value="elegant" <?= val('style_font_preset', $data) == 'elegant' ? 'selected' : '' ?>>Elegant (Playfair + Lato)</option>
                        <option value="modern"  <?= val('style_font_preset', $data) == 'modern' ? 'selected' : '' ?>>Modern (Poppins + Open Sans)</option>
                        <option value="classic" <?= val('style_font_preset', $data) == 'classic' ? 'selected' : '' ?>>Classic (Merriweather + Roboto)</option>
                    </select>

                    <label>Ukuran Font Dasar (px)</label>
                    <input type="number" name="settings[style_base_size]" value="<?= val('style_base_size', $data) ?: '16' ?>" min="12" max="24">

                    <label>Warna Background</label>
                    <div class="color-input-group">
                        <input type="color" name="settings[color_bg_cream]" value="<?= val('color_bg_cream', $data) ?: '#FDFBF7' ?>">
                        <input type="text" value="<?= val('color_bg_cream', $data) ?>" readonly>
                    </div>

                    <label>Warna Teks</label>
                    <div class="color-input-group">
                        <input type="color" name="settings[color_text_dark]" value="<?= val('color_text_dark', $data) ?: '#2C1810' ?>">
                        <input type="text" value="<?= val('color_text_dark', $data) ?>" readonly>
                    </div>

                    <label>Warna Aksen</label>
                    <div class="color-input-group">
                        <input type="color" name="settings[color_accent]" value="<?= val('color_accent', $data) ?: '#D97757' ?>">
                        <input type="text" value="<?= val('color_accent', $data) ?>" readonly>
                    </div>
                </div>

                <div id="home" style="margin-top: 50px; padding-top: 20px; border-top: 1px dashed #ddd;">
                    <div class="section-title">Halaman Utama (Home)</div>
                    <label>Judul Hero</label>
                    <textarea name="settings[hero_title]"><?= val('hero_title', $data) ?></textarea>
                    
                    <label>Deskripsi Hero</label>
                    <textarea name="settings[hero_desc]"><?= val('hero_desc', $data) ?></textarea>
                    
                    <label>Teks Marquee</label>
                    <input type="text" name="settings[marquee_text]" value="<?= val('marquee_text', $data) ?>">
                    
                    <label>Judul About</label>
                    <input type="text" name="settings[about_title]" value="<?= val('about_title', $data) ?>">
                    
                    <label>Deskripsi About</label>
                    <textarea name="settings[about_desc]"><?= val('about_desc', $data) ?></textarea>
                    
                    <label>URL Gambar About</label>
                    <input type="text" name="settings[about_img]" value="<?= val('about_img', $data) ?>">
                </div>

                <div id="custom" style="margin-top: 50px; padding-top: 20px; border-top: 1px dashed #ddd;">
                    <div class="section-title">Halaman Custom</div>
                    <label>Judul Header</label>
                    <input type="text" name="settings[custom_title]" value="<?= val('custom_title', $data) ?>">
                    
                    <label>Deskripsi Header</label>
                    <textarea name="settings[custom_desc]"><?= val('custom_desc', $data) ?></textarea>
                    
                    <label>Judul CTA</label>
                    <input type="text" name="settings[cta_title]" value="<?= val('cta_title', $data) ?>">
                    
                    <label>Deskripsi CTA</label>
                    <textarea name="settings[cta_desc]"><?= val('cta_desc', $data) ?></textarea>
                </div>

                <div id="contact" style="margin-top: 50px; padding-top: 20px; border-top: 1px dashed #ddd;">
                    <div class="section-title">Kontak & Lokasi</div>
                    <label>No. WhatsApp (628...)</label>
                    <input type="text" name="settings[contact_phone]" value="<?= val('contact_phone', $data) ?>">
                    
                    <label>Alamat</label>
                    <textarea name="settings[contact_address]"><?= val('contact_address', $data) ?></textarea>
                    
                    <label>Link Google Maps</label>
                    <input type="text" name="settings[gmaps_url]" value="<?= val('gmaps_url', $data) ?>">
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelector('.reveal').classList.add('active');
        
        // Auto update text input when color picker changes
        document.querySelectorAll('input[type="color"]').forEach(picker => {
            picker.addEventListener('input', (e) => {
                e.target.nextElementSibling.value = e.target.value;
            });
        });
    });

    // Smooth Scroll Manual (Optional)
    function scrollToSection(id) {
        document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
</script>

</body>
</html>