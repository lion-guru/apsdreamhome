// Enhanced Lazy Loading Implementation with Fade Effect
const lazyLoadImages = () => {
    const lazyImages = document.querySelectorAll('img.lazy-load');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.style.opacity = '0';
                        img.src = img.dataset.src;
                        img.onload = () => {
                            img.style.transition = 'opacity 0.5s ease-in-out';
                            img.style.opacity = '1';
                            img.classList.remove('lazy-load');
                            img.removeAttribute('data-src');
                        };
                        observer.unobserve(img);
                    }
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });

        lazyImages.forEach(img => {
            if (img.src) {
                img.dataset.src = img.src;
                img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'; // Tiny transparent placeholder
            }
            imageObserver.observe(img);
        });
    }
};

// Form Validation
const validateForm = (formElement) => {
    const inputs = formElement.querySelectorAll('input[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
            const errorMessage = document.createElement('div');
            errorMessage.className = 'error-message';
            errorMessage.textContent = 'This field is required';
            
            const existingError = input.nextElementSibling;
            if (existingError && existingError.className === 'error-message') {
                existingError.remove();
            }
            
            input.parentNode.insertBefore(errorMessage, input.nextSibling);
        }
    });

    return isValid;
};

// Special Offer Popup Functions
const showPopup = () => {
    const popup = document.getElementById('specialOfferPopup');
    if (popup) {
        popup.style.display = 'flex';
    }
};

const closePopup = () => {
    const popup = document.getElementById('specialOfferPopup');
    if (popup) {
        popup.style.display = 'none';
    }
};

$(document).ready(function() {
  // Initialize lazy loading
  lazyLoadImages();

  // Initialize form validation
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
      form.addEventListener('submit', function(e) {
          if (!validateForm(this)) {
              e.preventDefault();
          }
      });
  });

  // Show popup after 5 seconds
  setTimeout(showPopup, 5000);

  // Close popup when clicking outside
  $(document).on('click', function(event) {
      const popup = document.getElementById('specialOfferPopup');
      const popupContent = document.querySelector('.popup-content');
      
      if (popup && event.target === popup) {
          closePopup();
      }
  });

  // Smooth scrolling for navigation links
  $('a[href^="#"]').on('click', function(e) {
      e.preventDefault();
      const target = $(this.getAttribute('href'));
      if (target.length) {
          $('html, body').animate({
              scrollTop: target.offset().top - 100
          }, 800);
      }
  });

  // Dropdown menu handling
  $('.dropdown').on('hide.bs.dropdown', function() {
    $(this).find('.dropdown-menu').removeClass('show');
    $(this).find('.dropdown-toggle').attr('aria-expanded', 'false');
  });

  // Overlay handling
  $(`[unique-script-id="w-w-dm-id"] .btn-box`).click(function() {
    $(this).parent().children(".overlay").show();
  });

  $(`[unique-script-id="w-w-dm-id"] .close`).click(function() {
    $(".overlay").hide();
  });

  // Navbar dropdown handling
  $('.navbar-nav .dropdown').on('click', function() {
    $(this).toggleClass('open');
  });

  // Touchstart event handler for dropdowns
  const dropdowns = document.querySelectorAll('.dropdown');
  dropdowns.forEach((dropdown) => {
    dropdown.addEventListener('touchstart', (e) => {
      e.preventDefault();
      dropdown.classList.toggle('open');
    });
  });

  // Dropdown container and toggle button handling
  const dropdownContainer = document.querySelector('.dropdown-container');
  const dropdownToggle = document.querySelector('.dropdown-toggle');
  dropdownToggle.addEventListener('click', () => {
    dropdownContainer.classList.toggle('open');
  });
});



//gallery

