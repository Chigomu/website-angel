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
    body { 
        padding-top: 85px; 
        background-color: var(--bg-cream); 
    }
    .section {
        padding-top: 20px !important;
        padding-bottom: 40px !important;
    }

    /* Style Tabel */
    .table-container { background: var(--bg-card); border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); overflow: hidden; border: 1px solid var(--line-color); margin-top: 20px; }
    .product-table { width: 100%; border-collapse: collapse; font-family: var(--font-body); }
    .product-table th { background-color: var(--text-dark); color: var(--bg-cream); padding: 18px; text-align: left; }
    .product-table td { padding: 16px 18px; border-bottom: 1px solid var(--line-color); vertical-align: middle; }
    .product-table tbody tr:hover { background-color: #FDFBF7; transform: scale(1.005); transition: all 0.2s ease; }
    
    /* Header & Tombol Tambah */
    .btn-add { background-color: var(--accent); color: #fff; text-decoration: none; padding: 12px 25px; border-radius: 4px; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease; font-weight: 600; }
    .btn-add:hover { background-color: var(--text-dark); }
    
    /* === TOMBOL AKSI DIPERBAIKI === */
    .action-links { display: flex; gap: 8px; }
    
    .btn-action { 
        text-decoration: none; /* Hapus garis bawah link */
        width: 35px;           /* Lebar tetap */
        height: 35px;          /* Tinggi tetap = Persegi */
        padding: 0;            /* Reset padding agar icon di tengah */
        border-radius: 4px;    /* Sudut sedikit tumpul */
        border: none; 
        transition: 0.3s; 
        color: #fff; 
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Detail - Biru */
    .btn-view { background-color: #3498db; }
    .btn-view:hover { background-color: #2980b9; transform: translateY(-2px); }
    
    /* Edit - Kuning/Oranye */
    .btn-edit { background-color: #f39c12; }
    .btn-edit:hover { background-color: #d35400; transform: translateY(-2px); }
    
    /* Hapus - Merah */
    .btn-delete { background-color: #e74c3c; }
    .btn-delete:hover { background-color: #c0392b; transform: translateY(-2px); }

    /* === PAGINATION KOTAK === */
    .pagination { display: flex; justify-content: center; gap: 5px; margin-top: 30px; }
    .pagination a { 
        padding: 10px 16px; 
        border: 1px solid var(--line-color); 
        text-decoration: none; 
        color: var(--text-dark); 
        border-radius: 4px; 
        font-weight: 600;
        transition: all 0.3s;
        background: #fff;
    }
    .pagination a.active, .pagination a:hover { 
        background: var(--accent); 
        color: #fff; 
        border-color: var(--accent); 
    }
  </style>
</head>
<body>

  <nav class="navbar">
    <a href="dashboard.php" class="logo">Ibu Angel Admin</a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="orders.php">Pesanan</a>
        <a href="products.php" style="color: var(--accent);">Produk</a>
        <a href="settings.php">Pengaturan</a>
        <a href="logout.php" style="color: #C0392B;">Keluar</a>
    </div>
  </nav>

  <div class="section">
    <div class="admin-container">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="margin:0;">Daftar Produk</h2> 
        <a href="add_product.php" class="btn-add"><i class="fas fa-plus"></i> Tambah Baru</a>
      </div>

      <?php if(isset($_GET['updated'])): ?><div style="background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:15px; text-align:center;">Data berhasil diupdate!</div><?php endif; ?>
      <?php if(isset($_GET['deleted'])): ?><div style="background:#f8d7da; color:#721c24; padding:10px; border-radius:5px; margin-bottom:15px; text-align:center;">Data berhasil dihapus!</div><?php endif; ?>
      <?php if(isset($_GET['added'])): ?><div style="background:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-bottom:15px; text-align:center;">Produk baru berhasil ditambahkan!</div><?php endif; ?>

      <div class="table-container reveal active">
        <table class="product-table">
          <thead>
            <tr>
              <th>Gambar</th>
              <th>Nama</th>
              <th>Kategori</th>
              <th>Tipe</th>
              <th>Harga</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
              <td>
                <?php 
                    $imgSrc = $p['image_url'];
                    // Cek jika bukan URL eksternal, tambahkan ../
                    if (!empty($imgSrc) && !preg_match("~^(?:f|ht)tps?://~i", $imgSrc)) {
                        $imgSrc = "../" . $imgSrc; 
                    }
                    if(empty($imgSrc)) $imgSrc = "https://placehold.co/50x50?text=No+Img";
                ?>
                <img src="<?= htmlspecialchars($imgSrc) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
              </td>

              <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
              <td><?= $p['category'] ?></td>
              <td>
                  <?php if($p['type'] == 'regular'): ?>
                      <span style="color: var(--accent); font-weight:500;">Regular</span>
                  <?php else: ?>
                      <span style="color: #2980b9; font-weight:500;">Custom</span>
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
                  <a href="detail_product.php?id=<?= $p['id'] ?>" class="btn-action btn-view" title="Lihat Detail"><i class="fas fa-eye"></i></a>
                  <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn-action btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                  <a href="delete_product.php?id=<?= $p['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus?')" title="Hapus"><i class="fas fa-trash"></i></a>
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