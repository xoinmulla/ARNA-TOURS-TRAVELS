<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$admin = currentAdmin();
$currentPage = basename($_SERVER['PHP_SELF']);
function adminNavActive(string $file): string
{
    global $currentPage;
    return $currentPage === $file ? 'active' : '';
}
function adminInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $out = '';
    foreach (array_slice($parts ?: [], 0, 2) as $p) {
        $out .= strtoupper(substr($p, 0, 1));
    }
    return $out ?: 'A';
}
function adminHeader(string $title, string $subtitle = ''): void
{
    global $admin; ?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title><?= e($title) ?> | Arna Admin</title>
        <link rel="icon" href="../assets/img/favicon.png" type="image/png">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../assets/css/admin.css">
    </head>

    <body class="admin-body">
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="admin-brand"><img src="../assets/img/arna-logo.png" alt="Arna Tour & Travels"
                    class="admin-brand-logo" width="62" height="62">
                <div><strong>ARNA</strong><span>TOURS & TRAVELS</span></div>
            </div>
            <nav class="admin-nav"><a class="<?= adminNavActive('dashboard.php') ?>" href="dashboard.php"><i
                        class="ri-dashboard-3-line"></i>Dashboard</a><a class="<?= adminNavActive('bookings.php') ?>"
                    href="bookings.php"><i class="ri-calendar-check-line"></i>Bookings</a><a
                    class="<?= adminNavActive('customers.php') ?>" href="customers.php"><i
                        class="ri-user-3-line"></i>Customers</a><a class="<?= adminNavActive('vehicles.php') ?>"
                    href="vehicles.php"><i class="ri-car-line"></i>Vehicles</a><a
                    class="<?= adminNavActive('packages.php') ?>" href="packages.php"><i class="ri-map-pin-line"></i>Tour
                    Packages</a><a class="<?= adminNavActive('home-tour-packages.php') ?>" href="home-tour-packages.php"><i
                        class="ri-home-smile-2-line"></i>Homepage Tours</a><a class="<?= adminNavActive('services.php') ?>"
                    href="services.php"><i class="ri-service-line"></i>Services</a><a
                    class="<?= adminNavActive('destinations.php') ?>" href="destinations.php"><i
                        class="ri-map-pin-2-line"></i>Destinations</a><a class="<?= adminNavActive('drivers.php') ?>"
                    href="drivers.php"><i class="ri-steering-2-line"></i>Drivers</a><a
                    class="<?= adminNavActive('testimonials.php') ?>" href="testimonials.php"><i
                        class="ri-chat-quote-line"></i>Testimonials</a><a href="../index.php"><i
                        class="ri-external-link-line"></i>View Website</a></nav>
        </aside>
        <main class="admin-main">
            <header class="admin-topbar">
                <div class="d-flex align-items-center gap-3"><button class="mobile-toggle" id="sidebarToggle"
                        type="button"><i class="ri-menu-line"></i></button>
                    <div>
                        <h1><?= e($title) ?></h1><?php if ($subtitle): ?>
                            <div class="text-muted small mt-1"><?= e($subtitle) ?></div><?php endif; ?>
                    </div>
                </div>
                <div class="admin-user">
                    <div class="user-info text-end">
                        <strong><?= e($admin['name'] ?? 'Admin') ?></strong><small>Administrator</small></div>
                    <div class="admin-avatar"><?= e(adminInitials($admin['name'] ?? 'Admin')) ?></div><a
                        class="btn-admin btn-light" href="logout.php" title="Logout"><i
                            class="ri-logout-box-r-line"></i></a>
                </div>
            </header>
            <div class="admin-content">
            <?php }
function adminFooter(): void
{ ?>
            </div>
        </main>
        <script>const toggle = document.getElementById('sidebarToggle'), sidebar = document.getElementById('adminSidebar'), backdrop = document.getElementById('sidebarBackdrop'); if (toggle) { toggle.onclick = () => { sidebar.classList.toggle('open'); backdrop.classList.toggle('show') } } if (backdrop) { backdrop.onclick = () => { sidebar.classList.remove('open'); backdrop.classList.remove('show') } }</script>
    </body>

    </html>
<?php }
