<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

if (!isset($_GET['id'])) { die("ID produk tidak ditemukan."); }
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { die("Produk tidak ditemukan."); }

// Ambil daftar kategori
$cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. TAMBAH KATEGORI
    if (isset($_POST['new_category_name'])) {
        $newCat = trim($_POST['new_category_name']);
        if ($newCat) {
            $pdo->prepare("INSERT IGNORE INTO categories (name) VALUES (?)")->execute([$newCat]);
            header("Location: edit_product.php?id=" . $id); exit;
        }
    }

    $name        = $_POST['name'];
    $category    = $_POST['category'];
    $type        = $_POST['type'];
    $price       = $_POST['price'] ?: 0;
    $price_min   = $_POST['price_min'] ?: 0;
    $price_max   = $_POST['price_max'] ?: 0;
    $ingredients = $_POST['ingredients'];
    $description = $_POST['description'];
    
    // 2. UPDATE GAMBAR
    $image_url = $product['image_url']; 
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newName = uniqid('img_', true) . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/';
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $newName)) {
                if (!empty($product['image_url']) && file_exists(__DIR__ . '/../' . $product['image_url'])) {
                    unlink(__DIR__ . '/../' . $product['image_url']);
                }
                $image_url = 'uploads/' . $newName;
            }
        }
    }

    if ($type == 'regular') { $price_min = 0; $price_max = 0; } else { $price = 0; }

    $update = $pdo->prepare("UPDATE products SET name=?, category=?, type=?, price=?, price_min=?, price_max=?, ingredients=?, description=?, image_url=? WHERE id=?");
    $update->execute([$name, $category, $type, $price, $price_min, $price_max, $ingredients, $description, $image_url, $id]);

    header("Location: products.php?updated=1");
    exit;
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
        .edit-container { max-width: 1200px; width: 95%; margin: 0 auto 50px; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 15px 40px rgba(0,0,0,0.05); border: 1px solid var(--line-color); }
        .edit-header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid var(--line-color); padding-bottom: 20px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .full-width { grid-column: span 2; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 0.9rem; }
        input, select, textarea { width: 100%; padding: 12px 15px; border: 1px solid var(--line-color); border-radius: 6px; font-family: var(--font-body); background: #FAFAFA; }
        .price-section { display: none; }
        .price-section.active { display: block; animation: fadeIn 0.3s ease; }
        .btn-group { grid-column: span 2; margin-top: 20px; display: flex; gap: 15px; }
        .btn-save { flex: 2; text-align: center; cursor: pointer; background-color: var(--accent); color: #fff; border: none; padding: 14px; font-weight: 600; }
        .btn-cancel { flex: 1; text-align: center; border: 1px solid var(--line-color); padding: 14px; text-decoration: none; color: var(--text-light); font-weight: 600; }
        
        .modal-bg { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center; }
        .modal-box { background: #fff; padding: 30px; border-radius: 8px; width: 400px; text-align: center; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
        
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

        <div class="edit-container">
            <div class="edit-header">
                <h2>Edit Produk</h2>
                <p>Perbarui informasi produk: <strong><?= htmlspecialchars($product['name']) ?></strong></p>
            </div>

            <form method="POST" enctype="multipart/form-data" class="form-grid">
                
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($product['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Ganti Gambar (Kosongkan jika tidak ubah)</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <?php if (!empty($product['image_url'])): ?>
                            <img src="../<?= htmlspecialchars($product['image_url']) ?>" style="height:45px; border-radius:4px; border:1px solid #ddd;">
                        <?php endif; ?>
                        <input type="file" name="image_file" accept="image/*" style="padding:10px; background:#fff;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Kategori <a href="#" onclick="openCatModal()" style="font-size:0.8rem; color:var(--accent); float:right;">+ Tambah Baru</a></label>
                    <select name="category" required>
                        <?php foreach ($cats as $c): ?>
                            <option value="<?= htmlspecialchars($c['name']) ?>" <?= $product['category'] == $c['name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jenis Produk</label>
                    <select name="type" id="typeSelect" onchange="togglePriceInputs()">
                        <option value="regular" <?= $product['type']=='regular'?'selected':''; ?>>Regular (Harga Tetap)</option>
                        <option value="custom"  <?= $product['type']=='custom'?'selected':''; ?>>Custom (Range Harga)</option>
                    </select>
                </div>

                <div id="priceRegular" class="form-group full-width price-section">
                    <label>Harga Satuan (Rp)</label>
                    <input type="number" name="price" value="<?= $product['price']; ?>">
                </div>
                <div id="priceCustom1" class="form-group price-section">
                    <label>Harga Minimum (Rp)</label>
                    <input type="number" name="price_min" value="<?= $product['price_min']; ?>">
                </div>
                <div id="priceCustom2" class="form-group price-section">
                    <label>Harga Maksimum (Rp)</label>
                    <input type="number" name="price_max" value="<?= $product['price_max']; ?>">
                </div>

                <div class="form-group">
                    <label>Bahan Utama</label>
                    <textarea name="ingredients" rows="6"><?= htmlspecialchars($product['ingredients']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Deskripsi Lengkap</label>
                    <textarea name="description" rows="6"><?= htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="btn-group">
                    <a href="products.php" class="btn-cancel">Batal</a>
                    <button type="submit" class="btn-save">Simpan Perubahan</button>
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