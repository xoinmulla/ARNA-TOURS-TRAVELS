<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';
sendSecurityHeaders();
require_once __DIR__ . '/../includes/auth.php';
redirectIfAdminLoggedIn();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyAdminCsrf()) {
        $error = 'Your session expired. Please refresh and try again.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $error = 'Please enter a valid email and password.';
        } else {
            try {
                $db = Database::getConnection();
                $stmt = $db->prepare('SELECT id,name,email,password,status FROM admin_users WHERE email = ? LIMIT 1');
                $stmt->execute([$email]);
                $admin = $stmt->fetch();
                if (!$admin || $admin['status'] !== 'ACTIVE' || !password_verify($password, $admin['password'])) {
                    $error = 'Invalid email or password.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = (int)$admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $update = $db->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?');
                    $update->execute([(int)$admin['id']]);
                    header('Location: dashboard.php');
                    exit;
                }
            } catch (Throwable $e) {
                error_log('Arna admin login error: '.$e->getMessage());
                $error = 'Unable to sign in right now. Please try again.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login | Arna Tours & Travels</title>
<link rel="icon" href="../assets/img/favicon.png" type="image/png">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="login-body">
<main class="login-page login-modern">
    <span class="login-orb orb-one"></span>
    <span class="login-orb orb-two"></span>

    <section class="login-modern-shell">
        <div class="login-modern-brand">
            <img class="login-modern-logo" src="../assets/img/arna-logo.png" alt="Arna Tour & Travels">
            <div class="eyebrow">Arna Tour & Travels</div>
            <h2>Manage every journey from <span>one place.</span></h2>
            <p>Use the Arna Admin dashboard to manage bookings, customers, vehicles, tour packages, services and travel operations.</p>
            <div class="login-modern-features">
                <span class="login-modern-feature"><i class="ri-calendar-check-line"></i> Bookings</span>
                <span class="login-modern-feature"><i class="ri-car-line"></i> Fleet</span>
                <span class="login-modern-feature"><i class="ri-map-pin-line"></i> Tour Packages</span>
                <span class="login-modern-feature"><i class="ri-customer-service-2-line"></i> Operations</span>
            </div>
        </div>

        <div class="login-modern-card">
            <div class="login-modern-form">
                <div class="login-modern-form-head">
                    <img class="login-modern-form-logo" src="../assets/img/arna-logo.png" alt="Arna Tour & Travels logo">
                    <h1>Arna Admin</h1>
                    <p>Secure access to your travel management dashboard.</p>
                </div>

                <?php if($error): ?>
                    <div class="alert-admin alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="post" autocomplete="on">
                    <input type="hidden" name="csrf_token" value="<?= e(adminCsrfToken()) ?>">
                    <div class="field">
                        <label for="email">Email address</label>
                        <input class="form-control" id="email" name="email" type="email" placeholder="admin@arnatours.com" required autocomplete="username">
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input class="form-control" id="password" name="password" type="password" placeholder="Enter your password" required autocomplete="current-password">
                    </div>
                    <button class="btn-login" type="submit">Sign in to Dashboard <i class="ri-arrow-right-line"></i></button>
                </form>

                <a href="../index.php" class="login-modern-back">
                    <i class="ri-arrow-left-line"></i> Back to website
                </a>
            </div>
        </div>
    </section>
</main>
</body>
</html>