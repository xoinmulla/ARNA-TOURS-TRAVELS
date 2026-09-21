<?php
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Vehicle.php';
require_once __DIR__ . '/models/TourPackage.php';
$vehicles=(new Vehicle())->all(null,'AVAILABLE');
$siteTourCategories=(new TourPackage())->categories(true);
sendSecurityHeaders();
?>
<!doctype html><html lang="en" class="scroll-smooth"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Our Fleet | Arna Tour & Travels</title><link rel="icon" href="assets/img/favicon.png" type="image/png"><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"><script>window.tailwind=window.tailwind||{};window.tailwind.config={theme:{extend:{fontFamily:{sans:['"Plus Jakarta Sans"','Inter','sans-serif']},colors:{tapsi:{purple:'#130424',violet:'#1d0838',surface:'#240a45',accent:'#8b4df5',electric:'#9d5eff',lightText:'#c4b9d3',bgLight:'#f5f5f7',slateText:'#5f5e6b',darkHeading:'#16092b'}}}}};</script><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="assets/css/tailwind-design-fallback.css"><link rel="stylesheet" href="assets/css/website-ui-polish.css"><style>body{font-family:'Plus Jakarta Sans',sans-serif;margin:0}.fleet-card{transition:.45s cubic-bezier(.2,.7,.2,1)}.fleet-card:hover{transform:translateY(-7px);box-shadow:0 24px 60px rgba(19,4,36,.12)}.fleet-image{height:270px;background:linear-gradient(135deg,#f7f3fc,#fff);display:flex;align-items:center;justify-content:center}.fleet-image img{width:100%;height:100%;object-fit:contain;padding:28px}</style></head><body class="bg-tapsi-bgLight text-tapsi-darkHeading selection:bg-tapsi-accent selection:text-white">
  <!-- ==================== HEADER / NAVIGATION ==================== -->
  <nav id="mainNav"
    class="fixed top-0 left-0 w-full z-50 transition-all duration-300 py-5 px-6 lg:px-14 border-b border-transparent">
    <div class="max-w-7xl mx-auto flex items-center justify-between">
      <!-- Official Arna Tour & Travels logo -->
      <a href="index.php#hero" class="flex items-center gap-3 group arna-brand-link" aria-label="Arna Tour & Travels Home">
        <img src="assets/img/arna-logo.png" alt="Arna Tour & Travels" class="arna-header-logo" width="58" height="58" decoding="async">
        <span class="text-xl font-bold tracking-tight text-white nav-logo-text transition-colors">Arna Tour & Travels<span class="text-tapsi-accent">.</span></span>
      </a>

      <!-- Desktop Nav Links -->
      <div class="hidden md:flex items-center gap-7 glass-pill py-2 px-5 rounded-full border border-white/10 nav-links-box transition-colors">
        <a href="index.php#hero" class="text-xs font-medium uppercase tracking-wider text-white/90 hover:text-tapsi-electric transition-colors">Home</a>
        <a href="index.php#revolution" class="text-xs font-medium uppercase tracking-wider text-white/70 hover:text-white transition-colors">Solutions</a>
        <a href="index.php#services" class="text-xs font-medium uppercase tracking-wider text-white/70 hover:text-white transition-colors">Services</a>
        <a href="vehicles.php" class="text-xs font-medium uppercase tracking-wider text-white/70 hover:text-white transition-colors">Our Fleet</a>
        <div class="nav-dropdown">
          <button type="button" class="nav-dropdown-trigger text-xs font-medium uppercase tracking-wider text-white/70 hover:text-white transition-colors" aria-expanded="false">Tour Package <span class="nav-dropdown-chevron">🡫</span></button>
          <div class="nav-dropdown-menu">
            <?php foreach($siteTourCategories as $cat): ?><a href="tours.php?category=<?=e($cat['slug'])?>"><?=e($cat['title'])?></a><?php endforeach; ?>
          </div>
        </div>
        <a href="index.php#testimonials" class="text-xs font-medium uppercase tracking-wider text-white/70 hover:text-white transition-colors">Clients</a>
        <a href="index.php#benefits" class="text-xs font-medium uppercase tracking-wider text-white/70 hover:text-white transition-colors">Benefits</a>
      </div>

      <!-- Header CTA -->
      <div class="flex items-center gap-4">
        <a href="index.php#booking"
          class="nav-cta hidden sm:inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider px-5 py-2.5 rounded-full bg-white text-tapsi-purple hover:bg-tapsi-electric hover:text-white transition-all transform hover:-translate-y-0.5 shadow-md shadow-purple-950/40">
          Book a Ride
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </a>

        <!-- Mobile Menu Hamburger -->
        <button id="mobileMenuBtn" aria-label="Toggle Menu"
          class="md:hidden p-2 text-white/80 hover:text-white focus:outline-none">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Navigation Overlay -->
    <div id="mobileMenu" class="hidden md:hidden px-6 pt-4 pb-6 mt-3 bg-tapsi-violet/95 backdrop-blur-xl border border-white/10 rounded-2xl flex-col gap-2">
      <a href="index.php#hero" class="text-sm font-semibold text-white py-3 px-3 rounded-xl">Home</a>
      <a href="index.php#revolution" class="text-sm font-semibold text-white/80 py-3 px-3 rounded-xl">Solutions</a>
      <a href="index.php#services" class="text-sm font-semibold text-white/80 py-3 px-3 rounded-xl">Services</a>
      <a href="vehicles.php" class="text-sm font-semibold text-white/80 py-3 px-3 rounded-xl">Our Fleet</a>
      <div class="nav-mobile-package">
        <button type="button" class="nav-mobile-package-trigger" aria-expanded="false">Tour Package <span>🡫</span></button>
        <div class="nav-mobile-package-menu">
          <?php foreach($siteTourCategories as $cat): ?><a href="tours.php?category=<?=e($cat['slug'])?>"><?=e($cat['title'])?></a><?php endforeach; ?>
        </div>
      </div>
      <a href="index.php#testimonials" class="text-sm font-semibold text-white/80 py-3 px-3 rounded-xl">Clients</a>
      <a href="index.php#benefits" class="text-sm font-semibold text-white/80 py-3 px-3 rounded-xl">Benefits</a>
      <a href="index.php#booking" class="text-center text-xs font-bold uppercase tracking-wider py-3.5 rounded-full bg-white text-tapsi-purple mt-2">Book a Ride</a>
    </div>
  </nav>

