<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-layout.php';
require_once __DIR__ . '/../models/TourPackage.php';

$m = new TourPackage();
$flash = getFlashMessage();
$message = $flash['message'] ?? '';
$type = $flash['type'] ?? 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!verifyAdminCsrf()) throw new RuntimeException('Security token expired. Refresh the page.');
        $ids = $_POST['package_id'] ?? [];
        $featured = $_POST['featured'] ?? [];
        $orders = $_POST['home_sort_order'] ?? [];
        foreach ($ids as $id) {
            $id=(int)$id;
            if ($id<1 || !$m->find($id)) continue;
            $sort=max(0,(int)($orders[$id] ?? 0));
            $isFeatured=isset($featured[$id]);
            $m->updateHomeSettings($id,$isFeatured,$sort);
        }
        setFlashMessage('Homepage Tour Packages updated successfully.');
        header('Location: home-tour-packages.php');
        exit;
    } catch (Throwable $e) { $message=$e->getMessage(); $type='danger'; }
}

$rows=$m->all(null,'ACTIVE');
$csrf=adminCsrfToken();
adminHeader('Homepage Tour Packages','Choose and order the tour cards displayed directly after the homepage hero.');
?>
<div class="admin-card mb-4">
  <div class="admin-card-head"><div><h2>Tour Packages Section</h2><p>The card content comes from Tour Packages. Edit package name, destination, image, duration, price and description in <a href="packages.php">Tour Packages</a>; use this page to control homepage visibility and order.</p></div></div>
  <?php if($message): ?><div class="px-4 pt-3"><div class="alert alert-<?=e($type) ?> border-0 mb-0"><?=e($message) ?></div></div><?php endif; ?>
  <form method="post" class="p-4">
    <input type="hidden" name="csrf_token" value="<?=e($csrf) ?>">
    <div class="table-responsive"><table class="table admin-table align-middle mb-4"><thead><tr><th>Homepage</th><th>Package</th><th>Destination</th><th>Category</th><th>Image</th><th>Order</th></tr></thead><tbody>
    <?php foreach($rows as $r): $id=(int)$r['id']; ?>
      <tr>
        <td><input class="form-check-input" type="checkbox" name="featured[<?=$id?>]" value="1" <?=!empty($r['featured_home'])?'checked':''?> aria-label="Show <?=e($r['package_name'])?> on homepage"><input type="hidden" name="package_id[]" value="<?=$id?>"></td>
        <td><strong><?=e($r['package_name'])?></strong><div class="small text-muted"><?=e($r['description']??'')?></div></td>
        <td><?=e($r['destination'])?></td>
        <td><?=e($r['category_title']??'—')?></td>
        <td><?php if(!empty($r['image'])): ?><img src="../<?=e($r['image'])?>" alt="<?=e($r['package_name'])?>" style="width:72px;height:48px;object-fit:cover;border-radius:10px"><?php else: ?><span class="small text-muted">No image</span><?php endif; ?></td>
        <td><input class="form-control" style="width:90px" type="number" name="home_sort_order[<?=$id?>]" value="<?= (int)($r['home_sort_order']??0) ?>" min="0"></td>
      </tr>
    <?php endforeach; ?>
    <?php if(!$rows): ?><tr><td colspan="6" class="text-center py-5 text-muted">Add active tour packages first.</td></tr><?php endif; ?>
    </tbody></table></div>
    <button class="btn-admin btn-primary" type="submit"><i class="ri-save-line"></i> Save Homepage Tour Packages</button>
  </form>
</div>
<?php adminFooter(); ?>
