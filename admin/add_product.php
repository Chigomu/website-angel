<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

$errors = [];

// Ambil daftar kategori untuk dropdown
$cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. LOGIKA TAMBAH KATEGORI BARU
    if (isset($_POST['new_category_name'])) {
        $newCat = trim($_POST['new_category_name']);
        if ($newCat) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
            $stmt->execute([$newCat]);
            header("Location: add_product.php");
            exit;
        }
    }

    // 2. LOGIKA TAMBAH PRODUK
    $name        = $_POST['name'] ?? '';
    $category    = $_POST['category'] ?? '';
    $type        = $_POST['type'] ?? 'regular';
    $price       = $_POST['price'] ?? 0;
    $price_min   = $_POST['price_min'] ?? 0;
    $price_max   = $_POST['price_max'] ?? 0;
    $ingredients = $_POST['ingredients'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // 3. LOGIKA UPLOAD GAMBAR
    $image_url = '';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newName = uniqid('img_', true) . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $newName)) {
                $image_url = 'uploads/' . $newName;
            } else {
                $errors[] = "Gagal mengupload gambar.";
            }
        } else {
            $errors[] = "Format gambar tidak valid (hanya JPG, PNG, WEBP).";
        }
    }

    if (trim($name) === '') $errors[] = "Nama produk wajib diisi.";
    if ($type == 'regular') { $price_min = 0; $price_max = 0; } else { $price = 0; }

    if (empty($errors)) {
        try {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            $stmt = $pdo->prepare("INSERT INTO products (slug, name, category, type, price, price_min, price_max, ingredients, description, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$slug, $name, $category, $type, $price, $price_min, $price_max, $ingredients, $description, $image_url]);
            header("Location: products.php?added=1");
            exit;
        } catch (Exception $e) {
            $errors[] = "Gagal menyimpan: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Admin</title>
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body { padding-top: 85px; background-color: var(--bg-cream); }
        .section { padding-top: 20px !important; }

        .add-container {
            max-width: 1200px; width: 95%; margin: 0 auto 50px; background: #fff;
            padding: 40px; border-radius: 12px; box-shadow: 0 15px 40px rgba(0,0,0,0.05); border: 1px solid var(--line-color);
        }
        .add-header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid var(--line-color); padding-bottom: 20px; }
        .add-header h2 { font-size: 2rem; color: var(--text-dark); margin-bottom: 5px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .form-group { margin-bottom: 5px; }
        .full-width { grid-column: span 2; }
        
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 0.9rem; }
        
        input, select, textarea {
            width: 100%; padding: 12px 15px; border: 1px solid var(--line-color); border-radius: 6px;
            font-family: var(--font-body); font-size: 1rem; background: #FAFAFA;
        }
        /* File Input Style */
        input[type="file"] { padding: 10px; background: #fff; cursor: pointer; }

        .btn-group { grid-column: span 2; margin-top: 20px; display: flex; gap: 15px; }
        .btn-cancel { padding: 14px 25px; border: 1px solid var(--line-color); color: var(--text-light); text-decoration: none; font-weight: 600; text-align: center; flex: 1; }
        .btn-save { flex: 2; text-align: center; cursor: pointer; background-color: var(--accent); border: 1px solid var(--accent); color: #fff; font-weight: 600; }

        .price-section { display: none; }
        .price-section.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
        
        .modal-bg { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center; }
        .modal-box { background: #fff; padding: 30px; border-radius: 8px; width: 400px; text-align: center; }
        
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .full-width { grid-column: span 1; } }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="logo">Ibu Angel Admin</a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="orders.php">Pesanan</a>
            <a href="products.php" style="color: var(--accent);">Produk</a>
            <a href="settings.php">Tampilan</a>
            <a href="logout.php" style="color: #C0392B;">Logout</a>
        </div>
    </nav>

    <div class="section reveal active">
        <div style="max-width: 1200px; width: 95%; margin: 0 auto 20px;">
            <a href="products.php" style="color: var(--text-light); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Kembali ke Menu Produk
            </a>
        </div>

        <div class="add-container">
            <div class="add-header">
                <h2>Tambah Produk Baru</h2>
                <p>Silakan isi informasi produk dengan lengkap</p>
            </div>

            <?php if (!empty($errors)): ?>
            <div style="background:#fde8e7; color:#c0392b; padding:15px; border-radius:6px; margin-bottom:20px;">
                <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="form-grid">
                
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="name" required placeholder="Contoh: Nastar Premium">
                </div>
                
                <div class="form-group">
                    <label>Upload Gambar Produk</label>
                    <input type="file" name="image_file" accept="image/*">
                    <small style="color:#888; display:block; margin-top:5px;">Format: JPG, PNG, WEBP. Maks 2MB.</small>
                </div>

                <div class="form-group">
                    <label>Kategori 
                        <a href="#" onclick="openCatModal()" style="font-size:0.8rem; color:var(--accent); float:right;">+ Tambah Baru</a>
                    </label>
                    <select name="category" required>
                        <option value="" disabled selected>-- Pilih Kategori --</option>
                        <?php foreach ($cats as $c): ?>
                            <option value="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jenis Produk</label>
                    <select name="type" id="typeSelect" onchange="togglePriceInputs()">
                        <option value="regular">Regular (Harga Tetap)</option>
                        <option value="custom">Custom (Range Harga)</option>
                    </select>
                </div>

                <div id="priceRegular" class="form-group full-width price-section">
                    <label>Harga Satuan (Rp)</label>
                    <input type="number" name="price" placeholder="Contoh: 75000">
                </div>
                <div id="priceCustom1" class="form-group price-section">
                    <label>Harga Minimum (Rp)</label>
                    <input type="number" name="price_min" placeholder="Min">
                </div>
                <div id="priceCustom2" class="form-group price-section">
                    <label>Harga Maksimum (Rp)</label>
                    <input type="number" name="price_max" placeholder="Max">
                </div>

                <div class="form-group">
                    <label>Bahan Utama</label>
                    <textarea name="ingredients" rows="6" placeholder="Contoh: Tepung terigu, mentega wisman..."></textarea>
                </div>
                <div class="form-group">
                    <label>Deskripsi Lengkap</label>
                    <textarea name="description" rows="6" placeholder="Jelaskan rasa, tekstur, dan keunggulan..."></textarea>
                </div>

                <div class="btn-group">
                    <a href="products.php" class="btn-cancel">Batal</a>
                    <button type="submit" class="btn-primary btn-save"><i class="fas fa-save"></i> Simpan Produk</button>
                </div>
            </form>
        </div>
    </div>

    <div id="catModal" class="modal-bg">
        <div class="modal-box">
            <h3>Tambah Kategori Baru</h3>
            <form method="POST">
                <input type="text" name="new_category_name" placeholder="Nama Kategori" required>
                <div style="margin-top:20px; display:flex; gap:10px;">
                    <button type="button" onclick="closeCatModal()" style="flex:1; padding:10px; border:1px solid #ddd; background:#fff; cursor:pointer;">Batal</button>
                    <button type="submit" style="flex:1; padding:10px; border:none; background:var(--accent); color:#fff; cursor:pointer;">Simpan</button>
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
            const reg = document.getElementById('priceRegular');
            const cust1 = document.getElementById('priceCustom1');
            const cust2 = document.getElementById('priceCustom2');
            if (type === 'regular') {
                reg.classList.add('active'); cust1.classList.remove('active'); cust2.classList.remove('active');
            } else {
                reg.classList.remove('active'); cust1.classList.add('active'); cust2.classList.add('active');
            }
        }
        function openCatModal() { document.getElementById('catModal').style.display = 'flex'; }
        function closeCatModal() { document.getElementById('catModal').style.display = 'none'; }
    </script>
</body>
</html>