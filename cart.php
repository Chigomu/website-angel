<?php
require_once 'app/settings_loader.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Keranjang Belanja | Ibuké Enjel</title>
  <link rel="stylesheet" href="style.css">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <?php require_once 'app/dynamic_style.php'; ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

  <style>
    body { padding-top: 80px; background-color: var(--bg-cream); }
    .nav-links, .nav-links li { list-style: none !important; padding: 0; margin: 0; }
    
    .section { padding: 20px !important; }

    /* === FIX TOMBOL 'MULAI BELANJA' (Agar tidak hilang saat hover) === */
    #empty-cart-msg .btn-primary:hover {
        background-color: var(--text-dark); /* Warna gelap saat hover */
        color: #fff;
        border-color: var(--text-dark);
    }

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
    }

    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 15px 0;
        border-bottom: 1px dashed var(--line-color);
    }
    .cart-item:last-child { border-bottom: none; }

    .item-info h4 { margin: 0 0 5px 0; font-size: 1.1rem; color: var(--text-dark); }
    .item-info .price { color: var(--text-light); font-size: 0.9rem; }
    .custom-details { font-size: 0.8rem; color: #777; margin-top: 5px; background: #fafafa; padding: 5px 10px; border-radius: 5px; display: inline-block; }
    
    .qty-wrapper {
        display: flex; align-items: center; border: 1px solid var(--line-color); border-radius: 5px; overflow: hidden;
    }
    .qty-btn { width: 30px; height: 30px; border: none; background: #fff; cursor: pointer; transition: 0.2s; }
    .qty-btn:hover { background: #eee; }
    .qty-val { padding: 0 10px; font-weight: 600; font-size: 0.9rem; }

    /* TOMBOL AKSI KERANJANG */
    .cart-actions {
        margin-top: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
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

    /* === KOLOM KANAN: FORM CHECKOUT === */
    .checkout-form {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        border: 1px solid var(--line-color);
        box-shadow: 0 5px 20px rgba(0,0,0,0.03);
        position: sticky;
        top: 100px;
        text-align: center; /* Agar Turnstile di tengah */
    }

    .form-header { border-bottom: 2px solid var(--accent); padding-bottom: 15px; margin-bottom: 20px; text-align: left; }
    .form-header h3 { margin: 0; font-size: 1.3rem; }

    .form-group { margin-bottom: 15px; text-align: left; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 0.9rem; }
    .form-group input, .form-group textarea {
        width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-family: inherit; font-size: 0.95rem; background: #fafafa;
    }
    .form-group textarea { min-height: 80px; resize: vertical; }

    .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.95rem; color: var(--text-light); }
    .summary-total { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 15px; border-top: 1px dashed #ccc; font-weight: 700; font-size: 1.2rem; color: var(--text-dark); }

    /* === FIX TURNSTILE CENTERING === */
    .cf-turnstile {
        margin: 20px auto;
        display: table; /* Trik agar container mengikuti lebar konten dan bisa di-margin auto */
    }

    .btn-whatsapp {
        width: 100%; padding: 14px; background: #25d366; color: #fff; border: none; border-radius: 8px;
        font-weight: 700; text-transform: uppercase; font-size: 1rem; margin-top: 20px; cursor: pointer; transition: 0.3s;
        display: flex; align-items: center; justify-content: center; gap: 10px;
    }
    .btn-whatsapp:hover { background: #1ebd59; box-shadow: 0 5px 15px rgba(37, 211, 102, 0.3); }
    
    @media (max-width: 850px) {
        .checkout-grid { grid-template-columns: 1fr; }
        .cart-section, .checkout-form { padding: 20px; }
    }
  </style>
</head>
<body>

<nav class="navbar" id="navbar">
    <a href="index.php" class="logo">Ibuké Enjel</a>
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
            <div id="cart-items-list"></div>

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

                <div class="cf-turnstile" data-sitekey="0x4AAAAAACDUspNryUVk8fAB" style="margin-top: 20px;"></div> <!-- Hosting -->
                <!--<div class="cf-turnstile" data-sitekey="1x00000000000000000000AA" style="margin-top: 20px;"></div>-->

                <button type="submit" class="btn-whatsapp">
                    <i class="fab fa-whatsapp"></i> Kirim Pesanan
                </button>
            </form>
        </div>

    </div>
</div>

<script>
    const ADMIN_WA = "<?= set('contact_phone') ?>";
</script>
<script>
    let cart = JSON.parse(localStorage.getItem('ibuangel_cart')) || [];

    function initCart() {
        if (cart.length === 0) {
            document.getElementById('checkout-content').style.display = 'none';
            document.getElementById('empty-cart-msg').style.display = 'flex'; 
            return;
        }

        const listContainer = document.getElementById('cart-items-list');
        listContainer.innerHTML = '';
        let total = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;
            
            let customHtml = '';
            if(item.type === 'custom') {
                customHtml = `<div class="custom-details">Note: ${item.details} <br> Tgl: ${item.date}</div>`;
            }

            listContainer.innerHTML += `
            <div class="cart-item">
                <div class="item-info">
                    <h4>${item.name} <span style="font-size:0.8rem; color:var(--accent);">x${item.qty}</span></h4>
                    <div class="price">Rp ${item.price.toLocaleString('id-ID')}</div>
                    ${customHtml}
                </div>
                <div class="qty-wrapper">
                    <button class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
                    <span class="qty-val">${item.qty}</span>
                    <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                </div>
                <button onclick="deleteItem(${index})" style="color:#c0392b; background:none; border:none; cursor:pointer; margin-left:10px;">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
        });

        document.getElementById('subtotal-display').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('total-display').innerText = 'Rp ' + total.toLocaleString('id-ID');
        updateBadge();
    }

    function updateQty(index, change) {
        if (cart[index].qty + change > 0) { cart[index].qty += change; } 
        else { if (confirm("Hapus item ini?")) cart.splice(index, 1); }
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

        // === VALIDASI TURNSTILE SEMENTARA DI CLIENT ===
        // Untuk localhost, token dummy '1x000...' akan selalu valid secara visual.
        // Di server production, Anda harus cek token ini ke API Cloudflare.
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
                let msg = `Halo Ibuké Enjel, saya *${name}* ingin memesan:%0A%0A`;
                cart.forEach((item, i) => {
                    const subtotal = item.price * item.qty;
                    msg += `${i+1}. ${item.name} (x${item.qty}) - Rp ${subtotal.toLocaleString('id-ID')}%0A`;
                    if(item.type === 'custom') msg += `   _Note: ${item.details}, Tgl: ${item.date}_%0A`;
                });
                msg += `%0A*Total Estimasi: Rp ${total.toLocaleString('id-ID')}*`;
                msg += `%0A%0A*Alamat Pengiriman:*%0A${address}`;
                
                window.open(`https://wa.me/${ADMIN_WA}?text=${msg}`, "_blank");
                
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