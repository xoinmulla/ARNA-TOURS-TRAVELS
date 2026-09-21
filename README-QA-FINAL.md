# Arna Tours & Travels — QA Final Build

Fixes from the supplied manual test report are included in source.

- Booking list/view APIs implemented.
- Status/assignment validation hardened.
- Customer mobile uniqueness added to clean schema; migration provided.
- Logout/auth redirect uses APP_URL.
- Responsive WebP vehicle images, intrinsic dimensions and lazy loading added.
- Heavy GSAP/Lenis startup removed; native reveal/carousel behavior retained.
- Mobile and carousel controls use 44px minimum hit areas.
- Online booking form is the desktop fallback for booking CTAs.
- Contact page added.
- Testimonial content is CMS-only and no longer uses fictional public identities.
- Placeholder navigation links replaced.

Runtime browser/Lighthouse tests still need execution on the user's WAMP machine.
