<?php
session_start();
require_once __DIR__.'/../app/db.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $u=$_POST['username'];$p=$_POST['password'];
 $st=$pdo->prepare('SELECT * FROM admins WHERE username=?');
 $st->execute([$u]);$a=$st->fetch();
 if($a && password_verify($p,$a['password_hash'])){
  $_SESSION['admin_logged_in']=1;
  header('Location: /admin/dashboard.php');exit;
 }
 $err='Login salah';
}
?><form method=post>
<input name=username placeholder=Username>
<input name=password type=password placeholder=Password>
<button>Login</button>
<?php if(!empty($err)) echo $err;?>
</form>
