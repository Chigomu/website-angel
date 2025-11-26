<?php
require_once __DIR__ . '/db.php';

function getSiteSettings($pdo) {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    $results = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Hasil jadi ['key' => 'value']
    return $results;
}

// Load settings global
$site_settings = getSiteSettings($pdo);

// Fungsi helper untuk memanggil setting dengan aman (fallback jika kosong)
function set($key, $default = '') {
    global $site_settings;
    return !empty($site_settings[$key]) ? $site_settings[$key] : $default;
}
?>