<main>
<section class="bg-tapsi-purple text-white px-6 lg:px-14 pt-36 pb-24"><div class="max-w-7xl mx-auto"><p class="text-xs font-bold uppercase tracking-[.25em] text-tapsi-electric">Arna Fleet</p><h1 class="mt-4 text-5xl md:text-7xl font-extrabold tracking-tight">Travel in comfort.<br><span class="text-tapsi-electric">Choose your ride.</span></h1><p class="mt-6 max-w-2xl text-white/60 leading-relaxed">Explore the vehicles currently listed by Arna Tour & Travels. Fleet availability and specifications are managed from the admin panel.</p></div></section>
<section class="px-6 lg:px-14 py-20 bg-tapsi-bgLight motion-section"><div class="max-w-7xl mx-auto"><div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-7"><?php foreach($vehicles as $vehicle):?><article class="fleet-card motion-item overflow-hidden rounded-3xl bg-white border border-purple-100 shadow-sm"><div class="fleet-image"><?php if(!empty($vehicle['image'])):?><img src="<?=e($vehicle['image'])?>" alt="<?=e($vehicle['vehicle_name'])?>" loading="lazy"><?php else:?><div class="text-6xl text-tapsi-accent">🚕</div><?php endif;?></div><div class="p-7"><div class="flex items-center justify-between gap-3"><h2 class="text-2xl font-extrabold"><?=e($vehicle['vehicle_name'])?></h2><span class="rounded-full bg-purple-50 px-3 py-1 text-xs font-bold text-tapsi-accent"><?= (int)$vehicle['seating_capacity']?> Seats</span></div><p class="mt-2 text-sm font-semibold text-tapsi-accent"><?=e($vehicle['vehicle_type'])?:'Travel Vehicle'?></p><p class="mt-4 text-sm leading-relaxed text-gray-500"><?=e($vehicle['description'])?:'Comfortable travel for local, outstation and tour journeys.'?></p><div class="mt-6 flex items-center justify-between"><span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Available</span><a href="index.php#booking" class="text-sm font-bold text-tapsi-darkHeading">Book this ride →</a></div></div></article><?php endforeach;?><?php if(!$vehicles):?><div class="sm:col-span-2 lg:col-span-3 rounded-3xl bg-white p-12 text-center text-gray-500">Fleet vehicles will appear here once they are added from the admin panel.</div><?php endif;?></div></div></section>
<section class="bg-white px-6 lg:px-14 py-20 border-t border-purple-100 motion-section"><div class="max-w-4xl mx-auto text-center"><p class="text-xs font-bold uppercase tracking-[.25em] text-tapsi-accent">Serving All India</p><h2 class="mt-4 text-4xl md:text-5xl font-extrabold">One travel partner for every journey.</h2><p class="mt-5 text-gray-500 leading-relaxed">From local taxi rides to outstation travel and travel booking assistance, Arna Tour & Travels can be positioned as a single point of contact for journeys across India.</p><a href="index.php#booking" class="inline-flex mt-8 rounded-full bg-tapsi-purple text-white px-7 py-3.5 font-bold text-sm">Start Your Journey →</a></div></section>
</main>
  <!-- ==================== SECTION 6: FOOTER ==================== -->
  <!-- Storyboard Frames 57 - 71: Deep purple cinematic closing & minimalist footer -->
  <footer
    class="motion-section bg-tapsi-purple text-white pt-24 pb-12 px-6 lg:px-14 border-t border-white/10 relative overflow-hidden">
    <!-- Subtle footer glow -->
    <div class="absolute bottom-0 left-1/3 w-96 h-96 bg-purple-600/10 rounded-full blur-[140px] pointer-events-none">
    </div>

    <div class="max-w-7xl mx-auto w-full relative z-10">

      <!-- Big CTA Banner before Links -->
      <div
        class="rounded-3xl bg-gradient-to-r from-tapsi-violet via-tapsi-surface to-purple-950 p-8 sm:p-14 border border-white/10 shadow-2xl mb-16 flex flex-col lg:flex-row items-center justify-between gap-8">
        <div class="space-y-3 text-center lg:text-left">
          <h3 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Ready to plan your next journey?</h3>
          <p class="text-sm text-tapsi-lightText max-w-lg">Share your travel requirements and our team will help plan the right vehicle and journey.</p>
        </div>
        <div class="flex flex-wrap items-center justify-center gap-4">
          <a href="index.php#booking"
            class="px-8 py-3.5 rounded-full bg-white text-tapsi-purple font-bold text-xs uppercase tracking-wider hover:bg-tapsi-electric hover:text-white transition-all transform hover:-translate-y-0.5 shadow-xl">
            Request Corporate Demo
          </a>
          <a href="contact.php"
            class="px-7 py-3.5 rounded-full glass-pill text-white hover:bg-white/10 font-semibold text-xs uppercase tracking-wider transition-all">
            Contact Chauffeur Desk
          </a>
        </div>
      </div>

      <!-- Links Grid -->
      <div class="grid grid-cols-2 md:grid-cols-5 gap-8 pb-16 border-b border-white/10">

        <!-- Col 1: Brand & Bio -->
        <div class="col-span-2 space-y-4">
          <div class="flex items-center gap-2.5">
            <div
              class="w-8 h-8 rounded-lg bg-gradient-to-br from-tapsi-accent to-purple-800 flex items-center justify-center text-white">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.85 7h10.29l1.04 3H5.81l1.04-3z" />
              </svg>
            </div>
            <span class="text-xl font-bold tracking-tight text-white">Arna Tour & Travels<span
                class="text-tapsi-accent">.</span></span>
          </div>
          <p class="text-xs text-tapsi-lightText max-w-sm leading-relaxed">
            Premium travel support for local, airport and outstation journeys across India.
          </p>
          <div class="footer-contact-actions">
            <a href="contact.php" aria-label="Contact Arna"
              class="w-10 h-10 rounded-full glass-pill flex items-center justify-center hover:text-white hover:bg-white/10 transition-colors">Contact</a>
            <a href="contact.php" aria-label="Contact Arna"
              class="w-10 h-10 rounded-full glass-pill flex items-center justify-center hover:text-white hover:bg-white/10 transition-colors">Call</a>
            <a href="contact.php" aria-label="Contact Arna"
              class="w-10 h-10 rounded-full glass-pill flex items-center justify-center hover:text-white hover:bg-white/10 transition-colors">Info</a>
          </div>
        </div>

        <!-- Col 2: Services -->
        <div class="space-y-3">
          <div class="text-xs font-bold text-white uppercase tracking-wider">Vehicles</div>
          <ul class="space-y-2 text-xs text-tapsi-lightText">
            <li><a href="vehicles.php" class="hover:text-white transition-colors">Toyota Etios</a></li>
            <li><a href="vehicles.php" class="hover:text-white transition-colors">Swift Dezire</a></li>
            <li><a href="vehicles.php" class="hover:text-white transition-colors">Kiya Karan</a></li>
            <li><a href="vehicles.php" class="hover:text-white transition-colors">Innova Crysta</a></li>
            <li><a href="vehicles.php" class="hover:text-white transition-colors">Tempo Traveller 12 to 25 Seats</a></li>
            <li><a href="vehicles.php" class="hover:text-white transition-colors">Urbania AC</a></li>
          </ul>
        </div>

        <!-- Col 4: Company -->
        <div class="space-y-3">
          <div class="text-xs font-bold text-white uppercase tracking-wider">Travel</div>
          <ul class="space-y-2 text-xs text-tapsi-lightText">
            <li><a href="index.php#services" class="hover:text-white transition-colors">Taxi & Cab Booking</a></li>
            <li><a href="index.php#services" class="hover:text-white transition-colors">Bus Booking</a></li>
            <li><a href="index.php#services" class="hover:text-white transition-colors">Train Booking</a></li>
            <li><a href="index.php#services" class="hover:text-white transition-colors">Flight Booking</a></li>
            <li><a href="index.php#services" class="hover:text-white transition-colors">Hotel & Resort Booking</a></li>
            <li><a href="vehicles.php" class="hover:text-white transition-colors">Our Fleet</a></li>
          </ul>
        </div>

      </div>


          <div class="site-footer-socials" aria-label="Social media links">
            <a href="<?=e(defined('ARNA_INSTAGRAM_URL')?ARNA_INSTAGRAM_URL:'https://www.instagram.com/arnatoursandtravels/') ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><svg viewBox="0 0 24 24"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3.5A4.5 4.5 0 1 1 7.5 12 4.5 4.5 0 0 1 12 7.5Zm0 2A2.5 2.5 0 1 0 14.5 12 2.5 2.5 0 0 0 12 9.5ZM17.5 6.5a1 1 0 1 1-1 1 1 1 0 0 1 1-1Z"/></svg></a>
            <a href="<?=e(defined('ARNA_FACEBOOK_URL')?ARNA_FACEBOOK_URL:'https://www.facebook.com/profile.php?id=61594357268416') ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M13.5 22v-8h2.7l.4-3h-3.1V9.08c0-.87.24-1.46 1.49-1.46h1.6V4.94c-.28-.04-1.24-.12-2.36-.12-2.34 0-3.94 1.43-3.94 4.05V11H8v3h2.3v8h3.2Z"/></svg></a>
            <a href="<?=e(defined('ARNA_YOUTUBE_URL')?ARNA_YOUTUBE_URL:'https://www.youtube.com/@arnatoursandtravels') ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube"><svg viewBox="0 0 24 24"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.12C19.55 3.5 12 3.5 12 3.5s-7.55 0-9.4.58A3 3 0 0 0 .5 6.2 31.3 31.3 0 0 0 0 12a31.3 31.3 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.12c1.85.58 9.4.58 9.4.58s7.55 0 9.4-.58a3 3 0 0 0 2.1-2.12A31.3 31.3 0 0 0 24 12a31.3 31.3 0 0 0-.5-5.8ZM9.75 15.5v-7l6 3.5-6 3.5Z"/></svg></a>
          </div>

      <!-- Copyright & Bottom Disclaimers -->
      <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-tapsi-lightText gap-4">
        <div>
          © <?= date('Y') ?> Arna Tour & Travels. All rights reserved. Travel services across India.
        </div>
        <div class="flex items-center gap-6">
          <a href="contact.php#policies" class="hover:text-white transition-colors">Privacy Policy</a>
          <a href="contact.php#policies" class="hover:text-white transition-colors">Terms of Service</a>
          <a href="contact.php#policies" class="hover:text-white transition-colors">Cookie Settings</a>
        </div>
      </div>

    </div>
  </footer>
