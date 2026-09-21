<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-layout.php';
require_once __DIR__ . '/../models/Booking.php';
$booking = new Booking(); $stats=$booking->getDashboardStats(); $recent=$booking->getRecent(8);
adminHeader('Dashboard','Overview of your Arna Tours & Travels operations.');
?>
<div class="stat-grid">
<?php $cards=[['total','Total Bookings','ri-calendar-check-line'],['new','New Requests','ri-notification-3-line'],['confirmed','Confirmed','ri-checkbox-circle-line'],['completed','Completed','ri-flag-line'],['customers','Customers','ri-user-3-line']]; foreach($cards as [$key,$label,$icon]): ?><div class="stat-card"><div class="icon"><i class="<?=$icon?>"></i></div><div class="label"><?=e($label)?></div><div class="value"><?=number_format((int)($stats[$key]??0))?></div></div><?php endforeach; ?>
</div>
<div class="section-card"><div class="section-head"><div><h2>Recent Bookings</h2><p>Latest booking requests submitted from the website.</p></div><a class="btn-admin btn-primary" href="bookings.php">View all <i class="ri-arrow-right-line"></i></a></div><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Booking</th><th>Customer</th><th>Route</th><th>Date</th><th>Visitors</th><th>Status</th><th></th></tr></thead><tbody><?php if(!$recent): ?><tr><td colspan="7"><div class="empty-state">No bookings yet.</div></td></tr><?php else: foreach($recent as $row): $status=strtolower($row['status']); $cls=['new'=>'badge-new','confirmed'=>'badge-confirmed','in_progress'=>'badge-progress','completed'=>'badge-completed','cancelled'=>'badge-cancelled'][$status]??'badge-new'; ?><tr><td><strong><?=e($row['booking_number'])?></strong><div class="text-muted small"><?=e(date('d M Y, h:i A',strtotime($row['created_at'])))?></div></td><td><?=e($row['full_name'])?><div class="text-muted small"><?=e($row['mobile_number'])?></div></td><td class="route-cell"><strong><?=e($row['source_location'])?></strong><span>to <?=e($row['destination_location'])?></span></td><td><?=e(date('d M Y',strtotime($row['preferred_date'])))?></td><td><?=e((string)$row['participants'])?></td><td><span class="badge <?=$cls?>"><?=e(str_replace('_',' ',$row['status']))?></span></td><td><a class="btn-admin btn-light" href="booking-view.php?id=<?=e((string)$row['id'])?>">View</a></td></tr><?php endforeach; endif;?></tbody></table></div></div>
<?php adminFooter(); ?>
