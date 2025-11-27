<?php
require_once 'app/db.php';

header('Content-Type: application/json');
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Data invalid']);
    exit;
}

// === VALIDASI CLOUDFLARE TURNSTILE (Opsional - Aktifkan jika punya Key) ===

$secretKey = '0x4AAAAAACDUsrwihLsJ1EDQih414ao7r5Y';
$token = $data['cf-turnstile-response'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'];

$verify = file_get_contents("https://challenges.cloudflare.com/turnstile/v0/siteverify", false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query(['secret' => $secretKey, 'response' => $token, 'remoteip' => $ip])
    ]
]));
$captchaResult = json_decode($verify);
if (!$captchaResult->success) {
    echo json_encode(['status' => 'error', 'message' => 'Verifikasi Captcha Gagal']);
    exit;
}

// ========================================================================

$name = $data['name'] ?? 'No Name';
// Kita gabungkan alamat ke dalam nama atau buat kolom baru di DB (disini digabung biar simpel)
$full_info = $name . " (Alamat: " . ($data['address'] ?? '-') . ")";

$items = json_encode($data['items']);
$total = $data['total'] ?? 0;

try {
    // Simpan ke DB
    $stmt = $pdo->prepare("INSERT INTO orders (customer_name, items, total_price, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$full_info, $items, $total]);
    
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>