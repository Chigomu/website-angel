<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

// === LOGIC: HANDLE POST REQUESTS (UPDATE & DELETE) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. HAPUS PESANAN
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = intval($_POST['order_id']);
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: orders.php?msg=deleted");
        exit;
    }

    // 2. UPDATE STATUS
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $id = intval($_POST['order_id']);
        $new_status = $_POST['status'];
        
        $valid_statuses = ['pending', 'processed', 'completed', 'cancelled'];
        
        if (in_array($new_status, $valid_statuses)) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
        }
        
        header("Location: orders.php?msg=updated");
        exit;
    }
}

// Ambil data orders
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log Pesanan Masuk</title>
    <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { padding-top: 100px; background-color: var(--bg-cream); }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        .order-card {
            background: white;
            border: 1px solid var(--line-color);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            transition: transform 0.2s;
        }
        .order-card:hover { transform: translateY(-2px); }

        .order-header {
            display: flex; justify-content: space-between;
            border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;
        }
        
        .item-list { list-style: none; padding: 0; }
        .item-list li { padding: 5px 0; border-bottom: 1px dashed #eee; display: flex; justify-content: space-between; }
        
        .total { font-weight: bold; color: var(--accent); font-size: 1.2rem; text-align: right; margin-top: 10px;}
        
        /* === STYLE BADGE STATUS MODERN === */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-family: var(--font-body);
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        .badge::before {
            content: ''; display: inline-block; width: 8px; height: 8px;
            border-radius: 50%; background-color: currentColor; opacity: 0.7;
        }

        /* Warna Status */
        .badge.completed { background-color: #E8F5E9; color: #2E7D32; border-color: #C8E6C9; }
        .badge.pending { background-color: #FFF3E0; color: #EF6C00; border-color: #FFE0B2; }
        .badge.processed { background-color: #E3F2FD; color: #1565C0; border-color: #BBDEFB; }
        .badge.cancelled { background-color: #FFEBEE; color: #C62828; border-color: #FFCDD2; }

        /* Actions */
        .order-actions {
            margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;
            display: flex; justify-content: space-between; align-items: center;
        }
        .status-form { display: flex; gap: 10px; align-items: center; }
        
        .status-select {
            padding: 6px 10px; border: 1px solid var(--line-color);
            border-radius: 4px; background: #fafafa;
            font-family: var(--font-body); color: var(--text-dark); cursor: pointer;
        }
        
        .btn-update {
            background: var(--text-dark); color: #fff; border: none;
            padding: 6px 12px; border-radius: 4px; cursor: pointer;
            font-size: 0.85rem; transition: 0.3s;
        }
        .btn-update:hover { background: var(--accent); }

        .btn-delete {
            background: transparent; color: #c0392b; border: 1px solid #c0392b;
            padding: 6px 12px; border-radius: 4px; cursor: pointer;
            font-size: 0.85rem; transition: 0.3s;
        }
        .btn-delete:hover { background: #c0392b; color: white; }
        
        .alert { padding: 10px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

    </style>
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="logo">Ibu Angel Admin</a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="orders.php" style="color: var(--accent);">Pesanan</a>
        <a href="products.php">Produk</a>  
        <a href="settings.php">Tampilan</a>
        <a href="logout.php" style="color: #C0392B;">Logout</a>
    </div>
</nav>

<div class="container">
    <div class="reveal active">
        <h2>Log Pesanan (Pre-Order WhatsApp)</h2>
        <p style="margin-bottom: 20px; color: #666;">Kelola status pesanan masuk atau hapus riwayat yang tidak diperlukan.</p>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Pesanan berhasil dihapus.</div>
        <?php elseif($_GET['msg'] == 'updated'): ?>
            <div class="alert alert-success">Status pesanan berhasil diperbarui.</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php foreach ($orders as $o): ?>
        <?php 
            $items = json_decode($o['items'], true); 
            
            // ARRAY TRANSLATE: Mengubah kode Inggris ke Indonesia untuk TAMPILAN
            $status_indo = [
                'pending'   => 'Menunggu',
                'processed' => 'Diproses',
                'completed' => 'Selesai',
                'cancelled' => 'Dibatalkan'
            ];
            
            // Ambil label indo, jika tidak ada pakai default Inggris
            $status_label = isset($status_indo[$o['status']]) ? $status_indo[$o['status']] : $o['status'];
        ?>
        
        <div class="order-card reveal">
            <div class="order-header">
                <div>
                    <strong style="font-size: 1.1rem; color: var(--text-dark);">
                        #<?= $o['id'] ?> - <?= htmlspecialchars($o['customer_name']) ?>
                    </strong>
                    <br>
                    <small style="color: #888;">
                        <i class="far fa-clock"></i> <?= date('d M Y, H:i', strtotime($o['created_at'])) ?>
                    </small>
                </div>
                
                <div>
                    <span class="badge <?= strtolower($o['status']) ?>">
                        <?= strtoupper($status_label) ?>
                    </span>
                </div>
            </div>

            <ul class="item-list">
                <?php foreach ($items as $item): ?>
                    <li>
                        <div style="flex: 1;">
                            <strong><?= htmlspecialchars($item['name']) ?></strong> 
                            <span style="color: #666;">(x<?= $item['qty'] ?>)</span>
                            <?php if(isset($item['type']) && $item['type'] == 'custom'): ?>
                                <br><small style="color: var(--accent);">
                                    <em>Custom: <?= htmlspecialchars($item['details']) ?> 
                                    (Tgl: <?= htmlspecialchars($item['date']) ?>)</em>
                                </small>
                            <?php endif; ?>
                        </div>
                        <span style="font-weight: 500;">Rp <?= number_format($item['price'] * $item['qty']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="total">
                Total: Rp <?= number_format($o['total_price']) ?>
            </div>

            <div class="order-actions">
                
                <form method="POST" class="status-form">
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <input type="hidden" name="action" value="update_status">
                    
                    <select name="status" class="status-select">
                        <option value="pending" <?= $o['status'] == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="processed" <?= $o['status'] == 'processed' ? 'selected' : '' ?>>Diproses</option>
                        <option value="completed" <?= $o['status'] == 'completed' ? 'selected' : '' ?>>Selesai</option>
                        <option value="cancelled" <?= $o['status'] == 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                    
                    <button type="submit" class="btn-update" title="Simpan Status">
                        <i class="fas fa-save"></i> Update
                    </button>
                </form>

                <form method="POST" onsubmit="return confirm('Yakin ingin menghapus riwayat pesanan ini secara permanen?');">
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    
                    <button type="submit" class="btn-delete" title="Hapus Pesanan">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>

            </div>

        </div>
    <?php endforeach; ?>
    
    <?php if(empty($orders)): ?>
        <div class="reveal" style="text-align:center; padding: 50px; background: #fff; border-radius: 8px; border: 1px dashed var(--line-color);">
            <i class="fas fa-inbox" style="font-size: 3rem; color: #ddd; margin-bottom: 15px;"></i>
            <p style="color: #888;">Belum ada pesanan masuk.</p>
        </div>
    <?php endif; ?>

</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if(entry.isIntersecting) entry.target.classList.add('active');
            });
        });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    });
</script>

</body>
</html>