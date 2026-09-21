<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-layout.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../models/Driver.php';
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT); $booking=new Booking(); $row=$id?$booking->findById((int)$id):null;
if(!$row){adminHeader('Booking Not Found');?><div class="section-card"><div class="empty-state">The requested booking could not be found.<br><a class="btn-admin btn-primary mt-3" href="bookings.php">Back to bookings</a></div></div><?php adminFooter();exit;}
$flash=getFlashMessage();$message='';$error='';if($flash){if(($flash['type']??'success')==='danger'){$error=(string)($flash['message']??'');}else{$message=(string)($flash['message']??'');}} $vehicleModel=new Vehicle(); $driverModel=new Driver();
if($_SERVER['REQUEST_METHOD']==='POST') {
    if(!verifyAdminCsrf()) {
        $error='Session expired. Please refresh and try again.';
    } else {
        $action=$_POST['action']??'status';
        if($action==='assign') {
            $vehicleId=(int)($_POST['vehicle_id']??0);
            $driverId=(int)($_POST['driver_id']??0);
            if($booking->assign((int)$row['id'],$vehicleId?:null,$driverId?:null)) {
                setFlashMessage('Vehicle and driver assignment saved.');
                header('Location: booking-view.php?id='.(int)$row['id']);
                exit;
            } else { $error='Unable to save assignment.'; }
        } else {
            $newStatus=strtoupper(trim($_POST['status']??''));
            if($booking->updateStatus((int)$row['id'],$newStatus)) {
                setFlashMessage('Booking status updated successfully.');
                header('Location: booking-view.php?id='.(int)$row['id']);
                exit;
            } else { $error='Invalid booking status.'; }
        }
    }
}
$vehicles=$vehicleModel->all(); $drivers=$driverModel->all(); adminHeader('Booking Details',$row['booking_number']);
if($message):?><div class="alert-admin alert-success"><?=e($message)?></div><?php endif;if($error):?><div class="alert-admin alert-danger"><?=e($error)?></div><?php endif;?>
<div class="section-card"><div class="section-head"><div><h2><?=e($row['booking_number'])?></h2><p>Submitted <?=e(date('d M Y, h:i A',strtotime($row['created_at'])))?></p></div><a class="btn-admin btn-light" href="bookings.php"><i class="ri-arrow-left-line"></i> Back</a></div><div class="detail-grid"><div class="detail-item"><label>Customer</label><strong><?=e($row['full_name'])?></strong></div><div class="detail-item"><label>Mobile Number</label><strong><a href="tel:<?=e($row['mobile_number'])?>"><?=e($row['mobile_number'])?></a></strong></div><div class="detail-item"><label>Source</label><strong><?=e($row['source_location'])?></strong></div><div class="detail-item"><label>Destination</label><strong><?=e($row['destination_location'])?></strong></div><div class="detail-item"><label>Trip Type</label><strong><?=e(str_replace('_',' ',$row['trip_type']))?></strong></div><div class="detail-item"><label>Preferred Date</label><strong><?=e(date('d F Y',strtotime($row['preferred_date'])))?></strong></div><div class="detail-item"><label>Participants</label><strong><?=e((string)$row['participants'])?></strong></div><div class="detail-item"><label>Current Status</label><strong><?=e(str_replace('_',' ',$row['status']))?></strong></div></div><div class="section-head"><div><h2>Update Status</h2><p>Move this booking through its operational lifecycle.</p></div><form class="toolbar" method="post"><input type="hidden" name="csrf_token" value="<?=e(adminCsrfToken())?>"><select class="form-select" name="status"><?php foreach(['NEW','CONFIRMED','IN_PROGRESS','COMPLETED','CANCELLED'] as $s):?><option value="<?=$s?>" <?=$row['status']===$s?'selected':''?>><?=e(str_replace('_',' ',$s))?></option><?php endforeach;?></select><button class="btn-admin btn-primary" type="submit">Update Status</button></form></div><div class="section-head mt-4"><div><h2>Resource Assignment</h2><p>Assign a vehicle and driver to this booking.</p></div></div><form method="post" class="row g-3 align-items-end"><input type="hidden" name="csrf_token" value="<?=e(adminCsrfToken())?>"><input type="hidden" name="action" value="assign"><div class="col-md-5"><label class="form-label">Vehicle</label><select class="form-select" name="vehicle_id"><option value="0">Not assigned</option><?php foreach($vehicles as $v):?><option value="<?=(int)$v['id']?>" <?=((int)($row['vehicle_id']??0)===(int)$v['id'])?'selected':''?>><?=e($v['vehicle_name'])?><?= $v['registration_number']?' — '.e($v['registration_number']):''?> (<?=e($v['status'])?>)</option><?php endforeach;?></select></div><div class="col-md-5"><label class="form-label">Driver</label><select class="form-select" name="driver_id"><option value="0">Not assigned</option><?php foreach($drivers as $d):?><option value="<?=(int)$d['id']?>" <?=((int)($row['driver_id']??0)===(int)$d['id'])?'selected':''?>><?=e($d['full_name'])?> — <?=e($d['mobile_number'])?> (<?=e($d['status'])?>)</option><?php endforeach;?></select></div><div class="col-md-2"><button class="btn-admin btn-primary w-100">Save Assignment</button></div></form></div><?php adminFooter(); ?>
