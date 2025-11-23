<?php
require_once __DIR__ . '/../app/auth_check.php';
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin | Ibu Angel</title>
  
  <!-- Gunakan Style Utama agar konsisten -->
  <link rel="stylesheet" href="../style.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <!-- === IMPORT FIREBASE === -->
  <script type="module">
    const firebaseConfig = {
      apiKey: "AIzaSyAlAO2yzW1xBSR-qq0CUARBtckPK2EypqE",
      authDomain: "webkue-b82fb.firebaseapp.com",
      projectId: "webkue-b82fb",
      storageBucket: "webkue-b82fb.firebasestorage.app",
      messagingSenderId: "682883354783",
      appId: "1:682883354783:web:b39cfd14937a3cab27d2aa",
      measurementId: "G-F3ET24JL31"
    };

    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
    import { getAuth, signInAnonymously, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
    import { 
      getFirestore, doc, collection, addDoc, setDoc, deleteDoc, onSnapshot, query, getDocs, limit
    } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);
    const db = getFirestore(app);

    window.firebase = {
      app, auth, db,
      signInAnonymously, onAuthStateChanged,
      doc, collection, addDoc, setDoc, deleteDoc, onSnapshot, query, getDocs, limit
    };
  </script>
  <!-- === AKHIR IMPORT FIREBASE === -->

  <style>
    /* === DASHBOARD SPECIFIC STYLES === */
    body { padding-top: 100px; }

    .admin-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px;
      display: grid;
      grid-template-columns: 1fr 1.5fr;
      gap: 50px;
    }

    .admin-card {
      background: #fff;
      padding: 40px;
      border: 2px solid var(--text-dark);   /* DOUBLE BORDER TANPA PSEUDO */
      outline: 1px solid var(--line-color); /* BORDER HALUS LUAR */
      outline-offset: -6px;                 /* MENIRU EFEK BEFORE */
    }
    .admin-card::before {
      display: none !important;
    }

    .admin-header {
      text-align: center;
      margin-bottom: 40px;
    }
    .admin-header h2 { font-size: 2.5rem; color: var(--text-dark); }
    .admin-header p { color: var(--text-light); }

    .form-group { margin-bottom: 20px; }
    .form-group label { 
      display: block; 
      font-family: var(--font-heading); 
      font-size: 1.1rem;
      margin-bottom: 8px; 
      color: var(--text-dark);
    }
    
    input, select, textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid var(--line-color);
      font-family: var(--font-body);
      font-size: 0.95rem;
      background: #fff;
      transition: all 0.3s ease;
    }
    
    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 2px rgba(217, 119, 87, 0.1);
    }

    .product-list-container {
      max-height: 800px;
      overflow-y: auto;
      padding-right: 10px;
    }

    .product-list-container::-webkit-scrollbar { width: 6px; }
    .product-list-container::-webkit-scrollbar-track { background: #f1f1f1; }
    .product-list-container::-webkit-scrollbar-thumb { background: var(--line-color); }

    .admin-product-item {
      display: flex;
      align-items: center;
      padding: 20px 0;
      border-bottom: 1px dashed var(--line-color);
      transition: background 0.2s;
    }
    .admin-product-item:hover { background-color: rgba(255,255,255,0.5); }

    .item-img {
      width: 70px; height: 70px;
      object-fit: cover;
      margin-right: 20px;
      border: 1px solid var(--line-color);
    }

    .item-info { flex: 1; }
    .item-info h4 { margin: 0; font-size: 1.2rem; color: var(--text-dark); }
    .item-info span { display: block; font-size: 0.9rem; color: var(--text-light); margin-top: 4px; }
    .item-price { color: var(--accent); font-weight: 600; }

    .action-buttons { display: flex; gap: 10px; margin-left: 15px; }
    .btn-icon {
      width: 35px; height: 35px;
      display: flex; align-items: center; justify-content: center;
      border: 1px solid var(--line-color);
      background: #fff;
      cursor: pointer;
      transition: all 0.2s;
      color: var(--text-dark);
    }
    .btn-icon:hover { background: var(--text-dark); color: #fff; }
    .btn-icon.delete:hover { background: #c0392b; border-color: #c0392b; color: #fff; }

    @media (max-width: 900px) {
      .admin-container { grid-template-columns: 1fr; }
      .admin-card { padding: 25px; }
    }
  </style>
</head>
<body>

  <!-- NAVBAR ADMIN (Desain Sama dengan Index) -->
  <nav class="navbar" id="navbar">
    <a href="../index.php" class="logo">Ibu Angel</a>
    <div class="nav-links">
      <a href="../index.php">Home</a>
      <a href="../admin/orders.php">Pesanan</a>
      <a href="#" class="active" style="color: var(--accent);">Dashboard</a>
      <a href="logout.php" style="color: #C0392B;">Logout</a>

    </div>
  </nav>

  <!-- KONTEN DASHBOARD -->
  <div class="section">
    <div class="admin-header reveal active">
      <h2>Manajemen Produk</h2>
      <p>Kelola menu kue kering dan custom cake Anda di sini.</p>
    </div>

    <div class="admin-container">
      
      <!-- KOLOM KIRI: FORM INPUT -->
      <div class="admin-card reveal active">
        <h3 id="formTitle" style="margin-bottom: 25px; border-bottom: 2px solid var(--line-color); padding-bottom: 10px;">Tambah Produk Baru</h3>
        
        <form id="productForm">
          <input type="hidden" id="productId" />
          
          <div class="form-group">
            <label>Nama Produk</label>
            <input type="text" id="productName" placeholder="Contoh: Nastar Premium" required />
          </div>

          <div class="form-group">
            <label>Kategori</label>
            <select id="productCategory" required>
              <option value="Kue Kering">🍪 Kue Kering</option>
              <option value="Cake & Bolu">🍰 Cake & Bolu</option>
              <option value="Ulang Tahun Anak">🎈 Ulang Tahun Anak</option>
              <option value="Sweet Seventeen">💄 Sweet Seventeen</option>
              <option value="Pernikahan">💍 Pernikahan</option>
              <option value="Lamaran">💍 Lamaran</option>
            </select>
          </div>

          <div class="form-group">
            <label>Tipe Produk</label>
            <select id="productType" required onchange="togglePriceInputs()">
              <option value="regular">Reguler (Harga Tetap)</option>
              <option value="custom">Custom (Range Harga)</option>
            </select>
          </div>
          
          <!-- Input Harga Dinamis -->
          <div id="priceRegular" class="form-group">
            <label>Harga (Rp)</label>
            <input type="number" id="productPrice" placeholder="75000" />
          </div>

          <div id="priceCustom" class="form-group" style="display:none; grid-template-columns: 1fr 1fr; gap: 10px;">
            <div>
              <label>Harga Min</label>
              <input type="number" id="productPriceMin" placeholder="150000" />
            </div>
            <div>
              <label>Harga Max</label>
              <input type="number" id="productPriceMax" placeholder="300000" />
            </div>
          </div>
          
          <div class="form-group">
            <label>Deskripsi Singkat</label>
            <textarea id="productDesc" rows="3" required placeholder="Deskripsi menggugah selera..."></textarea>
          </div>
          
          <div class="form-group">
            <label>Bahan Utama (Opsional)</label>
            <textarea id="productIngredients" rows="2" placeholder="Butter, Telur, Coklat..."></textarea>
          </div>
          
          <div class="form-group">
            <label>URL Gambar</label>
            <input type="url" id="productImageUrl" required placeholder="https://..." />
          </div>
          
          <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn-primary" style="width: 100%;">Simpan</button>
            <button type="button" id="cancelEditBtn" class="btn-primary" style="background: #666; border-color: #666; display: none;">Batal</button>
          </div>
        </form>
      </div>
      
      <!-- KOLOM KANAN: DAFTAR PRODUK -->
      <div class="admin-card reveal active">
        <h3 style="margin-bottom: 25px; border-bottom: 2px solid var(--line-color); padding-bottom: 10px;">Daftar Menu Saat Ini</h3>
        
        <div class="product-list-container" id="dashboardProductList">
          <p style="text-align: center; padding: 20px;">Memuat data...</p>
        </div>
      </div>

    </div>
  </div>

  <!-- FOOTER -->
  <footer>
    <span class="footer-logo">Ibu Angel Admin</span>
    <p>&copy; 2025 Control Panel.</p>
  </footer>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      
      // === ANIMASI REVEAL ===
      setTimeout(() => {
        document.querySelectorAll('.reveal').forEach(el => el.classList.add('active'));
      }, 100);

      // === UI TOGGLE HARGA ===
      window.togglePriceInputs = function() {
        const type = document.getElementById('productType').value;
        const regDiv = document.getElementById('priceRegular');
        const custDiv = document.getElementById('priceCustom');
        
        if (type === 'regular') {
          regDiv.style.display = 'block';
          custDiv.style.display = 'none';
        } else {
          regDiv.style.display = 'none';
          custDiv.style.display = 'grid';
        }
      }
      
      // === 3. LOGIKA FIREBASE CRUD ===
      const { 
        app, auth, db,
        signInAnonymously, onAuthStateChanged,
        doc, collection, addDoc, setDoc, deleteDoc, onSnapshot
      } = window.firebase;

      let userId = null;
      let productsCollectionRef = null;
      const projectId = window.firebase.app.options.projectId; 
      let allProductsCache = []; 

      const productForm = document.getElementById("productForm");
      const dashboardProductList = document.getElementById("dashboardProductList");
      const formTitle = document.getElementById("formTitle");
      const productIdField = document.getElementById("productId");
      const cancelEditBtn = document.getElementById("cancelEditBtn");
      
      async function initializeFirebase() {
        try {
          await onAuthStateChanged(auth, async (user) => {
            if (!user) await signInAnonymously(auth);
            
            userId = auth.currentUser.uid;
            productsCollectionRef = collection(db, `artifacts/${projectId}/public/data/products`);
            listenForDashboardProducts();
          });
        } catch (error) {
          console.error("Firebase Auth Error:", error);
        }
      }

      function listenForDashboardProducts() {
        onSnapshot(productsCollectionRef, (snapshot) => {
          if (!dashboardProductList) return;
          dashboardProductList.innerHTML = ""; 
          
          allProductsCache = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));

          if (allProductsCache.length === 0) {
            dashboardProductList.innerHTML = "<p style='text-align:center; color:#999;'>Belum ada produk.</p>";
            return;
          }
          
          allProductsCache.sort((a, b) => a.category.localeCompare(b.category));
          
          allProductsCache.forEach(product => {
            const isRegular = product.type === 'regular' || !product.type;
            const priceText = isRegular
              ? `Rp ${(product.price || 0).toLocaleString()}`
              : `Rp ${(product.priceMin || 0).toLocaleString()} - ${(product.priceMax || 0).toLocaleString()}`;
              
            const itemHtml = `
              <div class="admin-product-item">
                <img src="${product.imageUrl || 'https://placehold.co/70'}" class="item-img" alt="${product.name}" onerror="this.src='https://placehold.co/70'">
                <div class="item-info">
                  <h4>${product.name || 'Tanpa Nama'}</h4>
                  <span class="item-price">${priceText}</span>
                  <span>${product.category} (${isRegular ? 'Reguler' : 'Custom'})</span>
                </div>
                <div class="action-buttons">
                  <button class="btn-icon" onclick="editProduct('${product.id}')" title="Edit"><i class="fas fa-pen"></i></button>
                  <button class="btn-icon delete" onclick="deleteProduct('${product.id}')" title="Hapus"><i class="fas fa-trash"></i></button>
                </div>
              </div>
            `;
            dashboardProductList.innerHTML += itemHtml;
          });
        });
      }
      
      window.editProduct = function(id) {
        const product = allProductsCache.find(p => p.id === id);
        if (!product) return;
        
        formTitle.textContent = 'Edit Produk: ' + product.name;
        productIdField.value = product.id;
        
        document.getElementById('productName').value = product.name || '';
        document.getElementById('productDesc').value = product.description || '';
        document.getElementById('productIngredients').value = product.ingredients || '';
        document.getElementById('productImageUrl').value = product.imageUrl || '';
        document.getElementById('productCategory').value = product.category || 'Kue Kering';
        document.getElementById('productType').value = product.type || 'regular';
        
        document.getElementById('productPrice').value = product.price || '';
        document.getElementById('productPriceMin').value = product.priceMin || '';
        document.getElementById('productPriceMax').value = product.priceMax || '';

        togglePriceInputs(); 
        
        cancelEditBtn.style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }

      window.deleteProduct = async function(id) {
        if (confirm('Yakin ingin menghapus produk ini?')) {
          try {
            await deleteDoc(doc(productsCollectionRef, id));
          } catch (error) {
            alert("Gagal menghapus: " + error.message);
          }
        }
      }

      if (productForm) {
        productForm.addEventListener('submit', async (e) => {
          e.preventDefault();
          
          const type = document.getElementById('productType').value;
          const productData = {
            name: document.getElementById('productName').value,
            description: document.getElementById('productDesc').value,
            ingredients: document.getElementById('productIngredients').value,
            imageUrl: document.getElementById('productImageUrl').value,
            category: document.getElementById('productCategory').value,
            type: type
          };

          if (type === 'regular') {
             productData.price = parseInt(document.getElementById('productPrice').value) || 0;
             productData.priceMin = 0; productData.priceMax = 0;
          } else {
             productData.price = 0;
             productData.priceMin = parseInt(document.getElementById('productPriceMin').value) || 0;
             productData.priceMax = parseInt(document.getElementById('productPriceMax').value) || 0;
          }
          
          try {
            const id = productIdField.value;
            if (id) {
              await setDoc(doc(productsCollectionRef, id), productData, { merge: true });
            } else {
              await addDoc(productsCollectionRef, productData);
            }
            resetProductForm();
          } catch (error) {
            console.error("Error saving:", error);
            alert("Gagal menyimpan: " + error.message);
          }
        });
      }
      
      function resetProductForm() {
        formTitle.textContent = 'Tambah Produk Baru';
        productForm.reset();
        productIdField.value = '';
        cancelEditBtn.style.display = 'none';
        document.getElementById('productType').value = 'regular';
        togglePriceInputs();
      }
      
      if (cancelEditBtn) cancelEditBtn.addEventListener('click', resetProductForm);
      
      initializeFirebase();
    });
  </script>
</body>
</html>