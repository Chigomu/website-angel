<?php
require_once 'app/settings_loader.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Keranjang Belanja | Ibu Angel</title>
  <link rel="stylesheet" href="style.css">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <?php require_once 'app/dynamic_style.php'; ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

  <style>
    body { padding-top: 80px; background-color: var(--bg-cream); }
    .nav-links, .nav-links li { list-style: none !important; padding: 0; margin: 0; }
    
    .section { padding: 20px !important; }

    /* === STYLE EMPTY STATE === */
    #empty-cart-msg {
        display: none; 
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        min-height: 60vh; 
        gap: 15px;
    }
    #empty-cart-msg i { font-size: 5rem; color: #e0e0e0; margin-bottom: 10px; }
    #empty-cart-msg h3 { font-size: 1.5rem; color: var(--text-dark); margin: 0; }
    #empty-cart-msg p { color: var(--text-light); margin: 0; }

    /* LAYOUT UTAMA */
    .checkout-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 20px;
        max-width: 1100px;
        margin: 0 auto;
        align-items: start;
    }

    /* KOLOM KIRI: KERANJANG */
    .cart-section {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        border: 1px solid var(--line-color);
        box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        min-height: 380px;
        display: flex;
        flex-direction: column;
    }

    .cart-items-container {
        flex: 1; 
    }

    /* ITEM KERANJANG */
    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center; 
        padding: 15px 0;
        border-bottom: 1px dashed var(--line-color);
        animation: fadeIn 0.3s ease;
    }
    .cart-item:last-child { border-bottom: none; }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .item-info { flex: 1; padding-right: 15px; }
    .item-info .price { color: var(--text-light); font-size: 0.9rem; margin-top: 2px; }
    
    /* LINK "LIHAT" */
    .read-more-link {
        color: var(--accent);
        cursor: pointer;
        text-decoration: underline;
        font-weight: 600;
        margin-left: 3px;
        font-size: 0.75rem;
    }
    .read-more-link:hover { color: var(--text-dark); }

    /* CONTROLS */
    .cart-controls-wrapper { display: flex; align-items: center; }
    
    .qty-wrapper {
        display: inline-flex; 
        align-items: center; 
        border: 1px solid #ddd; 
        border-radius: 6px; 
        overflow: hidden;
        background: #fff;
    }
    
    .qty-btn { 
        width: 30px; height: 30px; 
        border: none; background: #f8f9fa; cursor: pointer; 
        display: flex; align-items: center; justify-content: center;
        color: #555; transition: 0.2s;
    }
    .qty-btn:hover { background: #e9ecef; color: #000; }
    
    .qty-val { 
        min-width: 35px; text-align: center; font-weight: 600; font-size: 0.9rem; 
        line-height: 30px; border-left: 1px solid #eee; border-right: 1px solid #eee;
    }

    .btn-delete-item {
        color: #e74c3c; background: rgba(231, 76, 60, 0.1); border: none; border-radius: 6px;
        width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: 0.2s; margin-left: 10px;
    }
    .btn-delete-item:hover { background: #e74c3c; color: #fff; }

    /* PAGINATION */
    .cart-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 2px solid #f0f0f0;
    }
    .page-btn {
        background: #fff; border: 1px solid var(--accent); color: var(--accent);
        width: 35px; height: 35px; border-radius: 50%;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: 0.3s;
    }
    .page-btn:hover { background: var(--accent); color: #fff; }
    .page-btn:disabled { border-color: #ddd; color: #ccc; cursor: not-allowed; background: #fff; }
    .page-info { font-size: 0.9rem; font-weight: 600; color: var(--text-light); }

    /* ACTIONS & FORM */
    .cart-actions { margin-top: 20px; display: flex; justify-content: space-between; align-items: center; }
    .btn-secondary {
        text-decoration: none; color: var(--text-light); font-weight: 600; font-size: 0.9rem; transition: 0.3s;
        padding: 10px 20px; border: 1px solid var(--line-color); border-radius: 6px; display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-secondary:hover { background: #eee; color: var(--text-dark); }
    .btn-danger {
        text-decoration: none; color: #c0392b; font-weight: 600; font-size: 0.9rem; transition: 0.3s;
        padding: 10px 20px; border: 1px solid #e74c3c; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px;
    }
    .btn-danger:hover { background: #c0392b; color: #fff; }

    .checkout-form {
        background: #fff; padding: 25px; border-radius: 12px;
        border: 1px solid var(--line-color); box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        position: sticky; top: 100px;
    }
    .form-header { border-bottom: 2px solid var(--accent); padding-bottom: 15px; margin-bottom: 20px; }
    .form-header h3 { margin: 0; font-size: 1.3rem; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; }
    .form-group input, .form-group textarea {
        width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-family: inherit; font-size: 0.95rem; background: #fafafa;
    }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.95rem; color: var(--text-light); }
    .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 15px; border-top: 1px dashed #ccc; font-weight: 700; font-size: 1.2rem; color: var(--text-dark); }
    
    .btn-whatsapp {
        width: 100%; padding: 14px; background: #25d366; color: #fff; border: none; border-radius: 8px;
        font-weight: 700; text-transform: uppercase; font-size: 1rem; margin-top: 20px; cursor: pointer; transition: 0.3s;
        display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .btn-whatsapp:hover { background: #1ebd59; box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3); }

    /* === STYLE MODAL NOTE KHUSUS === */
    /* Kita menimpa style modal default agar lebih sederhana untuk teks */
    #noteModal .modal-content {
        display: block !important; /* Matikan Grid dari style.css */
        width: 90%; 
        max-width: 450px;
        height: auto; 
        margin: 15vh auto;
        padding: 30px;
        text-align: left;
        border-radius: 12px;
    }

    @media (max-width: 850px) {
        .checkout-grid { grid-template-columns: 1fr; }
        .cart-item { flex-wrap: wrap; }
        .item-info { width: 100%; margin-bottom: 10px; padding-right: 0; }
        .cart-controls-wrapper { margin-left: auto; }
    }
  </style>
</head>
<body>

<nav class="navbar" id="navbar">
    <a href="index.php" class="logo">Ibu Angel</a>
    <ul class="nav-links">
      <li><a href="index.php#home">Beranda</a></li>
      <li><a href="index.php#about">Tentang</a></li>
      <li><a href="index.php#produk">Menu</a></li>
      <li><a href="custom.php">Custom</a></li>
      <li><a href="index.php#lokasi">Kontak</a></li>
      <li><a href="cart.php" class="active" style="color: var(--accent); font-size: 1.2rem;"><i class="fas fa-shopping-cart"></i> <span id="cart-badge" style="font-size: 0.8rem; vertical-align: top;"></span></a></li>
    </ul>
</nav>

<div class="section reveal active">
    
    <div id="empty-cart-msg">
        <i class="fas fa-shopping-basket"></i>
        <h3>Keranjang Belanja Kosong</h3>
        <p>Sepertinya Anda belum menambahkan kue favorit.</p>
        <a href="index.php#produk" class="btn-primary" style="margin-top: 15px; display: inline-block;">Mulai Belanja</a>
    </div>

    <div class="checkout-grid" id="checkout-content">
        
        <div class="cart-section">
            <h3 style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">Daftar Pesanan</h3>
            
            <div id="cart-items-container" class="cart-items-container">
                <!-- Item di-render via JS -->
            </div>

            <div id="pagination-controls" class="cart-pagination" style="display: none;">
                <button class="page-btn" onclick="changePage(-1)" id="btn-prev"><i class="fas fa-chevron-left"></i></button>
                <span class="page-info" id="page-indicator">1 / 1</span>
                <button class="page-btn" onclick="changePage(1)" id="btn-next"><i class="fas fa-chevron-right"></i></button>
            </div>

            <div class="cart-actions">
                <a href="index.php#produk" class="btn-secondary">
                    <i class="fas fa-arrow-left"></i> Lanjut Belanja
                </a>
                <button onclick="cancelOrder()" class="btn-danger">
                    <i class="fas fa-trash"></i> Batalkan Pesanan
                </button>
            </div>
        </div>

        <div class="checkout-form">
            <div class="form-header">
                <h3>Data Pemesan</h3>
            </div>
            
            <form id="checkoutForm" onsubmit="processCheckout(event)">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" id="custName" required placeholder="Nama Anda...">
                </div>
                
                <div class="form-group">
                    <label>Alamat Pengiriman</label>
                    <textarea id="custAddress" required placeholder="Alamat lengkap pengiriman..."></textarea>
                </div>

                <div class="summary-row">
                    <span>Subtotal Produk</span>
                    <span id="subtotal-display">Rp 0</span>
                </div>
                <div class="summary-row">
                    <span>Ongkos Kirim</span>
                    <span>Menyesuaikan</span>
                </div>
                <div class="summary-total">
                    <span>Total Estimasi</span>
                    <span id="total-display">Rp 0</span>
                </div>

                <div class="cf-turnstile" data-sitekey="0x4AAAAAACDUspNryUVk8fAB" style="margin-top: 20px;"></div>

                <button type="submit" class="btn-whatsapp">
                    <i class="fab fa-whatsapp"></i> Kirim Pesanan
                </button>
            </form>
        </div>

    </div>
</div>

<!-- === MODAL POPUP UNTUK NOTE === -->
<div id="noteModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeNoteModal">&times;</span>
        <h3 style="margin-bottom: 15px; font-size: 1.5rem; color: var(--accent);">Catatan Pesanan</h3>
        <div style="background: #fafafa; padding: 15px; border-radius: 8px; border: 1px solid #eee;">
            <p id="fullNoteContent" style="white-space: pre-wrap; color: var(--text-dark); line-height: 1.6; margin: 0;"></p>
        </div>
        <p style="margin-top: 15px; font-size: 0.85rem; color: #888;">
            <i class="fas fa-info-circle"></i> Tanggal: <span id="noteDate" style="font-weight: 600;"></span>
        </p>
    </div>
</div>

<script>
    let cart = JSON.parse(localStorage.getItem('ibuangel_cart')) || [];
    
    let currentPage = 1;
    const itemsPerPage = 4;
    const CHAR_LIMIT = 25; 

    function initCart() {
        if (cart.length === 0) {
            document.getElementById('checkout-content').style.display = 'none';
            document.getElementById('empty-cart-msg').style.display = 'flex'; 
            return;
        }

        let total = 0;
        cart.forEach(i => total += i.price * i.qty);
        document.getElementById('subtotal-display').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('total-display').innerText = 'Rp ' + total.toLocaleString('id-ID');
        updateBadge();

        renderPage(); 
    }

    function renderPage() {
        const container = document.getElementById('cart-items-container');
        const paginationControls = document.getElementById('pagination-controls');
        
        container.innerHTML = '';

        const totalPages = Math.ceil(cart.length / itemsPerPage);
        if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const itemsToShow = cart.slice(startIndex, endIndex);

        itemsToShow.forEach((item, index) => {
            const originalIndex = startIndex + index; 
            
            let customHtml = '';
            
            if(item.type === 'custom') {
                let displayText = item.details;
                let expandAction = '';

                // Logika Potong Teks & Modal Trigger
                if (item.details.length > CHAR_LIMIT) {
                    displayText = item.details.substring(0, CHAR_LIMIT) + '...';
                    // Memanggil fungsi openNoteModal dengan index asli
                    expandAction = `<span class="read-more-link" onclick="openNoteModal(${originalIndex})">Lihat</span>`;
                }

                customHtml = `<span style="font-size: 0.75rem; font-weight: normal; color: #666; background: #f4f4f4; padding: 2px 8px; border-radius: 4px; border: 1px solid #eee; display: inline-flex; align-items: center; margin-left: 0;">
                    <i class="fas fa-pen" style="font-size:0.6rem; margin-right:4px;"></i> 
                    <span>${displayText}</span> ${expandAction}
                </span>`;
            }

            container.innerHTML += `
            <div class="cart-item">
                <div class="item-info">
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px;">
                        <h4 style="margin: 0; font-size: 1.1rem; color: var(--text-dark);">${item.name}</h4>
                        ${customHtml}
                    </div>

                    <div class="price">
                        Rp ${item.price.toLocaleString('id-ID')} 
                        <span style="font-size:0.8rem; color:#888; margin-left:5px;">x ${item.qty}</span>
                    </div>
                </div>
                
                <div class="cart-controls-wrapper">
                    <div class="qty-wrapper">
                        <button class="qty-btn" onclick="updateQty(${originalIndex}, -1)"><i class="fas fa-minus" style="font-size:0.7rem"></i></button>
                        <span class="qty-val">${item.qty}</span>
                        <button class="qty-btn" onclick="updateQty(${originalIndex}, 1)"><i class="fas fa-plus" style="font-size:0.7rem"></i></button>
                    </div>
                    <button onclick="deleteItem(${originalIndex})" class="btn-delete-item" title="Hapus Item">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>`;
        });

        if (totalPages > 1) {
            paginationControls.style.display = 'flex';
            document.getElementById('page-indicator').innerText = `Halaman ${currentPage} / ${totalPages}`;
            document.getElementById('btn-prev').disabled = (currentPage === 1);
            document.getElementById('btn-next').disabled = (currentPage === totalPages);
        } else {
            paginationControls.style.display = 'none';
        }
    }

    // === LOGIKA MODAL POPUP ===
    const noteModal = document.getElementById("noteModal");
    const closeNoteBtn = document.getElementById("closeNoteModal");
    const noteContent = document.getElementById("fullNoteContent");
    const noteDate = document.getElementById("noteDate");

    function openNoteModal(index) {
        const item = cart[index];
        noteContent.innerText = item.details; // Isi teks lengkap
        noteDate.innerText = item.date;       // Isi tanggal
        noteModal.style.display = "block";    // Tampilkan modal
    }

    closeNoteBtn.onclick = () => noteModal.style.display = "none";
    window.onclick = (e) => { 
        // Menutup modal jika klik area hitam di luar box
        if (e.target == noteModal) noteModal.style.display = "none"; 
    };

    // Fungsi Navigasi Lain
    function changePage(direction) {
        currentPage += direction;
        renderPage();
    }

    function updateQty(index, change) {
        if (cart[index].qty + change > 0) { 
            cart[index].qty += change; 
        } else { 
            if (confirm("Hapus item ini?")) cart.splice(index, 1); 
        }
        saveCart();
    }

    function deleteItem(index) {
        if(confirm("Hapus item ini?")) { cart.splice(index, 1); saveCart(); }
    }

    function cancelOrder() {
        if(confirm("Yakin ingin membatalkan seluruh pesanan?")) {
            cart = []; saveCart(); window.location.href = 'index.php';
        }
    }

    function saveCart() {
        localStorage.setItem('ibuangel_cart', JSON.stringify(cart));
        initCart();
    }

    function updateBadge() {
        const count = cart.reduce((sum, item) => sum + item.qty, 0);
        const badge = document.getElementById('cart-badge');
        if(badge) badge.textContent = count > 0 ? `(${count})` : '';
    }

    async function processCheckout(e) {
        e.preventDefault();
        
        const name = document.getElementById('custName').value;
        const address = document.getElementById('custAddress').value;
        const formData = new FormData(document.getElementById('checkoutForm'));
        const turnstileToken = formData.get('cf-turnstile-response'); 

        if (!turnstileToken) {
            alert("Silakan selesaikan verifikasi keamanan (Captcha) terlebih dahulu.");
            return;
        }

        let total = 0;
        cart.forEach(i => total += i.price * i.qty);

        const orderData = {
            name: name,
            address: address,
            items: cart,
            total: total,
            'cf-turnstile-response': turnstileToken 
        };

        try {
            const response = await fetch('save_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            });
            
            const result = await response.json();

            if(result.status === 'success') {
                let msg = `Halo Ibu Angel, saya *${name}* ingin memesan:%0A%0A`;
                cart.forEach((item, i) => {
                    const subtotal = item.price * item.qty;
                    msg += `${i+1}. ${item.name} (x${item.qty}) - Rp ${subtotal.toLocaleString('id-ID')}%0A`;
                    if(item.type === 'custom') msg += `   _Note: ${item.details}, Tgl: ${item.date}_%0A`;
                });
                msg += `%0A*Total Estimasi: Rp ${total.toLocaleString('id-ID')}*`;
                msg += `%0A%0A*Alamat Pengiriman:*%0A${address}`;
                
                window.open(`https://wa.me/6289689433798?text=${msg}`, "_blank");
                
                cart = [];
                localStorage.setItem('ibuangel_cart', JSON.stringify([]));
                window.location.href = 'index.php';
            } else {
                alert('Gagal memproses pesanan: ' + (result.message || 'Unknown error'));
                if (typeof turnstile !== 'undefined') turnstile.reset();
            }
        } catch(err) {
            console.error(err);
            alert('Terjadi kesalahan koneksi.');
        }
    }

    document.addEventListener("DOMContentLoaded", initCart);
</script>

</body>
</html>