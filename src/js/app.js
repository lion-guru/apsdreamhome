// APS Dream Home – main JS entry
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

console.log('APS Dream Home – Vite build active');

// Example: mobile nav toggle
const navToggle = document.querySelector('.nav-toggle');
const navMenu   = document.querySelector('.nav-menu');

if (navToggle && navMenu) {
  navToggle.addEventListener('click', () => {
    navMenu.classList.toggle('show');
  });
}

// Example: lazy-load images
if ('IntersectionObserver' in window) {
  const images = document.querySelectorAll('img[data-src]');
  const imgObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.classList.remove('lazy');
        imgObserver.unobserve(img);
      }
    });
  });
  images.forEach(img => imgObserver.observe(img));
}