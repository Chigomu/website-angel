<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

if (!isset($_GET['id'])) {
    die("ID produk tidak ditemukan.");
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("Produk tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - Admin</title>
    
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* === DETAIL PAGE STYLES === */
        body { padding-top: 100px; background-color: var(--bg-cream); }

        .detail-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.05);
            border: 1px solid var(--line-color);
            overflow: hidden;
            display: grid;
            grid-template-columns: 400px 1fr; /* Layout Kiri Gambar, Kanan Teks */
            min-height: 500px;
        }

        /* Kolom Gambar */
        .product-image-col {
            background-color: #fafafa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-right: 1px solid var(--line-color);
            padding: 40px;
            position: relative;
        }

        .product-image-col img {
            width: 100%;
            max-height: 400px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .product-image-col img:hover { transform: scale(1.02); }

        .no-image {
            color: var(--text-light);
            font-size: 0.9rem;
            text-align: center;
            border: 2px dashed var(--line-color);
            padding: 40px;
            border-radius: 8px;
        }

        /* Kolom Info */
        .product-info-col {
            padding: 40px;
            display: flex;
            flex-direction: column;
        }

        /* Header Info */
        .product-meta-top {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .badge-category { background: var(--text-dark); color: #fff; }
        .badge-type { border: 1px solid var(--accent); color: var(--accent); }

        .product-title {
            font-family: var(--font-heading);
            font-size: 2.5rem;
            line-height: 1.1;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .product-price {
            font-size: 1.8rem;
            color: var(--accent);
            font-weight: 600;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--line-color);
        }

        /* Deskripsi & Bahan */
        .info-section { margin-bottom: 25px; }
        .info-section h4 {
            font-family: var(--font-body);
            font-size: 0.9rem;
            text-transform: uppercase;
            color: var(--text-light);
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .info-section p { color: var(--text-dark); font-size: 1rem; line-height: 1.7; }

        /* Tabel Data Teknis (Kecil di bawah) */
        .tech-data {
            margin-top: auto; /* Dorong ke bawah */
            background: #fdfdfd;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #eee;
            font-size: 0.85rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .tech-item span { display: block; color: var(--text-light); font-size: 0.75rem; margin-bottom: 2px; }
        .tech-item strong { color: var(--text-dark); }

        /* Tombol Aksi */
        .action-bar {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }

        .btn-back {
            padding: 12px 25px;
            border: 1px solid var(--line-color);
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            transition: 0.3s;
        }
        .btn-back:hover { background: var(--text-dark); color: #fff; }

        /* Responsive Mobile */
        @media (max-width: 900px) {
            .detail-card { grid-template-columns: 1fr; }
            .product-image-col { padding: 20px; }
            .product-image-col img { max-height: 300px; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="../index.php" class="logo">Ibu Angel</a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php" style="color: #C0392B;">Logout</a>
        </div>
    </nav>

    <div class="section">
        
        <div style="margin-bottom: 20px;">
            <a href="dashboard.php" style="color: var(--text-light); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <div class="detail-card reveal active">
            
            <div class="product-image-col">
                <?php if (!empty($product['image_url'])): ?>
                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <?php else: ?>
                    <div class="no-image">
                        <i class="fas fa-image" style="font-size: 3rem; margin-bottom: 10px; display:block;"></i>
                        Tidak ada gambar
                    </div>
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

                <div class="tech-data">
                    <div class="tech-item">
                        <span>ID Produk</span>
                        <strong>#<?= $product['id'] ?></strong>
                    </div>
                    <div class="tech-item">
                        <span>Slug URL</span>
                        <strong><?= htmlspecialchars($product['slug']) ?></strong>
                    </div>
                    <div class="tech-item">
                        <span>Tanggal Dibuat</span>
                        <strong><?= date('d M Y, H:i', strtotime($product['created_at'])) ?></strong>
                    </div>
                    <div class="tech-item">
                        <span>Terakhir Update</span>
                        <strong><?= date('d M Y, H:i', strtotime($product['updated_at'])) ?></strong>
                    </div>
                </div>

                <div class="action-bar">
                    <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn-primary">
                        <i class="fas fa-edit"></i> Edit Produk
                    </a>
                    <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn-back" onclick="return confirm('Hapus produk ini?')" style="border-color: #c0392b; color: #c0392b;">
                        <i class="fas fa-trash"></i> Hapus
                    </a>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Animasi Masuk
        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(() => {
                document.querySelector('.reveal').classList.add('active');
            }, 100);
        });
    </script>
</body>
</html>