var Gallery = (function() {
    var colors = ['#34495E', '#2E4053', '#283747', '#212F3C', '#1B2631', '#2C3E50', '#273746'];
    var scrollTimeId;
    var posLeft = 0;

    function Gallery(config) {
        this.list = $(config.list);
        this.items = this.list.find('li');
        this.itemWidth = this.items.outerWidth();
    };

    Gallery.prototype = {
        constructor: Gallery,

        init: function() {
            this.setGalleryWidth();
            this.setItemsColor();
            this.eventManager();

            return this;
        },

        eventManager: function() {
            var _this = this;

            $("html, body").on('mousewheel', function(event) {
                clearTimeout(scrollTimeId);
                scrollTimeId = setTimeout(onScrollEventHandler.bind(this, event, _this.itemWidth), 0);
            });
        },

        getRandomColor: function() {
            return colors[Math.floor(Math.random() * colors.length)];
        },

        setItemsColor: function() {
            var _this = this;

            $.each(this.items, function(index, item) {
                 item.style.backgroundColor = _this.getRandomColor();
            });
        },

        setGalleryWidth: function() {
            this.list.css('width', this.getGalleryWidth());
        },

        getGalleryWidth: function() {
            var width = 0;

            this.items.each(function(index, item) {
                width += $(this).outerWidth();
            });

            return width;
        }
    };

    function onScrollEventHandler(event, width) {
      if (event.deltaY > 0) {
        this.scrollLeft -= width / 2;
      } else {
        this.scrollLeft += width / 2;
      }
 
        // Firefox, please, stop it
         // if (navigator.userAgent.toLowerCase().indexOf('firefox') > -1) {
         //    if (event.originalEvent.detail > 0) {
         //        posLeft += width / 2;
         //        $('html').scrollLeft(posLeft);
         //    } else {
         //        posLeft -= width / 2;
         //        $('html').scrollLeft(posLeft);
         //    }
         // } else {
         //    if (event.originalEvent.wheelDelta > 0)  {
         //        this.body.scrollLeft -= width / 2;
         //    } else {
         //        this.body.scrollLeft += width / 2;
         //    }
         // }
        event.preventDefault();
    };

    return Gallery;
})();


$(document).ready(function() {
    var gallery = new Gallery({
        list: '.gallery'
    }).init();
});

const change = src => {
    document.getElementById('main').src = src
}




// this is for amenities slider


$(document).ready(function() {
  $(".owl-carousel").owlCarousel({
    loop: true,
    margin: 10,
    nav: true,
    autoplay: true,
    autoplayTimeout: 3000,
    autoplayHoverPause: true,
    responsive: {
      0: {
        items: 1
      },
      600: {
        items: 3
      },
      1000: {
        items: 5
      }
    }
  });
});

// Modern Lightbox Implementation
const initLightbox = () => {
    const createLightbox = () => {
        const lightbox = document.createElement('div');
        lightbox.id = 'gallery-lightbox';
        lightbox.innerHTML = `
            <div class="lightbox-content">
                <button class="lightbox-close">&times;</button>
                <button class="lightbox-prev">&lt;</button>
                <button class="lightbox-next">&gt;</button>
                <img src="" alt="Gallery image" class="lightbox-image">
            </div>
        `;
        document.body.appendChild(lightbox);
        return lightbox;
    };

    const lightbox = createLightbox();
    const lightboxImg = lightbox.querySelector('.lightbox-image');
    let currentIndex = 0;
    const galleryImages = document.querySelectorAll('.gallery img');

    const showImage = (index) => {
        currentIndex = index;
        const src = galleryImages[index].src;
        lightboxImg.style.opacity = '0';
        setTimeout(() => {
            lightboxImg.src = src;
            lightboxImg.style.opacity = '1';
        }, 200);
    };

    galleryImages.forEach((img, index) => {
        img.addEventListener('click', () => {
            lightbox.style.display = 'flex';
            showImage(index);
        });
    });

    lightbox.querySelector('.lightbox-close').addEventListener('click', () => {
        lightbox.style.display = 'none';
    });

    lightbox.querySelector('.lightbox-prev').addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + galleryImages.length) % galleryImages.length;
        showImage(currentIndex);
    });

    lightbox.querySelector('.lightbox-next').addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % galleryImages.length;
        showImage(currentIndex);
    });

    document.addEventListener('keydown', (e) => {
        if (lightbox.style.display === 'flex') {
            if (e.key === 'Escape') lightbox.style.display = 'none';
            if (e.key === 'ArrowLeft') lightbox.querySelector('.lightbox-prev').click();
            if (e.key === 'ArrowRight') lightbox.querySelector('.lightbox-next').click();
        }
    });
};

$(document).ready(function() {
    initLightbox();
});
// this is for amenities slider