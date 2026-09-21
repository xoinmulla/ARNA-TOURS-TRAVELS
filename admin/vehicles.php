<?php

declare(strict_types=1);

require_once __DIR__ . '/admin-layout.php';
require_once __DIR__ . '/../models/Vehicle.php';

$vehicleModel = new Vehicle();
$flash = getFlashMessage();
$message = $flash['message'] ?? '';
$messageType = $flash['type'] ?? 'success';
$editingVehicle = null;

function vehicleUpload(?array $file, ?string $oldImage = null): string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $oldImage ?? '';
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Vehicle image upload failed.');
    }
    if (($file['size'] ?? 0) > 500 * 1024) {
        throw new RuntimeException('Vehicle image should be 500 KB or smaller.');
    }
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Only JPG, PNG and WEBP vehicle images are allowed.');
    }
    $dimensions = @getimagesize($file['tmp_name']);
    if (!$dimensions || $dimensions[0] < 1 || $dimensions[1] < 1 || $dimensions[0] > 4000 || $dimensions[1] > 4000) {
        throw new RuntimeException('Vehicle image dimensions must be between 1 and 4000 pixels per side.');
    }
    if (!function_exists('imagecreatefromjpeg') || !function_exists('imagewebp')) {
        throw new RuntimeException('Server image processing (GD) is required for secure vehicle uploads.');
    }
    $source = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($file['tmp_name']),
        'image/png' => @imagecreatefrompng($file['tmp_name']),
        'image/webp' => @imagecreatefromwebp($file['tmp_name']),
        default => false,
    };
    if (!$source) throw new RuntimeException('The uploaded image could not be decoded.');
    $filename = 'vehicle-' . bin2hex(random_bytes(10)) . '.webp';
    $directory = __DIR__ . '/../assets/uploads/vehicles';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        imagedestroy($source);
        throw new RuntimeException('Unable to create vehicle upload directory.');
    }
    $target = $directory . '/' . $filename;
    $saved = imagewebp($source, $target, 82);
    imagedestroy($source);
    if (!$saved) throw new RuntimeException('Unable to save vehicle image.');
    return 'assets/uploads/vehicles/' . $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!verifyAdminCsrf()) {
            throw new RuntimeException('Your session token has expired. Refresh the page and try again.');
        }

        $action = $_POST['action'] ?? '';
        $id = (int) ($_POST['id'] ?? 0);

        if ($action === 'save') {
            $name = trim((string) ($_POST['vehicle_name'] ?? ''));
            $type = trim((string) ($_POST['vehicle_type'] ?? ''));
            $registration = strtoupper(trim((string) ($_POST['registration_number'] ?? '')));
            $seats = (int) ($_POST['seating_capacity'] ?? 0);
            $ratePerKm = (float) ($_POST['rate_per_km'] ?? 0);
            $includedKm = (int) ($_POST['included_km'] ?? 300);
            $pricePerDay = round($ratePerKm * $includedKm, 2);
            $description = trim((string) ($_POST['description'] ?? ''));
            $status = (string) ($_POST['status'] ?? 'AVAILABLE');

            if ($name === '') throw new RuntimeException('Vehicle name is required.');
            if (mb_strlen($name) > 100 || mb_strlen($type) > 100 || mb_strlen($registration) > 50 || mb_strlen($description) > 2000) throw new RuntimeException('One or more vehicle fields exceed the allowed length.');
            if ($seats < 1 || $seats > 100) throw new RuntimeException('Seating capacity must be between 1 and 100.');
            if ($ratePerKm < 0 || $ratePerKm > 100000) throw new RuntimeException('Rate per km must be between 0 and 100000.');
            if ($includedKm < 1 || $includedKm > 10000) throw new RuntimeException('Included km must be between 1 and 10000.');
            if ($pricePerDay < 0 || $pricePerDay > 10000000) throw new RuntimeException('1-day amount is invalid.');
            if ($pricePerDay <= 0 && $ratePerKm > 0) $pricePerDay = $ratePerKm * $includedKm;
            if (!in_array($status, ['AVAILABLE','BOOKED','MAINTENANCE','INACTIVE'], true)) throw new RuntimeException('Invalid vehicle status.');

            $old = $id > 0 ? $vehicleModel->find($id) : null;
            if ($id > 0 && !$old) throw new RuntimeException('Vehicle not found.');

            $image = vehicleUpload($_FILES['image'] ?? null, $old['image'] ?? '');

            $data = [
                'vehicle_name' => $name,
                'vehicle_type' => $type,
                'registration_number' => $registration,
                'seating_capacity' => $seats,
                'rate_per_km' => $ratePerKm,
                'included_km' => $includedKm,
                'price_per_day' => $pricePerDay,
                'image' => $image,
                'description' => $description,
                'status' => $status,
            ];

            if ($id > 0) {
                $vehicleModel->update($id, $data);
                $message = 'Vehicle updated successfully.';
            } else {
                $vehicleModel->create($data);
                $message = 'Vehicle added successfully.';
            }

            setFlashMessage($message);
            header('Location: vehicles.php');
            exit;
        } elseif ($action === 'delete') {
            if ($id < 1) throw new RuntimeException('Invalid vehicle.');
            $vehicle = $vehicleModel->find($id);
            if (!$vehicle) throw new RuntimeException('Vehicle not found.');
            $vehicleModel->delete($id);
            $message = 'Vehicle deleted successfully.';
            setFlashMessage($message);
            header('Location: vehicles.php');
            exit;
        }
    } catch (Throwable $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}

