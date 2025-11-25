<?php
// save_order.php
require_once 'app/db.php';

header('Content-Type: application/json');

// Ambil data JSON yang dikirim dari Javascript
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
    exit;
}

$name = $data['name'] ?? 'Tanpa Nama';
$items = json_encode($data['items']); // Konversi array cart jadi string JSON
$total = $data['total'] ?? 0;

try {
    $stmt = $pdo->prepare("INSERT INTO orders (customer_name, items, total_price) VALUES (?, ?, ?)");
    $stmt->execute([$name, $items, $total]);
    
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>