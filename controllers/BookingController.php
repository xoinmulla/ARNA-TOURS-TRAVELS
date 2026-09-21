<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/../includes/functions.php';

class BookingController
{
    private PDO $db;
    private Booking $booking;
    private Customer $customer;

    public function __construct()
    {
        $this->db = Database::getConnection();

        $this->booking = new Booking();
        $this->customer = new Customer();
    }

    public function create(array $data): array
    {
        $this->db->beginTransaction();

        try {

            /*
             * Find existing customer
             */
            $customer = $this->customer->findByMobile(
                $data['mobile_number']
            );

            if ($customer) {

                $customerId = (int) $customer['id'];

                $this->customer->updateName(
                    $customerId,
                    $data['full_name']
                );

            } else {

                $customerId = $this->customer->create(
                    $data['full_name'],
                    $data['mobile_number']
                );
            }


            /* Prevent accidental rapid duplicate submissions with identical booking details. */
            $duplicateStmt = $this->db->prepare(
                "SELECT b.id, b.booking_number FROM bookings b
                 INNER JOIN customers c ON c.id = b.customer_id
                 WHERE c.mobile_number = ? AND b.source_location = ? AND b.destination_location = ?
                   AND b.trip_type = ? AND b.preferred_date = ? AND b.participants = ?
                   AND b.created_at >= (NOW() - INTERVAL 5 MINUTE)
                 ORDER BY b.id DESC LIMIT 1"
            );
            $duplicateStmt->execute([
                $data['mobile_number'], $data['source_location'], $data['destination_location'],
                $data['trip_type'], $data['preferred_date'], $data['participants']
            ]);
            $duplicate = $duplicateStmt->fetch();
            if ($duplicate) {
                $this->db->commit();
                return [
                    'success' => true,
                    'duplicate' => true,
                    'booking_id' => (int)$duplicate['id'],
                    'booking_number' => (string)$duplicate['booking_number']
                ];
            }

            /* Generate booking number */
            $bookingNumber = generateBookingNumber();


            /*
             * Create booking
             */
            $bookingId = $this->booking->create([
                'booking_number'       => $bookingNumber,
                'customer_id'          => $customerId,
                'source_location'      => $data['source_location'],
                'destination_location' => $data['destination_location'],
                'trip_type'            => $data['trip_type'],
                'preferred_date'       => $data['preferred_date'],
                'participants'         => $data['participants'],
            ]);


            $this->db->commit();


            return [
                'success' => true,
                'booking_id' => $bookingId,
                'booking_number' => $bookingNumber
            ];


        } catch (Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}