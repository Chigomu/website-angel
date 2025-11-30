<?php
session_start();
require_once 'app/db.php'; 
require_once 'app/settings_loader.php';

// === LOGIKA PAGINATION & FILTER REGULER ===
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; 
$offset = ($page - 1) * $limit;
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

try {
    $stmt_cat = $pdo->query("SELECT DISTINCT category FROM products WHERE type = 'regular' ORDER BY category ASC");
    $categories = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);

    $sql_count = "SELECT COUNT(*) FROM products WHERE type = 'regular'";
    if ($category_filter && $category_filter !== 'all') $sql_count .= " AND category = :cat";
    $stmt_count = $pdo->prepare($sql_count);
    if ($category_filter && $category_filter !== 'all') $stmt_count->bindValue(':cat', $category_filter);
    $stmt_count->execute();
    $total_items = $stmt_count->fetchColumn();
    $total_pages = ceil($total_items / $limit);

    $sql_products = "SELECT * FROM products WHERE type = 'regular'";
    if ($category_filter && $category_filter !== 'all') $sql_products .= " AND category = :cat";
    $sql_products .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql_products);
    if ($category_filter && $category_filter !== 'all') $stmt->bindValue(':cat', $category_filter);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) { $products = []; $categories = []; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ibu Angel | Artisan Cookies & Cakes</title>
  <link rel="stylesheet" href="style.css">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <?php require_once 'app/dynamic_style.php'; ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <style>
    /* === PERBAIKAN UKURAN KARTU & POSISI TOMBOL === */
    
    .product-list { 
        gap: 15px !important; 
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)) !important; 
        align-items: stretch !important; 
    }

    .product-card {
        display: flex !important;
        flex-direction: column !important;
        height: 100% !important;
    }

    .product-card .img-wrapper { 
        height: 160px !important; 
        flex-shrink: 0; 
    }

    .info-wrapper {
        display: flex !important;
        flex-direction: column !important;
        flex: 1 !important; 
        padding: 12px !important; 
        text-align: left !important;
    }

    .product-card h3 { 
        font-size: 1.1rem !important; 
        margin-bottom: 3px !important; 
        white-space: nowrap; 
        overflow: hidden; 
        text-overflow: ellipsis; 
    }
    
    .product-card p { 
        font-size: 0.8rem !important; 
        color: #888; 
        margin-bottom: 8px !important; 
        min-height: 0 !important; 
        line-height: 1.3; 
    }

    .product-card .card-footer {
        margin-top: auto !important; 
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .price-row { 
        font-weight: 700; 
        color: var(--accent); 
        font-size: 1rem; 
        margin-bottom: 2px;
    }

    /* === UPDATE: TOMBOL TERPISAH (Sesuai Request) === */
    
    /* Wrapper Utama Tombol */
    .action-wrapper {
        position: relative;
        height: 36px;
        width: 100%;
    }

    /* 1. Tombol Awal "Tambah" */
    .btn-initial-add {
        width: 100%; height: 100%;
        background: #fff;
        border: 1px solid var(--accent);
        color: var(--accent);
        border-radius: 20px; 
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        font-size: 0.9rem;
    }
    .btn-initial-add:hover {
        background: var(--accent); color: #fff;
        transform: translateY(-2px);
    }

    /* 2. Wrapper Aktif (Berisi Tombol Sampah + Kontrol Qty) */
    .qty-active-wrapper {
        width: 100%; height: 100%;
        display: none; /* Hidden default */
        align-items: center;
        gap: 8px; /* Jarak antara sampah dan qty */
    }
    .qty-active-wrapper.active { display: flex; }

    /* Tombol Sampah (Kiri) */
    .btn-trash-separate {
        width: 36px; height: 36px;
        background: #ffebee; /* Merah sangat muda */
        border: 1px solid #ffcdd2;
        color: #c62828; /* Merah */
        border-radius: 50%;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: 0.2s;
        flex-shrink: 0;
    }
    .btn-trash-separate:hover {
        background: #c62828; color: #fff; border-color: #c62828;
    }

    /* Grup Kontrol Kuantitas (Kanan - Mengisi sisa ruang) */
    .qty-control-group {
        flex: 1; /* Mengisi sisa lebar */
        height: 100%;
        background: var(--accent);
        border-radius: 20px;
        display: flex; 
        align-items: center; justify-content: space-between;
        padding: 0 4px;
    }

    .qty-btn {
        width: 28px; height: 28px;
        background: rgba(255,255,255,0.2);
        border: none; border-radius: 50%;
        color: white; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; 
        flex-shrink: 0;
    }
    .qty-btn:hover { background: rgba(255,255,255,0.4); }
    /* Disabled state untuk minus jika qty 1 */
    .qty-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    .qty-display {
        flex: 1; 
        width: 100%;
        background: transparent; 
        border: none;
        color: white; 
        text-align: center; 
        font-weight: 700; 
        font-size: 1rem;
        pointer-events: auto !important;
        -moz-appearance: textfield; 
    }
    .qty-display::-webkit-outer-spin-button,
    .qty-display::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .qty-display:focus { outline: none; }

    /* CSS Warisan */
    .nav-links a.active { color: var(--accent) !important; font-weight: 700; }
    .nav-links a.active::after { width: 100%; }
    .hero { min-height: auto !important; height: auto !important; padding-top: 160px !important; padding-bottom: 80px !important; display: flex; align-items: center; justify-content: center; }
    .marquee-container { padding: 15px 0 !important; background-color: var(--text-dark) !important; color: var(--bg-cream) !important; border-top: 2px solid var(--accent); border-bottom: 2px solid var(--accent); position: relative; z-index: 10; margin-bottom: 0 !important; }
    .marquee-content span { padding: 0 40px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; font-size: 0.95rem; }
    .section { padding: 30px 20px !important; }
    .section-header { margin-bottom: 20px !important; }
    .section-header h2 { margin-bottom: 5px !important; font-size: 2.5rem; }
    .about-container { gap: 30px !important; align-items: start !important; }
    .feature-list { list-style: none; padding: 0; margin-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .feature-list li { display: flex; align-items: center; gap: 10px; color: var(--text-dark); font-weight: 500; font-size: 0.95rem; }
    .feature-list li i { color: var(--accent); }
    .filter-container { display: flex; justify-content: center; flex-wrap: wrap; gap: 10px; margin-bottom: 25px; }
    .filter-btn { background: transparent; border: 1px solid var(--accent); color: var(--accent); padding: 8px 20px; border-radius: 20px; text-decoration: none; font-weight: 600; transition: 0.3s; font-size: 0.9rem; display: inline-block; }
    .filter-btn:hover, .filter-btn.active { background: var(--accent); color: #fff; }
    
    .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 40px; }
    .page-link { display: flex; align-items: center; justify-content: center; width: 35px; height: 35px; border: 1px solid var(--line-color); border-radius: 4px; text-decoration: none; color: var(--text-dark); font-weight: 600; transition: 0.3s; }
    .page-link:hover, .page-link.active { background: var(--accent); color: white; border-color: var(--accent); }
    .nav-links, .nav-links li { list-style: none !important; padding: 0; margin: 0; }
    .marquee-content { display: inline-block; white-space: nowrap; animation: scroll-seamless 40s linear infinite; }
    @keyframes scroll-seamless { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    .custom-banner { padding: 40px 20px !important; margin-top: 20px !important; margin-bottom: 0 !important; }
    .custom-banner .product-list { gap: 20px !important; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)) !important; }
    .custom-product { position: relative; overflow: hidden; cursor: pointer; }
    .custom-product .img-wrapper { height: 180px !important; position: relative; } 
    .custom-product .img-wrapper img { transition: transform 0.5s ease; width: 100%; height: 100%; object-fit: cover; }
    .hover-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(44, 24, 16, 0.7); display: flex; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s ease; }
    .hover-btn { background: var(--accent); color: #fff; padding: 10px 20px; border-radius: 30px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem; transform: translateY(20px); transition: transform 0.3s ease; }
    .custom-product:hover .hover-overlay { opacity: 1; }
    .custom-product:hover .hover-btn { transform: translateY(0); }
    .custom-product:hover .img-wrapper img { transform: scale(1.1); }
    .custom-product .info-wrapper { padding: 15px !important; text-align: left !important; background: #fff; }
    .custom-product .info-wrapper h3 { margin-bottom: 5px !important; font-size: 1.2rem !important; }
    .custom-product .info-wrapper p { margin-bottom: 10px !important; min-height: 0 !important; line-height: 1.4; font-size: 0.9rem; color: #888; }
    .custom-product .info-wrapper .price { margin-top: 0 !important; display: block; font-weight: 700; font-size: 1rem; color: var(--accent); }
    .cta-section { background: var(--bg-cream); text-align: center; padding: 50px 20px !important; margin-top: 30px !important; border-top: 1px solid var(--line-color); margin-bottom: 0 !important; }
    .cta-section .btn-primary:hover, #addCustomToCart:hover { background-color: #c86445 !important; border-color: #c86445 !important; color: #fff !important; transform: translateY(-2px); }
    #lokasi { margin-top: 40px !important; padding-top: 0 !important; }
    footer { padding: 40px 20px 20px !important; margin-top: 0 !important; }
    @media (max-width: 768px) { 
        .hero { padding-top: 120px !important; }
        .product-list { grid-template-columns: repeat(2, 1fr) !important; gap: 10px !important; }
        .feature-list { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<header>
  <nav class="navbar" id="navbar">
    <div class="logo">Ibu Angel</div>
    <ul class="nav-links">
      <li><a href="#home" class="nav-link">Beranda</a></li>
      <li><a href="#about" class="nav-link">Tentang</a></li>
      <li><a href="#produk" class="nav-link">Menu</a></li>
      <li><a href="#custom" class="nav-link">Custom</a></li>
      <li><a href="#lokasi" class="nav-link">Kontak</a></li>
      <li><a href="cart.php" style="font-size: 1.2rem;"><i class="fas fa-shopping-cart"></i> <span id="cart-badge" style="font-size: 0.8rem; vertical-align: top;"></span></a></li> 
    </ul>
  </nav>
</header>

  <section id="home" class="hero section-scroll">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content reveal">
      <h1><?= set('hero_title') ?></h1> 
      <p><?= set('hero_desc') ?></p>
      <a href="#produk" class="btn-primary">Lihat Menu Kami</a>
    </div>
  </section>

  <div class="marquee-container">
    <div class="marquee-content">
      <span><?= set('marquee_text') ?></span> • <span><?= set('marquee_text') ?></span> • <span><?= set('marquee_text') ?></span> • <span><?= set('marquee_text') ?></span> • 
      <span><?= set('marquee_text') ?></span> • <span><?= set('marquee_text') ?></span> • <span><?= set('marquee_text') ?></span> • <span><?= set('marquee_text') ?></span> • 
    </div>
  </div>

  <section id="about" class="section section-scroll">
    <div class="about-container">
      <div class="about-text reveal">
        <h3>Cerita Kami</h3>
        <h2><?= set('about_title') ?></h2>
        <p><?= nl2br(set('about_desc')) ?></p>
        <p style="margin-top: 15px; font-weight: 500;">
            Kami berkomitmen menghadirkan cita rasa autentik yang memanjakan lidah. Setiap kue dibuat dengan ketelitian tinggi, memastikan tekstur yang lembut dan rasa yang pas.
        </p>
        <ul class="feature-list">
            <li><i class="fas fa-check-circle"></i> Bahan Premium Pilihan</li>
            <li><i class="fas fa-check-circle"></i> Tanpa Pengawet Buatan</li>
            <li><i class="fas fa-check-circle"></i> 100% Halal & Higienis</li>
            <li><i class="fas fa-check-circle"></i> Fresh from the Oven</li>
        </ul>
      </div>
      <div class="about-img reveal">
        <img src="<?= set('about_img') ?>" alt="Dapur Ibu Angel" onerror="this.src='https://images.unsplash.com/photo-1556910103-1c02745a30bf?w=800&q=80'">
      </div>
    </div>
  </section>

  <section id="produk" class="section section-scroll">
    <div class="section-header reveal">
      <h2>Pilihan Menu</h2>
      <p>Pilih kue favoritmu, langsung dari dapur kami.</p>
    </div>
    
    <div class="filter-container reveal">
        <a href="?category=all#produk" class="filter-btn <?= (!$category_filter || $category_filter == 'all') ? 'active' : '' ?>">Semua</a>
        <?php foreach($categories as $cat): ?>
            <a href="?category=<?= urlencode($cat) ?>#produk" class="filter-btn <?= ($category_filter == $cat) ? 'active' : '' ?>">
                <?= htmlspecialchars($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="product-list">
      <?php if(!empty($products)): ?>
        <?php foreach($products as $p): ?>
          <div class="product-card reveal item-card" id="card-<?= $p['id'] ?>">
            <div class="img-wrapper">
                <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" onerror="this.src='https://placehold.co/400x400?text=No+Image'">
            </div>
            <div class="info-wrapper">
              <h3><?= htmlspecialchars($p['name']) ?></h3>
              <p><?= htmlspecialchars(substr($p['description'], 0, 50)) ?></p>
              
              <div class="card-footer">
                  <div class="price-row">
                      Rp <?= number_format($p['price'], 0, ',', '.') ?>
                  </div>
                  
                  <div class="action-wrapper" 
                       data-id="<?= $p['id'] ?>" 
                       data-name="<?= htmlspecialchars($p['name']) ?>" 
                       data-price="<?= $p['price'] ?>" 
                       data-category="<?= htmlspecialchars($p['category']) ?>">
                      
                      <!-- Tombol Awal -->
                      <button class="btn-initial-add" onclick="initAddToCart(this)">
                          <i class="fas fa-plus"></i> Tambah
                      </button>

                      <!-- Wrapper Baru: Tombol Sampah + Qty Control -->
                      <div class="qty-active-wrapper">
                          
                          <!-- Tombol Hapus (Kiri, Merah) -->
                          <button class="btn-trash-separate" onclick="deleteItemDirectly(this)" title="Hapus dari keranjang">
                              <i class="fas fa-trash"></i>
                          </button>

                          <!-- Kontrol Kuantitas (Kanan) -->
                          <div class="qty-control-group">
                              <button class="qty-btn btn-minus" onclick="updateItemQty(this, -1)">
                                  <i class="fas fa-minus"></i>
                              </button>
                              
                              <input type="number" class="qty-display" value="1" min="1" onchange="manualQtyUpdate(this)">
                              
                              <button class="qty-btn" onclick="updateItemQty(this, 1)">
                                  <i class="fas fa-plus"></i>
                              </button>
                          </div>
                      </div>

                  </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="text-align: center; width:100%; color: var(--text-light);">Tidak ada produk dalam kategori ini.</p>
      <?php endif; ?>
    </div>

    <?php if($total_pages > 1): ?>
    <div class="pagination reveal">
        <?php 
            $catParam = ($category_filter && $category_filter !== 'all') ? '&category='.urlencode($category_filter) : '';
        ?>
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?><?= $catParam ?>#produk" class="page-link <?= ($i == $page) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

  </section>

  <section id="custom" class="custom-banner reveal section-scroll">
    <h2>Kue Custom</h2>
    <p>Punya desain impian? Kami siap mewujudkannya.</p>
    
    <div class="product-list" style="max-width: 1000px; margin: 20px auto 0; text-align: left;">
      <div class="product-card custom-product" data-category="Ulang Tahun Anak" data-name="Kue Ulang Tahun" data-price-min="150000" data-price-max="300000">
        <div class="img-wrapper">
            <img src="https://images.unsplash.com/photo-1558636508-e0db3814bd1d?w=500&q=80" alt="Kids Cake">
            <div class="hover-overlay"><span class="hover-btn">Pesan Sekarang</span></div>
        </div>
        <div class="info-wrapper">
          <h3>Ulang Tahun</h3>
          <p>Spiderman, Princess, Doraemon.</p>
          <span class="price">Mulai Rp 150k</span>
        </div>
      </div>
      <div class="product-card custom-product" data-category="Pernikahan" data-name="Kue Pernikahan" data-price-min="500000" data-price-max="2000000">
        <div class="img-wrapper">
            <img src="https://cdn-image.hipwee.com/wp-content/uploads/2021/10/hipwee-Gold-Wedding-Theme-_-Wedding-Ideas-By-Colour-_-CHWV-500x750.jpg" alt="Wedding">
            <div class="hover-overlay"><span class="hover-btn">Pesan Sekarang</span></div>
        </div>
        <div class="info-wrapper">
          <h3>Pernikahan</h3>
          <p>Elegant, Floral, Rustic Theme.</p>
          <span class="price">Mulai Rp 500k</span>
        </div>
      </div>
      <div class="product-card custom-product" data-category="Lamaran" data-name="Kue Lamaran" data-price-min="350000" data-price-max="800000">
        <div class="img-wrapper">
            <img src="https://down-id.img.susercontent.com/file/id-11134207-7rask-m19g6tfbxxr2ad" alt="Lamaran">
            <div class="hover-overlay"><span class="hover-btn">Pesan Sekarang</span></div>
        </div>
        <div class="info-wrapper">
          <h3>Lamaran</h3>
          <p>Desain romantis dan personal.</p>
          <span class="price">Mulai Rp 350k</span>
        </div>
      </div>
    </div>
    <div style="margin-top: 15px;">
      <a href="custom.php" class="btn-primary" style="background-color: #fff; color: var(--accent);">Lihat Katalog Lengkap</a>
    </div>
  </section>

  <section id="lokasi" class="section reveal section-scroll" style="padding-top: 0 !important;">
    <div class="section-header">
        <h2>Kunjungi Kami</h2>
        <p>Datang dan cium aroma kue segar langsung dari oven kami.</p>
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; align-items: stretch;">
        
        <div style="flex: 1; min-width: 300px; background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--line-color); box-shadow: 0 5px 20px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: center;">
            <h3 style="margin-bottom: 15px; color: var(--accent); font-family: var(--font-heading); font-size: 1.6rem;">Ibu Angel</h3>
            <div style="margin-bottom: 15px;">
                <strong style="display:block; color:var(--text-dark); margin-bottom: 5px;">Alamat:</strong>
                <p style="color: var(--text-light); line-height: 1.6;">
                    <i class="fas fa-map-pin" style="color: var(--accent); margin-right: 8px;"></i>
                    <?= nl2br(set('contact_address', '')) ?>
                </p>
            </div>
            <div style="margin-bottom: 20px;">
                <strong style="display:block; color:var(--text-dark); margin-bottom: 5px;">Kontak:</strong>
                <p style="color: var(--text-light);">
                    <i class="fab fa-whatsapp" style="color: var(--accent); margin-right: 8px;"></i>
                    <?= set('contact_phone', '') ?>
                </p>
            </div>
            <a href="https://wa.me/<?= set('contact_phone') ?>" target="_blank" class="btn-primary" style="width: 100%; text-align: center; display: block;">
                Hubungi via WhatsApp
            </a>
        </div>

        <div style="flex: 1.5; min-width: 300px; min-height: 300px; background: #eee; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <?php $mapUrl = set('gmaps_url', ''); ?>
            <iframe src="<?= $mapUrl ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
  </section>

  <footer>
    <span class="footer-logo">Ibu Angel</span>
    <p>Dibuat dengan kualitas dan bahan terbaik.</p>
    <div class="socials" style="margin-top: 15px;">
      <a href="#"><i class="fab fa-instagram"></i> Instagram</a>
      <a href="#"><i class="fab fa-facebook"></i> Facebook</a>
      <a href="#"><i class="fab fa-whatsapp"></i> WhatsApp</a>
    </div>
    <p style="margin-top: 20px; font-size: 0.8rem; opacity: 0.5;">© 2025 Ibu Angel Bakery.</p>
  </footer>

  <div id="customModal" class="modal">
    <div class="modal-content">
      <span class="close-modal" id="closeCustom">&times;</span>
      <div class="modal-img-col"><img id="customModalImg" src="" alt="Custom"></div>
      <div class="modal-info-col">
        <h3 id="customModalName">Custom Cake</h3>
        <p id="customModalCategory" style="font-weight:bold; color:var(--accent);">Kategori</p>
        <p id="customModalPrice">Range Harga</p>
        <div class="modal-form">
          <label>Detail Pesanan</label>
          <textarea id="customDetails" rows="3" placeholder="Contoh: Tulisan..."></textarea>
          <label>Tanggal Diperlukan</label>
          <input type="date" id="customDate">
        </div>
        <button id="addCustomToCart" class="btn-primary">Simpan ke Keranjang</button>
      </div>
    </div>
  </div>

  <a href="https://wa.me/6289689433798" style="position: fixed; bottom: 30px; right: 30px; background: #25d366; color: #fff; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; z-index: 999; box-shadow: 0 4px 10px rgba(0,0,0,0.2); text-decoration: none;">
    <i class="fab fa-whatsapp"></i>
  </a>

  <script>
    let cart = JSON.parse(localStorage.getItem('ibuangel_cart')) || [];
    function saveCart() { 
        localStorage.setItem('ibuangel_cart', JSON.stringify(cart)); 
        updateBadge(); 
        refreshAllCardUI(); 
    }
    
    function updateBadge() {
        const badge = document.getElementById('cart-badge');
        const count = cart.reduce((sum, item) => sum + item.qty, 0);
        if(badge) badge.textContent = count > 0 ? `(${count})` : '';
    }

    // --- LOGIKA TOMBOL BARU ---
    
    window.initAddToCart = function(btnElement) {
        const wrapper = btnElement.closest('.action-wrapper');
        const id = wrapper.dataset.id;
        const name = wrapper.dataset.name;
        const price = parseInt(wrapper.dataset.price);
        const category = wrapper.dataset.category;

        const existingItem = cart.find(item => item.id === id && item.type === 'regular');
        if (existingItem) {
            existingItem.qty++;
        } else {
            cart.push({ id: id, name: name, price: price, type: 'regular', category: category, qty: 1 });
        }
        saveCart();
    };

    window.updateItemQty = function(btnElement, change) {
        const wrapper = btnElement.closest('.action-wrapper');
        const id = wrapper.dataset.id;
        
        const itemIndex = cart.findIndex(item => item.id === id && item.type === 'regular');
        if (itemIndex > -1) {
            // Jika dikurangi dan hasilnya akan 0 atau kurang, JANGAN hapus. Biarkan minimal 1.
            // Penghapusan hanya lewat tombol sampah merah.
            if (change < 0 && cart[itemIndex].qty <= 1) {
                return; // Do nothing
            }
            
            cart[itemIndex].qty += change;
            saveCart();
        }
    };

    // FUNGSI BARU: HAPUS LANGSUNG LEWAT TOMBOL SAMPAH
    window.deleteItemDirectly = function(btnElement) {
        if(!confirm("Hapus item ini dari keranjang?")) return;

        const wrapper = btnElement.closest('.action-wrapper');
        const id = wrapper.dataset.id;
        
        const itemIndex = cart.findIndex(item => item.id === id && item.type === 'regular');
        if (itemIndex > -1) {
            cart.splice(itemIndex, 1); // Hapus langsung dari array
            saveCart();
        }
    };

    window.manualQtyUpdate = function(inputElement) {
        const wrapper = inputElement.closest('.action-wrapper');
        const id = wrapper.dataset.id;
        let newVal = parseInt(inputElement.value);

        if(isNaN(newVal) || newVal < 1) newVal = 1;

        const itemIndex = cart.findIndex(item => item.id === id && item.type === 'regular');
        if (itemIndex > -1) {
            cart[itemIndex].qty = newVal;
            saveCart();
        }
    };

    function refreshAllCardUI() {
        const wrappers = document.querySelectorAll('.action-wrapper');
        wrappers.forEach(wrapper => {
            const id = wrapper.dataset.id;
            const btnInitial = wrapper.querySelector('.btn-initial-add');
            
            // Elemen Baru
            const activeWrapper = wrapper.querySelector('.qty-active-wrapper');
            const qtyDisplay = wrapper.querySelector('.qty-display');
            const btnMinus = wrapper.querySelector('.btn-minus');

            const item = cart.find(i => i.id === id && i.type === 'regular');

            if (item) {
                // Item ada di keranjang -> Tampilkan Wrapper Aktif
                btnInitial.style.display = 'none';
                activeWrapper.classList.add('active'); // Tampilkan Flex
                qtyDisplay.value = item.qty;

                // Disable tombol minus jika qty = 1 (karena hapus lewat tombol merah)
                if (item.qty <= 1) {
                    btnMinus.disabled = true;
                    btnMinus.style.opacity = '0.5';
                    btnMinus.style.cursor = 'not-allowed';
                } else {
                    btnMinus.disabled = false;
                    btnMinus.style.opacity = '1';
                    btnMinus.style.cursor = 'pointer';
                }

            } else {
                // Item tidak ada -> Tampilkan tombol Tambah
                btnInitial.style.display = 'flex';
                activeWrapper.classList.remove('active');
            }
        });
    }

    // --- LOGIKA HALAMAN LAIN (Scroll Spy, Modal, dll) ---
    document.addEventListener("DOMContentLoaded", () => {
        updateBadge();
        refreshAllCardUI();

        const sections = document.querySelectorAll(".section-scroll");
        const navLinks = document.querySelectorAll(".nav-link");
        window.addEventListener("scroll", () => {
            let current = "";
            sections.forEach((section) => {
                const sectionTop = section.offsetTop;
                if (scrollY >= (sectionTop - 200)) current = section.getAttribute("id");
            });
            navLinks.forEach((li) => {
                li.classList.remove("active");
                if (li.getAttribute("href").includes(current)) li.classList.add("active");
            });
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) navbar.classList.add('scrolled'); else navbar.classList.remove('scrolled');
        });

        const observer = new IntersectionObserver((entries) => { entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('active'); }); }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // Modal Custom Logic
        const customModal = document.getElementById("customModal");
        const closeCustom = document.getElementById("closeCustom");
        const addCustomBtn = document.getElementById("addCustomToCart");
        const cName = document.getElementById("customModalName");
        const cImg = document.getElementById("customModalImg");
        const cCat = document.getElementById("customModalCategory");
        const cPrice = document.getElementById("customModalPrice");
        let currentCustomProduct = null;

        document.querySelectorAll(".custom-product").forEach(prod => {
            prod.addEventListener("click", () => {
                currentCustomProduct = { category: prod.dataset.category, name: prod.dataset.name, priceMin: parseInt(prod.dataset.priceMin), priceMax: parseInt(prod.dataset.priceMax), type: 'custom', qty: 1 };
                cImg.src = prod.querySelector("img").src;
                cName.textContent = currentCustomProduct.name;
                cCat.textContent = currentCustomProduct.category;
                cPrice.textContent = "Mulai Rp " + currentCustomProduct.priceMin.toLocaleString('id-ID');
                customModal.style.display = "block";
            });
        });

        closeCustom.onclick = () => customModal.style.display = "none";
        window.onclick = (e) => { if(e.target == customModal) customModal.style.display = "none"; };

        addCustomBtn.addEventListener("click", () => {
            const cDetails = document.getElementById("customDetails");
            const cDate = document.getElementById("customDate");
            if (!cDetails.value || !cDate.value) { alert("Mohon lengkapi detail dan tanggal!"); return; }
            const customItem = { ...currentCustomProduct, details: cDetails.value, date: cDate.value, price: currentCustomProduct.priceMin, id: 'custom-' + Date.now() };
            cart.push(customItem);
            saveCart();
            alert("Custom cake ditambahkan ke keranjang!");
            customModal.style.display = "none"; cDetails.value = ""; cDate.value = "";
        });
    });
  </script>
</body>
</html>