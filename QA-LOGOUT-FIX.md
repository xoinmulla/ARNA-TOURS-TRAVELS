# Logout / APP_URL Fix

The admin logout error `Undefined constant "APP_URL"` was caused by `admin/logout.php` using `APP_URL` without loading `config/config.php`.

This final build fixes that by loading the config before using `APP_URL` and by using the configured application URL for admin redirects.

## WAMP location

Place this folder at:

`C:\\wamp64\\www\\ARNA-TOURS-TRAVELS`

Open:

`http://localhost/ARNA-TOURS-TRAVELS/`

Admin:

`http://localhost/ARNA-TOURS-TRAVELS/admin/`
