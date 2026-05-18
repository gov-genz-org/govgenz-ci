/* ============================================
   GoV Gen Z Madagascar — main.js (public)
   Les chaînes du gabarit sont rendues côté serveur (PHP lang).
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
  document.body.classList.add('js-loaded');
});

function pageLocaleTag() {
  const lang = (document.documentElement.getAttribute('lang') || 'fr').toLowerCase().slice(0, 2);

  return lang === 'en' ? 'en-US' : 'fr-FR';
}

// ============================================
// HEADER SCROLL STATE
// ============================================
const header = document.getElementById('header');
let lastScroll = 0;

window.addEventListener('scroll', () => {
  const scroll = window.pageYOffset;
  if (scroll > 80) {
    header?.classList.add('is-scrolled');
  } else {
    header?.classList.remove('is-scrolled');
  }
  lastScroll = scroll;
}, { passive: true });

// ============================================
// MOBILE MENU
// ============================================
const menuToggle = document.getElementById('menu-toggle');
const nav = document.getElementById('nav');

if (menuToggle && nav) {
  menuToggle.addEventListener('click', () => {
    const open = !nav.classList.contains('is-open');
    menuToggle.classList.toggle('is-open', open);
    nav.classList.toggle('is-open', open);
    menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    menuToggle.setAttribute('aria-label', open
      ? (menuToggle.getAttribute('data-label-close') || 'Close menu')
      : (menuToggle.getAttribute('data-label-open') || 'Open menu'));
  });

  nav.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      menuToggle.classList.remove('is-open');
      nav.classList.remove('is-open');
      menuToggle.setAttribute('aria-expanded', 'false');
    });
  });
}

// ============================================
// SCROLL REVEAL
// ============================================
const revealElements = document.querySelectorAll('.reveal');

const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const delay = parseInt(entry.target.dataset.delay) || 0;
      setTimeout(() => {
        entry.target.classList.add('is-visible');
      }, delay);
      revealObserver.unobserve(entry.target);
    }
  });
}, {
  threshold: 0.1,
  rootMargin: '0px 0px -50px 0px'
});

revealElements.forEach(el => revealObserver.observe(el));

// ============================================
// COMPTEURS ANIMÉS
// ============================================
function animateCounter(el, target) {
  const duration = 2000;
  const decimals = (target.toString().split('.')[1] || '').length;
  const start = performance.now();
  const localeTag = pageLocaleTag();

  function update(now) {
    const elapsed = now - start;
    const progress = Math.min(elapsed / duration, 1);

    const eased = 1 - Math.pow(1 - progress, 3);
    const current = target * eased;

    el.textContent = current.toLocaleString(localeTag, {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    });

    if (progress < 1) {
      requestAnimationFrame(update);
    } else {
      el.textContent = target.toLocaleString(localeTag, {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
      });
    }
  }

  requestAnimationFrame(update);
}

const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const target = parseFloat(entry.target.dataset.count);
      if (!isNaN(target)) {
        animateCounter(entry.target, target);
      }
      counterObserver.unobserve(entry.target);
    }
  });
}, {
  threshold: 0.5
});

document.querySelectorAll('[data-count]').forEach(el => {
  counterObserver.observe(el);
});

// ============================================
// SMOOTH SCROLL POUR LES ANCRES
// ============================================
document.querySelectorAll('a[href^="#"]').forEach(link => {
  link.addEventListener('click', (e) => {
    const targetId = link.getAttribute('href');
    if (targetId === '#') return;

    const target = document.querySelector(targetId);
    if (target) {
      e.preventDefault();
      const headerHeight = document.querySelector('.header')?.offsetHeight || 0;
      const offset = headerHeight + 16;

      const targetPos = target.getBoundingClientRect().top + window.pageYOffset - offset;

      window.scrollTo({
        top: targetPos,
        behavior: 'smooth'
      });
    }
  });
});

// Navigation « active » : gérée côté serveur (pages dynamiques CodeIgniter).
