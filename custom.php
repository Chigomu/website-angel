<?php
require_once 'app/auth_check.php';
require_once 'app/db.php'; 
require_once 'app/settings_loader.php';

// === PAGINATION CUSTOM (LIMIT 10) ===
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

try {
    // === PERBAIKAN DI SINI ===
    // Hanya ambil kategori yang memiliki produk bertipe 'custom'
    // Menggunakan DISTINCT category FROM products WHERE type = 'custom'
    $stmt_cat = $pdo->query("SELECT DISTINCT category FROM products WHERE type = 'custom' ORDER BY category ASC");
    $categories = $stmt_cat->fetchAll(PDO::FETCH_COLUMN);

    $sql_count = "SELECT COUNT(*) FROM products WHERE type = 'custom'";
    if ($category_filter && $category_filter !== 'all') $sql_count .= " AND category = :cat";
    $stmt_count = $pdo->prepare($sql_count);
    if ($category_filter && $category_filter !== 'all') $stmt_count->bindValue(':cat', $category_filter);
    $stmt_count->execute();
    $total_items = $stmt_count->fetchColumn();
    $total_pages = ceil($total_items / $limit);

    $sql_products = "SELECT * FROM products WHERE type = 'custom'";
    if ($category_filter && $category_filter !== 'all') $sql_products .= " AND category = :cat";
    $sql_products .= " ORDER BY category ASC, created_at DESC LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($sql_products);
    if ($category_filter && $category_filter !== 'all') $stmt->bindValue(':cat', $category_filter);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $all_custom = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) { $all_custom = []; $categories = []; }

