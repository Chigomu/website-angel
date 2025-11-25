<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Custom Cakes | Ibu Angel</title>
  
  <link rel="stylesheet" href="style.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    body { padding-top: 80px; }
    
    .custom-header {
      background-color: var(--text-dark);
      color: #fff;
      padding: 100px 20px;
      text-align: center;
      margin-bottom: 60px;
      background-image: url('https://www.transparenttextures.com/patterns/cubes.png');
    }
    .custom-header h1 { color: #fff; font-size: 3.5rem; margin-bottom: 20px; }
    .custom-header p { color: rgba(255,255,255,0.8); max-width: 600px; margin: 0 auto; font-size: 1.2rem; }

    .cta-section {
      background: var(--cream);
      text-align: center;
      padding: 80px 20px;
      margin-top: 80px;
      border-top: 1px solid var(--line-color);
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar" id="navbar">
    <a href="index.html" class="logo">Ibu Angel</a>
    <div class="nav-links">
      <a href="index.html">Beranda</a>
      <a href="index.html#produk">Menu</a>
      <a href="custom.html" class="active" style="color: var(--accent);">Custom</a>
      <a href="index.html#pesan">Keranjang</a>
      <a href="dashboard.html" id="adminLink" class="admin-link">Dashboard</a>
    </div>
  </nav>

  <!-- HEADER -->
  <header class="custom-header reveal">
    <h1>Custom Cakes</h1>
    <p>Wujudkan kue impian untuk momen spesial Anda. Mulai dari Ulang Tahun Anak, Sweet Seventeen, hingga Pernikahan.</p>
  </header>

  <!-- CONTENT -->
  <div class="section">
    
    <div class="reveal">
      <h3 class="category-title">🎈 Ulang Tahun Anak</h3>
      <div class="product-list">
        <div class="product-card custom-product" data-category="Ulang Tahun Anak" data-name="Kue Tema Spiderman" data-price-min="150000" data-price-max="300000">
          <div class="img-wrapper"><img src="https://i.pinimg.com/736x/c3/b9/d1/c3b9d158ebb0cc1f3f300a40e4c00b92.jpg" alt="Spiderman"></div>
          <div class="info-wrapper">
            <h3>Tema Spiderman</h3>
            <p>Hero favorit anak dengan detail icing keren.</p>
            <span class="price">Mulai Rp 150.000</span>
          </div>
        </div>
        
        <div class="product-card custom-product" data-category="Ulang Tahun Anak" data-name="Kue Tema Princess" data-price-min="200000" data-price-max="400000">
          <div class="img-wrapper"><img src="https://cdn.shopify.com/s/files/1/1175/4972/products/Princess_20Cake_20-_20118.jpg?v=1666101697" alt="Princess"></div>
          <div class="info-wrapper">
            <h3>Tema Princess</h3>
            <p>Kastil megah untuk putri kecil Anda.</p>
            <span class="price">Mulai Rp 200.000</span>
          </div>
        </div>
        
        <div class="product-card custom-product" data-category="Ulang Tahun Anak" data-name="Kue Tema Doraemon" data-price-min="150000" data-price-max="300000">
          <div class="img-wrapper"><img src="https://cdn.bestcakedesign.com/wp-content/uploads/doraemon-cake-design-02.png" alt="Doraemon"></div>
          <div class="info-wrapper">
            <h3>Tema Doraemon</h3>
            <p>Warna biru ceria dengan figur lucu.</p>
            <span class="price">Mulai Rp 150.000</span>
          </div>
        </div>
      </div>
    </div>

    <!-- KATEGORI 2: Sweet Seventeen -->
    <div class="reveal" style="margin-top: 80px;">
      <h3 class="category-title">💄 Sweet Seventeen & Remaja</h3>
      <div class="product-list">
        <div class="product-card custom-product" data-category="Sweet Seventeen" data-name="Kue Tema Make Up" data-price-min="250000" data-price-max="450000">
          <div class="img-wrapper"><img src="https://down-id.img.susercontent.com/file/id-11134207-7rask-m19g6tfbxxr2ad" alt="Makeup"></div>
          <div class="info-wrapper">
            <h3>Beauty & Makeup</h3>
            <p>Dekorasi lipstik & bedak fondant yang chic.</p>
            <span class="price">Mulai Rp 250.000</span>
          </div>
        </div>

        <div class="product-card custom-product" data-category="Sweet Seventeen" data-name="Korean Minimalist" data-price-min="180000" data-price-max="350000">
          <div class="img-wrapper"><img src="https://caketogether.com/cdn/shop/products/CopyofIMG_1005_3450x3450.jpg?v=1619858063" alt="Korean Cake"></div>
          <div class="info-wrapper">
            <h3>Korean Minimalist</h3>
            <p>Desain simpel estetik dengan warna pastel.</p>
            <span class="price">Mulai Rp 180.000</span>
          </div>
        </div>
      </div>
    </div>

    <!-- KATEGORI 3: Pernikahan -->
    <div class="reveal" style="margin-top: 80px;">
      <h3 class="category-title">💍 Pernikahan & Lamaran</h3>
      <div class="product-list">
        <div class="product-card custom-product" data-category="Pernikahan" data-name="Rustic Wedding" data-price-min="750000" data-price-max="2500000">
          <div class="img-wrapper"><img src="https://clweddings.com/wp-content/uploads/2024/09/Rustic-Wedding-Cake-Ideas-15.png" alt="Rustic Wedding"></div>
          <div class="info-wrapper">
            <h3>Rustic Wedding</h3>
            <p>Naked cake dengan hiasan bunga segar.</p>
            <span class="price">Mulai Rp 750.000</span>
          </div>
        </div>

        <div class="product-card custom-product" data-category="Pernikahan" data-name="Elegant White" data-price-min="1000000" data-price-max="3000000">
          <div class="img-wrapper"><img src="https://cdn-image.hipwee.com/wp-content/uploads/2021/10/hipwee-Gold-Wedding-Theme-_-Wedding-Ideas-By-Colour-_-CHWV-500x750.jpg" alt="Elegant Wedding"></div>
          <div class="info-wrapper">
            <h3>Classic Elegant</h3>
            <p>Putih bersih dengan aksen gold mewah.</p>
            <span class="price">Mulai Rp 1.000.000</span>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- CTA SECTION -->
  <section class="cta-section reveal">
    <h2 style="font-size: 2.5rem; margin-bottom: 20px;">Punya Desain Sendiri?</h2>
    <p style="font-size: 1.1rem; color: var(--text-light); margin-bottom: 40px;">Konsultasikan ide kue impian Anda langsung dengan Ibu Angel.</p>
    <a href="https://wa.me/6281234567890?text=Halo%20Ibu%20Angel,%20saya%20ingin%20konsultasi%20kue%20custom." target="_blank" class="btn-primary">Chat via WhatsApp</a>
  </section>

  <!-- FOOTER -->
  <footer>
    <span class="footer-logo">Ibu Angel</span>
    <p>Dibuat dengan cinta dan bahan terbaik.</p>
    <p style="margin-top: 50px; font-size: 0.8rem; opacity: 0.5;">© 2025 Ibu Angel Bakery.</p>
  </footer>

  <!-- MODAL CUSTOM  -->
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
          <textarea id="customDetails" rows="3" placeholder="Tulis tulisan ucapan, request warna, dll..."></textarea>
          
          <label>Tanggal Diperlukan</label>
          <input type="date" id="customDate">
        </div>

        <button id="addCustomToCart" class="btn-primary">Simpan ke Keranjang</button>
        <p style="font-size: 0.8rem; color: #999; margin-top: 10px;">*Pesanan akan masuk ke keranjang di halaman utama.</p>
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

    document.querySelectorAll(".custom-product").forEach(prod => {
      prod.addEventListener("click", () => {
        currentProduct = {
          name: prod.dataset.name,
          category: prod.dataset.category,
          priceMin: parseInt(prod.dataset.priceMin),
          priceMax: parseInt(prod.dataset.priceMax)
        };
        
        cName.textContent = currentProduct.name;
        cImg.src = prod.querySelector("img").src;
        cCat.textContent = currentProduct.category;
        cPrice.textContent = `Estimasi: Rp ${currentProduct.priceMin.toLocaleString()} - Rp ${currentProduct.priceMax.toLocaleString()}`;
        
        customModal.style.display = "block";
      });
    });

    closeCustom.onclick = () => customModal.style.display = "none";
    window.onclick = (e) => { if(e.target == customModal) customModal.style.display = "none"; }

    addBtn.addEventListener("click", () => {
        if(!cDetails.value || !cDate.value) {
            alert("Mohon lengkapi detail!");
            return;
        }
        alert("Item berhasil disimpan! (Dalam implementasi nyata, ini akan tersimpan ke localStorage dan muncul di keranjang halaman utama).");
        customModal.style.display = "none";
        cDetails.value = "";
        cDate.value = "";
    });

    if (localStorage.getItem("isAdminLoggedIn") === "true") {
        document.getElementById("adminLink").style.display = "inline-block";
    } else {
        document.getElementById("adminLink").style.display = "none";
    }
  </script>
</body>
</html>