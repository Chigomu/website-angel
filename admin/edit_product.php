<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

if (!isset($_GET['id'])) die("ID tidak ditemukan.");
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) die("Produk tidak ditemukan.");

$cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_category_name']) && !empty(trim($_POST['new_category_name']))) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->execute([trim($_POST['new_category_name'])]);
        header("Location: edit_product.php?id=$id"); exit;
    }

    $name = $_POST['name']; $category = $_POST['category']; $type = $_POST['type'];
    $price = $_POST['price'] ?: 0; $price_min = $_POST['price_min'] ?: 0; $price_max = $_POST['price_max'] ?: 0;
    $ingredients = $_POST['ingredients']; $description = $_POST['description'];

    $final_image = $product['image_url']; 
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $newName = uniqid('prod_', true) . '.' . $ext;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $newName)) {
                if (!empty($product['image_url']) && file_exists(__DIR__ . '/../' . $product['image_url'])) unlink(__DIR__ . '/../' . $product['image_url']);
                $final_image = 'uploads/' . $newName;
            }
        }
    } elseif (!empty($_POST['image_url_text']) && $_POST['image_url_text'] !== $product['image_url']) {
        $final_image = trim($_POST['image_url_text']);
    }

    if ($type == 'regular') { $price_min = 0; $price_max = 0; } else { $price = 0; }

    $stmt = $pdo->prepare("UPDATE products SET name=?, category=?, type=?, price=?, price_min=?, price_max=?, ingredients=?, description=?, image_url=? WHERE id=?");
    $stmt->execute([$name, $category, $type, $price, $price_min, $price_max, $ingredients, $description, $final_image, $id]);
    header("Location: products.php?updated=1"); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Produk - Admin</title>
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding-top: 85px; background-color: var(--bg-cream); }
        .section { padding-top: 20px !important; }
        .edit-container { max-width: 1200px; width: 95%; margin: 0 auto 50px; background: #fff; padding: 40px; border-radius: 12px; border: 1px solid var(--line-color); }
        .edit-header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid var(--line-color); padding-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .form-group { margin-bottom: 5px; }
        .full-width { grid-column: span 2; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 0.9rem; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid var(--line-color); border-radius: 6px; background: #FAFAFA; }
        .price-section { display: none; }
        .price-section.active { display: block; }
        .img-tabs { display: flex; gap: 10px; margin-bottom: 10px; }
        .img-tab-btn { padding: 5px 15px; border: 1px solid #ddd; background: #f9f9f9; cursor: pointer; font-size: 0.8rem; border-radius: 4px; }
        .img-tab-btn.active { background: var(--accent); color: #fff; border-color: var(--accent); }
        .img-input-group { display: none; }
        .img-input-group.active { display: block; }
        .btn-save { width: 100%; padding: 14px; background: var(--accent); color: #fff; border: none; font-weight: 600; cursor: pointer; }
        .btn-cancel { display: block; width: 100%; padding: 14px; text-align: center; border: 1px solid var(--line-color); color: var(--text-light); text-decoration: none; font-weight: 600; }
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
        <a href="settings.php">Pengaturan</a> <a href="logout.php" style="color: #C0392B;">Keluar</a>
    </div>
</nav>

<div class="section reveal active">
    <div style="max-width: 1200px; width: 95%; margin: 0 auto 20px;">
        <a href="products.php" style="color: var(--text-light); text-decoration: none;">&larr; Kembali ke Menu Produk</a>
    </div>

    <div class="edit-container">
        <div class="edit-header">
            <h2>Edit Produk</h2>
            <p>Perbarui: <strong><?= htmlspecialchars($product['name']) ?></strong></p>
        </div>

        <form method="POST" enctype="multipart/form-data" class="form-grid">
            
            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Gambar Produk</label>
                <?php if (!empty($product['image_url'])): ?>
                    <div style="display:flex; align-items:center; margin-bottom:10px;">
                        <?php $imgSrc = (!preg_match("~^(?:f|ht)tps?://~i", $product['image_url'])) ? "../" . $product['image_url'] : $product['image_url']; ?>
                        <img src="<?= $imgSrc ?>" style="height:50px; border-radius:4px; margin-right:10px; border:1px solid #ddd;">
                        <small style="color:#888;">Gambar saat ini</small>
                    </div>
                <?php endif; ?>
                <div class="img-tabs">
                    <button type="button" class="img-tab-btn active" onclick="switchImgTab('upload')">Ganti File</button>
                    <button type="button" class="img-tab-btn" onclick="switchImgTab('url')">Ganti URL</button>
                </div>
                <div id="tab-upload" class="img-input-group active">
                    <input type="file" name="image_file" accept="image/*" style="background:#fff;">
                </div>
                <div id="tab-url" class="img-input-group">
                    <input type="text" name="image_url_text" placeholder="https://..." value="<?= filter_var($product['image_url'], FILTER_VALIDATE_URL) ? $product['image_url'] : '' ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Kategori <a href="#" onclick="openCatModal()" style="font-size:0.8rem; color:var(--accent); float:right;">+ Baru</a></label>
                <select name="category" required>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= htmlspecialchars($c['name']) ?>" <?= $product['category'] == $c['name'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Jenis Produk</label>
                <select name="type" id="typeSelect" onchange="togglePriceInputs()">
                    <option value="regular" <?= $product['type']=='regular'?'selected':'' ?>>Regular</option>
                    <option value="custom"  <?= $product['type']=='custom'?'selected':'' ?>>Custom</option>
                </select>
            </div>

            <div id="priceRegular" class="form-group full-width price-section">
                <label>Harga (Rp)</label>
                <input type="number" name="price" value="<?= $product['price'] ?>">
            </div>
            <div id="priceCustom1" class="form-group price-section">
                <label>Min (Rp)</label>
                <input type="number" name="price_min" value="<?= $product['price_min'] ?>">
            </div>
            <div id="priceCustom2" class="form-group price-section">
                <label>Max (Rp)</label>
                <input type="number" name="price_max" value="<?= $product['price_max'] ?>">
            </div>

            <div class="form-group">
                <label>Bahan Utama</label>
                <textarea name="ingredients" rows="5"><?= htmlspecialchars($product['ingredients']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Deskripsi Lengkap</label>
                <textarea name="description" rows="5"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="form-group"><a href="products.php" class="btn-cancel">Batal</a></div>
            <div class="form-group"><button type="submit" class="btn-save">Simpan Perubahan</button></div>
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
            document.getElementById('priceCustom1').style.display = 'none';
            document.getElementById('priceCustom2').style.display = 'none';
        } else {
            document.getElementById('priceRegular').style.display = 'none';
            document.getElementById('priceCustom1').style.display = 'block';
            document.getElementById('priceCustom2').style.display = 'block';
        }
    }
    function switchImgTab(mode) {
        document.querySelectorAll('.img-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.img-input-group').forEach(g => g.classList.remove('active'));
        if(mode === 'upload') {
            document.querySelectorAll('.img-tab-btn')[0].classList.add('active');
            document.getElementById('tab-upload').classList.add('active');
            document.querySelector('input[name="image_url_text"]').value = ''; 
        } else {
            document.querySelectorAll('.img-tab-btn')[1].classList.add('active');
            document.getElementById('tab-url').classList.add('active');
            document.querySelector('input[name="image_file"]').value = ''; 
        }
    }
    function openCatModal() { document.getElementById('catModal').style.display = 'flex'; }
    function closeCatModal() { document.getElementById('catModal').style.display = 'none'; }
</script>
</body>
</html>