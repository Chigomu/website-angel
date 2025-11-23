<?php
require_once __DIR__.'/../app/auth_check.php';
require_admin_login();
echo file_get_contents(__DIR__.'/../dashboard.html');