<script>document.addEventListener('DOMContentLoaded',()=>{const nav=document.getElementById('mainNav'),btn=document.getElementById('mobileMenuBtn'),menu=document.getElementById('mobileMenu');const setMenu=o=>{menu.classList.toggle('hidden',!o);btn.setAttribute('aria-expanded',o?'true':'false')};btn?.addEventListener('click',()=>setMenu(menu.classList.contains('hidden')));menu?.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>setMenu(false)));document.querySelectorAll('.nav-dropdown-trigger').forEach(b=>b.addEventListener('click',()=>{const box=b.closest('.nav-dropdown');const open=box.classList.toggle('is-open');b.setAttribute('aria-expanded',open?'true':'false')}));document.addEventListener('click',e=>document.querySelectorAll('.nav-dropdown.is-open').forEach(box=>{if(!box.contains(e.target)){box.classList.remove('is-open');box.querySelector('.nav-dropdown-trigger')?.setAttribute('aria-expanded','false')}}));document.querySelectorAll('.nav-mobile-package-trigger').forEach(b=>b.addEventListener('click',()=>b.closest('.nav-mobile-package').classList.toggle('is-open')));const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('motion-in');io.unobserve(e.target)}}),{threshold:.1});document.querySelectorAll('.motion-section').forEach(e=>io.observe(e));const update=()=>{const dark=window.scrollY<window.innerHeight*.65||window.scrollY<100;nav.classList.toggle('nav-light',!dark);nav.classList.toggle('nav-scrolled',window.scrollY>30)};addEventListener('scroll',update,{passive:true});update()});</script><?php require_once __DIR__ . '/includes/site-elements.php'; ?></body></html>