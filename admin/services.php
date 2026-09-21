<?php

declare(strict_types=1);

require_once __DIR__ . '/admin-layout.php';
require_once __DIR__ . '/../models/Service.php';

$model = new Service();
$flash = getFlashMessage();
$message = $flash['message'] ?? '';
$type = $flash['type'] ?? 'success';
$editing = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!verifyAdminCsrf()) throw new RuntimeException('Your session token has expired. Refresh and try again.');
        $action = $_POST['action'] ?? '';
        $id = (int) ($_POST['id'] ?? 0);

        if ($action === 'save') {
            $title = trim((string) ($_POST['title'] ?? ''));
            $description = trim((string) ($_POST['short_description'] ?? ''));
            $icon = trim((string) ($_POST['icon'] ?? ''));
            $sort = (int) ($_POST['sort_order'] ?? 0);
            $status = (string) ($_POST['status'] ?? 'ACTIVE');
            if ($title === '') throw new RuntimeException('Service title is required.');
            if (!in_array($status, ['ACTIVE','INACTIVE'], true)) throw new RuntimeException('Invalid status.');
            $data = ['title'=>$title,'short_description'=>$description,'icon'=>$icon,'image'=>'','sort_order'=>$sort,'status'=>$status];
            if ($id > 0) { $model->update($id, $data); $success = 'Service updated successfully.'; }
            else { $model->create($data); $success = 'Service added successfully.'; }
            setFlashMessage($success); header('Location: services.php'); exit;
        } elseif ($action === 'delete') {
            if ($id < 1) throw new RuntimeException('Invalid service.');
            $model->delete($id); setFlashMessage('Service deleted successfully.'); header('Location: services.php'); exit;
        }
    } catch (Throwable $e) { $message = $e->getMessage(); $type = 'danger'; }
}

if (isset($_GET['edit'])) $editing = $model->find((int) $_GET['edit']);
$services = $model->all();
$csrf = adminCsrfToken();
adminHeader('Services', 'Manage the travel services shown on the public website.');
?>
<?php if ($message): ?><div class="alert alert-<?= e($type) ?> border-0 shadow-sm"><?= e($message) ?></div><?php endif; ?>
<div class="admin-card mb-4">
 <div class="admin-card-head"><div><h2><?= $editing ? 'Edit Service' : 'Add Service' ?></h2><p>These entries are designed to become editable public website content.</p></div><?php if($editing): ?><a class="btn-admin btn-light" href="services.php">Cancel</a><?php endif; ?></div>
 <form method="post" class="row g-3 p-4">
  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($editing['id'] ?? 0) ?>">
  <div class="col-md-6"><label class="form-label">Service Title *</label><input class="form-control" name="title" required maxlength="150" value="<?= e($editing['title'] ?? '') ?>"></div>
  <div class="col-md-3"><label class="form-label">Icon</label><input class="form-control" name="icon" maxlength="100" placeholder="🚕" value="<?= e($editing['icon'] ?? '') ?>"></div>
  <div class="col-md-3"><label class="form-label">Display Order</label><input class="form-control" type="number" name="sort_order" value="<?= (int)($editing['sort_order'] ?? 0) ?>"></div>
  <div class="col-md-9"><label class="form-label">Description</label><textarea class="form-control" name="short_description" rows="3" maxlength="500"><?= e($editing['short_description'] ?? '') ?></textarea></div>
  <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="ACTIVE" <?= (($editing['status'] ?? 'ACTIVE') === 'ACTIVE')?'selected':'' ?>>Active</option><option value="INACTIVE" <?= (($editing['status'] ?? '') === 'INACTIVE')?'selected':'' ?>>Inactive</option></select></div>
  <div class="col-12"><button class="btn-admin btn-primary" type="submit"><i class="ri-save-line"></i> <?= $editing ? 'Update Service' : 'Add Service' ?></button></div>
 </form>
</div>
<div class="admin-card"><div class="admin-card-head"><div><h2>Website Services</h2><p><?= count($services) ?> service(s)</p></div></div><div class="table-responsive"><table class="table admin-table align-middle mb-0"><thead><tr><th>Order</th><th>Service</th><th>Description</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
<?php foreach($services as $service): ?><tr><td><?= (int)$service['sort_order'] ?></td><td><span class="fs-5 me-2"><?= e($service['icon']) ?></span><strong><?= e($service['title']) ?></strong></td><td class="text-muted"><?= e($service['short_description']) ?></td><td><span class="status-pill status-<?= strtolower(e($service['status'])) ?>"><?= e($service['status']) ?></span></td><td class="text-end"><a class="btn-admin btn-light btn-sm" href="services.php?edit=<?= (int)$service['id'] ?>"><i class="ri-edit-line"></i> Edit</a> <form method="post" class="d-inline" onsubmit="return confirm('Delete this service?');"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$service['id'] ?>"><button class="btn-admin btn-danger-soft btn-sm"><i class="ri-delete-bin-line"></i></button></form></td></tr><?php endforeach; ?>
<?php if(!$services): ?><tr><td colspan="5" class="text-center py-5 text-muted">No services yet.</td></tr><?php endif; ?></tbody></table></div></div>
<?php adminFooter(); ?>
