<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-layout.php';
require_once __DIR__ . '/../models/TourPackage.php';

$m = new TourPackage();
$flash = getFlashMessage();
$message = $flash['message'] ?? '';
$type = $flash['type'] ?? 'success';
$editing = null;

function tourPackageUpload(?array $file, ?string $oldImage = null): string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $oldImage ?? '';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Tour package image upload failed.');
    }
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('Tour package image should be 2 MB or smaller.');
    }
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Only JPG, PNG and WEBP tour package images are allowed.');
    }
    $dimensions = @getimagesize($file['tmp_name']);
    if (!$dimensions || $dimensions[0] < 1 || $dimensions[1] < 1 || $dimensions[0] > 5000 || $dimensions[1] > 5000) {
        throw new RuntimeException('Tour package image dimensions must be between 1 and 5000 pixels per side.');
    }
    if (!function_exists('imagecreatefromjpeg') || !function_exists('imagewebp')) {
        throw new RuntimeException('Server image processing (GD) is required for secure tour package uploads.');
    }
    $source = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
        'image/png' => @imagecreatefrompng($file['tmp_name']),
        'image/webp' => @imagecreatefromwebp($file['tmp_name']),
        default => false,
    };
    if (!$source) {
        throw new RuntimeException('The uploaded tour package image could not be decoded.');
    }
    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagepalettetotruecolor($source);
        imagealphablending($source, false);
        imagesavealpha($source, true);
    }
    $filename = 'tour-package-' . bin2hex(random_bytes(10)) . '.webp';
    $directory = __DIR__ . '/../assets/uploads/tour-packages';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        imagedestroy($source);
        throw new RuntimeException('Unable to create tour package upload directory.');
    }
    $target = $directory . '/' . $filename;
    $saved = imagewebp($source, $target, 82);
    imagedestroy($source);
    if (!$saved) {
        throw new RuntimeException('Unable to save tour package image.');
    }
    return 'assets/uploads/tour-packages/' . $filename;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!verifyAdminCsrf()) {
            throw new RuntimeException('Security token expired. Refresh the page.');
        }

        $action = $_POST['action'] ?? '';
        $id = (int) ($_POST['id'] ?? 0);

        if ($action === 'save_category') {
            $title = trim((string)($_POST['category_title'] ?? ''));
            $slug = strtolower(trim((string)($_POST['category_slug'] ?? '')));
            $sort = (int)($_POST['category_sort'] ?? 0);
            $status = (string)($_POST['category_status'] ?? 'ACTIVE');
            if ($title === '' || !preg_match('/^[a-z0-9-]+$/', $slug)) throw new RuntimeException('Category title and a valid slug are required.');
            if (!in_array($status, ['ACTIVE','INACTIVE'], true)) throw new RuntimeException('Invalid category status.');
            $cid = (int)($_POST['category_id'] ?? 0);
            if ($cid) { if (!$m->category($cid)) throw new RuntimeException('Category not found.'); $m->updateCategory($cid,['title'=>$title,'slug'=>$slug,'sort_order'=>$sort,'status'=>$status]); $ok='Tour package category updated successfully.'; }
            else { $m->createCategory(['title'=>$title,'slug'=>$slug,'sort_order'=>$sort,'status'=>$status]); $ok='Tour package category added successfully.'; }
            setFlashMessage($ok); header('Location: packages.php'); exit;
        }
        if ($action === 'delete_category') { $cid=(int)($_POST['category_id']??0); if($cid<1||!$m->category($cid)) throw new RuntimeException('Invalid category.'); $m->deleteCategory($cid); setFlashMessage('Tour package category deleted successfully.'); header('Location: packages.php'); exit; }

        if ($action === 'save') {
            $name = trim((string) ($_POST['package_name'] ?? ''));
            $dest = trim((string) ($_POST['destination'] ?? ''));
            $status = (string) ($_POST['status'] ?? 'ACTIVE');

            if ($name === '' || $dest === '') {
                throw new RuntimeException('Package name and destination are required.');
            }
            if (!in_array($status, ['ACTIVE', 'INACTIVE'], true)) {
                throw new RuntimeException('Invalid status.');
            }

            $price = trim((string) ($_POST['price'] ?? ''));
            if ($price !== '' && (!is_numeric($price) || (float) $price < 0)) {
                throw new RuntimeException('Price must be a valid non-negative number.');
            }

            $existingPackage = $id > 0 ? $m->find($id) : null;
            if ($id > 0 && !$existingPackage) {
                throw new RuntimeException('Package not found.');
            }
            $data = [
                'package_name' => $name,
                'destination' => $dest,
                'category_id' => (int) ($_POST['category_id'] ?? 0),
                'duration' => trim((string) ($_POST['duration'] ?? '')),
                'price' => $price,
                'image' => tourPackageUpload($_FILES['image'] ?? null, $existingPackage['image'] ?? ''),
                'description' => trim((string) ($_POST['description'] ?? '')),
                'status' => $status,
            ];

            if ($id > 0) {
                $m->update($id, $data);
                $success = 'Tour package updated successfully.';
            } else {
                $m->create($data);
                $success = 'Tour package added successfully.';
            }

            // POST/Redirect/GET prevents browser refresh from submitting the INSERT again.
            setFlashMessage($success);
            header('Location: packages.php');
            exit;
        }

        if ($action === 'delete') {
            if ($id < 1 || !$m->find($id)) {
                throw new RuntimeException('Invalid package.');
            }
            $m->delete($id);
            setFlashMessage('Tour package deleted successfully.');
            header('Location: packages.php');
            exit;
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $type = 'danger';
    }
}

