<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

$errors = [];
$cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logic Tambah Kategori Cepat
    if (isset($_POST['new_category_name']) && !empty(trim($_POST['new_category_name']))) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->execute([trim($_POST['new_category_name'])]);
        header("Location: add_product.php"); exit;
    }

    $name = $_POST['name'] ?? '';
    $category = $_POST['category'] ?? '';
    $type = $_POST['type'] ?? 'regular';
    $price = $_POST['price'] ?? 0;
    $price_min = $_POST['price_min'] ?? 0;
    $price_max = $_POST['price_max'] ?? 0;
    $ingredients = $_POST['ingredients'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Logic Gambar
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
        
        .add-container { 
            max-width: 1100px; 
            width: 95%; 
            margin: 0 auto 50px; 
            background: #fff; 
            padding: 40px; 
            border-radius: 12px; 
            border: 1px solid var(--line-color); 
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        }
        
        /* HEADER RATA KIRI */
        .add-header { 
            text-align: left; 
            margin-bottom: 30px; 
            border-bottom: 1px solid var(--line-color); 
            padding-bottom: 15px; 
        }
        .add-header h2 { margin: 0; color: var(--text-dark); font-size: 1.8rem; }
        
        /* LAYOUT UTAMA: 2 KOLOM */
        .layout-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr; 
            gap: 40px;
        }

        .col-left { display: flex; flex-direction: column; gap: 20px; }
        .col-right { display: flex; flex-direction: column; gap: 20px; }

        /* FORM ELEMENTS */
        .form-group { width: 100%; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 0.9rem; }
        
        input, select, textarea { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 6px; 
            background: #FAFAFA; 
            font-family: inherit;
            transition: 0.3s;
            box-sizing: border-box; 
        }
        input:focus, select:focus, textarea:focus { border-color: var(--accent); outline: none; background: #fff; }
        textarea { resize: vertical; }

        /* IMAGE INPUTS BERDAMPINGAN */
        .image-row {
            display: grid;
            grid-template-columns: 1fr 1fr; 
            gap: 15px;
            align-items: start;
        }
        .file-upload-box { position: relative; }
        input[type="file"] { font-size: 0.85rem; background: #fff; padding: 9px; }

        /* PRICE SECTION */
        .price-section { display: none; margin-top: 10px; }
        .price-section.active { display: block; animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

        /* BUTTONS */
        .btn-group { 
            grid-column: span 2; 
            display: flex; 
            gap: 15px; 
            margin-top: 30px; 
            padding-top: 20px; 
            border-top: 1px solid var(--line-color);
        }
        .btn-save { flex: 2; padding: 14px; background: var(--accent); color: #fff; border: none; font-weight: 700; cursor: pointer; border-radius: 6px; font-size: 1rem; transition:0.3s; }
        .btn-save:hover { background: #d35400; transform: translateY(-2px); }
        .btn-cancel { flex: 1; padding: 14px; text-align: center; border: 1px solid #ddd; background: #f5f5f5; color: #666; text-decoration: none; font-weight: 600; border-radius: 6px; transition:0.3s; }
        .btn-cancel:hover { background: #eee; color: #333; }

        /* MODAL */
        .modal-bg { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center; }
        .modal-box { background: #fff; padding: 30px; border-radius: 8px; width: 400px; text-align: center; }

        @media (max-width: 768px) { 
            .layout-wrapper { grid-template-columns: 1fr; gap: 20px; } 
            .btn-group { grid-column: span 1; flex-direction: column-reverse; }
            .image-row { grid-template-columns: 1fr; }
        }
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
    <div style="max-width: 1100px; width: 95%; margin: 0 auto 20px;">
        <a href="products.php" style="color: var(--text-light); text-decoration: none; display:inline-flex; align-items:center; gap:5px;">
            <i class="fas fa-arrow-left"></i> Kembali ke Menu Produk
        </a>
    </div>

    <div class="add-container">
        <!-- HEADER KIRI -->
        <div class="add-header">
            <h2>Tambah Produk Baru</h2>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div style="background:#fde8e7; color:#c0392b; padding:15px; border-radius:6px; margin-bottom:25px; border: 1px solid #fcc;">
                <i class="fas fa-exclamation-circle"></i> <?= implode('<br>', $errors) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="layout-wrapper">
                
                <!-- KOLOM KIRI -->
                <div class="col-left">
                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" name="name" required placeholder="Contoh: Nastar Premium">
                    </div>

                    <div class="form-group">
                        <label>Kategori <a href="#" onclick="openCatModal()" style="font-size:0.8rem; color:var(--accent); float:right; text-decoration:none;">+ Buat Baru</a></label>
                        <select name="category" required>
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            <?php foreach ($cats as $c): ?><option value="<?= htmlspecialchars($c['name']) ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Jenis Produk</label>
                            <select name="type" id="typeSelect" onchange="togglePriceInputs()">
                                <option value="regular">Regular (Tetap)</option>
                                <option value="custom">Custom (Range)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <!-- Input Regular -->
                            <div id="priceRegular" class="price-section active" style="margin-top:0;">
                                <label>Harga (Rp)</label>
                                <input type="number" name="price" placeholder="0">
                            </div>
                            <!-- Input Custom (Min) -->
                            <div id="priceCustom1" class="price-section" style="margin-top:0;">
                                <label>Harga Min (Rp)</label>
                                <input type="number" name="price_min" placeholder="0">
                            </div>
                        </div>
                    </div>
                    
                    <div id="priceCustom2" class="form-group price-section">
                        <label>Harga Max (Rp)</label>
                        <input type="number" name="price_max" placeholder="0">
                    </div>
                </div>

                <!-- KOLOM KANAN -->
                <div class="col-right">
                    <div class="form-group">
                        <label>Gambar Produk (Pilih Salah Satu)</label>
                        
                        <!-- LAYOUT GAMBAR BERDAMPINGAN -->
                        <div class="image-row">
                            <!-- Sisi Kiri: Upload File -->
                            <div class="file-upload-box">
                                <label style="font-size:0.8rem; color:#666; margin-bottom:5px;">Upload File</label>
                                <input type="file" name="image_file" accept="image/*">
                            </div>
                            
                            <!-- Sisi Kanan: Input URL -->
                            <div class="url-input-box">
                                <label style="font-size:0.8rem; color:#666; margin-bottom:5px;">Atau URL Gambar</label>
                                <input type="text" name="image_url_text" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Bahan Utama (Opsional)</label>
                        <!-- TEXTAREA DIPERBESAR KE BAWAH -->
                        <textarea name="ingredients" rows="10" style="height: 111px;" placeholder="Contoh: Tepung protein rendah, mentega wisman..."></textarea>
                    </div>
                </div>

                <!-- FULL WIDTH BAWAH -->
                <div class="form-group" style="grid-column: span 2;">
                    <label>Deskripsi Lengkap</label>
                    <textarea name="description" rows="5" placeholder="Jelaskan rasa, tekstur, dan keunggulan produk ini..."></textarea>
                </div>

                <!-- TOMBOL -->
                <div class="btn-group">
                    <a href="products.php" class="btn-cancel">Batal</a>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Simpan Produk</button>
                </div>

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
        if (type === 'regular') {
            document.getElementById('priceRegular').style.display = 'block';
            document.getElementById('priceCustom1').style.display = 'none';
            document.getElementById('priceCustom2').style.display = 'none';
        } else {
            document.getElementById('priceRegular').style.display = 'none';
            document.getElementById('priceCustom1').style.display = 'block';
            document.getElementById('priceCustom2').style.display = 'block';
        }
    }
    function openCatModal() { document.getElementById('catModal').style.display = 'flex'; }
    function closeCatModal() { document.getElementById('catModal').style.display = 'none'; }
    window.onclick = (e) => { if(e.target == document.getElementById('catModal')) closeCatModal(); }
</script>
</body>
</html>