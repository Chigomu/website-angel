<?php
session_start();
require_once 'app/db.php'; 
require_once 'app/settings_loader.php';

// === AMBIL DATA PRODUK ===
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE type = 'regular' ORDER BY category ASC, created_at DESC");
    $stmt->execute();
    $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $grouped_products = [];
    foreach ($all_products as $p) {
        $cat = $p['category'] ?: 'Lainnya';
        $grouped_products[$cat][] = $p;
    }
} catch (Exception $e) { $grouped_products = []; }
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
    /* === STYLE FIXES === */
    .nav-links, .nav-links li { list-style: none !important; padding: 0; margin: 0; }
    .section { padding: 30px 20px !important; }
    .section-header { margin-bottom: 20px !important; }
    .section-header h2 { margin-bottom: 5px !important; font-size: 2.5rem; }
    .hero { min-height: auto !important; padding: 100px 0 40px !important; }
    .marquee-container { padding: 8px 0 !important; }
    .custom-banner { padding: 40px 20px !important; margin-top: 20px !important; margin-bottom: 0 !important; }
    footer { padding: 40px 20px 20px !important; margin-top: 0 !important; }

    .custom-banner .product-list { grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)) !important; gap: 15px !important; }
    .custom-product .info-wrapper { padding: 15px 20px 20px !important; }
    .custom-product .info-wrapper h3 { margin-bottom: 2px !important; font-size: 1.4rem; }
    .custom-product .info-wrapper p { margin-bottom: 5px !important; min-height: 0 !important; line-height: 1.2; font-size: 0.85rem; color: #888; }
    .custom-product .info-wrapper .price { margin-top: 0 !important; display: block; font-weight: 700; }

    .marquee-content { display: inline-block; white-space: nowrap; animation: scroll-seamless 40s linear infinite; }
    @keyframes scroll-seamless { from { transform: translateX(0); } to { transform: translateX(-50%); } }
    
    .product-list { gap: 20px !important; }
    .about-container { gap: 30px !important; }
  </style>
</head>
<body>

<header>
  <nav class="navbar" id="navbar">
    <div class="logo">Ibu Angel</div>
    <ul class="nav-links">
      <li><a href="#home">Beranda</a></li>
      <li><a href="#about">Tentang</a></li>
      <li><a href="#produk">Menu</a></li>
      <li><a href="#custom">Custom</a></li>
      <li><a href="#lokasi">Kontak</a></li>
      <li><a href="cart.php" style="font-size: 1.2rem;"><i class="fas fa-shopping-cart"></i> <span id="cart-badge" style="font-size: 0.8rem; vertical-align: top;"></span></a></li> 
    </ul>
  </nav>
</header>

  <section id="home" class="hero">
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

  <section id="about" class="section">
    <div class="about-container">
      <div class="about-text reveal">
        <h3 style="margin-bottom: 5px;">Cerita Kami</h3>
        <h2 style="margin-bottom: 10px;"><?= set('about_title') ?></h2>
        <p><?= nl2br(set('about_desc')) ?></p>
      </div>
      <div class="about-img reveal">
        <img src="<?= set('about_img') ?>" alt="Dapur Ibu Angel" onerror="this.src='https://images.unsplash.com/photo-1556910103-1c02745a30bf?w=800&q=80'">
      </div>
    </div>
  </section>

  <section id="produk" class="section">
    <div class="section-header reveal">
      <h2>Menu Favorit</h2>
      <p>Pilihan kue kering renyah dan cake lembut yang selalu dirindukan.</p>
    </div>
    
    <?php if(!empty($grouped_products)): ?>
      <?php foreach($grouped_products as $category_name => $products): ?>
        <h3 class="category-title reveal" style="margin: 20px 0 15px !important;">
            <?php 
                $icon = '🍪'; 
                if(stripos($category_name, 'Bolu') !== false || stripos($category_name, 'Cake') !== false) $icon = '🍰';
                if(stripos($category_name, 'Hampers') !== false) $icon = '🎁';
                echo $icon . ' ' . htmlspecialchars($category_name); 
            ?>
        </h3>
        <div class="product-list">
          <?php foreach($products as $p): ?>
            <div class="product-card reveal" 
                 data-name="<?= htmlspecialchars($p['name']) ?>" 
                 data-price="<?= $p['price'] ?>" 
                 data-ingredients="<?= htmlspecialchars($p['ingredients']) ?>" 
                 data-type="regular">
              <div class="img-wrapper">
                  <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" onerror="this.src='https://placehold.co/400x400?text=No+Image'">
              </div>
              <div class="info-wrapper">
                <h3><?= htmlspecialchars($p['name']) ?></h3>
                <p><?= htmlspecialchars(substr($p['description'], 0, 50)) . (strlen($p['description']) > 50 ? '...' : '') ?></p>
                <span class="price">Rp <?= number_format($p['price'], 0, ',', '.') ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; color: var(--text-light); margin-top: 20px;">Belum ada produk yang tersedia.</p>
    <?php endif; ?>
  </section>

  <section id="custom" class="custom-banner reveal">
    <h2>Kue Custom</h2>
    <p>Punya desain impian? Kami siap mewujudkannya.</p>
    
    <div class="product-list" style="max-width: 1300px; margin: 20px auto 0; text-align: left;">
      <div class="product-card custom-product" data-category="Ulang Tahun Anak" data-name="Kustom Kue" data-price-min="150000" data-price-max="300000">
        <div class="img-wrapper"><img src="https://images.unsplash.com/photo-1558636508-e0db3814bd1d?w=500&q=80" alt="Kids Cake"></div>
        <div class="info-wrapper" style="background: #fff; color: var(--text-dark);">
          <h3>Ulang Tahun</h3>
          <p>Spiderman, Princess, Doraemon.</p>
          <span class="price">Mulai Rp 150k</span>
        </div>
      </div>
      <div class="product-card custom-product" data-category="Pernikahan" data-name="Kustom Kue" data-price-min="500000" data-price-max="2000000">
        <div class="img-wrapper"><img src="https://cdn-image.hipwee.com/wp-content/uploads/2021/10/hipwee-Gold-Wedding-Theme-_-Wedding-Ideas-By-Colour-_-CHWV-500x750.jpg" alt="Wedding"></div>
        <div class="info-wrapper" style="background: #fff; color: var(--text-dark);">
          <h3>Pernikahan</h3>
          <p>Elegant, Floral, Rustic Theme.</p>
          <span class="price">Mulai Rp 500k</span>
        </div>
      </div>
      <div class="product-card custom-product" data-category="Lamaran" data-name="Kustom Kue" data-price-min="350000" data-price-max="800000">
        <div class="img-wrapper"><img src="https://down-id.img.susercontent.com/file/id-11134207-7rask-m19g6tfbxxr2ad" alt="Lamaran"></div>
        <div class="info-wrapper" style="background: #fff; color: var(--text-dark);">
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

  <section id="lokasi" class="section reveal" style="padding-top: 0 !important;">
    <div class="section-header">
        <h2>Kunjungi Kami</h2>
        <p>Datang dan cium aroma kue segar langsung dari oven kami.</p>
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; align-items: flex-start;">
        <div style="flex: 1; min-width: 300px; background: #fff; padding: 25px; border-radius: 12px; border: 1px solid var(--line-color); box-shadow: 0 5px 20px rgba(0,0,0,0.03);">
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
        <div style="flex: 1.5; min-width: 300px; height: 300px; background: #eee; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <?php $mapUrl = set('gmaps_url', ''); ?>
            <iframe src="<?= $mapUrl ?>" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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

  <div id="productModal" class="modal">
    <div class="modal-content">
      <span class="close-modal" id="closeRegular">&times;</span>
      <div class="modal-img-col"><img id="modalImg" src="" alt="Produk"></div>
      <div class="modal-info-col">
        <h3 id="modalName">Nama Produk</h3>
        <p id="modalIngredients">Deskripsi bahan...</p>
        <div class="price" id="modalPrice">Rp 0</div>
        <button id="addToCart" class="btn-primary">Tambah ke Keranjang</button>
      </div>
    </div>
  </div>

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
          <textarea id="customDetails" rows="3" placeholder="Contoh: Tulisan 'Happy Birthday', Warna dominan Biru..."></textarea>
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
    const observer = new IntersectionObserver((entries) => { entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('active'); }); }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    window.addEventListener('scroll', () => {
      const navbar = document.getElementById('navbar');
      if (window.scrollY > 50) navbar.classList.add('scrolled'); else navbar.classList.remove('scrolled');
    });

    const modal = document.getElementById("productModal");
    const customModal = document.getElementById("customModal");
    const modalImg = document.getElementById("modalImg");
    const modalName = document.getElementById("modalName");
    const modalIngredients = document.getElementById("modalIngredients");
    const modalPrice = document.getElementById("modalPrice");
    const addToCartBtn = document.getElementById("addToCart");

    const customModalImg = document.getElementById("customModalImg");
    const customModalName = document.getElementById("customModalName");
    const customModalCategory = document.getElementById("customModalCategory");
    const customModalPrice = document.getElementById("customModalPrice");
    const customDetails = document.getElementById("customDetails");
    const customDate = document.getElementById("customDate");
    const addCustomToCartBtn = document.getElementById("addCustomToCart");

    let currentProduct = null;
    let currentCustomProduct = null;
    
    // === LOGIKA CART GLOBAL (LocalStorage) ===
    let cart = JSON.parse(localStorage.getItem('ibuangel_cart')) || [];

    function saveCart() {
        localStorage.setItem('ibuangel_cart', JSON.stringify(cart));
        updateBadge();
    }

    function updateBadge() {
        const badge = document.getElementById('cart-badge');
        const count = cart.reduce((sum, item) => sum + item.qty, 0);
        if(badge) badge.textContent = count > 0 ? `(${count})` : '';
    }

    // Panggil saat load
    updateBadge();

    const productSection = document.getElementById('produk');
    if (productSection) {
        productSection.addEventListener('click', function(e) {
            const prod = e.target.closest(".product-card[data-type='regular']");
            if (prod) {
                currentProduct = { name: prod.dataset.name, price: parseInt(prod.dataset.price), ingredients: prod.dataset.ingredients, type: 'regular', qty: 1 };
                modalImg.src = prod.querySelector("img").src;
                modalName.textContent = currentProduct.name;
                modalIngredients.textContent = currentProduct.ingredients || "Kue lezat buatan Ibu Angel.";
                modalPrice.textContent = "Rp " + currentProduct.price.toLocaleString('id-ID');
                modal.style.display = "block";
            }
        });
    }

    document.querySelectorAll(".custom-product").forEach(prod => {
      prod.addEventListener("click", () => {
        currentCustomProduct = { category: prod.dataset.category, name: prod.dataset.name, priceMin: parseInt(prod.dataset.priceMin), priceMax: parseInt(prod.dataset.priceMax), type: 'custom', qty: 1 };
        customModalImg.src = prod.querySelector("img").src;
        customModalName.textContent = currentCustomProduct.name;
        customModalCategory.textContent = currentCustomProduct.category;
        customModalPrice.textContent = "Mulai Rp " + currentCustomProduct.priceMin.toLocaleString('id-ID');
        customModal.style.display = "block";
      });
    });

    document.getElementById("closeRegular").onclick = () => modal.style.display = "none";
    document.getElementById("closeCustom").onclick = () => customModal.style.display = "none";
    window.onclick = e => { if (e.target == modal) modal.style.display = "none"; if (e.target == customModal) customModal.style.display = "none"; };

    addToCartBtn.addEventListener("click", () => {
      // Cek apakah item sudah ada di cart
      const existingItem = cart.find(item => item.name === currentProduct.name && item.type === 'regular');
      if (existingItem) { 
          existingItem.qty++; 
      } else { 
          cart.push(currentProduct); 
      }
      saveCart();
      alert("Produk ditambahkan ke keranjang!");
      modal.style.display = "none";
    });

    addCustomToCartBtn.addEventListener("click", () => {
      if (!customDetails.value || !customDate.value) { alert("Mohon lengkapi detail dan tanggal!"); return; }
      const customItem = { ...currentCustomProduct, details: customDetails.value, date: customDate.value, price: currentCustomProduct.priceMin };
      cart.push(customItem);
      saveCart();
      alert("Custom cake ditambahkan ke keranjang!");
      customModal.style.display = "none"; customDetails.value = ""; customDate.value = "";
    });
  </script>
</body>
</html>