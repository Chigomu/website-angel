<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

// === LOGIC: UPDATE & DELETE ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = intval($_POST['order_id']);
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: orders.php?msg=deleted"); exit;
    }
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $id = intval($_POST['order_id']);
        $new_status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        header("Location: orders.php?msg=updated"); exit;
    }
}

// === LOGIC: FILTER & PAGINATION ===
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; 
$offset = ($page - 1) * $limit;
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql_base = "FROM orders";
$params = [];

if ($status_filter !== 'all') {
    $sql_base .= " WHERE status = ?";
    $params[] = $status_filter;
}

$stmt_count = $pdo->prepare("SELECT COUNT(*) " . $sql_base);
$stmt_count->execute($params);
$total_items = $stmt_count->fetchColumn();
$total_pages = ceil($total_items / $limit);

$sql_final = "SELECT * " . $sql_base . " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql_final);
$stmt->execute($params);
$orders = $stmt->fetchAll();

function countStatus($pdo, $status) {
    return $pdo->query("SELECT COUNT(*) FROM orders WHERE status = '$status'")->fetchColumn();
}
$cnt_pending = countStatus($pdo, 'pending');
$cnt_processed = countStatus($pdo, 'processed');
$cnt_completed = countStatus($pdo, 'completed');
$cnt_cancelled = countStatus($pdo, 'cancelled');
$cnt_all = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
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
        body { padding-top: 85px; background-color: var(--bg-cream); }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        
        /* === TAB FILTER === */
        .status-tabs {
            display: flex; gap: 10px; margin-bottom: 25px; overflow-x: auto; padding-bottom: 5px;
            border-bottom: 1px solid var(--line-color);
        }
        .tab-link {
            text-decoration: none; padding: 8px 18px; border-radius: 30px; font-size: 0.9rem; font-weight: 600;
            color: var(--text-light); transition: 0.3s; white-space: nowrap; display: flex; align-items: center; gap: 8px;
            border: 1px solid transparent;
        }
        .tab-link:hover { background: #eee; color: var(--text-dark); }
        
        .tab-link.active[data-status="all"] { background: var(--accent); color: #fff; }
        .tab-link.active[data-status="pending"] { background: #FFF3E0; color: #EF6C00; border-color: #FFE0B2; }
        .tab-link.active[data-status="processed"] { background: #E3F2FD; color: #1565C0; border-color: #BBDEFB; }
        .tab-link.active[data-status="completed"] { background: #E8F5E9; color: #2E7D32; border-color: #C8E6C9; }
        .tab-link.active[data-status="cancelled"] { background: #FFEBEE; color: #C62828; border-color: #FFCDD2; }

        .counter-badge { background: rgba(0,0,0,0.1); font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; }
        
        /* === GRID ORDER === */
        .order-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .order-card {
            background: white; border: 1px solid var(--line-color); border-radius: 10px;
            padding: 20px; display: flex; flex-direction: column; gap: 15px;
            transition: transform 0.2s, box-shadow 0.2s; position: relative; overflow: hidden;
        }
        .order-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.05); }
        
        .card-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px dashed #eee; padding-bottom: 12px; }
        .order-id { font-size: 0.85rem; color: #888; font-weight: 600; letter-spacing: 0.5px; }
        .cust-name { font-size: 1.1rem; font-weight: 700; color: var(--text-dark); margin-top: 2px; display: block; }
        .order-date { font-size: 0.8rem; color: #aaa; margin-top: 2px; display: block; }

        .card-items { flex-grow: 1; font-size: 0.9rem; color: #555; line-height: 1.5; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
        
        /* STYLE KHUSUS TOMBOL "LIHAT LAINNYA" */
        .more-items { 
            font-size: 0.85rem; color: var(--accent); font-weight: 600; font-style: italic; margin-top: 8px; 
            cursor: pointer; display: inline-flex; align-items: center; gap: 5px; 
            padding: 4px 8px; background: #FFF3E0; border-radius: 4px; width: fit-content;
            transition: 0.2s;
        }
        .more-items:hover { background: var(--accent); color: #fff; }

        .card-footer { display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: 12px; border-top: 1px solid #eee; }
        .total-price { font-size: 1.1rem; font-weight: 700; color: var(--accent); }
        
        .action-group { display: flex; gap: 8px; }
        .btn-icon { width: 34px; height: 34px; border-radius: 6px; border: 1px solid #eee; background: #fff; color: var(--text-light); cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; }
        .btn-icon:hover { background: #f5f5f5; color: var(--text-dark); border-color: #ccc; }
        .btn-icon.delete:hover { background: #c0392b; color: #fff; border-color: #c0392b; }

        .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge.pending { background: #FFF3E0; color: #EF6C00; }
        .badge.processed { background: #E3F2FD; color: #1565C0; }
        .badge.completed { background: #E8F5E9; color: #2E7D32; }
        .badge.cancelled { background: #FFEBEE; color: #C62828; }

        .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 30px; }
        .page-link { display: flex; align-items: center; justify-content: center; width: 35px; height: 35px; border: 1px solid var(--line-color); border-radius: 4px; text-decoration: none; color: var(--text-dark); font-weight: 600; transition: 0.3s; }
        .page-link:hover, .page-link.active { background: var(--accent); color: white; border-color: var(--accent); }

        @media (max-width: 768px) { .order-grid { grid-template-columns: 1fr; } }

        /* MODAL */
        .modal-bg { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-box { background: #fff; padding: 25px; border-radius: 8px; width: 300px; text-align: center; }
        
        /* MODAL ITEM KHUSUS (LEBIH LEBAR & SCROLLABLE) */
        .modal-box.items-view {
            width: 450px;
            max-width: 90%;
            text-align: left;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-box select { width: 100%; padding: 10px; margin: 20px 0; border: 1px solid #ddd; border-radius: 6px; }

        /* TABEL DALAM POPUP */
        .item-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .item-table th { text-align: left; border-bottom: 2px solid #eee; padding: 8px; color: #888; font-size: 0.85rem; }
        .item-table td { border-bottom: 1px solid #f5f5f5; padding: 10px 8px; font-size: 0.95rem; }
        .item-table tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="logo">Ibu Angel Admin</a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="orders.php" style="color: var(--accent);">Pesanan</a>
        <a href="products.php">Produk</a>  
        <a href="settings.php">Pengaturan</a> 
        <a href="logout.php" style="color: #C0392B;">Keluar</a> 
    </div>
</nav>

<div class="container">
    <div class="reveal active">
        <h2 style="margin-bottom: 5px;">Manajemen Pesanan</h2>
        <p style="color: #666; margin-bottom: 20px;">Pantau dan kelola status pesanan masuk.</p>
    </div>

    <div class="status-tabs reveal">
        <a href="?status=all" class="tab-link <?= $status_filter == 'all' ? 'active' : '' ?>" data-status="all">
            Semua <span class="counter-badge"><?= $cnt_all ?></span>
        </a>
        <a href="?status=pending" class="tab-link <?= $status_filter == 'pending' ? 'active' : '' ?>" data-status="pending">
            Menunggu <span class="counter-badge"><?= $cnt_pending ?></span>
        </a>
        <a href="?status=processed" class="tab-link <?= $status_filter == 'processed' ? 'active' : '' ?>" data-status="processed">
            Diproses <span class="counter-badge"><?= $cnt_processed ?></span>
        </a>
        <a href="?status=completed" class="tab-link <?= $status_filter == 'completed' ? 'active' : '' ?>" data-status="completed">
            Selesai <span class="counter-badge"><?= $cnt_completed ?></span>
        </a>
        <a href="?status=cancelled" class="tab-link <?= $status_filter == 'cancelled' ? 'active' : '' ?>" data-status="cancelled">
            Dibatalkan <span class="counter-badge"><?= $cnt_cancelled ?></span>
        </a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div style="background:#d4edda; color:#155724; padding:10px; border-radius:4px; margin-bottom:20px; text-align:center;">Data berhasil diperbarui.</div>
    <?php endif; ?>

    <div class="order-grid">
        <?php foreach ($orders as $o): ?>
            <?php 
                $items = json_decode($o['items'], true);
                $status_labels = ['pending'=>'Menunggu', 'processed'=>'Diproses', 'completed'=>'Selesai', 'cancelled'=>'Dibatalkan'];
                $label = $status_labels[$o['status']] ?? $o['status'];
            ?>
            
            <div class="order-card reveal">
                <div class="card-header">
                    <div>
                        <span class="order-id">ID #<?= $o['id'] ?></span>
                        <span class="cust-name"><?= htmlspecialchars($o['customer_name']) ?></span>
                        <span class="order-date"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></span>
                    </div>
                    <span class="badge <?= $o['status'] ?>"><?= $label ?></span>
                </div>

                <div class="card-items">
                    <?php $countItem = 0; ?>
                    <?php foreach ($items as $item): ?>
                        <?php if($countItem < 3): // Tampilkan max 3 item agar rapi ?>
                            <div class="item-row">
                                <span><?= htmlspecialchars($item['name']) ?> <small class="text-muted">x<?= $item['qty'] ?></small></span>
                            </div>
                        <?php endif; $countItem++; ?>
                    <?php endforeach; ?>
                    
                    <?php if($countItem > 3): ?>
                        <!-- TOMBOL TRIGGER POPUP (WINDOW KECIL) -->
                        <div class="more-items" onclick="openItemsModal(<?= $o['id'] ?>)">
                            + <?= $countItem - 3 ?> item lainnya... <i class="fas fa-external-link-alt" style="font-size:0.8em"></i>
                        </div>
                    <?php endif; ?>

                    <!-- DATA TERSEMBUNYI UNTUK POPUP -->
                    <div id="items-data-<?= $o['id'] ?>" style="display:none;">
                        <table class="item-table">
                            <thead>
                                <tr>
                                    <th>Nama Produk</th>
                                    <th style="width:50px;">Jml</th>
                                    <th style="text-align:right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $detail): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($detail['name']) ?>
                                        <?php if(isset($detail['type']) && $detail['type'] == 'custom'): ?>
                                            <br><small style="color:#e67e22;">(Custom: <?= htmlspecialchars($detail['details']) ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight:bold;">x<?= $detail['qty'] ?></td>
                                    <td style="text-align:right;">Rp <?= number_format($detail['price'] * $detail['qty']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div style="margin-top:15px; text-align:right; font-weight:bold; font-size:1.1rem; border-top:2px solid #eee; padding-top:10px;">
                            Total: Rp <?= number_format($o['total_price']) ?>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="total-price">Rp <?= number_format($o['total_price']) ?></div>
                    
                    <div class="action-group">
                        <button class="btn-icon" title="Ubah Status" onclick="openStatusModal(<?= $o['id'] ?>, '<?= $o['status'] ?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <form method="POST" onsubmit="return confirm('Hapus permanen?');" style="margin:0;">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn-icon delete" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if(empty($orders)): ?>
        <div style="text-align:center; padding:50px; color:#888;">Belum ada pesanan pada status ini.</div>
    <?php endif; ?>

    <?php if($total_pages > 1): ?>
    <div class="pagination reveal">
        <?php $stParam = ($status_filter !== 'all') ? '&status='.$status_filter : ''; ?>
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?><?= $stParam ?>" class="page-link <?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

</div>

<!-- MODAL UBAH STATUS -->
<div id="statusModal" class="modal-bg" style="display:none;">
    <div class="modal-box">
        <h3>Ubah Status Pesanan</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="order_id" id="modalOrderId">
            
            <select name="status" id="modalStatusSelect">
                <option value="pending">Menunggu</option>
                <option value="processed">Diproses</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
            
            <div style="display:flex; gap:10px; justify-content:center;">
                <button type="button" onclick="closeModal('statusModal')" style="padding:10px 20px; border:1px solid #ddd; background:#fff; cursor:pointer; border-radius:6px; font-weight:600;">Batal</button>
                <button type="submit" style="padding:10px 20px; border:none; background:var(--accent); color:#fff; cursor:pointer; border-radius:6px; font-weight:600;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL LIHAT DETAIL ITEM (BARU) -->
<div id="itemsModal" class="modal-bg" style="display:none;">
    <div class="modal-box items-view">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3 style="margin:0; color:var(--accent);">Detail Item Pesanan</h3>
            <span onclick="closeModal('itemsModal')" style="cursor:pointer; font-size:1.5rem;">&times;</span>
        </div>
        
        <div id="itemsModalContent">
            <!-- Konten akan diisi lewat JS -->
        </div>

        <div style="text-align:right; margin-top:20px;">
            <button onclick="closeModal('itemsModal')" style="padding:8px 20px; background:#eee; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Tutup</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => { if(entry.isIntersecting) entry.target.classList.add('active'); });
        });
        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    });

    // === LOGIKA MODAL STATUS ===
    function openStatusModal(id, currentStatus) {
        document.getElementById('modalOrderId').value = id;
        document.getElementById('modalStatusSelect').value = currentStatus;
        document.getElementById('statusModal').style.display = 'flex';
    }

    // === LOGIKA MODAL DETAIL ITEM (BARU) ===
    function openItemsModal(orderId) {
        // Ambil HTML dari div tersembunyi berdasarkan ID
        const content = document.getElementById('items-data-' + orderId).innerHTML;
        
        // Masukkan ke dalam modal
        document.getElementById('itemsModalContent').innerHTML = content;
        
        // Tampilkan modal
        document.getElementById('itemsModal').style.display = 'flex';
    }

    // === FUNGSI TUTUP MODAL UNIVERSAL ===
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Tutup jika klik di luar box
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-bg')) {
            event.target.style.display = "none";
        }
    }
</script>

</body>
</html>