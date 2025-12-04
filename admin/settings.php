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
    <title>Pengaturan Website</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <style>
        body { padding-top: 100px; background: var(--bg-cream); }
        .container { max-width: 1100px; width: 95%; margin: 0 auto 50px; }
        
        h2 { text-align:center; margin-bottom: 5px; color: var(--text-dark); }
        p.subtitle { text-align:center; color: #666; margin-bottom: 40px; }

        .settings-grid { display: grid; grid-template-columns: 240px 1fr; gap: 30px; align-items: start; }

        /* SIDEBAR MENU */
        .settings-menu { 
            background: #fff; padding: 15px; border-radius: 12px; 
            border: 1px solid var(--line-color); position: sticky; top: 100px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.03); 
        }
        
        .tab-btn { 
            display: block; width: 100%; text-align: left; padding: 12px 15px; 
            text-decoration: none; color: var(--text-light); font-weight: 500; 
            border-radius: 8px; margin-bottom: 5px; transition: 0.3s; 
            border: 1px solid transparent; background: transparent; 
            cursor: pointer; font-family: inherit; font-size: 0.95rem; 
        }
        .tab-btn:hover { background: #f5f5f5; color: var(--text-dark); }
        .tab-btn.active { background: var(--accent); color: #fff; font-weight: 600; box-shadow: 0 4px 10px rgba(217, 119, 87, 0.2); }

        /* TOMBOL SIMPAN (DI DALAM MENU) */
        .btn-save { 
            background: #2C1810; color: #fff; padding: 15px 0; border: none; cursor: pointer; 
            font-size: 0.9rem; margin-top: 20px; transition: 0.3s; font-weight: 700; 
            text-transform: uppercase; letter-spacing: 1px; width: 100%; border-radius: 0;
        }
        .btn-save:hover { background: var(--accent); }

        /* CONTENT CARD */
        .settings-content { 
            background: #fff; padding: 40px; border-radius: 12px; 
            border: 1px solid var(--line-color); min-height: 400px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.03); position: relative; 
        }
        
        .tab-pane { display: none; animation: fadeIn 0.3s ease; }
        .tab-pane.active { display: block; }

        .section-title { 
            font-size: 1.5rem; margin-bottom: 15px; color: var(--text-dark); 
            border-bottom: 2px solid var(--line-color); padding-bottom: 10px; margin-top: 0; 
        }
        
        /* GRID SYSTEM UNTUK FORM (2 KOLOM) - PERBAIKAN UTAMA */
        .form-row { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 20px; 
            margin-bottom: 15px; 
        }
        .form-group { margin-bottom: 15px; }
        .form-group.full { grid-column: span 2; }

        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 0.9rem; }
        
        input[type="text"], textarea, select, input[type="number"] { 
            width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; 
            font-family: inherit; background: #fafafa; font-size: 0.95rem; transition: 0.3s; 
        }
        input:focus, textarea:focus, select:focus { outline: none; border-color: var(--accent); background: #fff; }
        textarea { min-height: 80px; resize: vertical; }
        
        .color-input-group { display: flex; align-items: center; gap: 10px; background: #fafafa; padding: 5px; border-radius: 6px; border: 1px solid #ccc; }
        input[type="color"] { border: none; width: 40px; height: 35px; cursor: pointer; background: none; padding: 0; border-radius: 4px; }
        
        .alert { background: #d4edda; color: #155724; padding: 15px; margin-bottom: 30px; border-radius: 8px; text-align: center; border: 1px solid #c3e6cb; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) { .settings-grid { grid-template-columns: 1fr; gap: 20px; } .settings-menu { position: static; } .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

  <nav class="navbar">
    <a href="dashboard.php" class="logo">Ibuké Enjel Admin</a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="orders.php">Pesanan</a>
        <a href="products.php">Produk</a>
        <a href="settings.php" style="color: var(--accent);">Pengaturan</a>
        <a href="logout.php" style="color: #C0392B;">Keluar</a>
    </div>
  </nav>

<div class="container reveal active">
    <h2>Pengaturan Website</h2>
    <p class="subtitle">Kontrol penuh tampilan dan isi website Anda.</p>

    <?php if(isset($_GET['updated'])): ?>
        <div class="alert">Perubahan berhasil disimpan!</div>
    <?php endif; ?>

    <form method="POST" id="settingsForm">
        <div class="settings-grid">
            
            <div class="settings-menu">
                <button type="button" class="tab-btn active" onclick="openTab('visual', this)">🎨 Tampilan & Gaya</button>
                <button type="button" class="tab-btn" onclick="openTab('home', this)">🏠 Halaman Home</button>
                <button type="button" class="tab-btn" onclick="openTab('custom', this)">🎂 Halaman Custom</button>
                <button type="button" class="tab-btn" onclick="openTab('contact', this)">📞 Kontak & Lokasi</button>
                <button type="button" class="tab-btn" onclick="openTab('footer', this)">🦶 Footer & Sosmed</button>
                
                <button type="submit" class="btn-save">Simpan Perubahan</button>
            </div>

            <div class="settings-content">
                
                <div id="tab-visual" class="tab-pane active">
                    <div class="section-title">Tampilan & Gaya</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Jenis Font</label>
                            <select name="settings[style_font_preset]">
                                <option value="default" <?= val('style_font_preset', $data) == 'default' ? 'selected' : '' ?>>Default (DM Serif)</option>
                                <option value="elegant" <?= val('style_font_preset', $data) == 'elegant' ? 'selected' : '' ?>>Elegant (Playfair)</option>
                                <option value="modern"  <?= val('style_font_preset', $data) == 'modern' ? 'selected' : '' ?>>Modern (Poppins)</option>
                                <option value="classic" <?= val('style_font_preset', $data) == 'classic' ? 'selected' : '' ?>>Classic (Merriweather)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ukuran Font Dasar (px)</label>
                            <input type="number" name="settings[style_base_size]" value="<?= val('style_base_size', $data) ?: '16' ?>" min="12" max="24">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Warna Background</label>
                            <div class="color-input-group">
                                <input type="color" name="settings[color_bg_cream]" value="<?= val('color_bg_cream', $data) ?: '#FDFBF7' ?>">
                                <input type="text" value="<?= val('color_bg_cream', $data) ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Warna Teks</label>
                            <div class="color-input-group">
                                <input type="color" name="settings[color_text_dark]" value="<?= val('color_text_dark', $data) ?: '#2C1810' ?>">
                                <input type="text" value="<?= val('color_text_dark', $data) ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Warna Aksen (Tombol)</label>
                        <div class="color-input-group">
                            <input type="color" name="settings[color_accent]" value="<?= val('color_accent', $data) ?: '#D97757' ?>">
                            <input type="text" value="<?= val('color_accent', $data) ?>" readonly>
                        </div>
                    </div>
                </div>

                <div id="tab-home" class="tab-pane">
                    <div class="section-title">Halaman Utama (Home)</div>
                    
                    <div class="form-group full">
                        <label>Judul Hero</label>
                        <textarea name="settings[hero_title]" style="height:60px;"><?= val('hero_title', $data) ?></textarea>
                    </div>
                    
                    <div class="form-row">
                         <div class="form-group">
                            <label>Deskripsi Hero</label>
                            <textarea name="settings[hero_desc]" style="height:100px;"><?= val('hero_desc', $data) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Teks Marquee</label>
                            <textarea name="settings[marquee_text]" style="height:100px;"><?= val('marquee_text', $data) ?></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Judul About</label>
                            <input type="text" name="settings[about_title]" value="<?= val('about_title', $data) ?>">
                        </div>
                        <div class="form-group">
                            <label>URL Gambar About</label>
                            <input type="text" name="settings[about_img]" value="<?= val('about_img', $data) ?>">
                        </div>
                    </div>
                    <div class="form-group full">
                        <label>Deskripsi About</label>
                        <textarea name="settings[about_desc]" style="height:120px;"><?= val('about_desc', $data) ?></textarea>
                    </div>
                </div>

                <div id="tab-custom" class="tab-pane">
                    <div class="section-title">Halaman Custom</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Judul Header</label>
                            <input type="text" name="settings[custom_title]" value="<?= val('custom_title', $data) ?>">
                        </div>
                        <div class="form-group">
                            <label>Judul CTA (Bawah)</label>
                            <input type="text" name="settings[cta_title]" value="<?= val('cta_title', $data) ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Deskripsi Header</label>
                            <textarea name="settings[custom_desc]" style="height:100px;"><?= val('custom_desc', $data) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Deskripsi CTA</label>
                            <textarea name="settings[cta_desc]" style="height:100px;"><?= val('cta_desc', $data) ?></textarea>
                        </div>
                    </div>
                </div>

                <div id="tab-contact" class="tab-pane">
                    <div class="section-title">Kontak & Lokasi</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>No. WhatsApp (Utama)</label>
                            <input type="text" name="settings[contact_phone]" value="<?= val('contact_phone', $data) ?>">
                        </div>
                        <div class="form-group">
                            <label>Link Google Maps (Embed)</label>
                            <input type="text" name="settings[gmaps_url]" value="<?= val('gmaps_url', $data) ?>">
                        </div>
                    </div>
                    <div class="form-group full">
                        <label>Alamat Lengkap</label>
                        <textarea name="settings[contact_address]" style="height:80px;"><?= val('contact_address', $data) ?></textarea>
                    </div>
                </div>

                <div id="tab-footer" class="tab-pane">
                    <div class="section-title">Footer & Sosial Media</div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Judul Footer</label>
                            <input type="text" name="settings[footer_title]" value="<?= val('footer_title', $data) ?: 'Ibu Angel' ?>">
                        </div>
                        <div class="form-group">
                            <label>Deskripsi Singkat</label>
                            <input type="text" name="settings[footer_desc]" value="<?= val('footer_desc', $data) ?: 'Dibuat dengan kualitas dan bahan terbaik.' ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Link Instagram</label>
                            <input type="text" name="settings[social_instagram]" value="<?= val('social_instagram', $data) ?>">
                        </div>
                        <div class="form-group">
                            <label>Link Facebook</label>
                            <input type="text" name="settings[social_facebook]" value="<?= val('social_facebook', $data) ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Link WhatsApp Footer</label>
                            <input type="text" name="settings[social_whatsapp]" value="<?= val('social_whatsapp', $data) ?>">
                        </div>
                        <div class="form-group">
                            <label>Teks Copyright</label>
                            <input type="text" name="settings[footer_copy]" value="<?= val('footer_copy', $data) ?: '© 2025 Ibuké Enjel Bakery.' ?>">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelector('.reveal').classList.add('active');
        
        document.querySelectorAll('input[type="color"]').forEach(picker => {
            picker.addEventListener('input', (e) => {
                e.target.nextElementSibling.value = e.target.value;
            });
        });

        const form = document.getElementById('settingsForm');
        let isFormDirty = false;

        form.addEventListener('change', () => isFormDirty = true);
        form.addEventListener('input', () => isFormDirty = true);
        
        form.addEventListener('submit', () => isFormDirty = false);

        window.addEventListener('beforeunload', (e) => {
            if (isFormDirty) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    });

    function openTab(tabName, btn) {
        document.querySelectorAll('.tab-pane').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(button => button.classList.remove('active'));
        document.getElementById('tab-' + tabName).classList.add('active');
        btn.classList.add('active');
    }
</script>

</body>
</html>