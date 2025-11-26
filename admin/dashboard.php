<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

// === HITUNG STATISTIK ===
try {
    // 1. Pesanan Pending
    $pendingCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    
    // 2. Pesanan Diproses
    $processedCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'processed'")->fetchColumn();
    
    // 3. Total Pesanan Masuk
    $totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    
    // 4. Total Pemasukan (Estimasi dari pesanan yg tidak cancel)
    $totalRevenue = $pdo->query("SELECT SUM(total_price) FROM orders WHERE status != 'cancelled'")->fetchColumn();

    // 5. Jumlah Produk Regular
    $regProductCount = $pdo->query("SELECT COUNT(*) FROM products WHERE type = 'regular'")->fetchColumn();

    // 6. Jumlah Produk Custom
    $custProductCount = $pdo->query("SELECT COUNT(*) FROM products WHERE type = 'custom'")->fetchColumn();

} catch (Exception $e) {
    die("Error database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin | Ibu Angel</title>
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding-top: 100px; background-color: var(--bg-cream); }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--line-color);
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); }

        .stat-icon {
            width: 60px; height: 60px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }

        .stat-info h3 { margin: 0; font-size: 2rem; color: var(--text-dark); }
        .stat-info p { margin: 0; color: var(--text-light); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }

        /* Warna Khusus Icon */
        .bg-orange { background: #FFF3E0; color: #EF6C00; }
        .bg-blue { background: #E3F2FD; color: #1565C0; }
        .bg-green { background: #E8F5E9; color: #2E7D32; }
        .bg-brown { background: #EFEBE9; color: #5D4037; }

        /* Section Title */
        .section-title {
            font-size: 1.5rem; 
            margin-bottom: 20px; 
            border-left: 5px solid var(--accent); 
            padding-left: 15px;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="logo">Ibu Angel Admin</a>
    <div class="nav-links">
      <a href="dashboard.php" style="color: var(--accent);">Dashboard</a>
      <a href="orders.php">Pesanan</a>
      <a href="products.php">Produk</a>
      <a href="settings.php">Tampilan</a>
      <a href="logout.php" style="color: #C0392B;">Logout</a>
    </div>
</nav>

<div class="section reveal active">
    <h2 style="text-align:center; margin-bottom: 10px;">Selamat Datang, Ibu Angel!</h2>
    <p style="text-align:center; color: var(--text-light); margin-bottom: 50px;">Berikut adalah ringkasan aktivitas toko hari ini.</p>

    <!-- STATISTIK PESANAN -->
    <div class="section-title">Ringkasan Pesanan</div>
    <div class="dashboard-grid">
        <!-- Pending -->
        <div class="stat-card">
            <div class="stat-icon bg-orange"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <h3><?= $pendingCount ?></h3>
                <p>Menunggu</p>
            </div>
        </div>
        <!-- Processed -->
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="fas fa-spinner"></i></div>
            <div class="stat-info">
                <h3><?= $processedCount ?></h3>
                <p>Diproses</p>
            </div>
        </div>
        <!-- Total Orders -->
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-info">
                <h3><?= $totalOrders ?></h3>
                <p>Total Pesanan</p>
            </div>
        </div>
        <!-- Revenue (Opsional) -->
        <div class="stat-card">
            <div class="stat-icon bg-brown"><i class="fas fa-coins"></i></div>
            <div class="stat-info">
                <h3 style="font-size: 1.5rem;">Rp <?= number_format($totalRevenue / 1000, 0) ?>k</h3>
                <p>Estimasi Omzet</p>
            </div>
        </div>
    </div>

    <!-- STATISTIK PRODUK -->
    <div class="section-title" style="margin-top: 50px;">Ringkasan Produk</div>
    <div class="dashboard-grid">
        <!-- Reguler -->
        <div class="stat-card">
            <div class="stat-icon" style="background: #FCE4EC; color: #C2185B;"><i class="fas fa-cookie"></i></div>
            <div class="stat-info">
                <h3><?= $regProductCount ?></h3>
                <p>Kue Reguler</p>
            </div>
        </div>
        <!-- Custom -->
        <div class="stat-card">
            <div class="stat-icon" style="background: #F3E5F5; color: #7B1FA2;"><i class="fas fa-birthday-cake"></i></div>
            <div class="stat-info">
                <h3><?= $custProductCount ?></h3>
                <p>Katalog Custom</p>
            </div>
        </div>
        <!-- Pintasan -->
        <a href="add_product.php" class="stat-card" style="text-decoration: none; border: 2px dashed var(--accent); justify-content: center;">
            <div style="color: var(--accent); font-weight: 600;">
                <i class="fas fa-plus-circle"></i> Tambah Produk Baru
            </div>
        </a>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => { document.querySelector('.reveal').classList.add('active'); }, 100);
    });
</script>

</body>
</html>