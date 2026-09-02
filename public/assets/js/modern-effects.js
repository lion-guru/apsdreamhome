/* ============================================
   APS Dream Home - Modern Effects Engine
   Particles, Typed.js, Scroll Reveals, 3D Tilt
   ============================================ */

(function () {
  'use strict';

  /* ---- Scroll Progress Bar ---- */
  function initScrollProgress() {
    var bar = document.createElement('div');
    bar.className = 'scroll-progress';
    bar.id = 'scrollProgress';
    document.body.prepend(bar);

    window.addEventListener(
      'scroll',
      function () {
        var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        var scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        var progress = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
        bar.style.width = progress + '%';
      },
      { passive: true }
    );
  }

  /* ---- Back to Top Button ---- */
  function initBackToTop() {
    var btn = document.createElement('button');
    btn.className = 'back-to-top';
    btn.id = 'backToTop';
    btn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    btn.setAttribute('aria-label', 'Back to top');
    document.body.appendChild(btn);

    window.addEventListener(
      'scroll',
      function () {
        if (window.scrollY > 400) {
          btn.classList.add('visible');
        } else {
          btn.classList.remove('visible');
        }
      },
      { passive: true }
    );

    btn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /* ---- Scroll Reveal (Lightweight AOS alternative) ---- */
  function initScrollReveal() {
    var elements = document.querySelectorAll('.reveal, .premium-reveal, .reveal-left, .reveal-right, .reveal-scale, .reveal-rotate');
    if (!elements.length) return;

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
    );

    elements.forEach(function (el) {
      observer.observe(el);
    });
  }

  /* ---- Animated Counters ---- */
  function initCounters() {
    var counters = document.querySelectorAll('.stat-number[data-target]');
    if (!counters.length) return;

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            animateCounter(entry.target);
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.5 }
    );

    counters.forEach(function (counter) {
      observer.observe(counter);
    });
  }

  function animateCounter(el) {
    var target = parseInt(el.getAttribute('data-target'), 10);
    var duration = 2000;
    var startTime = null;
    var suffix = el.getAttribute('data-suffix') || '';
    var currentText = el.textContent.replace(/[+,]/g, '').trim();
    var currentVal = parseInt(currentText, 10);
    var start = (!isNaN(currentVal) && currentVal > 0) ? currentVal : 0;

    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3); // easeOutCubic
      var current = Math.floor(start + eased * (target - start));
      el.textContent = current.toLocaleString('en-IN') + suffix;
      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        el.textContent = target.toLocaleString('en-IN') + suffix;
        el.classList.add('counting');
        setTimeout(function () {
          el.classList.remove('counting');
        }, 300);
      }
    }
    requestAnimationFrame(step);
  }

  /* ---- 3D Tilt Effect on Cards ---- */
  function init3DTilt() {
    var cards = document.querySelectorAll('.card-3d, .project-card-modern, .service-card-modern');
    cards.forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var rect = card.getBoundingClientRect();
        var x = e.clientX - rect.left;
        var y = e.clientY - rect.top;
        var centerX = rect.width / 2;
        var centerY = rect.height / 2;
        var rotateX = (y - centerY) / 20;
        var rotateY = (centerX - x) / 20;
        card.style.transform =
          'perspective(1000px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) translateY(-6px)';
      });
      card.addEventListener('mouseleave', function () {
        card.style.transform = '';
      });
    });
  }

  /* ---- Particles.js Lightweight ---- */
  function initParticles(canvasId) {
    var canvas = document.getElementById(canvasId);
    if (!canvas) return;

    var ctx = canvas.getContext('2d');
    var particles = [];
    var particleCount = 60;
    var mouse = { x: null, y: null };

    function resize() {
      canvas.width = canvas.parentElement.offsetWidth;
      canvas.height = canvas.parentElement.offsetHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    canvas.parentElement.addEventListener('mousemove', function (e) {
      var rect = canvas.getBoundingClientRect();
      mouse.x = e.clientX - rect.left;
      mouse.y = e.clientY - rect.top;
    });

    function Particle() {
      this.x = Math.random() * canvas.width;
      this.y = Math.random() * canvas.height;
      this.vx = (Math.random() - 0.5) * 0.5;
      this.vy = (Math.random() - 0.5) * 0.5;
      this.radius = Math.random() * 2 + 1;
      this.opacity = Math.random() * 0.4 + 0.1;
    }

    for (var i = 0; i < particleCount; i++) {
      particles.push(new Particle());
    }

    function drawParticles() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      particles.forEach(function (p, i) {
        // Move
        p.x += p.vx;
        p.y += p.vy;

        // Bounce
        if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
        if (p.y < 0 || p.y > canvas.height) p.vy *= -1;

        // Mouse interaction
        if (mouse.x !== null) {
          var dx = mouse.x - p.x;
          var dy = mouse.y - p.y;
          var dist = Math.sqrt(dx * dx + dy * dy);
          if (dist < 150) {
            p.x -= dx * 0.01;
            p.y -= dy * 0.01;
          }
        }

        // Draw particle
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(129, 140, 248, ' + p.opacity + ')';
        ctx.fill();

        // Draw connections
        for (var j = i + 1; j < particles.length; j++) {
          var p2 = particles[j];
          var dx2 = p.x - p2.x;
          var dy2 = p.y - p2.y;
          var dist2 = Math.sqrt(dx2 * dx2 + dy2 * dy2);
          if (dist2 < 120) {
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            ctx.lineTo(p2.x, p2.y);
            ctx.strokeStyle = 'rgba(129, 140, 248, ' + 0.08 * (1 - dist2 / 120) + ')';
            ctx.lineWidth = 0.5;
            ctx.stroke();
          }
        }
      });

      requestAnimationFrame(drawParticles);
    }
    drawParticles();
  }

  /* ---- Typed.js Lightweight ---- */
  function initTyped(elementId, strings, options) {
    var el = document.getElementById(elementId);
    if (!el) return;

    var opts = options || {};
    var typeSpeed = opts.typeSpeed || 80;
    var deleteSpeed = opts.deleteSpeed || 50;
    var pauseTime = opts.pauseTime || 2000;
    var loop = opts.loop !== false;

    var stringIndex = 0;
    var charIndex = 0;
    var isDeleting = false;

    function type() {
      var currentString = strings[stringIndex];

      if (isDeleting) {
        el.textContent = currentString.substring(0, charIndex - 1);
        charIndex--;
      } else {
        el.textContent = currentString.substring(0, charIndex + 1);
        charIndex++;
      }

      var delay = isDeleting ? deleteSpeed : typeSpeed;

      if (!isDeleting && charIndex === currentString.length) {
        delay = pauseTime;
        isDeleting = true;
      } else if (isDeleting && charIndex === 0) {
        isDeleting = false;
        stringIndex = (stringIndex + 1) % strings.length;
        delay = 500;
      }

      setTimeout(type, delay);
    }
    type();
  }

  /* ---- Navbar Scroll Effect ---- */
  function initNavbarScroll() {
    var header = document.querySelector('.premium-header');
    if (!header) return;

    window.addEventListener(
      'scroll',
      function () {
        if (window.scrollY > 50) {
          header.classList.add('scrolled');
        } else {
          header.classList.remove('scrolled');
        }
      },
      { passive: true }
    );
  }

  /* ---- Smooth Section Transitions ---- */
  function initSectionTransitions() {
    var sections = document.querySelectorAll('section');
    sections.forEach(function (section) {
      section.classList.add('reveal');
    });

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('revealed');
          }
        });
      },
      { threshold: 0.05, rootMargin: '0px 0px -30px 0px' }
    );

    sections.forEach(function (s) {
      observer.observe(s);
    });
  }

  /* ---- Initialize All ---- */
  function init() {
    initScrollProgress();
    initBackToTop();
    initScrollReveal();
    initCounters();
    init3DTilt();
    initNavbarScroll();
    // initSectionTransitions() REMOVED — conflicts with premium-animations.js
    // That function adds .reveal to ALL <section> tags, setting opacity: 0,
    // which hides all homepage content. premium-animations.js handles reveals.

    // Particles on hero (if canvas exists)
    initParticles('particles-canvas');

    // Typed.js on hero (if element exists)
    var typedEl = document.getElementById('typed-text');
    if (typedEl) {
      var strings = (typedEl.getAttribute('data-strings') || '').split('||');
      if (strings.length && strings[0]) {
        initTyped('typed-text', strings, {
          typeSpeed: 70,
          deleteSpeed: 40,
          pauseTime: 2500,
        });
      }
    }
  }

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Expose for manual init
  window.APSModern = {
    init: init,
    initParticles: initParticles,
    initTyped: initTyped,
    initCounters: initCounters,
    init3DTilt: init3DTilt,
  };
})();
