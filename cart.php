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
  <style>
    /* CSS Khusus Halaman Cart */
    body { padding-top: 80px; }
    .nav-links, .nav-links li { list-style: none !important; padding: 0; margin: 0; }
    
    .cart-container { max-width: 800px; margin: 0 auto; padding: 20px; background: #fff; border-radius: 12px; border: 1px solid var(--line-color); box-shadow: 0 5px 15px rgba(0,0,0,0.03); }
    .empty-cart { text-align: center; padding: 50px 20px; color: var(--text-light); }
    
    .cart-item { display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 1px dashed var(--line-color); }
    .cart-item:last-child { border-bottom: none; }
    
    .item-details h4 { font-size: 1.1rem; margin-bottom: 5px; color: var(--text-dark); }
    .item-price { color: var(--text-light); font-size: 0.95rem; }
    .custom-badge { background: var(--accent); color: white; font-size: 0.7rem; padding: 2px 8px; border-radius: 12px; font-weight: 600; margin-left: 8px; }
    .custom-details { background: var(--bg-cream); padding: 8px 12px; border-radius: 6px; margin-top: 8px; font-size: 0.85rem; color: var(--text-light); }
    
    .cart-controls { display: flex; align-items: center; gap: 15px; }
    .quantity-controls { display: flex; align-items: center; border: 1px solid var(--line-color); border-radius: 6px; overflow: hidden; }
    .qty-btn { width: 32px; height: 32px; background: #fff; border: none; cursor: pointer; font-size: 1rem; transition: 0.2s; }
    .qty-btn:hover { background: var(--bg-cream); }
    .qty-display { padding: 0 12px; font-weight: 600; min-width: 30px; text-align: center; }
    
    .delete-btn { color: #c0392b; cursor: pointer; border: none; background: none; font-size: 1.1rem; padding: 5px; transition: 0.2s; }
    .delete-btn:hover { transform: scale(1.1); }
    
    .cart-summary { margin-top: 30px; padding-top: 20px; border-top: 2px solid var(--text-dark); }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1rem; }
    .summary-total { font-weight: 700; font-size: 1.3rem; color: var(--accent); margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--line-color); }
    
    .btn-checkout { width: 100%; padding: 15px; background: var(--accent); color: white; border: none; font-weight: 600; font-size: 1.1rem; text-transform: uppercase; cursor: pointer; margin-top: 20px; border-radius: 8px; transition: 0.3s; }
    .btn-checkout:hover { background: var(--text-dark); }
    
    @media (max-width: 600px) {
        .cart-item { flex-direction: column; align-items: flex-start; gap: 15px; }
        .cart-controls { width: 100%; justify-content: space-between; }
    }
  </style>
</head>
<body>

<nav class="navbar" id="navbar">
    <a href="index.php" class="logo">Ibu Angel</a>
    <ul class="nav-links">
      <li><a href="index.php">Beranda</a></li>
      <li><a href="index.php#about">Tentang</a></li>
      <li><a href="index.php#produk">Menu</a></li>
      <li><a href="custom.php">Custom</a></li>
      <li><a href="index.php#lokasi">Kontak</a></li>
      <li><a href="cart.php" class="active" style="color: var(--accent); font-size: 1.2rem;"><i class="fas fa-shopping-cart"></i> <span id="cart-badge" style="font-size: 0.8rem; vertical-align: top;"></span></a></li>
    </ul>
</nav>

<div class="section reveal active">
    <div class="section-header" style="text-align: center; margin-bottom: 30px;">
        <h2>Keranjang Belanja</h2>
        <p>Cek kembali pesanan Anda sebelum checkout.</p>
    </div>

    <div class="cart-container" id="cart-content">
        <div class="empty-cart">
            <i class="fas fa-shopping-basket" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
            <p>Keranjang Anda masih kosong.</p>
            <a href="index.php#produk" style="color: var(--accent); text-decoration: none; font-weight: 600; margin-top: 10px; display: inline-block;">Mulai Belanja</a>
        </div>
    </div>
</div>

<script>
    // === LOGIKA KERANJANG (LOCALSTORAGE) ===
    let cart = JSON.parse(localStorage.getItem('ibuangel_cart')) || [];

    function saveCart() {
        localStorage.setItem('ibuangel_cart', JSON.stringify(cart));
        renderCart();
        updateBadge();
    }

    function updateBadge() {
        const badge = document.getElementById('cart-badge');
        const count = cart.reduce((sum, item) => sum + item.qty, 0);
        badge.textContent = count > 0 ? `(${count})` : '';
    }

    function renderCart() {
        const container = document.getElementById('cart-content');
        if (cart.length === 0) {
            container.innerHTML = `
                <div class="empty-cart">
                    <i class="fas fa-shopping-basket" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                    <p>Keranjang Anda masih kosong.</p>
                    <a href="index.php#produk" style="color: var(--accent); text-decoration: none; font-weight: 600; margin-top: 10px; display: inline-block;">Mulai Belanja</a>
                </div>`;
            return;
        }

        let html = '';
        let total = 0;

        cart.forEach((item, index) => {
            const itemTotal = item.price * item.qty;
            total += itemTotal;
            
            const customBadge = item.type === 'custom' ? `<span class="custom-badge">Custom</span>` : '';
            const customInfo = item.type === 'custom' ? 
                `<div class="custom-details">
                    <div><strong>Kategori:</strong> ${item.category}</div>
                    <div><strong>Detail:</strong> ${item.details}</div>
                    <div><strong>Tanggal:</strong> ${item.date}</div>
                 </div>` : '';

            html += `
            <div class="cart-item">
                <div class="item-details">
                    <h4>${item.name} ${customBadge}</h4>
                    <span class="item-price">Rp ${item.price.toLocaleString('id-ID')}</span>
                    ${customInfo}
                </div>
                <div class="cart-controls">
                    <div class="quantity-controls">
                        <button class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
                        <span class="qty-display">${item.qty}</span>
                        <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                    </div>
                    <button class="delete-btn" onclick="deleteItem(${index})"><i class="fas fa-trash"></i></button>
                </div>
            </div>`;
        });

        html += `
        <div class="cart-summary">
            <div class="summary-row"><span>Subtotal</span> <span>Rp ${total.toLocaleString('id-ID')}</span></div>
            <div class="summary-row" style="color: var(--text-light);"><span>Ongkos Kirim</span> <span>Menyesuaikan</span></div>
            <div class="summary-row summary-total"><span>Total Estimasi</span> <span>Rp ${total.toLocaleString('id-ID')}</span></div>
            <button class="btn-checkout" onclick="checkout()">Checkout via WhatsApp</button>
        </div>`;

        container.innerHTML = html;
    }

    window.updateQty = (index, change) => {
        if (cart[index].qty + change > 0) {
            cart[index].qty += change;
        } else {
            if (confirm("Hapus item ini dari keranjang?")) cart.splice(index, 1);
        }
        saveCart();
    };

    window.deleteItem = (index) => {
        if (confirm("Hapus item ini?")) {
            cart.splice(index, 1);
            saveCart();
        }
    };

    window.checkout = async () => {
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
                        msg += `   Detail: ${item.details}%0A   Kategori: ${item.category}%0A   Tgl: ${item.date}%0A`; 
                    }
                    msg += `   Harga: Rp ${subtotal.toLocaleString('id-ID')}%0A%0A`;
                });
                msg += `--------------------%0A*Total Estimasi: Rp ${total.toLocaleString('id-ID')}*`;

                window.open(`https://wa.me/6289689433798?text=${msg}`, "_blank");
                
                // Kosongkan keranjang setelah sukses checkout
                cart = [];
                saveCart();
            } else {
                alert("Gagal menyimpan pesanan, silakan coba lagi.");
            }
        } catch (error) {
            console.error("Error:", error);
            alert("Terjadi kesalahan koneksi.");
        }
    };

    document.addEventListener("DOMContentLoaded", () => {
        renderCart();
        updateBadge();
        setTimeout(() => document.querySelector('.reveal').classList.add('active'), 100);
    });
</script>

</body>
</html>