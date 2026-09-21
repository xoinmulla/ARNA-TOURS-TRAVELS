# Arna Tours & Travels — QA Remediation Update

## Source-level remediation completed

This build addresses the supplied manual test report items that can be fixed and verified from the project source.

### Backend
- Booking list API implemented: `api/booking/list.php`
- Booking view API implemented: `api/booking/view.php`
- Booking status updates reject nonexistent booking IDs
- Vehicle/driver assignment validates booking, vehicle and driver IDs
- Rapid identical booking submissions are treated as duplicate requests for five minutes
- Customer mobile uniqueness added to the clean database schema
- Migration added: `database/007_customer_mobile_unique.sql`
- Admin logout/auth redirects consistently use `APP_URL`

### Frontend
- Primary vehicle images converted to responsive WebP variants
- `srcset` / `sizes` added for key images
- `width` / `height` added to key content images
- Below-fold images use lazy loading
- Hero image is preloaded as the likely LCP image
- Heavy GSAP/Lenis homepage startup removed
- Native IntersectionObserver reveals used instead
- Lightweight native vehicle carousel implemented
- Carousel controls and mobile menu controls use 44px minimum hit areas
- Booking form is the primary desktop booking fallback
- Contact page added for non-phone users
- Placeholder links replaced with real destinations
- Public testimonial content is now CMS/database-driven and fictional identities removed
- Text contrast tokens improved

## Static QA
- PHP files linted: 47
- PHP syntax failures: 0
- JavaScript syntax failures: 0

## Runtime QA still required on WAMP
The supplied test report includes browser, MySQL and Lighthouse checks that cannot be executed against the user's local machine from this environment. Use `qa/run_qa.php` and the `QA-FINAL-REPORT.xlsx` checklist on the WAMP machine.

Required runtime checks:
1. Admin valid/invalid login and logout protection
2. Booking create → MySQL → admin
3. Booking validation and duplicate submission behavior
4. Booking status lifecycle
5. Vehicle/driver assignment
6. Tour package create → refresh duplicate test
7. Customer duplicate/unique constraint test
8. CSRF/SQL injection/XSS negative tests
9. 320/375/414/768 responsive checks
10. Current Lighthouse performance/accessibility checks

AI route recommendation work remains intentionally pending until backend runtime QA is signed off.