if (isset($_GET['edit'])) {
    $editing = $m->find((int) $_GET['edit']);
}

$search = trim((string) ($_GET['search'] ?? ''));
$statusFilter = strtoupper(trim((string) ($_GET['status'] ?? '')));
$categories = $m->categories(false);
$categoryEdit = isset($_GET['edit_category']) ? $m->category((int)$_GET['edit_category']) : null;
$rows = $m->all($search, $statusFilter);
$csrf = adminCsrfToken();
adminHeader('Tour Packages', 'Create and manage destination-based packages.');
?>
<div class="admin-card mb-4">
    <div class="admin-card-head">
        <div>
            <h2><?= $editing ? 'Edit Tour Package' : 'Add Tour Package' ?></h2>
            <p>Keep duration, pricing and descriptions editable from the admin panel.</p>
        </div>
        <?php if ($editing): ?><a class="btn-admin btn-light" href="packages.php">Cancel</a><?php endif; ?>
    </div>
    <?php if ($message): ?>
        <div class="px-4 pt-3"><div class="alert alert-<?= e($type) ?> border-0 mb-0"><?= e($message) ?></div></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="row g-3 p-4">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
        <div class="col-md-6"><label class="form-label">Package Name *</label><input class="form-control" name="package_name" required maxlength="150" value="<?= e($editing['package_name'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Destination *</label><input class="form-control" name="destination" required maxlength="150" value="<?= e($editing['destination'] ?? '') ?>"></div><div class="col-md-4"><label class="form-label">Navbar Category</label><select class="form-select" name="category_id"><option value="0">Uncategorized</option><?php foreach($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= (int)($editing['category_id'] ?? 0)===(int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['title']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><label class="form-label">Duration</label><input class="form-control" name="duration" placeholder="3 Days / 2 Nights" value="<?= e($editing['duration'] ?? '') ?>"></div>
        <div class="col-md-3"><label class="form-label">Price (₹)</label><input class="form-control" type="number" min="0" step="0.01" name="price" value="<?= e((string) ($editing['price'] ?? '')) ?>"></div>
        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="ACTIVE" <?= ($editing['status'] ?? 'ACTIVE') === 'ACTIVE' ? 'selected' : '' ?>>Active</option><option value="INACTIVE" <?= ($editing['status'] ?? '') === 'INACTIVE' ? 'selected' : '' ?>>Inactive</option></select></div>
        <div class="col-md-6"><label class="form-label">Tour Package Image</label><input class="form-control" id="packageImageInput" type="file" name="image" accept="image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG or WEBP · max 2 MB</div><div id="packageImageSizeMsg" class="small text-danger mt-1 d-none" role="alert">Tour package image should be 2 MB or smaller.</div><?php if (!empty($editing['image'])): ?><div class="mt-3 d-flex align-items-center gap-3"><img src="<?= e($editing['image']) ?>" alt="Current package image" style="width:110px;height:72px;object-fit:cover;border-radius:12px;border:1px solid #e6def3"><span class="small text-muted">Current image. Choose a new file to replace it.</span></div><?php endif; ?></div>
        <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="4"><?= e($editing['description'] ?? '') ?></textarea></div>
        <div class="col-12"><button class="btn-admin btn-primary" type="submit"><i class="ri-save-line"></i> <?= $editing ? 'Update Package' : 'Add Package' ?></button></div>
    </form>
</div>

