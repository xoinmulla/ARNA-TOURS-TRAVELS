<?php
declare(strict_types=1);
require_once '../../includes/auth.php';
requireAdmin();
require_once '../../models/Booking.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false,'Invalid request method.',[],405);
if (!verifyAdminCsrf()) jsonResponse(false,'Invalid security token.',[],403);
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);$status=strtoupper(trim($_POST['status']??''));
if(!$id || !in_array($status,['NEW','CONFIRMED','IN_PROGRESS','COMPLETED','CANCELLED'],true)) jsonResponse(false,'Invalid booking or status.',[],422);
try{$ok=(new Booking())->updateStatus((int)$id,$status);if(!$ok)jsonResponse(false,'Booking not found or status transition is not allowed.',[],404);jsonResponse(true,'Booking status updated successfully.',['status'=>$status]);}catch(Throwable $e){error_log('Arna status update error: '.$e->getMessage());jsonResponse(false,'Unable to update booking status.',[],500);}
