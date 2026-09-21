<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-layout.php';
require_once __DIR__ . '/../models/Cms.php';
$m = new Cms();
$flash = getFlashMessage();
$message = $flash['message'] ?? '';
$type = $flash['type'] ?? 'success';
$keys = ['business_phone' => 'Business Phone', 'business_email' => 'Business Email', 'business_address' => 'Business Address', 'booking_notice' => 'Booking Notice'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!verifyAdminCsrf())
            throw new RuntimeException('Security token expired. Refresh the page.');
        foreach ($keys as $k => $label) {
            $v = trim($_POST[$k] ?? '');
            if ($k === 'business_email' && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL))
                throw new RuntimeException('Business email is invalid.');
            $m->saveSetting($k, $v);
        }
        setFlashMessage('Settings saved successfully.');
        header('Location: settings.php');
        exit;
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $type = 'danger';
    }
}
$settings = $m->settings();
$csrf = adminCsrfToken();
adminHeader('Settings', 'Manage business contact and booking configuration.');
?>
<div class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>Business Settings</h2>
            <p>These values are safe to expose to the public website when needed.</p>
        </div>
    </div><?php if ($message): ?>
        <div class="px-4 pt-3">
            <div class="alert alert-<?= $type ?> border-0 mb-0"><?= e($message) ?></div>
        </div><?php endif; ?>
    <form method="post" class="row g-3 p-4"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <div class="col-md-6"><label class="form-label">Business Phone</label><input class="form-control"
                name="business_phone" value="<?= e($settings['business_phone']['setting_value'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Business Email</label><input class="form-control" type="email"
                name="business_email" value="<?= e($settings['business_email']['setting_value'] ?? '') ?>"></div>
        <div class="col-12"><label class="form-label">Business Address</label><input class="form-control"
                name="business_address" value="<?= e($settings['business_address']['setting_value'] ?? '') ?>"></div>
        <div class="col-12"><label class="form-label">Booking Notice</label><textarea class="form-control"
                name="booking_notice" rows="4"><?= e($settings['booking_notice']['setting_value'] ?? '') ?></textarea></div>
        <div class="col-12"><button class="btn-admin btn-primary"><i class="ri-save-line"></i> Save Settings</button>
        </div>
    </form>
</div>
<?php adminFooter();