<div class="admin-card mb-4"><div class="admin-card-head"><div><h2><?= $categoryEdit ? 'Edit Tour Package Category' : 'Navbar Tour Package Categories' ?></h2><p>These active categories become the public “Tour Package” dropdown links.</p></div><?php if($categoryEdit):?><a class="btn-admin btn-light" href="packages.php">Cancel</a><?php endif;?></div><form method="post" class="row g-3 p-4"><input type="hidden" name="csrf_token" value="<?=e($csrf)?>"><input type="hidden" name="action" value="save_category"><input type="hidden" name="category_id" value="<?= (int)($categoryEdit['id']??0) ?>"><div class="col-md-4"><label class="form-label">Title *</label><input class="form-control" name="category_title" required maxlength="100" value="<?=e($categoryEdit['title']??'')?>"></div><div class="col-md-4"><label class="form-label">Slug *</label><input class="form-control" name="category_slug" pattern="[a-z0-9-]+" required maxlength="100" value="<?=e($categoryEdit['slug']??'')?>" placeholder="domestic"></div><div class="col-md-2"><label class="form-label">Order</label><input class="form-control" type="number" name="category_sort" value="<?= (int)($categoryEdit['sort_order']??0) ?>"></div><div class="col-md-2"><label class="form-label">Status</label><select class="form-select" name="category_status"><option value="ACTIVE" <?=($categoryEdit['status']??'ACTIVE')==='ACTIVE'?'selected':''?>>Active</option><option value="INACTIVE" <?=($categoryEdit['status']??'')==='INACTIVE'?'selected':''?>>Inactive</option></select></div><div class="col-12"><button class="btn-admin btn-primary" type="submit"><i class="ri-save-line"></i><?=$categoryEdit?'Update Category':'Add Category'?></button></div></form><div class="table-responsive"><table class="table admin-table align-middle mb-0"><thead><tr><th>Title</th><th>Slug</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody><?php foreach($categories as $cat):?><tr><td><strong><?=e($cat['title'])?></strong></td><td><?=e($cat['slug'])?></td><td><?= (int)$cat['sort_order'] ?></td><td><span class="status-pill status-<?=strtolower($cat['status'])?>"><?=e($cat['status'])?></span></td><td class="text-end"><a class="btn-admin btn-light btn-sm" href="packages.php?edit_category=<?=(int)$cat['id']?>">Edit</a> <form class="d-inline" method="post" onsubmit="return confirm('Delete this category? Packages will become uncategorized.')"><input type="hidden" name="csrf_token" value="<?=e($csrf)?>"><input type="hidden" name="action" value="delete_category"><input type="hidden" name="category_id" value="<?=(int)$cat['id']?>"><button class="btn-admin btn-danger-soft btn-sm" type="submit">Delete</button></form></td></tr><?php endforeach;?></tbody></table></div></div>
<div class="admin-card">
    <div class="admin-card-head"><div><h2>Packages</h2><p><?= count($rows) ?> package(s)</p></div>
        <form class="toolbar" method="get"><input class="form-control" name="search" placeholder="Search package or destination" value="<?= e($search) ?>"><select class="form-select" name="status"><option value="">All</option><option value="ACTIVE" <?= $statusFilter === 'ACTIVE' ? 'selected' : '' ?>>Active</option><option value="INACTIVE" <?= $statusFilter === 'INACTIVE' ? 'selected' : '' ?>>Inactive</option></select><button class="btn-admin btn-primary">Search</button></form>
    </div>
    <div class="table-responsive"><table class="table admin-table align-middle mb-0"><thead><tr><th>Package</th><th>Destination</th><th>Category</th><th>Duration</th><th>Price</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr><td><strong><?= e($r['package_name']) ?></strong><div class="small text-muted"><?= e($r['description'] ?? '') ?></div></td><td><?= e($r['destination']) ?></td><td><?= e($r['category_title'] ?? '—') ?></td><td><?= e($r['duration'] ?? '—') ?></td><td><?= $r['price'] !== null ? '₹' . number_format((float) $r['price'], 2) : 'On request' ?></td><td><span class="status-pill status-<?= strtolower($r['status']) ?>"><?= e($r['status']) ?></span></td><td class="text-end"><a class="btn-admin btn-light btn-sm" href="packages.php?edit=<?= (int) $r['id'] ?>">Edit</a> <form class="d-inline" method="post" onsubmit="return confirm('Delete this package?')"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn-admin btn-danger-soft btn-sm" type="submit">Delete</button></form></td></tr><?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="text-center py-5 text-muted">No packages found.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('packageImageInput');
    const msg = document.getElementById('packageImageSizeMsg');
    if (!input || !msg) return;
    const form = input.closest('form');
    const maxBytes = 2 * 1024 * 1024;
    const validate = function () {
        const file = input.files && input.files[0];
        const invalid = !!file && file.size > maxBytes;
        msg.classList.toggle('d-none', !invalid);
        input.setCustomValidity(invalid ? 'Tour package image should be 2 MB or smaller.' : '');
        return !invalid;
    };
    input.addEventListener('change', validate);
    form?.addEventListener('submit', function (event) {
        if (!validate()) event.preventDefault();
    });
});
</script>
<?php adminFooter();
