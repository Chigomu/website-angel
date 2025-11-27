<?php
require_once 'app/auth_check.php';
require_once 'app/db.php'; 
require_once 'app/settings_loader.php';

// ... (Kode Ambil Data Custom Sama Seperti Sebelumnya) ...
// (Agar ringkas, saya tidak menulis ulang logika PHP di atas yang sama, 
//  asumsikan logika PHP pengambilan data tetap ada)
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE type = 'custom' ORDER BY category ASC, created_at DESC");
    $stmt->execute();
    $all_custom = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $grouped_custom = [];
    foreach ($all_custom as $p) {
        $cat = $p['category'] ?: 'Lainnya';
        $grouped_custom[$cat][] = $p;
    }
} catch (Exception $e) { $grouped_custom = []; }

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
  <title>Kue Kustom | Ibu Angel</title>
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <?php require_once 'app/dynamic_style.php'; ?>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    /* ... (CSS Sama Seperti Sebelumnya) ... */
    body { padding-top: 80px; }
    .nav-links, .nav-links li { list-style: none !important; padding: 0; margin: 0; }
    .custom-header { background-color: var(--text-dark); color: #fff; padding: 60px 20px !important; text-align: center; margin-bottom: 30px !important; background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); }
    .custom-header h1 { color: #fff; font-size: 3rem; margin-bottom: 15px; }
    .custom-header p { color: rgba(255,255,255,0.8); max-width: 600px; margin: 0 auto; font-size: 1.1rem; }
    .section { padding: 30px 20px !important; } 
    .cta-section { background: var(--bg-cream); text-align: center; padding: 50px 20px !important; margin-top: 30px !important; border-top: 1px solid var(--line-color); margin-bottom: 0 !important; }
    .cta-section .btn-primary:hover { background-color: #c86445 !important; border-color: #c86445 !important; color: #fff !important; transform: translateY(-2px); }
    .custom-product .info-wrapper h3 { margin-bottom: 5px !important; }
    .custom-product .info-wrapper p { margin-bottom: 10px !important; min-height: 0 !important; line-height: 1.3; }
    .product-list { gap: 25px !important; }
    #addCustomToCart:hover { background-color: #c86445 !important; border-color: #c86445 !important; color: #fff !important; transform: translateY(-2px); }
    footer { margin-top: 0 !important; padding: 40px 20px 20px !important; }
  </style>
</head>
<body>

  <nav class="navbar" id="navbar">
    <a href="index.php" class="logo">Ibu Angel</a>
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
    <?php if (!empty($grouped_custom)): ?>
        <?php foreach ($grouped_custom as $category => $products): ?>
            <div class="reveal" style="margin-top: 30px;">
                <h3 class="category-title" style="margin-bottom: 20px;"><?= getCategoryEmoji($category) ?> <?= htmlspecialchars($category) ?></h3>
                <div class="product-list">
                    <?php foreach ($products as $p): ?>
                        <div class="product-card custom-product" data-category="<?= htmlspecialchars($p['category']) ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-price-min="<?= $p['price_min'] ?>" data-price-max="<?= $p['price_max'] ?>">
                            <div class="img-wrapper"><img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" onerror="this.src='https://placehold.co/400x400?text=No+Image'"></div>
                            <div class="info-wrapper">
                                <h3><?= htmlspecialchars($p['name']) ?></h3>
                                <p><?= htmlspecialchars(substr($p['description'], 0, 60)) . (strlen($p['description']) > 60 ? '...' : '') ?></p>
                                <span class="price">Mulai Rp <?= number_format($p['price_min'], 0, ',', '.') ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="reveal" style="text-align: center; padding: 50px;"><p style="color: var(--text-light);">Belum ada katalog custom cake saat ini.</p></div>
    <?php endif; ?>
  </div>

  <section class="cta-section reveal">
    <h2 style="font-size: 2.5rem; margin-bottom: 20px;"><?= set('cta_title') ?></h2>
    <p style="font-size: 1.1rem; color: var(--text-light); margin-bottom: 40px;"><?= set('cta_desc') ?></p>
    <a href="https://wa.me/6289689433798?text=Halo%20Ibu%20Angel,%20saya%20ingin%20konsultasi%20kue%20custom." target="_blank" class="btn-primary">Chat via WhatsApp</a>
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
          <textarea id="customDetails" rows="3" placeholder="Tulis tulisan ucapan, request warna, dll..."></textarea>
          <label>Tanggal Diperlukan</label>
          <input type="date" id="customDate">
        </div>
        <button id="addCustomToCart" class="btn-primary">Pesan via WhatsApp</button>
      </div>
    </div>
  </div>

  <script>
    const observer = new IntersectionObserver((entries) => { entries.forEach(entry => { if (entry.isIntersecting) entry.target.classList.add('active'); }); }, { threshold: 0.1 });
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

    // === LOGIKA CART (Sama dengan index.php) ===
    let cart = JSON.parse(localStorage.getItem('ibuangel_cart')) || [];
    function saveCart() { localStorage.setItem('ibuangel_cart', JSON.stringify(cart)); updateBadge(); }
    function updateBadge() {
        const badge = document.getElementById('cart-badge');
        const count = cart.reduce((sum, item) => sum + item.qty, 0);
        if(badge) badge.textContent = count > 0 ? `(${count})` : '';
    }
    updateBadge(); // Init

    document.addEventListener('click', function(e) {
        const prod = e.target.closest('.custom-product');
        if (prod) {
            currentProduct = { name: prod.dataset.name, category: prod.dataset.category, priceMin: parseInt(prod.dataset.priceMin), priceMax: parseInt(prod.dataset.priceMax) };
            cName.textContent = currentProduct.name;
            cImg.src = prod.querySelector("img").src;
            cCat.textContent = currentProduct.category;
            cPrice.textContent = `Estimasi: Rp ${currentProduct.priceMin.toLocaleString('id-ID')} - Rp ${currentProduct.priceMax.toLocaleString('id-ID')}`;
            customModal.style.display = "block";
        }
    });

    closeCustom.onclick = () => customModal.style.display = "none";
    window.onclick = (e) => { if(e.target == customModal) customModal.style.display = "none"; }

    // === ADD CUSTOM TO CART LOGIC ===
    addBtn.addEventListener("click", async () => {
        if(!cDetails.value || !cDate.value) { alert("Mohon lengkapi detail dan tanggal!"); return; }
        // Untuk custom di halaman katalog, logic "Pesan" sebenarnya adalah ADD TO CART
        // Tapi jika tombolnya "Pesan via WhatsApp", berarti langsung checkout satu item ini saja?
        // Sesuai permintaan "keranjang dipisah", sebaiknya ini masuk keranjang.
        
        const customItem = { 
            name: currentProduct.name, 
            qty: 1, 
            price: currentProduct.priceMin, // Gunakan harga min sbg estimasi
            type: 'custom', 
            category: currentProduct.category, 
            details: cDetails.value, 
            date: cDate.value 
        };
        
        cart.push(customItem);
        saveCart();
        alert("Custom cake ditambahkan ke keranjang!");
        customModal.style.display = "none"; cDetails.value = ""; cDate.value = "";
    });
  </script>
</body>
</html>