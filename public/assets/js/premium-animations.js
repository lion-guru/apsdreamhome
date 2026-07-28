/**
 * Premium Animations - APS Dream Home
 * Scroll reveal, tilt effects, and micro-interactions
 * Progressive enhancement: page works without JS,
 * animations only activate when this script loads.
 */
// Activate animations IMMEDIATELY (before DOMContentLoaded)
// This ensures .js-animations is set before any paint
document.documentElement.classList.add('js-animations');

(function () {
  'use strict';

  // ============================================================
  // CONFIGURATION
  // ============================================================
  const config = {
    // Scroll reveal settings
    scrollReveal: {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px',
      once: true,
    },
    // Tilt effect settings
    tilt: {
      max: 8, // max tilt in degrees
      perspective: 1000, // perspective
      scale: 1.02, // scale on hover
      speed: 500, // transition duration in ms
    },
    // Parallax settings
    parallax: {
      speed: 0.3,
    },
  };

  // ============================================================
  // UTILITY FUNCTIONS
  // ============================================================
  const utils = {
    // Debounce function
    debounce(fn, wait) {
      let timeout;
      return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn.apply(this, args), wait);
      };
    },

    // Check if element is in viewport
    isInViewport(el, threshold = 0) {
      const rect = el.getBoundingClientRect();
      return (
        rect.top <= (window.innerHeight || document.documentElement.clientHeight) * (1 - threshold) && rect.bottom >= 0
      );
    },

    // Generate random ID
    generateId() {
      return 'anim-' + Math.random().toString(36).substr(2, 9);
    },
  };

  // ============================================================
  // SCROLL REVEAL ANIMATION
  // ============================================================
  function initScrollReveal() {
    const observer = new IntersectionObserver(
      entries => {
        // Group entries by intersection time for staggering
        let delayCounter = 0;
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const el = entry.target;

            // Check if it's part of a staggered group
            const staggerBase = el.closest('.stagger-group');
            let applyDelay = parseInt(el.style.animationDelay) || 0;

            if (staggerBase && !el.hasAttribute('data-stagger-applied')) {
              applyDelay += delayCounter * 100; // 100ms stagger between elements
              el.style.transitionDelay = `${applyDelay}ms`;
              el.setAttribute('data-stagger-applied', 'true');
              delayCounter++;
            }

            // Add visible + revealed class with delay
            setTimeout(() => {
              el.classList.add('visible');
              el.classList.add('revealed');
            }, applyDelay || 10);

            // Unobserve after animation
            if (config.scrollReveal.once) {
              observer.unobserve(el);
            }
          }
        });
      },
      {
        threshold: config.scrollReveal.threshold,
        rootMargin: config.scrollReveal.rootMargin,
      }
    );

    // Observe all elements with scroll-reveal OR premium-reveal class
    document.querySelectorAll('.scroll-reveal, .premium-reveal').forEach(el => {
      observer.observe(el);
    });
  }

  // ============================================================
  // PAGE TRANSITION FADE-IN (Disabled to prevent link interception bugs)
  // ============================================================
  function initPageTransitions() {
    // Disabled.
  }

  // ============================================================
  // 3D TILT EFFECT
  // ============================================================
  function initTiltEffect() {
    const tiltElements = document.querySelectorAll('.glass-card, .props-grid-card, .glass-card');

    tiltElements.forEach(card => {
      let timeout = null;

      const handleMouseMove = e => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotateX = ((y - centerY) / centerY) * config.tilt.max;
        const rotateY = ((centerX - x) / centerX) * config.tilt.max;

        card.style.transform = `
                    perspective(${config.tilt.perspective}px)
                    rotateX(${rotateX}deg)
                    rotateY(${rotateY}deg)
                    scale3d(${config.tilt.scale}, ${config.tilt.scale}, ${config.tilt.scale})
                `;
        card.style.transition = `transform ${config.tilt.speed}ms cubic-bezier(0.4, 0, 0.2, 1)`;
      };

      const handleMouseLeave = () => {
        card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
        card.style.transition = `transform ${config.tilt.speed}ms cubic-bezier(0.4, 0, 0.2, 1)`;
      };

      card.addEventListener('mousemove', utils.debounce(handleMouseMove, 16));
      card.addEventListener('mouseleave', handleMouseLeave);
    });
  }

  // ============================================================
  // PARALLAX EFFECT FOR HERO SECTIONS
  // ============================================================
  function initParallax() {
    const parallaxElements = document.querySelectorAll('[data-parallax]');

    if (parallaxElements.length === 0) return;

    let ticking = false;

    const updateParallax = () => {
      const scrollY = window.scrollY || window.pageYOffset;

      parallaxElements.forEach(el => {
        const rect = el.getBoundingClientRect();
        const speed = parseFloat(el.dataset.parallax) || config.parallax.speed;

        if (rect.bottom >= 0 && rect.top <= window.innerHeight) {
          const yPos = scrollY * speed;
          el.style.transform = `translateY(${yPos}px)`;
        }
      });

      ticking = false;
    };

    window.addEventListener('scroll', () => {
      if (!ticking) {
        requestAnimationFrame(updateParallax);
        ticking = true;
      }
    });
  }

  // ============================================================
  // HOVER GLOW EFFECT FOR BUTTONS
  // ============================================================
  function initButtonGlow() {
    const glowButtons = document.querySelectorAll('.btn-glow, .btn-premium');

    glowButtons.forEach(btn => {
      btn.addEventListener('mouseenter', () => {
        btn.style.transform = 'translateY(-2px)';
      });

      btn.addEventListener('mouseleave', () => {
        btn.style.transform = 'translateY(0)';
      });
    });
  }

  // ============================================================
  // CARD IMAGE ZOOM ON HOVER
  // ============================================================
  function initImageZoom() {
    const cards = document.querySelectorAll('.props-grid-card, .glass-card');

    cards.forEach(card => {
      const img = card.querySelector('img');
      if (!img) return;

      card.addEventListener('mouseenter', () => {
        img.style.transform = 'scale(1.05)';
        img.style.transition = 'transform 0.6s ease';
      });

      card.addEventListener('mouseleave', () => {
        img.style.transform = 'scale(1)';
      });
    });
  }

  // ============================================================
  // SMOOTH SCROLL FOR ANCHOR LINKS
  // ============================================================
  function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        const targetId = this.getAttribute('href');
        if (targetId === '#') return;

        const target = document.querySelector(targetId);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
          });
        }
      });
    });
  }

  // ============================================================
  // LAZY LOAD IMAGES WITH FADE-IN
  // ============================================================
  function initLazyLoad() {
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');

    const imgObserver = new IntersectionObserver(entries => {
      entries.forEach(
        entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.classList.add('loaded');
            imgObserver.unobserve(img);
          }
        },
        {
          rootMargin: '50px 0px',
          threshold: 0.01,
        }
      );

      lazyImages.forEach(img => imgObserver.observe(img));
    });
  }

  // ============================================================
  // TYPING ANIMATION FOR HERO TEXT
  // ============================================================
  function initTypedText() {
    const typedElement = document.getElementById('typed-text');
    if (!typedElement) return;

    const strings = typedElement.dataset.strings?.split('||') || [];
    if (strings.length === 0) return;

    let stringIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    let typeSpeed = 80;

    function type() {
      const currentString = strings[stringIndex];

      if (isDeleting) {
        typedElement.textContent = currentString.substring(0, charIndex - 1);
        charIndex--;
        typeSpeed = 50;
      } else {
        typedElement.textContent = currentString.substring(0, charIndex + 1);
        charIndex++;
        typeSpeed = 80;
      }

      if (!isDeleting && charIndex === currentString.length) {
        isDeleting = true;
        typeSpeed = 2000; // Pause at end
      } else if (isDeleting && charIndex === 0) {
        isDeleting = false;
        stringIndex = (stringIndex + 1) % strings.length;
        typeSpeed = 500; // Pause before next string
      }

      setTimeout(type, typeSpeed);
    }

    type();
  }

  // ============================================================
  // PARTICLES BACKGROUND
  // ============================================================
  function initParticles() {
    const canvas = document.getElementById('particles-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const particles = [];
    const particleCount = 50;

    function resize() {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    }

    class Particle {
      constructor() {
        this.reset();
      }

      reset() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.size = Math.random() * 2 + 1;
        this.speedX = (Math.random() - 0.5) * 0.5;
        this.speedY = (Math.random() - 0.5) * 0.5;
        this.opacity = Math.random() * 0.5 + 0.1;
      }

      update() {
        this.x += this.speedX;
        this.y += this.speedY;

        if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
        if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
      }

      draw() {
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(212, 175, 55, ${this.opacity})`;
        ctx.fill();
      }
    }

    for (let i = 0; i < particleCount; i++) {
      particles.push(new Particle());
    }

    function animate() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);

      particles.forEach(p => {
        p.update();
        p.draw();
      });

      requestAnimationFrame(animate);
    }

    window.addEventListener('resize', resize);
    resize();
    animate();
  }

  // ============================================================
  // SMOOTH NAVBAR ON SCROLL
  // ============================================================
  function initNavbarScroll() {
    const header = document.querySelector('.premium-header');
    if (!header) return;

    let lastScrollY = window.scrollY;
    let ticking = false;

    const updateNavbar = () => {
      const scrollY = window.scrollY;

      if (scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }

      // Keep header stably fixed at top without scroll jump
      header.style.transform = 'translateY(0)';

      lastScrollY = scrollY;
      ticking = false;
    };

    window.addEventListener(
      'scroll',
      () => {
        if (!ticking) {
          requestAnimationFrame(updateNavbar);
          ticking = true;
        }
      },
      { passive: true }
    );
  }

  // ============================================================
  // INITIALIZATION
  // ============================================================
  function init() {
    // Initialize all animations
    initScrollReveal();
    initTiltEffect();
    initParallax();
    initButtonGlow();
    initImageZoom();
    initSmoothScroll();
    initLazyLoad();
    initTypedText();
    initParticles();
    initNavbarScroll();
    initPageTransitions();

    console.log('🎨 Premium Animations initialized');
  }

  // ============================================================
  // REVEAL ELEMENTS ON LOAD
  // ============================================================
  function revealOnLoad() {
    // Reveal all premium-reveal elements in viewport immediately
    document.querySelectorAll('.premium-reveal, .scroll-reveal').forEach(el => {
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight + 50) {
        el.classList.add('revealed');
        el.classList.add('visible');
      }
    });
    // Trigger scroll observer for elements below fold
    window.dispatchEvent(new Event('scroll'));
  }

  // ============================================================
  // INITIALIZATION — run reveal at the right time
  // ============================================================
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      init();
      revealOnLoad();
    });
  } else {
    init();
    // DOM already ready — reveal immediately
    revealOnLoad();
  }
  // Safety net: also reveal on window load (after all assets)
  window.addEventListener('load', revealOnLoad);
})();
