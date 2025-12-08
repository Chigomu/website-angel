<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

// 1. Cek apakah ID tersedia
if (!isset($_GET['id'])) {
    header("Location: products.php?error=missing_id");
    exit;
}

$id = intval($_GET['id']);

try {
    // 2. AMBIL DATA DULU (Sebelum dihapus dari DB)
    $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        header("Location: products.php?error=not_found");
        exit;
    }

    // 3. HAPUS FILE GAMBAR (Jika ada dan merupakan file lokal)
    $image_url = $product['image_url'];
    
    if (!empty($image_url)) {
        // Cek apakah ini URL eksternal (http/https) atau file lokal
        // Kita hanya menghapus jika file tersebut ada di server kita (tidak ada http di depannya)
        if (!preg_match("~^(?:f|ht)tps?://~i", $image_url)) {
            
            // Path file fisik (Naik satu folder dari admin ke root)
            $filePath = __DIR__ . '/../' . $image_url;
            
            // Hapus file jika ada
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath); 
            }
        }
    }

    // 4. BARU HAPUS DATA DI DATABASE
    $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $deleteStmt->execute([$id]);

    // Sukses
    header("Location: products.php?deleted=1");
    exit;

} catch (Exception $e) {
    // Jika terjadi error database
    header("Location: products.php?error=delete_failed");
    exit;
}
?>