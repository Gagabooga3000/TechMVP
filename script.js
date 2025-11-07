// Smooth scroll for anchor links
document.addEventListener('click', (e) => {
  const target = e.target.closest('a[href^="#"]');
  if (!target) return;
  const id = target.getAttribute('href');
  if (id.length > 1) {
    const el = document.querySelector(id);
    if (el) {
      e.preventDefault();
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      const nav = document.getElementById('nav');
      if (nav && nav.classList.contains('is-open')) nav.classList.remove('is-open');
      const toggle = document.querySelector('.nav-toggle');
      if (toggle) toggle.setAttribute('aria-expanded', 'false');
    }
  }
});

// Mobile nav toggle
const navToggle = document.querySelector('.nav-toggle');
if (navToggle) {
  navToggle.addEventListener('click', () => {
    const nav = document.getElementById('nav');
    const expanded = navToggle.getAttribute('aria-expanded') === 'true';
    navToggle.setAttribute('aria-expanded', String(!expanded));
    nav.classList.toggle('is-open');
  });
}

// Accordion (FAQ) with ARIA
document.querySelectorAll('.accordion__item').forEach((item, idx) => {
  const trigger = item.querySelector('.accordion__trigger');
  const content = item.querySelector('.accordion__content');
  if (!trigger || !content) return;
  const contentId = content.id || `accordion-content-${idx}`;
  content.id = contentId;
  trigger.setAttribute('aria-controls', contentId);
  trigger.setAttribute('aria-expanded', 'false');
  trigger.setAttribute('role', 'button');
  content.setAttribute('role', 'region');
  content.setAttribute('aria-hidden', 'true');
  trigger.addEventListener('click', () => {
    const isActive = item.classList.contains('active');
    document.querySelectorAll('.accordion__item.active').forEach((open) => {
      open.classList.remove('active');
      const c = open.querySelector('.accordion__content');
      const t = open.querySelector('.accordion__trigger');
      if (c) c.style.maxHeight = 0;
      if (t) t.setAttribute('aria-expanded', 'false');
      if (c) c.setAttribute('aria-hidden', 'true');
    });
    if (!isActive) {
      item.classList.add('active');
      content.style.maxHeight = content.scrollHeight + 'px';
      trigger.setAttribute('aria-expanded', 'true');
      content.setAttribute('aria-hidden', 'false');
    }
  });
});

// Year in footer
const yearEl = document.getElementById('year');
if (yearEl) yearEl.textContent = new Date().getFullYear();

// Simple form UX
const form = document.querySelector('.form');
if (form) {
  form.addEventListener('submit', () => {
    const btn = form.querySelector('button[type="submit"]');
    if (btn) {
      btn.disabled = true;
      btn.textContent = 'Отправка...';
      setTimeout(() => { btn.disabled = false; btn.textContent = 'Получить доступ'; }, 4000);
    }
  });
}

// 3D tilt interaction (safe, host-friendly)
(function(){
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const hasFinePointer = window.matchMedia('(pointer: fine)').matches;
  if (prefersReduced || !hasFinePointer) return;
  const tiltEls = Array.from(document.querySelectorAll('[data-tilt]'));
  const maxTilt = 10; // deg
  let rafId = null;

  function computeTransform(el, e){
    const rect = el.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    const y = (e.clientY - rect.top) / rect.height;
    const rx = (y - 0.5) * -2 * maxTilt;
    const ry = (x - 0.5) * 2 * maxTilt;
    el.style.transform = `rotateX(${rx.toFixed(2)}deg) rotateY(${ry.toFixed(2)}deg)`;
    el.style.setProperty('--mx', `${Math.round(x*100)}%`);
    el.style.setProperty('--my', `${Math.round(y*100)}%`);
  }

  function onMove(e){
    const el = e.currentTarget;
    if (rafId) cancelAnimationFrame(rafId);
    rafId = requestAnimationFrame(() => computeTransform(el, e));
  }

  function onLeave(e){
    e.currentTarget.style.transform = 'rotateX(0deg) rotateY(0deg)';
  }

  tiltEls.forEach((el)=>{
    el.addEventListener('mousemove', onMove);
    el.addEventListener('mouseleave', onLeave);
  });
})();

// Cookie banner & analytics lazy-load
(function(){
  const banner = document.getElementById('cookieBanner');
  if (!banner) return;
  const consentKey = 'cookie_consent_v1';
  const hasConsent = localStorage.getItem(consentKey);
  const acceptBtn = banner.querySelector('[data-cookie-accept]');
  const rejectBtn = banner.querySelector('[data-cookie-reject]');
  function loadAnalytics(){
    // GA4 example: replace G-XXXXXXX when ready
    // const g = document.createElement('script');
    // g.async = true; g.src = 'https://www.googletagmanager.com/gtag/js?id=G-XXXXXXX';
    // document.head.appendChild(g);
    // window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'G-XXXXXXX');
    // Yandex Metrika: uncomment and add ID
    // const ym = document.createElement('script'); ym.innerHTML = "(function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)})(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym'); ym(YOUR_ID,'init',{clickmap:true, trackLinks:true, accurateTrackBounce:true});"; document.head.appendChild(ym);
  }
  if (!hasConsent) {
    banner.style.display = 'block';
  } else if (hasConsent === 'accepted') {
    loadAnalytics();
  }
  acceptBtn && acceptBtn.addEventListener('click', () => {
    localStorage.setItem(consentKey, 'accepted');
    banner.style.display = 'none';
    loadAnalytics();
  });
  rejectBtn && rejectBtn.addEventListener('click', () => {
    localStorage.setItem(consentKey, 'rejected');
    banner.style.display = 'none';
  });
})();


