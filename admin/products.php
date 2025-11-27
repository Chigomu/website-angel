<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';  
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// PAGINATION
$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Ambil data produk
$stmt = $pdo->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll();

// Hitung total
$total = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_pages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Kelola Produk | Ibu Angel</title>
  <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* === FIX JARAK (GAP) === */
    body { 
        padding-top: 85px; /* Disesuaikan agar pas di bawah navbar */
        background-color: var(--bg-cream); 
    }
    
    /* Override padding section khusus halaman ini agar naik ke atas */
    .section {
        padding-top: 20px !important;
        padding-bottom: 40px !important;
    }

    /* Style Tabel & Pagination */
    .table-container { background: var(--bg-card); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid var(--line-color); margin-top: 20px; }
    .product-table { width: 100%; border-collapse: collapse; font-family: var(--font-body); }
    .product-table th { background-color: var(--text-dark); color: var(--bg-cream); padding: 18px; text-align: left; }
    .product-table td { padding: 16px 18px; border-bottom: 1px solid var(--line-color); vertical-align: middle; }
    .product-table tbody tr:hover { background-color: #FDFBF7; transform: scale(1.005); transition: all 0.2s ease; }
    .btn-add { background-color: var(--accent); color: #fff; text-decoration: none; padding: 12px 25px; border-radius: 0; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease; }
    .btn-add:hover { background-color: var(--text-dark); }
    .action-links { display: flex; gap: 10px; }
    .btn-action { padding: 6px 10px; border-radius: 4px; border: 1px solid transparent; transition: 0.3s; color: var(--text-dark); background: #f0f0f0; }
    .btn-action:hover { background: var(--accent); color: #fff; }
    .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 30px; }
    .pagination a { padding: 10px 15px; border: 1px solid var(--line-color); text-decoration: none; color: var(--text-dark); border-radius: 50%; }
    .pagination a.active, .pagination a:hover { background: var(--accent); color: #fff; }
  </style>
</head>
<body>

  <nav class="navbar">
    <a href="dashboard.php" class="logo">Ibu Angel Admin</a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="orders.php">Pesanan</a>
        <a href="products.php" style="color: var(--accent);">Produk</a> <a href="settings.php">Tampilan</a>
        <a href="logout.php" style="color: #C0392B;">Logout</a>
    </div>
  </nav>

  <div class="section">
    <div class="admin-container">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="margin:0;">Daftar Produk</h2> <a href="add_product.php" class="btn-add"><i class="fas fa-plus"></i> Tambah Baru</a>
      </div>

      <?php if(isset($_GET['updated'])): ?><p style="color: green; text-align: center;">Data berhasil diupdate!</p><?php endif; ?>
      <?php if(isset($_GET['deleted'])): ?><p style="color: red; text-align: center;">Data berhasil dihapus!</p><?php endif; ?>
      <?php if(isset($_GET['added'])): ?><p style="color: green; text-align: center;">Produk baru berhasil ditambahkan!</p><?php endif; ?>

      <div class="table-container reveal active">
        <table class="product-table">
          <thead>
            <tr>
              <th>Nama</th><th>Kategori</th><th>Tipe</th><th>Harga</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
              <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
              <td><?= $p['category'] ?></td>
              <td>
                  <?php if($p['type'] == 'regular'): ?>
                      <span style="color: var(--accent);">Regular</span>
                  <?php else: ?>
                      <span style="color: #2980b9;">Custom</span>
                  <?php endif; ?>
              </td>
              <td>
              <?php
                  if ($p['type'] === 'custom') echo "Rp " . number_format($p['price_min']) . " - " . number_format($p['price_max']);
                  else echo "Rp " . number_format($p['price']);
              ?>
              </td>
              <td>
                <div class="action-links">
                  <a href="detail_product.php?id=<?= $p['id'] ?>" class="btn-action"><i class="fas fa-eye"></i></a>
                  <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn-action"><i class="fas fa-edit"></i></a>
                  <a href="delete_product.php?id=<?= $p['id'] ?>" class="btn-action" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      
      <div class="pagination">
        <?php for ($i=1; $i <= $total_pages; $i++): ?>
          <a href="?page=<?= $i ?>" class="<?= ($i == $page ? 'active' : '') ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>
  </div>
  
  <script>
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => { document.querySelector('.reveal').classList.add('active'); }, 100);
    });
  </script>
</body>
</html>