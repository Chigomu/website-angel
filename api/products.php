<?php
require_once __DIR__.'/../app/db.php';
header('Content-Type: application/json');
$st=$pdo->query("SELECT * FROM products ORDER BY id DESC");
echo json_encode(['data'=>$st->fetchAll()], JSON_UNESCAPED_UNICODE);
