# Arna Tours & Travels — Content Management V2

## Database migration

The existing `arna_tours_travels` database already contains bookings/customers/admin data. Do **not** re-import `schema.sql` into the live development database.

Instead, import this file once in phpMyAdmin:

`database/002_content_management.sql`

It creates the `services` table and inserts the requested travel services, then seeds the initial fleet records if they do not already exist.

## New admin pages

- `/admin/vehicles.php` — add/edit/delete vehicles, upload image, set status and seating capacity.
- `/admin/services.php` — add/edit/delete website services and control display order/status.

## Public page

- `/vehicles.php` — public fleet page loaded from MySQL.
- Homepage `#services` — public travel-services section loaded from MySQL.

## Client-requested service content

- Taxi & Cab Booking
- Bus Booking
- Train Booking
- Flight Booking
- Hotel & Resort Booking
- Serving All India

These are editable from Admin > Services after the migration is imported.

## Backend Completion Migration
After the existing schema/content migrations, run:

```sql
SOURCE database/003_backend_completion.sql;
```

This adds:
- Drivers
- Vehicle/driver assignment fields on bookings
- Website CMS content
- Website settings
- Testimonials

Admin modules added:
- Tour Packages
- Destinations
- Drivers
- Website CMS
- Settings

Booking Details now supports vehicle + driver assignment.


## Homepage Tour Packages
After the existing migrations, run:

```sql
SOURCE database/009_home_tour_packages.sql;
```

This adds homepage visibility/order controls to the existing `tour_packages` records. Admin users can manage package details under **Tour Packages** and choose/order the cards shown in the homepage **Tour Packages** section under **Homepage Tours**.

## Public contact and social links
The public business number is configured in `config/config.php` as `+91 94800 01511`. The same configuration powers the Call and WhatsApp floating buttons. Instagram, Facebook and YouTube footer links are also configured there. Replace the default platform URLs with Arna's official profiles when available.
