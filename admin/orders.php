<?php
require_once __DIR__ . '/../app/auth_check.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Daftar Pesanan | Ibu Angel Admin</title>
  
  <!-- Style Utama -->
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
      getFirestore, doc, collection, setDoc, onSnapshot, query
    } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

    const app = initializeApp(firebaseConfig);
    const auth = getAuth(app);
    const db = getFirestore(app);

    window.firebase = {
      app, auth, db,
      signInAnonymously, onAuthStateChanged,
      doc, collection, setDoc, onSnapshot, query
    };
  </script>

  <style>
    body { padding-top: 100px; }

    .admin-container {
      max-width: 1300px;
      margin: 0 auto;
      padding: 0 20px;
      justify-content: center;
    }

    /* Kartu Utama (Paper Style) */
    .admin-card {
      background: #fff;
      padding: 50px;
      border: 1px solid var(--line-color);
      position: relative;
      min-height: 600px;
    }
    
    .admin-card::before {
      content: '';
      position: absolute;
      top: -6px; 
      left: -6px;
      width: calc(100% + 12px);
      height: calc(100% + 12px);
      border: 1px solid var(--text-dark);
      z-index: -1;
    }



    /* Order Item Styling */
    .order-item {
      border: 1px solid var(--line-color);
      padding: 25px;
      margin-bottom: 20px;
      background: var(--bg-cream);
      transition: transform 0.2s ease;
    }

    .order-item:hover {
      transform: translateY(-3px);
      border-color: var(--accent);
    }

    .order-header-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 15px;
      border-bottom: 1px dashed var(--line-color);
      padding-bottom: 15px;
    }

    .order-id {
      font-family: var(--font-heading);
      font-size: 1.2rem;
      color: var(--text-dark);
    }

    .order-date {
      font-size: 0.9rem;
      color: var(--text-light);
    }

    /* Status Badge yang Elegan */
    .status-badge {
      padding: 6px 16px;
      font-size: 0.85rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      border: 1px solid;
    }

    .status-pending { background: #fff3cd; color: #856404; border-color: #ffeeba; }
    .status-processing { background: #d1ecf1; color: #0c5460; border-color: #bee5eb; }
    .status-completed { background: #d4edda; color: #155724; border-color: #c3e6cb; }
    .status-rejected { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }

    .order-details p { margin-bottom: 5px; font-size: 0.95rem; }
    .order-details strong { color: var(--text-dark); }
    
    .order-items-list {
      margin: 15px 0;
      padding: 15px;
      background: #fff;
      border: 1px solid var(--line-color);
    }
    .order-items-list li {
      list-style: none;
      margin-bottom: 5px;
      display: flex;
      justify-content: space-between;
    }

    .order-actions {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      margin-top: 20px;
    }

    .btn-sm {
      padding: 8px 20px;
      font-size: 0.8rem;
    }
    
    .btn-success { background: #27ae60; border-color: #27ae60; color: white; }
    .btn-success:hover { background: #219150; }
    
    .btn-danger { background: #c0392b; border-color: #c0392b; color: white; }
    .btn-danger:hover { background: #a93226; }

    @media (max-width: 768px) {
      .admin-card { padding: 25px; }
      .order-header-row { flex-direction: column; gap: 10px; }
    }
  </style>
</head>
<body>

  <!-- NAVBAR ADMIN -->
<nav class="navbar" id="navbar">
    <a href="../index.php" class="logo">Ibu Angel</a>
    <div class="nav-links">
        <a href="../index.php">Home</a>
        <a href="orders.php" class="active" style="color: var(--accent);">Pesanan</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php" style="color:#C0392B;">Logout</a>
    </div>
</nav>


  <div class="section">
    <div class="section-header reveal active">
      <h2>Daftar Pesanan Masuk</h2>
      <p>Pantau dan kelola pesanan pelanggan secara real-time.</p>
    </div>

    <div class="admin-container reveal active">
      <div class="admin-card">
        <div id="ordersList">
          <p style="text-align: center; padding: 40px; color: var(--text-light);">Sedang memuat data pesanan...</p>
        </div>
      </div>
    </div>
  </div>

  <footer>
    <span class="footer-logo">Ibu Angel Admin</span>
    <p>&copy; 2025 Control Panel.</p>
  </footer>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // Animasi Reveal
      setTimeout(() => document.querySelectorAll('.reveal').forEach(el => el.classList.add('active')), 100);
      
      // Firebase Logic
      const { app, auth, db, signInAnonymously, onAuthStateChanged, doc, collection, setDoc, onSnapshot } = window.firebase;
      
      const projectId = window.firebase.app.options.projectId;
      const ordersList = document.getElementById("ordersList");
      let ordersCollectionRef = null;
      
      onAuthStateChanged(auth, async (user) => {
        if (!user) await signInAnonymously(auth);
        
        ordersCollectionRef = collection(db, `artifacts/${projectId}/public/data/orders`);
        
        onSnapshot(ordersCollectionRef, (snapshot) => {
          if (!ordersList) return;
          ordersList.innerHTML = "";
          
          let allOrders = snapshot.docs.map(doc => ({ id: doc.id, ...doc.data() }));

          if (allOrders.length === 0) {
            ordersList.innerHTML = "<p style='text-align:center; padding:40px;'>Belum ada pesanan masuk.</p>";
            return;
          }
          
          // Sort by date (terbaru di atas - asumsi format date string YYYY-MM-DD)
          allOrders.sort((a, b) => (b.date || '').localeCompare(a.date || ''));

          allOrders.forEach(order => {
            const statusClass = 'status-' + (order.status || 'pending').toLowerCase();
            
            const itemHtml = `
              <div class="order-item">
                <div class="order-header-row">
                  <div>
                    <span class="order-id">#${order.id.substring(0, 6).toUpperCase()}</span>
                    <div class="order-date"><i class="far fa-calendar-alt"></i> ${order.date || '-'}</div>
                  </div>
                  <span class="status-badge ${statusClass}">${order.status || 'Pending'}</span>
                </div>
                
                <div class="order-details">
                  <p><strong><i class="far fa-user"></i> Pelanggan:</strong> ${order.customer || 'Tanpa Nama'}</p>
                  <p><strong><i class="fas fa-phone"></i> Kontak:</strong> ${order.phone || '-'}</p>
                  
                  <ul class="order-items-list">
                    ${(order.items || []).map(item => `<li><span>${item}</span></li>`).join('')}
                  </ul>
                  
                  ${order.details ? `<p style="background:#fff; padding:10px; border:1px dashed #ccc; font-size:0.9em;"><em>Catatan: ${order.details}</em></p>` : ''}
                  
                  <p style="margin-top:15px; font-size:1.1rem; color:var(--accent);">
                    <strong>Total: Rp ${(order.total || 0).toLocaleString()}</strong>
                  </p>
                </div>

                <div class="order-actions">
                  ${order.status !== 'Completed' ? `<button class="btn-primary btn-sm btn-success" onclick="updateStatus('${order.id}', 'Completed')">Selesai</button>` : ''}
                  ${order.status !== 'Processing' && order.status !== 'Completed' ? `<button class="btn-primary btn-sm" onclick="updateStatus('${order.id}', 'Processing')">Proses</button>` : ''}
                  ${order.status !== 'Rejected' && order.status !== 'Completed' ? `<button class="btn-primary btn-sm btn-danger" onclick="updateStatus('${order.id}', 'Rejected')">Tolak</button>` : ''}
                </div>
              </div>
            `;
            ordersList.innerHTML += itemHtml;
          });
        });
      });

      window.updateStatus = async (orderId, newStatus) => {
        if (!ordersCollectionRef) return;
        try {
          await setDoc(doc(ordersCollectionRef, orderId), { status: newStatus }, { merge: true });
        } catch (error) {
          alert(`Gagal update status: ${error.message}`);
        }
      };
    });
  </script>
</body>
</html>