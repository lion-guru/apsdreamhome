/**
 * APS Dream Home — Image Gallery Lightbox v2.0
 *
 * Improved version with:
 *  - Full-screen API support
 *  - Pinch-to-zoom on mobile
 *  - Loading spinner + error handling for broken images
 *  - Preloading adjacent images
 *  - Smooth CSS transitions & animations
 *  - Share button (Web Share API / clipboard fallback)
 *  - Responsive thumbnail strip
 *  - Keyboard shortcuts overlay
 *  - Image counter with dots
 *  - Swipe gesture refinement (threshold + momentum)
 *  - Touch zoom with multi-touch
 */
(function () {
  'use strict';

  const SELECTOR = '[data-gallery] img, [data-gallery] .thumb, .property-image, .lightbox-trigger';

  class ImageGallery {
    constructor() {
      this.images = [];
      this.current = 0;
      this.zoom = 1;
      this.slideshow = null;
      this.slideshowDelay = 4000;
      this.touchStartX = 0;
      this.touchStartY = 0;
      this.touchStartTime = 0;
      this.pinchStartDist = 0;
      this.pinchStartZoom = 1;
      this.isOpen = false;
      this.overlay = null;
      this.isDragging = false;
      this.dragOffset = { x: 0, y: 0 };
      this.init();
    }

    init() {
      this.collectImages();
      this.bindDocumentClicks();
      this.bindKeyboard();
    }

    collectImages() {
      document.querySelectorAll('[data-gallery]').forEach(group => {
        const imgs = group.querySelectorAll('img');
        imgs.forEach((img, idx) => {
          img.style.cursor = 'zoom-in';
          img.dataset.galleryGroup = group.dataset.gallery;
          img.dataset.galleryIndex = idx;
        });
      });
    }

    bindDocumentClicks() {
      document.addEventListener('click', e => {
        const target = e.target.closest(
          'img[data-gallery-group], .lightbox-trigger, [data-gallery] img, [data-gallery] .thumb, .property-image'
        );
        if (!target) return;
        e.preventDefault();
        this.openGroup(target);
      });
    }

    openGroup(target) {
      const group = target.dataset.galleryGroup;
      if (!group) {
        this.images = [{ src: target.src || target.dataset.src, caption: target.alt || target.title || '' }];
        this.current = 0;
      } else {
        const groupEl = document.querySelector('[data-gallery="' + group + '"]');
        this.images = [];
        if (groupEl) {
          groupEl.querySelectorAll('img').forEach(img => {
            const fullSrc = img.dataset.full || img.src;
            this.images.push({ src: fullSrc, caption: img.alt || img.dataset.caption || img.title || '' });
          });
        }
        this.current = parseInt(target.dataset.galleryIndex || '0', 10);
      }
      if (this.images.length === 0) return;
      this.build();
      this.show();
    }

    bindKeyboard() {
      this._keyHandler = e => {
        if (!this.isOpen) return;
        switch (e.key) {
          case 'Escape':
            this.hide();
            break;
          case 'ArrowRight':
            e.preventDefault();
            this.next();
            break;
          case 'ArrowLeft':
            e.preventDefault();
            this.prev();
            break;
          case '+':
          case '=':
            e.preventDefault();
            this.zoomIn();
            break;
          case '-':
            e.preventDefault();
            this.zoomOut();
            break;
          case '0':
            e.preventDefault();
            this.resetZoom();
            break;
          case 'f':
            e.preventDefault();
            this.toggleFullScreen();
            break;
          case ' ':
            e.preventDefault();
            this.toggleSlideshow();
            break;
        }
      };
      document.addEventListener('keydown', this._keyHandler);
    }

    build() {
      if (this.overlay) this.overlay.remove();

      this.overlay = document.createElement('div');
      this.overlay.className = 'image-lightbox';
      this.overlay.innerHTML = `
                <div class="lightbox-backdrop"></div>
                <button type="button" class="lightbox-close" aria-label="Close">&times;</button>
                <button type="button" class="lightbox-nav lightbox-prev" aria-label="Previous">&#10094;</button>
                <button type="button" class="lightbox-nav lightbox-next" aria-label="Next">&#10095;</button>
                <div class="lightbox-stage">
                    <div class="lightbox-spinner"><div class="spinner"></div></div>
                    <img class="lightbox-image" alt="" draggable="false">
                </div>
                <div class="lightbox-caption"></div>
                <div class="lightbox-controls">
                    <button type="button" class="lightbox-btn" data-action="zoom-out" title="Zoom out (-)"><i class="fas fa-search-minus"></i></button>
                    <button type="button" class="lightbox-btn" data-action="zoom-in" title="Zoom in (+)"><i class="fas fa-search-plus"></i></button>
                    <button type="button" class="lightbox-btn" data-action="reset" title="Reset zoom (0)"><i class="fas fa-expand"></i></button>
                    <button type="button" class="lightbox-btn" data-action="fullscreen" title="Full screen (F)"><i class="fas fa-expand-arrows-alt"></i></button>
                    <button type="button" class="lightbox-btn" data-action="play" title="Slideshow (Space)"><i class="fas fa-play"></i></button>
                    <button type="button" class="lightbox-btn" data-action="share" title="Share"><i class="fas fa-share-alt"></i></button>
                    <button type="button" class="lightbox-btn" data-action="download" title="Download"><i class="fas fa-download"></i></button>
                </div>
                <div class="lightbox-counter"></div>
                <div class="lightbox-dots"></div>
                <div class="lightbox-thumbs"></div>
                <div class="lightbox-help">
                    <span>&#8592;&#8594; Navigate</span>
                    <span>+/- Zoom</span>
                    <span>F Fullscreen</span>
                    <span>Space Slideshow</span>
                    <span>Esc Close</span>
                </div>
            `;
      document.body.appendChild(this.overlay);

      // Bind events
      const backdrop = this.overlay.querySelector('.lightbox-backdrop');
      if (backdrop) backdrop.addEventListener('click', () => this.hide());
      const closeBtn = this.overlay.querySelector('.lightbox-close');
      if (closeBtn) closeBtn.addEventListener('click', () => this.hide());
      const prevBtn = this.overlay.querySelector('.lightbox-prev');
      if (prevBtn) prevBtn.addEventListener('click', () => this.prev());
      const nextBtn = this.overlay.querySelector('.lightbox-next');
      if (nextBtn) nextBtn.addEventListener('click', () => this.next());

      // Image load events
      const imgEl = this.overlay.querySelector('.lightbox-image');
      const spinner = this.overlay.querySelector('.lightbox-spinner');

      if (imgEl) {
        imgEl.addEventListener('load', () => {
          if (spinner) spinner.hidden = true;
          imgEl.style.opacity = '1';
          this.preloadAdjacent();
        });
        imgEl.addEventListener('error', () => {
          if (spinner)
            spinner.innerHTML = '<i class="fas fa-exclamation-triangle" style="font-size:2rem;color:#999"></i>';
        });

        // Click to zoom
        imgEl.addEventListener('click', e => {
          if (e.detail === 1) {
            // Single click — zoom in/out on desktop
            if (!('ontouchstart' in window)) {
              this.zoom = this.zoom === 1 ? 2 : 1;
              this.applyZoom();
            }
          }
        });
        imgEl.addEventListener('dblclick', e => {
          if (!('ontouchstart' in window)) {
            this.zoom = this.zoom === 2 ? 1 : 2;
            this.applyZoom();
          }
        });
      }

      // Control buttons
      this.overlay.querySelectorAll('.lightbox-btn').forEach(btn => {
        btn.addEventListener('click', () => this.handleAction(btn.dataset.action));
      });

      // Touch gestures
      this.bindTouch();
    }

    bindTouch() {
      const stage = this.overlay ? this.overlay.querySelector('.lightbox-stage') : null;
      if (!stage) return;
      let initialDistance = 0;
      let initialZoom = 1;

      // Swipe detection
      stage.addEventListener(
        'touchstart',
        e => {
          if (e.touches.length === 1) {
            this.touchStartX = e.touches[0].clientX;
            this.touchStartY = e.touches[0].clientY;
            this.touchStartTime = Date.now();
          }
          // Pinch start
          if (e.touches.length === 2) {
            initialDistance = this.getTouchDistance(e.touches);
            initialZoom = this.zoom;
          }
        },
        { passive: true }
      );

      stage.addEventListener(
        'touchmove',
        e => {
          // Pinch zoom
          if (e.touches.length === 2) {
            const dist = this.getTouchDistance(e.touches);
            const scale = dist / initialDistance;
            this.zoom = Math.max(0.5, Math.min(4, initialZoom * scale));
            this.applyZoom();
            e.preventDefault();
          }
        },
        { passive: false }
      );

      stage.addEventListener(
        'touchend',
        e => {
          if (e.changedTouches.length === 1 && initialDistance === 0) {
            const dx = this.touchStartX - e.changedTouches[0].clientX;
            const dy = this.touchStartY - e.changedTouches[0].clientY;
            const dt = Date.now() - this.touchStartTime;
            const absDx = Math.abs(dx);
            const absDy = Math.abs(dy);

            // Swipe: fast or long enough, mostly horizontal
            if (absDx > 40 && absDx > absDy * 1.5 && dt < 500) {
              if (dx > 0) this.next();
              else this.prev();
            }
            // Quick tap — toggle controls visibility
            else if (absDx < 10 && absDy < 10 && dt < 200) {
              this.toggleControls();
            }
          }
          initialDistance = 0;
        },
        { passive: true }
      );
    }

    getTouchDistance(touches) {
      const dx = touches[0].clientX - touches[1].clientX;
      const dy = touches[0].clientY - touches[1].clientY;
      return Math.sqrt(dx * dx + dy * dy);
    }

    toggleControls() {
      if (!this.overlay) return;
      const controls = this.overlay.querySelector('.lightbox-controls');
      const help = this.overlay.querySelector('.lightbox-help');
      const thumbs = this.overlay.querySelector('.lightbox-thumbs');
      if (controls) controls.classList.toggle('hidden');
      if (help) help.classList.toggle('hidden');
      if (thumbs) thumbs.classList.toggle('hidden');
    }

    handleAction(action) {
      switch (action) {
        case 'zoom-in':
          this.zoomIn();
          break;
        case 'zoom-out':
          this.zoomOut();
          break;
        case 'reset':
          this.resetZoom();
          break;
        case 'fullscreen':
          this.toggleFullScreen();
          break;
        case 'play':
          this.toggleSlideshow();
          break;
        case 'share':
          this.share();
          break;
        case 'download':
          this.download();
          break;
      }
    }

    zoomIn() {
      this.zoom = Math.min(this.zoom + 0.5, 4);
      this.applyZoom();
    }
    zoomOut() {
      this.zoom = Math.max(this.zoom - 0.5, 0.5);
      this.applyZoom();
    }
    resetZoom() {
      this.zoom = 1;
      this.applyZoom();
    }

    applyZoom() {
      const img = this.overlay ? this.overlay.querySelector('.lightbox-image') : null;
      if (!img) return;
      img.style.transform = `scale(${this.zoom})`;
      img.style.transition = 'transform 0.2s ease';
      // Update reset button label
      const resetBtn = this.overlay.querySelector('[data-action="reset"]');
      if (resetBtn) resetBtn.title = `Reset zoom (${this.zoom.toFixed(1)}x) — Press 0`;
    }

    toggleFullScreen() {
      if (!document.fullscreenElement) {
        (this.overlay || document.documentElement).requestFullscreen().catch(() => {});
      } else {
        document.exitFullscreen().catch(() => {});
      }
    }

    toggleSlideshow() {
      const btn = this.overlay ? this.overlay.querySelector('[data-action="play"]') : null;
      if (this.slideshow) {
        clearInterval(this.slideshow);
        this.slideshow = null;
        if (btn) btn.innerHTML = '<i class="fas fa-play"></i>';
      } else {
        if (btn) btn.innerHTML = '<i class="fas fa-pause"></i>';
        this.slideshow = setInterval(() => this.next(), this.slideshowDelay);
      }
    }

    download() {
      const img = this.images[this.current];
      if (!img) return;
      const a = document.createElement('a');
      a.href = img.src;
      a.download = img.caption || 'image-' + (this.current + 1);
      a.target = '_blank';
      a.rel = 'noopener';
      document.body.appendChild(a);
      a.click();
      a.remove();
    }

    async share() {
      const img = this.images[this.current];
      if (!img) return;
      if (navigator.share) {
        try {
          await navigator.share({ title: img.caption || 'Image', url: img.src });
        } catch (_) {}
      } else {
        // Clipboard fallback
        try {
          await navigator.clipboard.writeText(img.src);
          this.showToast('Link copied to clipboard');
        } catch (_) {
          this.showToast('Could not copy link');
        }
      }
    }

    showToast(msg) {
      const toast = document.createElement('div');
      toast.className = 'lightbox-toast';
      toast.textContent = msg;
      document.body.appendChild(toast);
      requestAnimationFrame(() => toast.classList.add('show'));
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }, 2000);
    }

    /* ─── Preloading ─── */
    preloadAdjacent() {
      const preload = idx => {
        if (idx < 0 || idx >= this.images.length) return;
        const img = new Image();
        img.src = this.images[idx].src;
      };
      preload(this.current - 1);
      preload(this.current + 1);
    }

    /* ─── Show / Hide ─── */
    show() {
      this.isOpen = true;
      document.body.classList.add('lightbox-open');
      this.overlay.classList.add('active');
      this.render();
    }

    hide() {
      this.isOpen = false;
      document.body.classList.remove('lightbox-open');
      if (this.overlay) this.overlay.classList.remove('active');
      if (this.slideshow) {
        clearInterval(this.slideshow);
        this.slideshow = null;
      }
      this.zoom = 1;
      // Exit fullscreen
      if (document.fullscreenElement) document.exitFullscreen().catch(() => {});
      setTimeout(() => {
        if (this.overlay && !this.isOpen) {
          this.overlay.remove();
          this.overlay = null;
        }
      }, 300);
    }

    /* ─── Navigation ─── */
    next() {
      this.current = (this.current + 1) % this.images.length;
      this.render();
    }

    prev() {
      this.current = (this.current - 1 + this.images.length) % this.images.length;
      this.render();
    }

    goTo(idx) {
      if (idx < 0 || idx >= this.images.length) return;
      this.current = idx;
      this.render();
    }

    render() {
      if (!this.overlay) return;
      const img = this.images[this.current];
      if (!img) return;
      const imgEl = this.overlay.querySelector('.lightbox-image');
      const spinner = this.overlay.querySelector('.lightbox-spinner');

      // Show spinner while loading
      if (spinner) {
        spinner.hidden = false;
        spinner.innerHTML = '<div class="spinner"></div>';
      }
      if (imgEl) {
        imgEl.style.opacity = '0';
        imgEl.src = img.src;
        imgEl.alt = img.caption;
      }

      const caption = this.overlay.querySelector('.lightbox-caption');
      if (caption) caption.textContent = img.caption || '';
      const counter = this.overlay.querySelector('.lightbox-counter');
      if (counter) counter.textContent = this.current + 1 + ' / ' + this.images.length;
      this.resetZoom();

      // Render dots
      this.renderDots();

      // Render thumbnails
      this.renderThumbs();
    }

    renderDots() {
      const dots = this.overlay.querySelector('.lightbox-dots');
      if (!dots) return;
      dots.innerHTML = '';
      this.images.forEach((_, idx) => {
        const dot = document.createElement('button');
        dot.className = 'lightbox-dot' + (idx === this.current ? ' active' : '');
        dot.setAttribute('aria-label', 'Go to image ' + (idx + 1));
        dot.addEventListener('click', () => this.goTo(idx));
        dots.appendChild(dot);
      });
    }

    renderThumbs() {
      const thumbs = this.overlay.querySelector('.lightbox-thumbs');
      if (!thumbs) return;
      thumbs.innerHTML = '';
      this.images.forEach((entry, idx) => {
        const t = document.createElement('img');
        t.src = entry.src;
        t.alt = entry.caption;
        t.className = idx === this.current ? 'active' : '';
        t.loading = 'lazy';
        t.addEventListener('click', () => this.goTo(idx));
        thumbs.appendChild(t);
      });
      // Scroll active thumbnail into view
      const active = thumbs.querySelector('.active');
      if (active) active.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
  }

  /* ─── Init ─── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      window.imageGallery = new ImageGallery();
    });
  } else {
    window.imageGallery = new ImageGallery();
  }
})();
