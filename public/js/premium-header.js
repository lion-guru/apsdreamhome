document.addEventListener('DOMContentLoaded', () => {
    const collapse = document.getElementById('mainNavbar');
    if (!collapse) {
        return;
    }

    const toggler = document.querySelector('.premium-navbar .navbar-toggler');
    const overlay = document.getElementById('mobileMenuOverlay');
    const navLinks = collapse.querySelectorAll('.nav-link');
    const dropdownToggles = collapse.querySelectorAll('.dropdown-toggle');
    const dropdownItems = collapse.querySelectorAll('.dropdown');

    const isDesktop = () => window.matchMedia('(min-width: 992px)').matches;

    const resetDropdown = (toggle) => {
        const menu = toggle.nextElementSibling;
        if (!menu) {
            return;
        }

        menu.classList.remove('show');
        menu.style.removeProperty('maxHeight');
        toggle.setAttribute('aria-expanded', 'false');
    };

    const resetAllDropdowns = () => {
        dropdownToggles.forEach(resetDropdown);
    };

    const openDropdown = (toggle) => {
        const menu = toggle.nextElementSibling;
        if (!menu) {
            return;
        }

        toggle.setAttribute('aria-expanded', 'true');
        menu.classList.add('show');
        menu.style.maxHeight = `${menu.scrollHeight}px`;
    };

    const closeMenu = ({ immediate = false } = {}) => {
        overlay?.classList.remove('active');
        document.body.style.removeProperty('overflow');
        collapse.classList.remove('premium-nav-open', 'closing');

        const instance = bootstrap.Collapse.getInstance(collapse);
        if (instance) {
            instance.hide();
        } else {
            collapse.classList.remove('show');
        }

        if (immediate) {
            collapse.classList.remove('collapsing');
        }
    };

    collapse.addEventListener('show.bs.collapse', () => {
        collapse.classList.add('premium-nav-open');
        collapse.classList.remove('closing');
        overlay?.classList.add('active');
        document.body.style.setProperty('overflow', 'hidden');
    });

    const dropdownLinks = collapse.querySelectorAll('.dropdown-menu a');
    dropdownLinks.forEach((link) => {
        link.addEventListener('click', () => {
            if (isDesktop()) {
                return;
            }

            closeMenu({ immediate: true });
        });
    });

    collapse.addEventListener('hide.bs.collapse', () => {
        collapse.classList.add('closing');
    });

    collapse.addEventListener('hidden.bs.collapse', () => {
        collapse.classList.remove('closing', 'premium-nav-open');
        overlay?.classList.remove('active');
        document.body.style.removeProperty('overflow');
        resetAllDropdowns();
    });

    if (overlay) {
        overlay.addEventListener('click', () => closeMenu({ immediate: true }));
    }

    if (toggler) {
        toggler.addEventListener('click', () => {
            if (!bootstrap.Collapse.getInstance(collapse)) {
                bootstrap.Collapse.getOrCreateInstance(collapse, { toggle: false });
            }
        });
    }

    navLinks.forEach((link) => {
        link.addEventListener('click', () => {
            if (isDesktop()) {
                return;
            }

            if (!link.classList.contains('dropdown-toggle')) {
                closeMenu({ immediate: true });
            }
        });
    });

    dropdownToggles.forEach((toggle) => {
        toggle.addEventListener('click', (event) => {
            if (isDesktop()) {
                // Prevent scrolling to top when toggle has href="#"
                const href = toggle.getAttribute('href');
                if (href && href.trim() === '#') {
                    event.preventDefault();
                }
                return;
            }

            const isOpen = toggle.getAttribute('aria-expanded') === 'true';
            const href = toggle.getAttribute('href');

            if (isOpen && href && href.trim() !== '#') {
                event.preventDefault();
                closeMenu({ immediate: true });
                window.location.href = href;
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            dropdownToggles.forEach((otherToggle) => {
                if (otherToggle !== toggle) {
                    resetDropdown(otherToggle);
                }
            });

            if (isOpen) {
                resetDropdown(toggle);
            } else {
                openDropdown(toggle);
            }
        });
    });

    const showDropdownDesktop = (toggle) => {
        const instance = bootstrap.Dropdown.getOrCreateInstance(toggle, { autoClose: true });
        instance.show();
        toggle.setAttribute('aria-expanded', 'true');
        toggle.nextElementSibling?.classList.add('show');
    };

    const hideDropdownDesktop = (toggle) => {
        const instance = bootstrap.Dropdown.getOrCreateInstance(toggle, { autoClose: true });
        instance.hide();
        toggle.setAttribute('aria-expanded', 'false');
        toggle.nextElementSibling?.classList.remove('show');
    };

    dropdownItems.forEach((dropdown) => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        if (!toggle) {
            return;
        }

        let hideTimeout;

        dropdown.addEventListener('mouseenter', () => {
            if (!isDesktop()) {
                return;
            }

            clearTimeout(hideTimeout);
            showDropdownDesktop(toggle);
        });

        dropdown.addEventListener('mouseleave', () => {
            if (!isDesktop()) {
                return;
            }

            hideTimeout = setTimeout(() => hideDropdownDesktop(toggle), 120);
        });

        dropdown.addEventListener('click', (event) => {
            if (!isDesktop()) {
                return;
            }

            const href = toggle.getAttribute('href');
            if (href && href.trim() === '#') {
                event.preventDefault();
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (isDesktop()) {
            return;
        }

        if (!event.target.closest('.premium-navbar')) {
            closeMenu();
        }
    });

    window.addEventListener('resize', () => {
        if (isDesktop()) {
            resetAllDropdowns();
            dropdownToggles.forEach((toggle) => toggle.nextElementSibling?.style.removeProperty('maxHeight'));
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
});
