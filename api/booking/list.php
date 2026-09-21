<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/auth.php';
requireAdmin();
require_once __DIR__ . '/../../models/Booking.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') jsonResponse(false, 'Invalid request method.', [], 405);
$status = strtoupper(trim((string)($_GET['status'] ?? '')));
$search = trim((string)($_GET['search'] ?? ''));
if (mb_strlen($search) > 100) $search = mb_substr($search, 0, 100);
$limit = filter_var($_GET['limit'] ?? 100, FILTER_VALIDATE_INT);
$offset = filter_var($_GET['offset'] ?? 0, FILTER_VALIDATE_INT);
$limit = ($limit === false) ? 100 : max(1, min((int)$limit, 100));
$offset = ($offset === false) ? 0 : max(0, (int)$offset);
try { $rows=(new Booking())->getAll($status,$search,$limit,$offset); jsonResponse(true,'Bookings retrieved successfully.',['bookings'=>$rows,'count'=>count($rows),'limit'=>$limit,'offset'=>$offset]); }
catch(Throwable $e){ error_log('Arna booking list error: '.$e->getMessage()); jsonResponse(false,'Unable to retrieve bookings.',[],500); }