if (isset($_GET['edit'])) {
    $editingVehicle = $vehicleModel->find((int) $_GET['edit']);
}

$search = trim((string) ($_GET['search'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
$vehicles = $vehicleModel->all($search, $statusFilter);
$csrf = adminCsrfToken();

adminHeader('Vehicles', 'Manage the fleet displayed across the Arna website.');
?>

<?php if ($message): ?>
<div class="alert alert-<?= e($messageType) ?> border-0 shadow-sm"><?= e($message) ?></div>
<?php endif; ?>

<div class="admin-card mb-4">
    <div class="admin-card-head">
        <div><h2><?= $editingVehicle ? 'Edit Vehicle' : 'Add Vehicle' ?></h2><p><?= $editingVehicle ? 'Update fleet information.' : 'Add a vehicle to your Arna fleet.' ?></p></div>
        <?php if ($editingVehicle): ?><a href="vehicles.php" class="btn-admin btn-light">Cancel Edit</a><?php endif; ?>
    </div>
    <form method="post" enctype="multipart/form-data" class="row g-3 p-4">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) ($editingVehicle['id'] ?? 0) ?>">

        <div class="col-md-6"><label class="form-label">Vehicle Name *</label><input class="form-control" name="vehicle_name" required maxlength="100" value="<?= e($editingVehicle['vehicle_name'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Vehicle Type</label><input class="form-control" name="vehicle_type" maxlength="100" placeholder="SUV / Sedan / Tempo Traveller" value="<?= e($editingVehicle['vehicle_type'] ?? '') ?>"></div>
        <div class="col-md-4"><label class="form-label">Registration Number</label><input class="form-control" name="registration_number" maxlength="50" value="<?= e($editingVehicle['registration_number'] ?? '') ?>"></div>
        <div class="col-md-4"><label class="form-label">Seating Capacity *</label><input class="form-control" type="number" name="seating_capacity" min="1" max="100" required value="<?= (int) ($editingVehicle['seating_capacity'] ?? 4) ?>"></div>
        <div class="col-md-4"><label class="form-label">Rate / KM (₹)</label><input class="form-control" type="number" name="rate_per_km" min="0" step="0.01" value="<?= e((string) ($editingVehicle['rate_per_km'] ?? 0)) ?>" placeholder="e.g. 14"></div>
        <div class="col-md-4"><label class="form-label">Included KM *</label><input class="form-control" type="number" name="included_km" min="1" max="10000" required value="<?= (int) ($editingVehicle['included_km'] ?? 300) ?>"></div>
        <div class="col-md-4"><label class="form-label">1-Day Amount (₹) *</label><input class="form-control" type="number" name="price_per_day" min="0" step="0.01" required value="<?= e((string) ($editingVehicle['price_per_day'] ?? 0)) ?>"><div class="form-text">Leave 0 to calculate Rate × Included KM.</div></div>
        <div class="col-md-4"><label class="form-label">Status *</label><select class="form-select" name="status"><option value="AVAILABLE" <?= (($editingVehicle['status'] ?? 'AVAILABLE') === 'AVAILABLE') ? 'selected' : '' ?>>Available</option><option value="BOOKED" <?= (($editingVehicle['status'] ?? '') === 'BOOKED') ? 'selected' : '' ?>>Booked</option><option value="MAINTENANCE" <?= (($editingVehicle['status'] ?? '') === 'MAINTENANCE') ? 'selected' : '' ?>>Maintenance</option><option value="INACTIVE" <?= (($editingVehicle['status'] ?? '') === 'INACTIVE') ? 'selected' : '' ?>>Inactive</option></select></div>
        <div class="col-md-6"><label class="form-label">Vehicle Image</label><input class="form-control" id="vehicleImageInput" type="file" name="image" accept="image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG or WEBP · max 500 KB</div><div id="vehicleImageSizeMsg" class="small text-danger mt-1 d-none" role="alert">Vehicle image should be 500 KB or smaller.</div></div>
        <div class="col-md-6"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="3" maxlength="2000"><?= e($editingVehicle['description'] ?? '') ?></textarea></div>
        <div class="col-12"><button class="btn-admin btn-primary" type="submit"><i class="ri-save-line"></i> <?= $editingVehicle ? 'Update Vehicle' : 'Save Vehicle' ?></button></div>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-head">
        <div><h2>Fleet Vehicles</h2><p><?= count($vehicles) ?> vehicle(s)</p></div>
        <form class="d-flex gap-2" method="get">
            <input class="form-control" name="search" value="<?= e($search) ?>" placeholder="Search vehicle...">
            <select class="form-select" name="status"><option value="">All statuses</option><option value="AVAILABLE" <?= $statusFilter === 'AVAILABLE' ? 'selected' : '' ?>>Available</option><option value="BOOKED" <?= $statusFilter === 'BOOKED' ? 'selected' : '' ?>>Booked</option><option value="MAINTENANCE" <?= $statusFilter === 'MAINTENANCE' ? 'selected' : '' ?>>Maintenance</option><option value="INACTIVE" <?= $statusFilter === 'INACTIVE' ? 'selected' : '' ?>>Inactive</option></select>
            <button class="btn-admin btn-primary" type="submit">Search</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead><tr><th>Vehicle</th><th>Type</th><th>Seats</th><th>Rate/KM</th><th>1-Day Amount</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($vehicles as $vehicle): ?>
                <tr>
                    <td><div class="d-flex align-items-center gap-3"><div class="vehicle-thumb"><?php if (!empty($vehicle['image'])): ?><img src="../<?= e($vehicle['image']) ?>" alt="<?= e($vehicle['vehicle_name']) ?>"><?php else: ?><i class="ri-car-line"></i><?php endif; ?></div><strong><?= e($vehicle['vehicle_name']) ?></strong></div></td>
                    <td><?= e($vehicle['vehicle_type']) ?: '—' ?></td><td><?= (int) $vehicle['seating_capacity'] ?></td><td><?= (float) $vehicle['rate_per_km'] > 0 ? '₹' . number_format((float) $vehicle['rate_per_km'], 2) : '—' ?></td><td><?= (float) $vehicle['price_per_day'] > 0 ? '₹' . number_format((float) $vehicle['price_per_day'], 0) : '—' ?></td>
                    <td><span class="status-pill status-<?= strtolower(e($vehicle['status'])) ?>"><?= e($vehicle['status']) ?></span></td>
                    <td class="text-end"><a href="vehicles.php?edit=<?= (int) $vehicle['id'] ?>" class="btn-admin btn-light btn-sm"><i class="ri-edit-line"></i> Edit</a> <form method="post" class="d-inline" onsubmit="return confirm('Delete this vehicle?');"><input type="hidden" name="csrf_token" value="<?= e($csrf) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $vehicle['id'] ?>"><button class="btn-admin btn-danger-soft btn-sm" type="submit"><i class="ri-delete-bin-line"></i></button></form></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$vehicles): ?><tr><td colspan="7" class="text-center py-5 text-muted">No vehicles found. Add your first fleet vehicle above.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    const input = document.getElementById('vehicleImageInput');
    const message = document.getElementById('vehicleImageSizeMsg');
    if (!input || !message) return;

    const maxBytes = 500 * 1024;
    const form = input.closest('form');

    function validateVehicleImage() {
        message.classList.add('d-none');
        if (!input.files || !input.files.length) return true;
        if (input.files[0].size > maxBytes) {
            message.textContent = 'Vehicle image should be 500 KB or smaller.';
            message.classList.remove('d-none');
            return false;
        }
        return true;
    }

    input.addEventListener('change', validateVehicleImage);
    if (form) {
        form.addEventListener('submit', function (event) {
            if (!validateVehicleImage()) {
                event.preventDefault();
                input.focus();
            }
        });
    }
})();
</script>

<?php adminFooter(); ?>
