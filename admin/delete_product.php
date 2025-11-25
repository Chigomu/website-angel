<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

// 1. Cek apakah ID tersedia
if (!isset($_GET['id'])) {
    header("Location: dashboard.php?error=missing_id");
    exit;
}

$id = intval($_GET['id']);

try {
    // 2. AMBIL DATA DULU (Sebelum dihapus)
    $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        // Jika produk tidak ditemukan di DB
        header("Location: dashboard.php?error=not_found");
        exit;
    }

    // 3. HAPUS FILE GAMBAR (Jika ada dan file lokal)
    if (!empty($product['image_url'])) {
        $filePath = __DIR__ . '/../' . $product['image_url'];

        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath); 
        }
    }

    // 4. BARU HAPUS DATA DI DATABASE
    $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $deleteStmt->execute([$id]);

    // Sukses
    header("Location: dashboard.php?deleted=1");
    exit;

} catch (Exception $e) {
    header("Location: dashboard.php?error=delete_failed");
    exit;
}
?>