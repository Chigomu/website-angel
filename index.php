<?php
session_start();
require_once 'app/db.php'; 

$isAdmin = !empty($_SESSION['admin_logged_in']);

// === AMBIL DATA PRODUK DARI DATABASE ===
try {
    // 1. Ambil semua produk Regular
    $stmt = $pdo->prepare("SELECT * FROM products WHERE type = 'regular' ORDER BY category ASC, created_at DESC");
    $stmt->execute();
    $all_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. KELOMPOKKAN BERDASARKAN KATEGORI (Agar Dinamis)
    $grouped_products = [];
    foreach ($all_products as $p) {
        $cat = $p['category'] ?: 'Lainnya'; // Jika kategori kosong, masukkan ke 'Lainnya'
        $grouped_products[$cat][] = $p;
    }

} catch (Exception $e) {
    $grouped_products = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ibu Angel | Artisan Cookies & Cakes</title>
  <link rel="stylesheet" href="style.css">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <style>
    /* === FIX NAVBAR TITIK HITAM === */
    .nav-links, .nav-links li {
      list-style: none !important; 
      padding: 0;
      margin: 0;
    }

    /* === FIX SCROLLING TEXT === */
    .marquee-content {
      display: inline-block;
      white-space: nowrap;
      animation: scroll-seamless 30s linear infinite; 
    }

    @keyframes scroll-seamless {
      from { transform: translateX(0); }
      to { transform: translateX(-50%); } 
    }
    
    /* === FIX KERANJANG === */
    .empty-cart { text-align: center; padding: 40px 20px; }
    .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 1px dashed var(--line-color); }
    .cart-item:last-child { border-bottom: none; }
    .item-details { flex: 1; }
    .item-header { display: flex; align-items: center; margin-bottom: 8px; }
    .item-header h4 { font-size: 1.3rem; margin: 0 10px 0 0; }
    .custom-badge { background: var(--accent); color: white; font-size: 0.7rem; padding: 3px 8px; border-radius: 12px; font-weight: 600; }
    .item-price { font-size: 1rem; color: var(--text-light); display: block; margin-bottom: 10px; }
    .custom-details { background: var(--bg-cream); padding: 12px; border-radius: 6px; margin-top: 10px; font-size: 0.85rem; }
    .custom-details p { margin: 5px 0; }
    .cart-controls { display: flex; flex-direction: column; align-items: flex-end; gap: 15px; }
    .quantity-controls { display: flex; align-items: center; border: 1px solid var(--line-color); border-radius: 6px; overflow: hidden; }
    .qty-btn { width: 36px; height: 36px; background: #fff; border: none; cursor: pointer; font-size: 1.1rem; }
    .qty-btn:hover { background: var(--bg-cream); }
    .qty-display { padding: 0 15px; font-weight: 600; min-width: 40px; text-align: center; }
    .item-subtotal { font-weight: 600; color: var(--accent); font-size: 1.1rem; }
    .delete-btn { background: none; border: none; color: #c0392b; cursor: pointer; font-size: 1.1rem; padding: 8px; border-radius: 4px; }
    .delete-btn:hover { background: rgba(192, 57, 43, 0.1); }
    .cart-total { margin-top: 0; padding: 30px 0 0 0; border-top: 2px solid var(--text-dark); }
    .total-line { display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px dashed var(--line-color); }
    .total-line:last-child { border-bottom: none; }
    .grand-total { font-size: 1.3rem; font-weight: 600; color: var(--text-dark); margin-top: 15px; padding-top: 15px; border-top: 2px solid var(--line-color); }
    
    @media (max-width: 768px) { 
        .cart-item { flex-direction: column; gap: 15px; } 
        .cart-controls { flex-direction: row; justify-content: space-between; width: 100%; align-items: center; } 
    }
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
      <li><a href="custom.php">Custom</a></li>
      <li><a href="#pesan">Keranjang</a></li>
      <?php if(!$isAdmin): ?>
        <li><a href="admin/admin_login.php">Admin</a></li>
      <?php else: ?>
        <li><a href="admin/dashboard.php">Dashboard</a></li>
        <li><a href="admin/logout.php" style="color: #C0392B;">Logout</a></li>
      <?php endif; ?>
    </ul>
  </nav>
</header>

  <section id="home" class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content reveal">
      <h1>Baked with Love,<br>Served with Joy.</h1>
      <p>Kue klasik dengan sentuhan modern. Dibuat segar setiap hari dari dapur rumah kami untuk momen istimewa Anda.</p>
      <a href="#produk" class="btn-primary">Lihat Menu Kami</a>
    </div>
  </section>

  <div class="marquee-container">
    <div class="marquee-content">
      <span>Fresh Tiap Hari</span> • 
      <span>Bahan Premium</span> • 
      <span>100% Halal</span> • 
      <span>Buatan Rumahan</span> • 
      <span>Tanpa Bahan Pengawet</span> • 
      <span>Fresh Setiap Saat</span> • 
      
      <span>Fresh Tiap Hari</span> • 
      <span>Bahan Premium</span> • 
      <span>100% Halal</span> • 
      <span>Buatan Rumahan</span> • 
      <span>Tanpa Bahan Pengawet</span> • 
      <span>Fresh Setiap Saat</span> • 
    </div>
  </div>

  <section id="about" class="section">
    <div class="about-container">
      <div class="about-text reveal">
        <h3>Cerita Kami</h3>
        <h2>Dari Dapur Sederhana, Penuh Rasa Cinta.</h2>
        <p>Berawal dari hobi membuat kue untuk keluarga, "Ibu Angel" kini hadir untuk berbagi kebahagiaan yang sama dengan Anda.</p>
        <p>Kami menggunakan bahan-bahan premium pilihan, tanpa pengawet, dan dipanggang dengan teknik artisan.</p>
      </div>
      <div class="about-img reveal">
        <img src="dapur.png" alt="Dapur Ibu Angel" onerror="this.src='https://images.unsplash.com/photo-1556910103-1c02745a30bf?w=800&q=80'">
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
        
        <h3 class="category-title reveal">
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

  <section class="custom-banner reveal">
    <h2>Kue Custom</h2>
    <p>Punya desain impian? Kami siap mewujudkannya.</p>
    
    <div class="product-list" style="max-width: 1000px; margin: 40px auto; text-align: left;">
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
    
    <div style="margin-top: 30px;">
      <a href="custom.php" class="btn-primary" style="background-color: #fff; color: var(--accent);">Lihat Katalog Lengkap</a>
    </div>
  </section>

  <section id="pesan" class="section reveal">
    <div class="section-header">
      <h2>Pesanan Anda</h2>
      <p>Cek kembali pesanan sebelum mengirim via WhatsApp.</p>
    </div>
    <div class="cart-container">
      <div id="cart">
        <div class="empty-cart">
          <i class="fas fa-shopping-cart" style="font-size: 3rem; color: var(--line-color); margin-bottom: 20px;"></i>
          <p style="text-align:center; color: #999; padding: 20px;">Keranjang masih kosong.</p>
        </div>
      </div>
      <div style="margin-top: 40px; text-align: right;">
        <button id="checkoutBtn" class="btn-primary dark-hover">Checkout WhatsApp</button>
      </div>
    </div>
  </section>

  <footer>
    <span class="footer-logo">Ibu Angel</span>
    <p>Dibuat dengan kualitas dan bahan terbaik.</p>
    <div class="socials" style="margin-top: 30px;">
      <a href="#"><i class="fab fa-instagram"></i> Instagram</a>
      <a href="#"><i class="fab fa-facebook"></i> Facebook</a>
      <a href="#"><i class="fab fa-whatsapp"></i> WhatsApp</a>
    </div>
    <p style="margin-top: 50px; font-size: 0.8rem; opacity: 0.5;">© 2025 Ibu Angel Bakery.</p>
  </footer>

  <div id="productModal" class="modal">
    <div class="modal-content">
      <span class="close-modal" id="closeRegular">&times;</span>
      <div class="modal-img-col">
        <img id="modalImg" src="" alt="Produk">
      </div>
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
      <div class="modal-img-col">
        <img id="customModalImg" src="" alt="Custom">
      </div>
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

  <a href="https://wa.me/6281351966722" style="position: fixed; bottom: 30px; right: 30px; background: #25d366; color: #fff; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; z-index: 999; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
    <i class="fab fa-whatsapp"></i>
  </a>

  <script>
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) entry.target.classList.add('active');
      });
    }, { threshold: 0.1 });
    document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

    window.addEventListener('scroll', () => {
      const navbar = document.getElementById('navbar');
      if (window.scrollY > 50) navbar.classList.add('scrolled');
      else navbar.classList.remove('scrolled');
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
    const cartDiv = document.getElementById("cart");
    const checkoutBtn = document.getElementById("checkoutBtn");

    let currentProduct = null;
    let currentCustomProduct = null;
    let cart = [];

    // EVENT DELEGATION UNTUK PRODUK DINAMIS
    const productSection = document.getElementById('produk');
    
    if (productSection) {
        productSection.addEventListener('click', function(e) {
            const prod = e.target.closest(".product-card[data-type='regular']");
            if (prod) {
                currentProduct = {
                    name: prod.dataset.name,
                    price: parseInt(prod.dataset.price),
                    ingredients: prod.dataset.ingredients,
                    type: 'regular',
                    qty: 1
                };
                modalImg.src = prod.querySelector("img").src;
                modalName.textContent = currentProduct.name;
                if(currentProduct.ingredients) {
                    modalIngredients.textContent = currentProduct.ingredients;
                } else {
                    modalIngredients.textContent = "Kue lezat buatan Ibu Angel.";
                }
                modalPrice.textContent = "Rp " + currentProduct.price.toLocaleString('id-ID');
                modal.style.display = "block";
            }
        });
    }

    document.querySelectorAll(".custom-product").forEach(prod => {
      prod.addEventListener("click", () => {
        currentCustomProduct = {
          category: prod.dataset.category,
          name: prod.dataset.name,
          priceMin: parseInt(prod.dataset.priceMin),
          priceMax: parseInt(prod.dataset.priceMax),
          type: 'custom',
          qty: 1
        };
        customModalImg.src = prod.querySelector("img").src;
        customModalName.textContent = currentCustomProduct.name;
        customModalCategory.textContent = currentCustomProduct.category;
        customModalPrice.textContent = "Mulai Rp " + currentCustomProduct.priceMin.toLocaleString('id-ID');
        customModal.style.display = "block";
      });
    });

    document.getElementById("closeRegular").onclick = () => modal.style.display = "none";
    document.getElementById("closeCustom").onclick = () => customModal.style.display = "none";
    window.onclick = e => { 
      if (e.target == modal) modal.style.display = "none";
      if (e.target == customModal) customModal.style.display = "none";
    };

    addToCartBtn.addEventListener("click", () => {
      const existingItem = cart.find(item => item.name === currentProduct.name && item.type === 'regular');
      if (existingItem) { existingItem.qty++; } else { cart.push(currentProduct); }
      updateCart();
      modal.style.display = "none";
    });

    addCustomToCartBtn.addEventListener("click", () => {
      if (!customDetails.value || !customDate.value) {
        alert("Mohon lengkapi detail dan tanggal!");
        return;
      }
      const customItem = {
        ...currentCustomProduct,
        details: customDetails.value,
        date: customDate.value,
        price: currentCustomProduct.priceMin 
      };
      cart.push(customItem);
      updateCart();
      customModal.style.display = "none";
      customDetails.value = "";
      customDate.value = "";
    });

    function updateCart() {
      cartDiv.innerHTML = "";
      if (cart.length === 0) {
        cartDiv.innerHTML = `
          <div class="empty-cart">
            <i class="fas fa-shopping-cart" style="font-size: 3rem; color: var(--line-color); margin-bottom: 20px;"></i>
            <p style="text-align:center; color: #999; padding: 20px;">Keranjang masih kosong.</p>
          </div>`;
        return;
      }
      let total = 0;
      cart.forEach((item, index) => {
        const itemTotal = item.price * item.qty;
        total += itemTotal;
        const div = document.createElement("div");
        div.className = "cart-item";
        const customBadge = item.type === 'custom' ? `<span class="custom-badge">Custom</span>` : '';
        const customDetails = item.type === 'custom' ? 
          `<div class="custom-details"><p><strong>Kategori:</strong> ${item.category}</p><p><strong>Detail:</strong> ${item.details}</p><p><strong>Tanggal:</strong> ${item.date}</p></div>` : '';
        div.innerHTML = `
          <div class="item-details">
            <div class="item-header"><h4>${item.name}</h4>${customBadge}</div>
            <span class="item-price">Rp ${item.price.toLocaleString('id-ID')}</span>${customDetails}
          </div>
          <div class="cart-controls">
            <div class="quantity-controls">
              <button class="qty-btn" onclick="changeQty(${index}, -1)">-</button>
              <span class="qty-display">${item.qty}</span>
              <button class="qty-btn" onclick="changeQty(${index}, 1)">+</button>
            </div>
            <div class="item-subtotal">Rp ${itemTotal.toLocaleString('id-ID')}</div>
            <button class="delete-btn" onclick="deleteItem(${index})" title="Hapus"><i class="fas fa-trash"></i></button>
          </div>`;
        cartDiv.appendChild(div);
      });
      const totalDiv = document.createElement("div");
      totalDiv.className = "cart-total";
      totalDiv.innerHTML = `
        <div class="total-line"><span>Subtotal:</span><span>Rp ${total.toLocaleString('id-ID')}</span></div>
        <div class="total-line"><span>Ongkos Kirim:</span><span>Disesuaikan</span></div>
        <div class="total-line grand-total"><span>Total Estimasi:</span><span>Rp ${total.toLocaleString('id-ID')}</span></div>`;
      cartDiv.appendChild(totalDiv);
    }

    window.changeQty = function(index, change) {
      if (cart[index].qty + change > 0) cart[index].qty += change; 
      else if (confirm("Hapus item ini?")) cart.splice(index, 1);
      updateCart();
    };
    window.deleteItem = function(index) {
      if (confirm("Hapus pesanan ini?")) { cart.splice(index, 1); updateCart(); }
    };
    
    // === LOGIKA CHECKOUT YANG BARU ===
    checkoutBtn.addEventListener("click", async () => {
      if (cart.length === 0) return alert("Keranjang kosong!");

      const customerName = prompt("Siapa nama pemesan?");
      if (!customerName) return; 

      let total = 0;
      cart.forEach(item => total += (item.price * item.qty));

      const orderData = {
          name: customerName,
          items: cart,
          total: total
      };

      try {
          const response = await fetch('save_order.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(orderData)
          });
          
          const result = await response.json();

          if (result.status === 'success') {
              let msg = `Halo Ibu Angel, saya *${customerName}* mau pesan:%0A%0A`;
              cart.forEach((item, i) => {
                const subtotal = item.price * item.qty;
                msg += `*${i+1}. ${item.name}* (x${item.qty})%0A`;
                if(item.type === 'custom') { 
                  msg += `   Detail: ${item.details}%0A`; 
                  msg += `   Kategori: ${item.category}%0A`;
                  msg += `   Tgl: ${item.date}%0A`; 
                }
                msg += `   Harga: Rp ${subtotal.toLocaleString('id-ID')}%0A%0A`;
              });
              msg += `--------------------%0A*Total: Rp ${total.toLocaleString('id-ID')}*`;

              window.open(`https://wa.me/6281351966722?text=${msg}`, "_blank");

              cart = [];
              updateCart();
          } else {
              alert("Gagal menyimpan pesanan, silakan coba lagi.");
          }

      } catch (error) {
          console.error("Error:", error);
          alert("Terjadi kesalahan koneksi.");
      }
    });
  </script>
</body>
</html>