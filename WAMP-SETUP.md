# WAMP Setup

1. Extract this folder as `C:\wamp64\www\ARNA-TOURS-TRAVELS`.
2. Create/import the `arna_tours_travels` database using `database/schema.sql`.
3. If upgrading an existing database, run `database/003_backend_completion.sql` and then `database/007_customer_mobile_unique.sql`.
4. Ensure PHP extensions `pdo_mysql`, `fileinfo`, and `gd` are enabled.
5. Open `http://localhost/ARNA-TOURS-TRAVELS/`.
6. Admin: `http://localhost/ARNA-TOURS-TRAVELS/admin/`.
7. Use `admin/setup.php` only when `admin_users` is empty.

The application reads `ARNA_APP_URL`, `ARNA_DB_*` environment variables when supplied; local WAMP defaults are provided for convenience. Do not use the root account with a blank password in production.
