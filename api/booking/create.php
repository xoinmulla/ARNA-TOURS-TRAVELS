<?php

declare(strict_types=1);

session_start();

require_once '../../config/database.php';
require_once '../../controllers/BookingController.php';
require_once '../../includes/functions.php';


/* Request method is checked before CSRF so malformed requests get a deterministic 405. */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method.', [], 405);
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {

    jsonResponse(
        false,
        'Your session has expired. Please refresh the page and try again.',
        [],
        403
    );
}


// Honeypot: silently reject likely automated submissions.
if (trim($_POST['website'] ?? '') !== '') {
    jsonResponse(false, 'Invalid submission.', [], 422);
}



/*
|--------------------------------------------------------------------------
| Collect Input
|--------------------------------------------------------------------------
*/

$data = [

    'full_name' => requestValue(
        'full_name'
    ),

    'mobile_number' => requestValue(
        'mobile_number'
    ),

    'source_location' => requestValue(
        'source_location'
    ),

    'destination_location' => requestValue(
        'destination_location'
    ),

    'trip_type' => requestValue(
        'trip_type'
    ),

    'preferred_date' => requestValue(
        'preferred_date'
    ),

    'participants' => requestValue(
        'participants'
    )
];


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if (mb_strlen($data['full_name']) > 150 || mb_strlen($data['mobile_number']) > 20 || mb_strlen($data['source_location']) > 255 || mb_strlen($data['destination_location']) > 255 || mb_strlen($data['trip_type']) > 20 || mb_strlen($data['preferred_date']) > 10) {
    jsonResponse(false, 'One or more booking fields exceed the allowed length.', [], 422);
}

if ($data['full_name'] === '') {

    jsonResponse(
        false,
        'Please enter your full name.',
        [],
        422
    );
}


if (
    !preg_match(
        '/^[6-9][0-9]{9}$/',
        $data['mobile_number']
    )
) {

    jsonResponse(
        false,
        'Please enter a valid 10-digit mobile number.',
        [],
        422
    );
}


if ($data['source_location'] === '') {

    jsonResponse(
        false,
        'Please enter your pickup location.',
        [],
        422
    );
}


if ($data['destination_location'] === '') {

    jsonResponse(
        false,
        'Please enter your destination.',
        [],
        422
    );
}


if (mb_strtolower($data['source_location']) === mb_strtolower($data['destination_location'])) {

    jsonResponse(
        false,
        'Pickup and destination should be different locations.',
        [],
        422
    );
}


if (
    !in_array(
        $data['trip_type'],
        ['ROUND_TRIP', 'DROP'],
        true
    )
) {

    jsonResponse(
        false,
        'Please select a valid trip type.',
        [],
        422
    );
}


if ($data['preferred_date'] === '') {

    jsonResponse(
        false,
        'Please select your travel date.',
        [],
        422
    );
}


$date = DateTime::createFromFormat(
    'Y-m-d',
    $data['preferred_date']
);

if (
    !$date ||
    $date->format('Y-m-d') !== $data['preferred_date']
) {

    jsonResponse(
        false,
        'Please select a valid travel date.',
        [],
        422
    );
}


/*
|--------------------------------------------------------------------------
| Prevent past date
|--------------------------------------------------------------------------
*/

$today = new DateTime(
    'today',
    new DateTimeZone('Asia/Kolkata')
);

if ($date < $today) {

    jsonResponse(
        false,
        'Travel date cannot be in the past.',
        [],
        422
    );
}


/*
|--------------------------------------------------------------------------
| Participants
|--------------------------------------------------------------------------
*/

if (
    !ctype_digit($data['participants']) ||
    (int) $data['participants'] < 1 ||
    (int) $data['participants'] > 100
) {

    jsonResponse(
        false,
        'Please enter a valid number of visitors (1–100).',
        [],
        422
    );
}


$data['participants'] =
    (int) $data['participants'];


/*
|--------------------------------------------------------------------------
| Create Booking
|--------------------------------------------------------------------------
*/

try {

    $controller =
        new BookingController();

    $result =
        $controller->create($data);


    jsonResponse(
        true,
        'Your booking request has been submitted successfully.',
        $result
    );


} catch (Throwable $e) {

    error_log(
        'Arna Booking Error: ' .
        $e->getMessage()
    );


    jsonResponse(
        false,
        'Something went wrong while submitting your booking. Please try again.',
        [],
        500
    );
}