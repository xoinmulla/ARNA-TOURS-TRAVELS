# WAMP Browser Retest

Website: http://localhost/ARNA-TOURS-TRAVELS/
Admin: http://localhost/ARNA-TOURS-TRAVELS/admin/

## Authentication
1. Valid login → dashboard.
2. Wrong password → generic error.
3. Logout.
4. Direct `/admin/dashboard.php` → redirect to `/admin/`, not 404.

## Booking
Use Asha QA / 9876543210 / Hubballi → Goa / ROUND_TRIP / future date / 2.
Verify success, booking number, MySQL row, customer row and admin visibility.
Repeat identical details within 5 minutes and verify no duplicate booking.
Test missing name, invalid mobile, past/malformed date, same route, participants 0/101/nonnumeric and invalid CSRF.

## Admin
Test status lifecycle NEW → CONFIRMED → IN_PROGRESS → COMPLETED.
Test cancellation from NEW/CONFIRMED/IN_PROGRESS.
Test illegal COMPLETED → NEW and expect rejection.
Test valid assignment plus inactive/maintenance/off-duty/nonexistent resources.

## Uploads
Test JPG/PNG/WEBP under 4 MB, invalid file, oversized file. Verify stored output is WebP.

## Responsive
Chrome DevTools: 320, 375, 414, 768, 1280. Check menu, targets, overflow, images and booking form.

## Lighthouse
Run desktop and mobile Lighthouse against the current build and record Performance, Accessibility, Best Practices, SEO, LCP, FCP, TBT/INP, CLS and resource audits.
