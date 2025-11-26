<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';  // sudah ada PDO $pdo
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

// Hitung total untuk pagination
$total = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_pages = ceil($total / $limit);
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Admin | Ibu Angel</title>
  <link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    /* === DASHBOARD SPECIFIC STYLES === */
    body { padding-top: 100px; }

    .admin-header {
      text-align: center;
      margin-bottom: 40px;
    }
    .admin-header h2 { font-size: 2.5rem; color: var(--text-dark); }
    .admin-header p { color: var(--text-light); }

    /* === MODERN TABLE STYLE === */
    .table-container {
      background: var(--bg-card);
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.03);
      overflow: hidden;
      border: 1px solid var(--line-color);
      margin-top: 20px;
    }

    .product-table {
      width: 100%;
      border-collapse: collapse;
      font-family: var(--font-body);
    }

    .product-table th {
      background-color: var(--text-dark);
      color: var(--bg-cream);
      font-family: var(--font-heading);
      font-weight: 400;
      letter-spacing: 1px;
      padding: 18px;
      text-align: left;
      font-size: 1.1rem;
    }

    .product-table td {
      padding: 16px 18px;
      border-bottom: 1px solid var(--line-color);
      color: var(--text-dark);
      vertical-align: middle;
    }

    .product-table tbody tr:nth-child(even) { background-color: #fafafa; }

    .product-table tbody tr:hover {
      background-color: #FDFBF7;
      transform: scale(1.005);
      transition: all 0.2s ease;
    }

    /* Styling Kolom Aksi */
    .action-links { display: flex; gap: 10px; }

    .btn-action {
      text-decoration: none;
      padding: 6px 10px;
      border-radius: 4px;
      font-size: 0.85rem;
      transition: 0.3s;
      border: 1px solid transparent;
    }

    .btn-detail { color: var(--text-light); background: #f0f0f0; }
    .btn-detail:hover { background: #e0e0e0; color: var(--text-dark); }

    .btn-edit { color: var(--accent); background: rgba(217, 119, 87, 0.1); }
    .btn-edit:hover { background: var(--accent); color: white; }

    .btn-delete { color: #C0392B; background: rgba(192, 57, 43, 0.1); }
    .btn-delete:hover { background: #C0392B; color: white; }

    /* Pagination */
    .pagination { 
      display: flex; 
      justify-content: center;
      align-items: center; 
      margin-top: 30px; 
      gap: 12px;       
    }

    .pagination a {
      text-decoration: none; 
      border: 1px solid var(--line-color);
      color: var(--text-dark);
      border-radius: 50%;  
      width: 40px; height: 40px;
      display: flex;
      align-items: center; 
      justify-content: center;
      transition: all 0.3s ease;
      font-weight: 500;
    }

    .pagination a:hover, .pagination a.active {
      background-color: var(--accent);
      color: white;
      border-color: var(--accent);
      transform: translateY(-2px);
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .table-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding: 0 10px;
    }

    .btn-add {
      background-color: var(--accent);
      color: #fff;
      text-decoration: none;
      padding: 12px 25px;
      border-radius: 0;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      text-transform: uppercase;
      letter-spacing: 1px;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(217, 119, 87, 0.2);
    }

    .btn-add:hover {
      background-color: var(--text-dark);
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(44, 24, 16, 0.2);
    }

    .btn-add i { font-size: 0.9em; }

  </style>
</head>
<body>

  <nav class="navbar" id="navbar">
    <a href="../index.php" class="logo">Ibu Angel</a>
    <div class="nav-links">
      <a href="../admin/orders.php">Pesanan</a>
      <a href="#" class="active" style="color: var(--accent);">Dashboard</a>
      <a href="logout.php" style="color: #C0392B;">Logout</a>
    </div>
  </nav>

  <div class="section">
    <div class="admin-header reveal active">
      <h2>Manajemen Produk</h2>
      <p>Kelola menu kue kering dan custom cake Anda di sini.</p>
    </div>

    <div class="admin-container">
      
      <div class="table-header reveal">
        <h3 style="font-size: 1.5rem; margin: 0;">Daftar Produk</h3>
        
        <a href="add_product.php" class="btn-add">
          <i class="fas fa-plus"></i> Tambah Produk Baru
        </a>
      </div>

      <div class="table-container reveal">
        <table class="product-table">
          <thead>
            <tr>
              <th width="25%">Nama Produk</th>
              <th width="15%">Kategori</th>
              <th width="10%">Jenis</th>
              <th width="15%">Harga</th>
              <th width="15%">Diperbarui</th>
              <th width="15%">Aksi</th>
            </tr>
          </thead>

          <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
              <td>
                  <strong style="font-family: var(--font-heading); font-size: 1.1em;">
                      <?= htmlspecialchars($p['name']) ?>
                  </strong>
              </td>
              <td>
                  <span style="color: var(--text-light); font-size: 0.9em;">
                      <?= $p['category'] ?>
                  </span>
              </td>
              <td>
                  <?php if($p['type'] == 'regular'): ?>
                      <span style="color: var(--accent); font-weight: 500;">Regular</span>
                  <?php else: ?>
                      <span style="color: #2980b9; font-weight: 500;">Custom</span>
                  <?php endif; ?>
              </td>
              <td style="font-weight: 600;">
              <?php
                  if ($p['type'] === 'custom') {
                      echo "Rp " . number_format($p['price_min']) . " - Rp " . number_format($p['price_max']);
                  }
                  elseif (empty($p['price']) || $p['price'] == 0) {
                      echo "Rp " . number_format($p['price_min']) . " - Rp " . number_format($p['price_max']);
                  }
                  else {
                      echo "Rp " . number_format($p['price']);
                  }
              ?>
          </td>

              <td style="font-size: 0.85em; color: var(--text-light);">
                  <?= date('d M Y', strtotime($p['updated_at'])) ?>
                  <br>
                  <?= date('H:i', strtotime($p['updated_at'])) ?>
              </td>
              <td>
                <div class="action-links">
                  <a href="detail_product.php?id=<?= $p['id'] ?>" class="btn-action btn-detail" title="Detail">
                      <i class="fas fa-eye"></i>
                  </a>
                  <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn-action btn-edit" title="Edit">
                      <i class="fas fa-edit"></i>
                  </a>
                  <a href="delete_product.php?id=<?= $p['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus produk ini?')" title="Hapus">
                      <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      
      <div class="pagination">
        <?php for ($i=1; $i <= $total_pages; $i++): ?>
          <a 
            href="?page=<?= $i ?>" 
            class="<?= ($i == $page ? 'active' : '') ?>"
          ><?= $i ?></a>
        <?php endfor; ?>
      </div>

    </div>
  </div>

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
   });
  </script>
</body>
</html>