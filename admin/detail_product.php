<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

if (!isset($_GET['id'])) { die("ID produk tidak ditemukan."); }
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { die("Produk tidak ditemukan."); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - Admin</title>
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* === FIX GAP === */
        body { padding-top: 85px; background-color: var(--bg-cream); }
        .section { padding-top: 20px !important; }

        .detail-card {
            background: #fff; border-radius: 12px; box-shadow: 0 15px 40px rgba(0,0,0,0.05);
            border: 1px solid var(--line-color); overflow: hidden; 
            display: grid; 
            /* REVISI: Grid proporsional (1.2 bagian gambar : 2 bagian info) agar tidak terlalu lebar */
            grid-template-columns: 1.2fr 2fr; 
            min-height: 450px;
        }
        
        /* KOLOM KIRI: GAMBAR (PERBAIKAN SPACE) */
        .product-image-col {
            background-color: #fff; /* Ubah jadi putih agar seamless */
            display: flex; 
            align-items: flex-start; /* Gambar rata atas agar tidak ada gap bawah */
            justify-content: center;
            border-right: 1px solid var(--line-color); 
            padding: 30px; /* Padding diperkecil */
            position: relative;
        }
        
        .product-image-col img { 
            width: 100%; 
            height: auto; /* Tinggi otomatis mengikuti aspek rasio */
            max-height: 450px; /* Batas maksimal agar tidak terlalu panjang */
            object-fit: contain; /* Gambar utuh */
            border-radius: 8px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.08); 
            transition: transform 0.3s; 
        }
        .product-image-col img:hover { transform: scale(1.02); }
        .no-image { color: var(--text-light); font-size: 0.9rem; text-align: center; border: 2px dashed var(--line-color); padding: 40px; border-radius: 8px; width: 100%; }
        
        /* KOLOM KANAN: INFO */
        .product-info-col { padding: 35px; display: flex; flex-direction: column; }
        .product-meta-top { display: flex; gap: 10px; margin-bottom: 15px; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .badge-category { background: var(--text-dark); color: #fff; }
        .badge-type { border: 1px solid var(--accent); color: var(--accent); }
        
        .product-title { font-family: var(--font-heading); font-size: 2.2rem; line-height: 1.1; margin-bottom: 10px; color: var(--text-dark); }
        
        .product-price { font-size: 1.6rem; color: var(--accent); font-weight: 600; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--line-color); }
        
        /* GRID INFO (DESKRIPSI & BAHAN) */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* 2 Kolom Sejajar */
            gap: 30px;
            margin-bottom: 25px;
        }

        .info-section h4 { font-family: var(--font-body); font-size: 0.85rem; text-transform: uppercase; color: var(--text-light); margin-bottom: 6px; letter-spacing: 1px; font-weight: 700; }
        .info-section p { color: var(--text-dark); font-size: 0.95rem; line-height: 1.6; margin: 0; }
        
        /* TOMBOL AKSI */
        .action-bar { margin-top: 20px; display: flex; gap: 15px; margin-bottom: 20px; }
        .btn-back { padding: 12px 25px; border: 1px solid var(--line-color); color: var(--text-dark); text-decoration: none; font-weight: 500; transition: 0.3s; border-radius: 4px; }
        .btn-back:hover { background: var(--text-dark); color: #fff; }
        
        /* INFO TEKNIS TERSEMBUNYI (COLLAPSIBLE) */
        details.tech-data {
            margin-top: auto; /* Dorong ke paling bawah */
            padding-top: 15px;
            border-top: 1px dashed #eee;
            color: var(--text-light);
            font-size: 0.8rem;
        }
        details.tech-data summary {
            cursor: pointer;
            font-weight: 600;
            margin-bottom: 10px;
            outline: none;
        }
        .tech-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px; background: #fafafa; padding: 12px; border-radius: 6px;
        }
        .tech-item span { display: block; font-size: 0.7rem; color: #aaa; text-transform: uppercase; }
        .tech-item strong { color: #555; }

        @media (max-width: 900px) { 
            .detail-card { grid-template-columns: 1fr; } 
            .product-image-col { padding: 20px; border-right: none; border-bottom: 1px solid var(--line-color); } 
            .product-image-col img { max-height: 300px; width: auto; max-width: 100%; } 
            .info-grid { grid-template-columns: 1fr; } /* Stack di mobile */
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
            <a href="settings.php">Pengaturan</a>
            <a href="logout.php" style="color: #C0392B;">Keluar</a>
        </div>
    </nav>

    <div class="section">
        
        <div style="margin-bottom: 20px;">
            <a href="products.php" style="color: var(--text-light); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Kembali ke Menu Produk
            </a>
        </div>

        <div class="detail-card reveal active">
            
            <div class="product-image-col">
                <?php 
                    $imgSrc = $product['image_url'];
                    if (!empty($imgSrc) && !preg_match("~^(?:f|ht)tps?://~i", $imgSrc)) {
                        $imgSrc = "../" . $imgSrc;
                    }
                ?>
                <?php if (!empty($imgSrc)): ?>
                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php else: ?>
                    <div class="no-image"><i class="fas fa-image" style="font-size: 3rem; margin-bottom: 10px; display:block;"></i>Tidak ada gambar</div>
                <?php endif; ?>
            </div>

            <div class="product-info-col">
                <div class="product-meta-top">
                    <span class="badge badge-category"><?= htmlspecialchars($product['category']) ?></span>
                    <span class="badge badge-type"><?= ucfirst($product['type']) ?></span>
                </div>

                <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>

                <div class="product-price">
                    <?php if ($product['type'] === 'custom' || $product['price'] == 0): ?>
                        Rp <?= number_format($product['price_min']) ?> – Rp <?= number_format($product['price_max']) ?>
                    <?php else: ?>
                        Rp <?= number_format($product['price']) ?>
                    <?php endif; ?>
                </div>

                <div class="info-grid">
                    <div class="info-section">
                        <h4>Deskripsi Produk</h4>
                        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>

                    <?php if(!empty($product['ingredients'])): ?>
                    <div class="info-section">
                        <h4>Bahan Utama</h4>
                        <p><?= nl2br(htmlspecialchars($product['ingredients'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="action-bar">
                    <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn-primary" style="border-radius:4px;">
                        <i class="fas fa-edit"></i> Edit Produk
                    </a>
                    <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn-back" onclick="return confirm('Hapus produk ini?')" style="border-color: #c0392b; color: #c0392b;">
                        <i class="fas fa-trash"></i> Hapus
                    </a>
                </div>

                <details class="tech-data">
                    <summary>Info Teknis & System (Klik untuk lihat)</summary>
                    <div class="tech-grid">
                        <div class="tech-item"><span>ID Produk</span><strong>#<?= $product['id'] ?></strong></div>
                        <div class="tech-item"><span>Slug URL</span><strong><?= htmlspecialchars($product['slug']) ?></strong></div>
                        <div class="tech-item"><span>Dibuat</span><strong><?= date('d M Y, H:i', strtotime($product['created_at'])) ?></strong></div>
                        <div class="tech-item"><span>Diupdate</span><strong><?= date('d M Y, H:i', strtotime($product['updated_at'])) ?></strong></div>
                    </div>
                </details>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(() => { document.querySelector('.reveal').classList.add('active'); }, 100);
        });
    </script>
</body>
</html>