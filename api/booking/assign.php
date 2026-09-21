<?php
declare(strict_types=1); require_once __DIR__.'/../../includes/auth.php'; requireAdmin(); require_once __DIR__.'/../../models/Booking.php';
if($_SERVER['REQUEST_METHOD']!=='POST')jsonResponse(false,'Invalid request method.',[],405);
if(!verifyAdminCsrf())jsonResponse(false,'Invalid security token.',[],403);
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);$vehicle=filter_var($_POST['vehicle_id']??0,FILTER_VALIDATE_INT);$driver=filter_var($_POST['driver_id']??0,FILTER_VALIDATE_INT);
if(!$id)jsonResponse(false,'Invalid booking ID.',[],422);
try{$ok=(new Booking())->assign((int)$id,$vehicle?:null,$driver?:null);if(!$ok)jsonResponse(false,'Booking, vehicle or driver was not found.',[],404);jsonResponse(true,'Booking assignment saved successfully.',['vehicle_id'=>$vehicle?:null,'driver_id'=>$driver?:null]);}catch(Throwable $e){error_log('Arna assignment error: '.$e->getMessage());jsonResponse(false,'Unable to save assignment.',[],500);}
