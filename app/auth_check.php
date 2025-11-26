<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$currentScript = $_SERVER['SCRIPT_NAME'];
if (strpos($currentScript, '/admin/') !== false && strpos($currentScript, 'admin_login.php') === false) {
    
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: admin_login.php');
        exit;
    }
}
?>