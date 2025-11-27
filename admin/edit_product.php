<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

if (!isset($_GET['id'])) { die("ID produk tidak ditemukan."); }
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { die("Produk tidak ditemukan."); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $_POST['name'];
    $category    = $_POST['category'];
    $type        = $_POST['type'];
    $price       = $_POST['price'] ?: 0;
    $price_min   = $_POST['price_min'] ?: 0;
    $price_max   = $_POST['price_max'] ?: 0;
    $ingredients = $_POST['ingredients'];
    $description = $_POST['description'];
    $image_url   = $_POST['image_url'];

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Admin</title>
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body { padding-top: 85px; background-color: var(--bg-cream); }
        .section { padding-top: 20px !important; }

        /* === PERBAIKAN LEBAR FORM === */
        .edit-container {
            max-width: 1200px; /* Diperlebar dari 800px ke 1200px */
            width: 95%;        /* Agar responsif di layar yang lebih kecil dari 1200px */
            margin: 0 auto 50px; 
            background: #fff; 
            padding: 40px; 
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.05); 
            border: 1px solid var(--line-color);
        }

        .edit-header { text-align: center; margin-bottom: 30px; border-bottom: 1px solid var(--line-color); padding-bottom: 20px; }
        .edit-header h2 { font-size: 2rem; color: var(--text-dark); }
        .edit-header p { color: var(--text-light); }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; } /* Gap antar kolom diperbesar sedikit */
        .form-group { margin-bottom: 5px; }
        .full-width { grid-column: span 2; }
        
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 0.9rem; }
        input[type="text"], input[type="number"], select, textarea {
            width: 100%; padding: 12px 15px; border: 1px solid var(--line-color); border-radius: 6px;
            font-family: var(--font-body); font-size: 1rem; transition: border-color 0.3s; background: #FAFAFA;
        }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--accent); background: #fff; }
        textarea { resize: vertical; min-height: 100px; }
        
        .btn-group { grid-column: span 2; margin-top: 20px; display: flex; gap: 15px; }
        .btn-cancel {
            padding: 14px 25px; border: 1px solid var(--line-color); color: var(--text-light); text-decoration: none;
            border-radius: 0; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; text-align: center; flex: 1;
        }
        .btn-cancel:hover { background: #eee; color: var(--text-dark); }
        .btn-save {
            flex: 2; text-align: center; cursor: pointer; background-color: var(--accent); border: 1px solid var(--accent);
            color: #fff; transition: all 0.3s ease; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;
        }
        .btn-save:hover { background-color: #c86445 !important; border-color: #c86445 !important; color: #fff !important; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(200, 100, 69, 0.3); }
        
        .price-section { display: none; }
        .price-section.active { display: block; animation: fadeIn 0.3s ease; }
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

            <form method="POST" class="form-grid">
                <div class="form-group full-width">
                    <label>Nama Produk</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($product['name']); ?>" required placeholder="Contoh: Nastar Premium">
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="category" value="<?= htmlspecialchars($product['category']); ?>" list="catList">
                    <datalist id="catList">
                        <option value="Kue Kering"><option value="Kue & Bolu"><option value="Ulang Tahun Anak"><option value="Pernikahan">
                    </datalist>
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
                    <input type="number" name="price" value="<?= $product['price']; ?>" placeholder="0">
                </div>
                <div id="priceCustom1" class="form-group price-section">
                    <label>Harga Minimum (Rp)</label>
                    <input type="number" name="price_min" value="<?= $product['price_min']; ?>" placeholder="0">
                </div>
                <div id="priceCustom2" class="form-group price-section">
                    <label>Harga Maksimum (Rp)</label>
                    <input type="number" name="price_max" value="<?= $product['price_max']; ?>" placeholder="0">
                </div>
                <div class="form-group full-width">
                    <label>URL Gambar</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="image_url" id="imgInput" value="<?= htmlspecialchars($product['image_url']); ?>" placeholder="https://...">
                        <button type="button" onclick="previewImage()" style="padding: 0 15px; border: 1px solid var(--line-color); background: #fff; cursor: pointer;"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <div class="form-group full-width">
                    <label>Bahan Utama</label>
                    <textarea name="ingredients" rows="3" placeholder="Tepung, Telur, Mentega..."><?= htmlspecialchars($product['ingredients']); ?></textarea>
                </div>
                <div class="form-group full-width">
                    <label>Deskripsi Lengkap</label>
                    <textarea name="description" rows="5"><?= htmlspecialchars($product['description']); ?></textarea>
                </div>
                <div class="btn-group">
                    <a href="products.php" class="btn-cancel">Batal</a>
                    <button type="submit" class="btn-primary btn-save">Simpan Perubahan</button>
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
            const regularGroup = document.getElementById('priceRegular');
            const customGroup1 = document.getElementById('priceCustom1');
            const customGroup2 = document.getElementById('priceCustom2');
            if (type === 'regular') {
                regularGroup.classList.add('active'); customGroup1.classList.remove('active'); customGroup2.classList.remove('active');
            } else {
                regularGroup.classList.remove('active'); customGroup1.classList.add('active'); customGroup2.classList.add('active');
            }
        }
        function previewImage() {
            const url = document.getElementById('imgInput').value;
            if(url) { window.open(url, '_blank'); } else { alert("URL gambar kosong"); }
        }
    </script>
</body>
</html>