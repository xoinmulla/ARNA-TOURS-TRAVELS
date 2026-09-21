<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-layout.php';
require_once __DIR__ . '/../models/Cms.php';
$m = new Cms();
$flash = getFlashMessage();
$message = $flash['message'] ?? '';
$type = $flash['type'] ?? 'success';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!verifyAdminCsrf())
            throw new RuntimeException('Security token expired. Refresh the page.');
        $action = $_POST['action'] ?? '';
        if ($action === 'content') {
            foreach (['hero_badge', 'hero_title', 'hero_description', 'about_title', 'about_description'] as $key)
                $m->saveContent($key, trim($_POST[$key] ?? ''), str_starts_with($key, 'hero_') ? 'Hero' : 'About');
            setFlashMessage('Website content saved successfully.');
            header('Location: cms.php');
            exit;
        } elseif ($action === 'testimonial') {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::getConnection();
            $id = (int) ($_POST['id'] ?? 0);
            $name = trim($_POST['customer_name'] ?? '');
            $msg = trim($_POST['message'] ?? '');
            $rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
            $status = $_POST['status'] ?? 'ACTIVE';
            if ($name === '' || $msg === '')
                throw new RuntimeException('Testimonial name and message are required.');
            if ($id) {
                $s = $db->prepare('UPDATE testimonials SET customer_name=?,message=?,rating=?,status=? WHERE id=?');
                $s->execute([$name, $msg, $rating, $status, $id]);
            } else {
                $s = $db->prepare('INSERT INTO testimonials(customer_name,message,rating,status) VALUES(?,?,?,?)');
                $s->execute([$name, $msg, $rating, $status]);
            }
            setFlashMessage('Testimonial saved successfully.');
            header('Location: cms.php');
            exit;
        } elseif ($action === 'delete_testimonial') {
            require_once __DIR__ . '/../config/database.php';
            $s = Database::getConnection()->prepare('DELETE FROM testimonials WHERE id=?');
            $s->execute([(int) ($_POST['id'] ?? 0)]);
            setFlashMessage('Testimonial deleted.');
            header('Location: cms.php');
            exit;
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $type = 'danger';
    }
}
$content = $m->content();
require_once __DIR__ . '/../config/database.php';
$db = Database::getConnection();
$testimonials = $db->query('SELECT * FROM testimonials ORDER BY created_at DESC')->fetchAll();
$csrf = adminCsrfToken();
adminHeader('Website CMS', 'Edit verified website messaging and testimonials without changing PHP templates.');
?>
<!-- <div class="admin-card mb-4">
    <div class="admin-card-head">
        <div>
            <h2>Website Content</h2>
            <p>Use only approved/verified business claims before production.</p>
        </div>
    </div><?php if ($message): ?>
        <div class="px-4 pt-3">
            <div class="alert alert-<?= $type ?> border-0 mb-0"><?= e($message) ?></div>
        </div><?php endif; ?>
    <form method="post" class="row g-3 p-4"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input
            type="hidden" name="action" value="content">
        <div class="col-md-4"><label class="form-label">Hero Badge</label><input class="form-control" name="hero_badge"
                value="<?= e($content['hero_badge']['content_value'] ?? '') ?>"></div>
        <div class="col-md-8"><label class="form-label">Hero Title</label><input class="form-control" name="hero_title"
                value="<?= e($content['hero_title']['content_value'] ?? '') ?>"></div>
        <div class="col-12"><label class="form-label">Hero Description</label><textarea class="form-control"
                name="hero_description" rows="3"><?= e($content['hero_description']['content_value'] ?? '') ?></textarea>
        </div>
        <div class="col-md-5"><label class="form-label">About Title</label><input class="form-control"
                name="about_title" value="<?= e($content['about_title']['content_value'] ?? '') ?>"></div>
        <div class="col-md-7"><label class="form-label">About Description</label><textarea class="form-control"
                name="about_description" rows="3"><?= e($content['about_description']['content_value'] ?? '') ?></textarea>
        </div>
        <div class="col-12"><button class="btn-admin btn-primary">Save Website Content</button></div>
    </form>
</div> -->
<div class="admin-card">
    <div class="admin-card-head">
        <div>
            <h2>Testimonials</h2>
            <p>Publish only genuine customer feedback.</p>
        </div>
    </div>
    <form method="post" class="row g-3 p-4 border-bottom"><input type="hidden" name="csrf_token"
            value="<?= e($csrf) ?>"><input type="hidden" name="action" value="testimonial">
        <div class="col-md-4"><label class="form-label">Customer Name *</label><input class="form-control"
                name="customer_name" required></div>
        <div class="col-md-2"><label class="form-label">Rating</label><select class="form-select"
                name="rating"><?php for ($i = 5; $i >= 1; $i--): ?>
                    <option><?= $i ?></option><?php endfor; ?>
            </select></div>
        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status">
                <option>ACTIVE</option>
                <option>INACTIVE</option>
            </select></div>
        <div class="col-12"><label class="form-label">Message *</label><textarea class="form-control" name="message"
                required rows="3"></textarea></div>
        <div class="col-12"><button class="btn-admin btn-primary">Add Testimonial</button></div>
    </form>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody><?php foreach ($testimonials as $t): ?>
                    <tr>
                        <td><strong><?= e($t['customer_name']) ?></strong></td>
                        <td><?= str_repeat('★', (int) $t['rating']) ?></td>
                        <td><?= e($t['message']) ?></td>
                        <td><?= e($t['status']) ?></td>
                        <td class="text-end">
                            <form method="post" onsubmit="return confirm('Delete this testimonial?')"><input type="hidden"
                                    name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="action"
                                    value="delete_testimonial"><input type="hidden" name="id"
                                    value="<?= (int) $t['id'] ?>"><button
                                    class="btn-admin btn-danger-soft btn-sm">Delete</button></form>
                        </td>
                    </tr><?php endforeach; ?><?php if (!$testimonials): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No testimonials yet.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php adminFooter();
