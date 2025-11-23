<?php
session_start();
function require_admin_login(){
 if(empty($_SESSION['admin_logged_in'])){header('Location: /admin/admin_login.php');exit;}
}
