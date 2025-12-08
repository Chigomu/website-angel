<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

$errors = [];
$cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_category_name']) && !empty(trim($_POST['new_category_name']))) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->execute([trim($_POST['new_category_name'])]);
        header("Location: add_product.php"); exit;
    }

    $name = $_POST['name'] ?? ''; $category = $_POST['category'] ?? ''; $type = $_POST['type'] ?? 'regular';
    $price = $_POST['price'] ?? 0; $price_min = $_POST['price_min'] ?? 0; $price_max = $_POST['price_max'] ?? 0;
    $ingredients = $_POST['ingredients'] ?? ''; $description = $_POST['description'] ?? '';
    
    $final_image_path = ''; 
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $newName = uniqid('prod_', true) . '.' . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $newName)) {
                $final_image_path = 'uploads/' . $newName;
            } else { $errors[] = "Gagal upload gambar."; }
        } else { $errors[] = "Format gambar salah."; }
    } elseif (!empty($_POST['image_url_text'])) {
        $final_image_path = trim($_POST['image_url_text']);
    }

    if (trim($name) === '') $errors[] = "Nama wajib diisi.";
    if ($type == 'regular') { $price_min = 0; $price_max = 0; } else { $price = 0; }

    if (empty($errors)) {
        try {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            $stmt = $pdo->prepare("INSERT INTO products (slug, name, category, type, price, price_min, price_max, ingredients, description, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$slug, $name, $category, $type, $price, $price_min, $price_max, $ingredients, $description, $final_image_path]);
            header("Location: products.php?added=1"); exit;
        } catch (Exception $e) { $errors[] = "Error: " . $e->getMessage(); }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk - Admin</title>
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding-top: 85px; background-color: var(--bg-cream); }
        .section { padding-top: 20px !important; }
        .add-container { max-width: 1200px; width: 95%; margin: 0 auto 50px; background: #fff; padding: 40px; border-radius: 12px; border: 1px solid var(--line-color); }
        .add-header { text-align: left; margin-bottom: 25px; border-bottom: 1px solid var(--line-color); padding-bottom: 15px; }
        
        /* GRID UTAMA */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .full-width { grid-column: span 2; }
        
        /* FORM ELEMENTS (PADDING DIPERKECIL) */
        .form-group { margin-bottom: 10px; } /* Jarak antar input diperkecil */
        label { display: block; margin-bottom: 5px; font-weight: 600; color: var(--text-dark); font-size: 0.9rem; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #FAFAFA; font-family: inherit; transition: 0.3s; }
        textarea { resize: vertical; }

        /* IMAGE ROW */
        .image-section { display: grid; grid-template-columns: 120px 1fr; gap: 20px; align-items: start; }
        .preview-box { width: 120px; height: 120px; border: 2px dashed #ddd; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f9f9f9; }
        .preview-box img { width: 100%; height: 100%; object-fit: cover; }
        .preview-placeholder { color: #aaa; font-size: 2rem; }

        /* TABS GAMBAR */
        .img-tabs { display: flex; gap: 10px; margin-bottom: 8px; }
        .img-tab-btn { padding: 5px 10px; border: 1px solid #ddd; background: #f9f9f9; cursor: pointer; font-size: 0.8rem; border-radius: 4px; }
        .img-tab-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }
        .img-input-group { display: none; }
        .img-input-group.active { display: block; }

        .price-section { display: none; }
        .price-section.active { display: block; }
        
        /* BTN GROUP (GAP DIPERKECIL) */
        .btn-group { 
            grid-column: span 2; 
            display: flex; 
            gap: 15px; 
            margin-top: 15px;      /* Jarak dari form diperkecil */
            padding-top: 15px; 
            border-top: 1px solid var(--line-color); 
        }
        .btn-save { flex: 2; padding: 12px; background: var(--accent); color: #fff; border: none; font-weight: 700; cursor: pointer; border-radius: 6px; }
        .btn-cancel { flex: 1; padding: 12px; text-align: center; border: 1px solid #ddd; background: #f5f5f5; color: #666; text-decoration: none; font-weight: 600; border-radius: 6px; }

        .modal-bg { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center; }
        .modal-box { background: #fff; padding: 30px; border-radius: 8px; width: 400px; text-align: center; }
        
        @media (max-width: 768px) { .form-grid, .image-section { grid-template-columns: 1fr; } .preview-box { margin: 0 auto; } }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="logo">Ibuké Enjel Admin</a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="orders.php">Pesanan</a>
        <a href="products.php" style="color: var(--accent);">Produk</a>
        <a href="settings.php">Pengaturan</a> <a href="logout.php" style="color: #C0392B;">Keluar</a>
    </div>
</nav>

<div class="section reveal active">
    <div style="max-width: 1200px; width: 95%; margin: 0 auto 20px;">
        <a href="products.php" style="color: var(--text-light); text-decoration: none;">&larr; Kembali ke Menu Produk</a>
    </div>

    <div class="add-container">
        <div class="add-header"><h2>Tambah Produk Baru</h2></div>
        <?php if (!empty($errors)): ?><div style="background:#fde8e7; color:#c0392b; padding:15px; border-radius:6px; margin-bottom:20px;"><?= implode('<br>', $errors) ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="form-grid">
            
            <div>
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="name" required placeholder="Contoh: Nastar Premium">
                </div>

                <div class="form-group">
                    <label>Kategori <a href="#" onclick="openCatModal()" style="font-size:0.8rem; color:var(--accent); float:right;">+ Baru</a></label>
                    <select name="category" required>
                        <option value="" disabled selected>-- Pilih --</option>
                        <?php foreach ($cats as $c): ?><option value="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jenis Produk</label>
                    <select name="type" id="typeSelect" onchange="togglePriceInputs()">
                        <option value="regular">Regular (Harga Tetap)</option>
                        <option value="custom">Custom (Range Harga)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Bahan Utama</label>
                    <textarea name="ingredients" rows="5"></textarea>
                </div>
            </div>

            <div>
                <div class="form-group">
                    <label>Gambar Produk</label>
                    <div class="image-section">
                        <div class="preview-box">
                            <i class="fas fa-image preview-placeholder" id="placeholderIcon"></i>
                            <img id="imgPreview" src="" style="display:none;">
                        </div>
                        <div>
                            <div class="img-tabs">
                                <button type="button" class="img-tab-btn active" onclick="switchImgTab('upload')">Upload</button>
                                <button type="button" class="img-tab-btn" onclick="switchImgTab('url')">URL</button>
                            </div>
                            <div id="tab-upload" class="img-input-group active">
                                <input type="file" name="image_file" id="fileInput" accept="image/*" onchange="previewFile()">
                            </div>
                            <div id="tab-url" class="img-input-group">
                                <input type="text" name="image_url_text" id="urlInput" placeholder="https://..." oninput="previewUrl()">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <div id="priceRegular" class="price-section active">
                        <input type="number" name="price" placeholder="0">
                    </div>
                    <div id="priceCustom" class="price-section" style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                        <input type="number" name="price_min" placeholder="Min">
                        <input type="number" name="price_max" placeholder="Max">
                    </div>
                </div>

                <div class="form-group">
                    <label>Deskripsi Lengkap</label>
                    <textarea name="description" rows="5"></textarea>
                </div>
            </div>

            <div class="btn-group">
                <a href="products.php" class="btn-cancel">Batal</a>
                <button type="submit" class="btn-save">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<div id="catModal" class="modal-bg">
    <div class="modal-box">
        <h3>Tambah Kategori</h3>
        <form method="POST">
            <input type="text" name="new_category_name" placeholder="Nama Kategori" required>
            <div style="margin-top:15px; display:flex; gap:10px;">
                <button type="button" onclick="closeCatModal()" style="flex:1; padding:10px; border:1px solid #ddd; background:#fff;">Batal</button>
                <button type="submit" style="flex:1; padding:10px; border:none; background:var(--accent); color:#fff;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => { document.querySelector('.reveal').classList.add('active'); }, 100);
        togglePriceInputs();
    });

    function togglePriceInputs() {
        const type = document.getElementById('typeSelect').value;
        if (type === 'regular') {
            document.getElementById('priceRegular').style.display = 'block';
            document.getElementById('priceCustom').style.display = 'none';
        } else {
            document.getElementById('priceRegular').style.display = 'none';
            document.getElementById('priceCustom').style.display = 'grid';
        }
    }

    function switchImgTab(mode) {
        document.querySelectorAll('.img-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.img-input-group').forEach(g => g.classList.remove('active'));
        
        if(mode === 'upload') {
            document.querySelectorAll('.img-tab-btn')[0].classList.add('active');
            document.getElementById('tab-upload').classList.add('active');
            document.getElementById('urlInput').value = ''; 
            resetPreview();
        } else {
            document.querySelectorAll('.img-tab-btn')[1].classList.add('active');
            document.getElementById('tab-url').classList.add('active');
            document.getElementById('fileInput').value = ''; 
            resetPreview();
        }
    }

    function previewFile() {
        const file = document.getElementById('fileInput').files[0];
        const preview = document.getElementById('imgPreview');
        const icon = document.getElementById('placeholderIcon');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                icon.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    }

    function previewUrl() {
        const url = document.getElementById('urlInput').value;
        const preview = document.getElementById('imgPreview');
        const icon = document.getElementById('placeholderIcon');

        if (url) {
            preview.src = url;
            preview.style.display = 'block';
            icon.style.display = 'none';
        } else {
            resetPreview();
        }
    }

    function resetPreview() {
        document.getElementById('imgPreview').style.display = 'none';
        document.getElementById('imgPreview').src = '';
        document.getElementById('placeholderIcon').style.display = 'block';
    }

    function openCatModal() { document.getElementById('catModal').style.display = 'flex'; }
    function closeCatModal() { document.getElementById('catModal').style.display = 'none'; }
</script>
</body>
</html>