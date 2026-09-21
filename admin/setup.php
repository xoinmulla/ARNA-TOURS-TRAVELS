<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
sendSecurityHeaders();
sendSecurityHeaders();
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$db = null;
$count = 0;
$locked = false;
$error=''; $success='';
try {
    $db = Database::getConnection();
    $count = (int)$db->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
    $locked = $count > 0;
} catch (Throwable $e) {
    error_log('Arna admin setup database error: ' . $e->getMessage());
    $error = 'The database is not ready. Please import the schema and verify the database configuration.';
}
if (!$locked && $db instanceof PDO && $_SERVER['REQUEST_METHOD']==='POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $error='Session expired. Refresh and try again.';
    } else {
        $name=trim($_POST['name']??''); $email=strtolower(trim($_POST['email']??'')); $password=(string)($_POST['password']??''); $confirm=(string)($_POST['confirm_password']??'');
        if($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<8) $error='Name, valid email and a password of at least 8 characters are required.';
        elseif($password!==$confirm) $error='Passwords do not match.';
        else { $stmt=$db->prepare('INSERT INTO admin_users (name,email,password,status) VALUES (?,?,?,\'ACTIVE\')'); $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]); $locked=true; $success='Admin account created successfully. You can now sign in.'; }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Admin Setup | Arna Tours & Travels</title><link rel="stylesheet" href="../assets/css/admin.css"></head><body><div style="text-align:center;margin-bottom:18px"><img src="../assets/img/arna-logo.png" alt="Arna Tour & Travels" style="width:110px;height:110px;object-fit:contain"></div><main class="login-page"><section class="login-card"><div class="login-logo"><div class="mark">A</div><h1>First Admin Setup</h1><p>Create the first administrator account.</p></div><?php if($error): ?><div class="alert-admin alert-danger"><?=e($error)?></div><?php endif;?><?php if($success): ?><div class="alert-admin alert-success"><?=e($success)?></div><a class="btn-admin btn-primary w-100" href="index.php">Go to Admin Login</a><?php elseif($locked): ?><div class="alert-admin alert-success">An administrator already exists. Setup is locked.</div><a class="btn-admin btn-primary w-100" href="index.php">Go to Admin Login</a><?php else: ?><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrfToken())?>"><div class="field"><label>Name</label><input class="form-control" name="name" required></div><div class="field"><label>Email</label><input class="form-control" name="email" type="email" required></div><div class="field"><label>Password</label><input class="form-control" name="password" type="password" minlength="8" required></div><div class="field"><label>Confirm Password</label><input class="form-control" name="confirm_password" type="password" minlength="8" required></div><button class="btn-login" type="submit">Create Administrator</button></form><?php endif;?></section></main></body></html>
