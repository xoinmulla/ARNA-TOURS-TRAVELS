<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../../models/Booking.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonResponse(false, 'Invalid request method.', [], 405);
$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
if(!$id) jsonResponse(false,'Invalid booking ID.',[],422);
try { $row=(new Booking())->findById((int)$id); if(!$row) jsonResponse(false,'Booking not found.',[],404); jsonResponse(true,'Booking retrieved successfully.',['booking'=>$row]); }
catch(Throwable $e){ error_log('Arna booking view error: '.$e->getMessage()); jsonResponse(false,'Unable to retrieve booking.',[],500); }
