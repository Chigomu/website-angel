<?php
require_once __DIR__ . '/../app/auth_check.php';
require_once __DIR__ . '/../app/db.php';

// 1. Cek apakah ID tersedia
if (!isset($_GET['id'])) {
    header("Location: products.php?error=missing_id"); // Redirect ke products.php
    exit;
}

$id = intval($_GET['id']);

try {
    // 2. AMBIL DATA DULU (Untuk hapus gambar)
    $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        header("Location: products.php?error=not_found"); // Redirect ke products.php
        exit;
    }

    // 3. HAPUS FILE GAMBAR (Jika ada dan file lokal)
    if (!empty($product['image_url'])) {
        // Cek apakah ini URL eksternal atau file lokal
        if (!preg_match("~^(?:f|ht)tps?://~i", $product['image_url'])) {
            $filePath = __DIR__ . '/../' . $product['image_url'];
            
            // Hapus file fisik jika ada
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath); 
            }
        }
    }

    // 4. HAPUS DATA DI DATABASE
    $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $deleteStmt->execute([$id]);

    // Sukses
    header("Location: products.php?deleted=1"); // Redirect ke products.php
    exit;

} catch (Exception $e) {
    // Jika error
    header("Location: products.php?error=delete_failed"); // Redirect ke products.php
    exit;
}
?>