// Fungsi helper untuk memberikan emoji
function getCategoryEmoji($categoryName) {
    if (stripos($categoryName, 'Ulang Tahun') !== false) return '🎈';
    if (stripos($categoryName, 'Pernikahan') !== false || stripos($categoryName, 'Lamaran') !== false) return '💍';
    if (stripos($categoryName, 'Seventeen') !== false || stripos($categoryName, 'Remaja') !== false) return '💄';
    if (stripos($categoryName, 'Wisuda') !== false) return '🎓';
    if (stripos($categoryName, 'Lebaran') !== false) return '🕌';
    return '✨'; 
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kue Kustom | Ibuké Enjel</title>
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <?php require_once 'app/dynamic_style.php'; ?>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    body { padding-top: 80px; }
    .nav-links, .nav-links li { list-style: none !important; padding: 0; margin: 0; }
    
    /* Header & Section */
    .custom-header { background-color: var(--text-dark); color: #fff; padding: 60px 20px !important; text-align: center; margin-bottom: 0 !important; background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); }
    .custom-header h1 { color: #fff; font-size: 3rem; margin-bottom: 15px; }
    .custom-header p { color: rgba(255,255,255,0.8); max-width: 600px; margin: 0 auto; font-size: 1.1rem; }
    .section { padding: 20px 20px 30px !important; }
    
    /* Filter Dropdown */
    .filter-container { display: flex; justify-content: center; align-items: center; gap: 15px; margin-bottom: 30px; margin-top: 30px; }
    .filter-label { font-weight: 600; color: var(--text-light); font-size: 1rem; }
    
    .category-select {
        padding: 10px 20px;
        border: 2px solid var(--accent);
        border-radius: 30px;
        background: transparent;
        color: var(--text-dark);
        font-family: var(--font-body);
        font-size: 1rem;
        cursor: pointer;
        outline: none;
        min-width: 200px;
        text-align: left;
        font-weight: 600;
        appearance: none;
        background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23D97757%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
        background-repeat: no-repeat;
        background-position: right 15px top 50%;
        background-size: 12px auto;
        padding-right: 40px;
    }
    .category-select:hover { background-color: rgba(217, 119, 87, 0.05); }

    /* Product List & Card */
    .product-list { gap: 15px !important; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)) !important; }
    .custom-product { position: relative; overflow: hidden; cursor: pointer; }
    .custom-product .img-wrapper { height: 180px !important; position: relative; }
    .custom-product .img-wrapper img { transition: transform 0.5s ease; }
    
    /* Hover Overlay */
    .hover-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(44, 24, 16, 0.7); display: flex; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s ease; border-radius: 8px 8px 0 0; }
    .hover-btn { background: var(--accent); color: #fff; padding: 10px 20px; border-radius: 30px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; font-size: 0.8rem; transform: translateY(20px); transition: transform 0.3s ease; }
    .custom-product:hover .hover-overlay { opacity: 1; }
    .custom-product:hover .hover-btn { transform: translateY(0); }
    .custom-product:hover .img-wrapper img { transform: scale(1.1); }

    .custom-product .info-wrapper { padding: 15px !important; text-align: left !important; }
    .custom-product .info-wrapper h3 { font-size: 1.1rem !important; margin-bottom: 5px !important; }
    .custom-product .info-wrapper p { font-size: 0.8rem !important; color: #888; margin-bottom: 5px !important; min-height: 0 !important; line-height: 1.3; }
    .custom-product .info-wrapper .price { margin-top: 0 !important; font-size: 0.95rem; font-weight: 700; color: var(--accent); }

    .cta-section { background: var(--bg-cream); text-align: center; padding: 50px 20px !important; margin-top: 30px !important; border-top: 1px solid var(--line-color); margin-bottom: 0 !important; }
    .cta-section .btn-primary:hover, #addCustomToCart:hover { background-color: #c86445 !important; border-color: #c86445 !important; color: #fff !important; transform: translateY(-2px); }
    
    /* Pagination */
    .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 40px; }
    .page-link { display: flex; align-items: center; justify-content: center; width: 35px; height: 35px; border: 1px solid var(--line-color); border-radius: 4px; text-decoration: none; color: var(--text-dark); font-weight: 600; transition: 0.3s; }
    .page-link:hover, .page-link.active { background: var(--accent); color: white; border-color: var(--accent); }

    /* Modal Form 2 Kolom */
    .modal-form { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 10px; }
    .modal-form label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; color: var(--text-dark); }
    .modal-form textarea, .modal-form input { width: 100%; padding: 10px; border: 1px solid var(--line-color); border-radius: 6px; font-family: var(--font-body); background: #fafafa; }
    
    @media (max-width: 600px) { .modal-form { grid-template-columns: 1fr; } }

    footer { margin-top: 0 !important; padding: 40px 20px 20px !important; }
  </style>
</head>
<body>

  <nav class="navbar" id="navbar">
    <a href="index.php" class="logo">Ibuké Enjel</a>
    <ul class="nav-links">
      <li><a href="index.php#home">Beranda</a></li>
      <li><a href="index.php#about">Tentang</a></li>
      <li><a href="index.php#produk">Menu</a></li>
      <li><a href="custom.php" class="active" style="color: var(--accent);">Custom</a></li>
      <li><a href="index.php#lokasi">Kontak</a></li>
      <li><a href="cart.php" style="font-size: 1.2rem;"><i class="fas fa-shopping-cart"></i> <span id="cart-badge" style="font-size: 0.8rem; vertical-align: top;"></span></a></li>
    </ul>
  </nav>

  <header class="custom-header reveal">
    <h1><?= set('custom_title') ?></h1>
    <p><?= set('custom_desc') ?></p>
  </header>

  <div class="section">
    
    <div class="filter-container reveal">
        <span class="filter-label">Filter sesuai kategori:</span>
        <select class="category-select" onchange="location = this.value;">
            <option value="?category=all" <?= (!$category_filter || $category_filter == 'all') ? 'selected' : '' ?>>Semua Kategori</option>
            <?php foreach($categories as $cat): ?>
                <option value="?category=<?= urlencode($cat) ?>" <?= ($category_filter == $cat) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="product-list">
        <?php if(!empty($all_custom)): ?>
            <?php foreach($all_custom as $p): ?>
                <div class="product-card custom-product item-card" 
                     data-category="<?= htmlspecialchars($p['category']) ?>"
                     data-name="<?= htmlspecialchars($p['name']) ?>"
                     data-price-min="<?= $p['price_min'] ?>"
                     data-price-max="<?= $p['price_max'] ?>">
                     
                    <div class="img-wrapper">
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" onerror="this.src='https://placehold.co/400x400?text=No+Image'">
                        <div class="hover-overlay"><span class="hover-btn">Pesan Sekarang</span></div>
                    </div>
                    
                    <div class="info-wrapper">
                        <h3><?= htmlspecialchars($p['name']) ?></h3>
                        <p><?= htmlspecialchars(substr($p['description'], 0, 50)) ?></p>
                        <span class="price">
                            <?php if($p['price_min'] > 0): ?>
                                Mulai Rp <?= number_format($p['price_min'], 0, ',', '.') ?>
                            <?php else: ?>
                                Harga Menyesuaikan
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; width:100%;">Tidak ada produk custom dalam kategori ini.</p>
        <?php endif; ?>
    </div>

    <?php if($total_pages > 1): ?>
    <div class="pagination reveal">
        <?php 
            $catParam = ($category_filter && $category_filter !== 'all') ? '&category='.urlencode($category_filter) : '';
        ?>
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?><?= $catParam ?>" class="page-link <?= ($i == $page) ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

  </div>

  <section class="cta-section reveal">
    <h2 style="font-size: 2.5rem; margin-bottom: 20px;"><?= set('cta_title') ?></h2>
    <p style="font-size: 1.1rem; color: var(--text-light); margin-bottom: 40px;"><?= set('cta_desc') ?></p>
    <a href="https://wa.me/6289689433798?text=Halo%20Ibu%20Angel,%20saya%20ingin%20konsultasi%20kue%20custom." target="_blank" class="btn-primary">Chat via WhatsApp</a>
  </section>

  <footer>
    <span class="footer-logo"><?= set('footer_title', 'Ibu Angel') ?></span>
    <p><?= set('footer_desc', 'Dibuat dengan kualitas dan bahan terbaik.') ?></p>
    <div class="socials" style="margin-top: 15px;">
      <a href="<?= set('social_instagram', '#') ?>" target="_blank"><i class="fab fa-instagram"></i> Instagram</a>
      <a href="<?= set('social_facebook', '#') ?>" target="_blank"><i class="fab fa-facebook"></i> Facebook</a>
      <a href="<?= set('social_whatsapp', '#') ?>" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
    </div>
    <p style="margin-top: 20px; font-size: 0.8rem; opacity: 0.5;"><?= set('footer_copy', '© 2025 Ibu Angel Bakery.') ?></p>
  </footer>

  <div id="customModal" class="modal">
    <div class="modal-content">
      <span class="close-modal" id="closeCustom">&times;</span>
      <div class="modal-img-col">
        <img id="customModalImg" src="" alt="Custom">
      </div>
      <div class="modal-info-col">
        <h3 id="customModalName">Custom Cake</h3>
        <p id="customModalCategory" style="font-weight:bold; color:var(--accent);">Kategori</p>
        <p id="customModalPrice">Range Harga</p>
        
        <div class="modal-form">
          <div>
              <label>Detail Pesanan</label>
              <textarea id="customDetails" rows="3" placeholder="Tulis tulisan ucapan, request warna, dll..."></textarea>
          </div>
          <div>
              <label>Tanggal Diperlukan</label>
              <input type="date" id="customDate">
          </div>
        </div>

        <button id="addCustomToCart" class="btn-primary">Simpan ke Keranjang</button>
      </div>
    </div>
  </div>

  <script>
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('active');
      });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    const customModal = document.getElementById("customModal");
    const closeCustom = document.getElementById("closeCustom");
    const addBtn = document.getElementById("addCustomToCart");

    const cName = document.getElementById("customModalName");
    const cImg = document.getElementById("customModalImg");
    const cCat = document.getElementById("customModalCategory");
    const cPrice = document.getElementById("customModalPrice");
    const cDetails = document.getElementById("customDetails");
    const cDate = document.getElementById("customDate");

    let currentProduct = null;
    
    // Cart Logic
    let cart = JSON.parse(localStorage.getItem('ibuangel_cart')) || [];
    function saveCart() { localStorage.setItem('ibuangel_cart', JSON.stringify(cart)); updateBadge(); }
    function updateBadge() {
        const badge = document.getElementById('cart-badge');
        const count = cart.reduce((sum, item) => sum + item.qty, 0);
        if(badge) badge.textContent = count > 0 ? `(${count})` : '';
    }
    updateBadge();

    // EVENT DELEGATION
    document.addEventListener('click', function(e) {
        const prod = e.target.closest('.custom-product');
        if (prod) {
            currentProduct = {
              name: prod.dataset.name,
              category: prod.dataset.category,
              priceMin: parseInt(prod.dataset.priceMin),
              priceMax: parseInt(prod.dataset.priceMax)
            };
            
            cName.textContent = currentProduct.name;
            cImg.src = prod.querySelector("img").src;
            cCat.textContent = currentProduct.category;
            cPrice.textContent = `Estimasi: Rp ${currentProduct.priceMin.toLocaleString('id-ID')} - Rp ${currentProduct.priceMax.toLocaleString('id-ID')}`;
            
            customModal.style.display = "block";
        }
    });

    closeCustom.onclick = () => customModal.style.display = "none";
    window.onclick = (e) => { if(e.target == customModal) customModal.style.display = "none"; }

    // === LOGIKA SIMPAN KE KERANJANG ===
    addBtn.addEventListener("click", async () => {
        if(!cDetails.value || !cDate.value) {
            alert("Mohon lengkapi detail dan tanggal!");
            return;
        }

        // Buat Objek Item Custom
        const customItem = {
            name: currentProduct.name,
            qty: 1,
            price: currentProduct.priceMin, // Gunakan harga min sebagai estimasi dasar
            type: 'custom',
            category: currentProduct.category,
            details: cDetails.value,
            date: cDate.value
        };

        // Masukkan ke Cart
        cart.push(customItem);
        
        // Simpan ke LocalStorage
        saveCart();

        alert("Custom cake ditambahkan ke keranjang!");
        
        // Tutup
        customModal.style.display = "none";
        cDetails.value = "";
        cDate.value = "";
    });
  </script>
</body>
</html>