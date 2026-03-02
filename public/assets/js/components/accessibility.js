// Accessibility Components
class AccessibilityManager {
    constructor() {
        this.init();
    }
    
    init() {
        this.setupKeyboardNavigation();
        this.setupScreenReaderSupport();
        this.setupFocusManagement();
        this.setupAriaLabels();
        this.setupColorContrast();
        this.setupReducedMotion();
        this.setupHighContrast();
    }
    
    setupKeyboardNavigation() {
        // Add keyboard navigation to interactive elements
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                this.handleTabNavigation(e);
            } else if (e.key === 'Enter' || e.key === ' ') {
                this.handleActivation(e);
            } else if (e.key === 'Escape') {
                this.handleEscape(e);
            }
        });
        
        // Add focus indicators
        this.addFocusIndicators();
    }
    
    handleTabNavigation(e) {
        // Ensure focus is visible
        document.body.classList.add('keyboard-navigation');
        
        // Remove class when mouse is used
        setTimeout(() => {
            document.body.classList.remove('keyboard-navigation');
        }, 100);
    }
    
    handleActivation(e) {
        const target = e.target;
        if (target.tagName === 'BUTTON' || target.tagName === 'A' || target.role === 'button') {
            target.click();
        }
    }
    
    handleEscape(e) {
        // Close modals, dropdowns, etc.
        this.closeModals();
        this.closeDropdowns();
    }
    
    addFocusIndicators() {
        const style = document.createElement('style');
        style.textContent = `
            .keyboard-navigation *:focus {
                outline: 2px solid #2563eb !important;
                outline-offset: 2px !important;
            }
            
            *:focus {
                outline: 2px solid transparent;
                outline-offset: 2px;
            }
            
            *:focus:not(:focus-visible) {
                outline: none;
            }
            
            *:focus-visible {
                outline: 2px solid #2563eb;
                outline-offset: 2px;
            }
        `;
        document.head.appendChild(style);
    }
    
    setupScreenReaderSupport() {
        // Add ARIA live regions
        this.createLiveRegion();
        
        // Add screen reader announcements
        this.setupAnnouncements();
        
        // Add proper semantic structure
        this.enhanceSemanticStructure();
    }
    
    createLiveRegion() {
        const liveRegion = document.createElement('div');
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.className = 'sr-only';
        liveRegion.style.position = 'absolute';
        liveRegion.style.left = '-10000px';
        liveRegion.style.width = '1px';
        liveRegion.style.height = '1px';
        liveRegion.style.overflow = 'hidden';
        
        document.body.appendChild(liveRegion);
        this.liveRegion = liveRegion;
    }
    
    announce(message) {
        if (this.liveRegion) {
            this.liveRegion.textContent = message;
            
            // Clear after announcement
            setTimeout(() => {
                this.liveRegion.textContent = '';
            }, 1000);
        }
    }
    
    setupAnnouncements() {
        // Announce page changes
        this.announcePageChanges();
        
        // Announce form errors
        this.announceFormErrors();
        
        // Announce loading states
        this.announceLoadingStates();
    }
    
    announcePageChanges() {
        // Announce when page loads
        window.addEventListener('load', () => {
            const title = document.title;
            this.announce(`${title} page loaded`);
        });
        
        // Announce navigation
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            const heading = node.querySelector('h1, h2, h3');
                            if (heading) {
                                this.announce(`Navigated to ${heading.textContent}`);
                            }
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    announceFormErrors() {
        // Monitor form submissions
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const errors = form.querySelectorAll('.error, .invalid');
            
            if (errors.length > 0) {
                setTimeout(() => {
                    this.announce(`Form has ${errors.length} error${errors.length > 1 ? 's' : ''}`);
                }, 100);
            }
        });
    }
    
    announceLoadingStates() {
        // Monitor loading indicators
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (node.classList.contains('loading') || node.classList.contains('spinner')) {
                            this.announce('Loading content');
                        }
                    }
                });
                
                mutation.removedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (node.classList.contains('loading') || node.classList.contains('spinner')) {
                            this.announce('Content loaded');
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    enhanceSemanticStructure() {
        // Add proper headings structure
        this.addSkipLinks();
        
        // Add landmarks
        this.addLandmarks();
        
        // Add proper labels
        this.addProperLabels();
    }
    
    addSkipLinks() {
        const skipLinks = document.createElement('div');
        skipLinks.className = 'skip-links';
        skipLinks.innerHTML = `
            <a href="#main-content" class="skip-link">Skip to main content</a>
            <a href="#navigation" class="skip-link">Skip to navigation</a>
        `;
        
        const style = document.createElement('style');
        style.textContent = `
            .skip-links {
                position: absolute;
                top: -40px;
                left: 0;
                z-index: 10000;
            }
            
            .skip-link {
                position: absolute;
                top: -40px;
                left: 0;
                background: #2563eb;
                color: white;
                padding: 8px;
                text-decoration: none;
                border-radius: 0 0 4px 4px;
            }
            
            .skip-link:focus {
                top: 0;
            }
        `;
        
        document.head.appendChild(style);
        document.body.insertBefore(skipLinks, document.body.firstChild);
    }
    
    addLandmarks() {
        // Add ARIA landmarks to existing elements
        const main = document.querySelector('main') || document.querySelector('[role="main"]');
        if (main && !main.hasAttribute('role')) {
            main.setAttribute('role', 'main');
        }
        
        const nav = document.querySelector('nav') || document.querySelector('[role="navigation"]');
        if (nav && !nav.hasAttribute('role')) {
            nav.setAttribute('role', 'navigation');
        }
        
        const header = document.querySelector('header') || document.querySelector('[role="banner"]');
        if (header && !header.hasAttribute('role')) {
            header.setAttribute('role', 'banner');
        }
        
        const footer = document.querySelector('footer') || document.querySelector('[role="contentinfo"]');
        if (footer && !footer.hasAttribute('role')) {
            footer.setAttribute('role', 'contentinfo');
        }
    }
    
    addProperLabels() {
        // Add labels to form elements
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            if (!input.hasAttribute('aria-label') && !input.hasAttribute('aria-labelledby')) {
                const label = document.querySelector(`label[for="${input.id}"]`);
                if (label) {
                    input.setAttribute('aria-labelledby', label.id);
                } else {
                    const placeholder = input.getAttribute('placeholder');
                    if (placeholder) {
                        input.setAttribute('aria-label', placeholder);
                    }
                }
            }
        });
        
        // Add labels to buttons
        const buttons = document.querySelectorAll('button');
        buttons.forEach(button => {
            if (!button.hasAttribute('aria-label') && !button.textContent.trim()) {
                const icon = button.querySelector('svg, i');
                if (icon) {
                    const iconClass = icon.className || icon.getAttribute('class');
                    button.setAttribute('aria-label', this.getButtonLabel(iconClass));
                }
            }
        });
    }
    
    getButtonLabel(iconClass) {
        const labels = {
            'fa-search': 'Search',
            'fa-menu': 'Menu',
            'fa-close': 'Close',
            'fa-check': 'Check',
            'fa-plus': 'Add',
            'fa-edit': 'Edit',
            'fa-trash': 'Delete',
            'fa-download': 'Download',
            'fa-upload': 'Upload',
            'fa-print': 'Print',
            'fa-share': 'Share',
            'fa-heart': 'Like',
            'fa-star': 'Star',
            'fa-bookmark': 'Bookmark'
        };
        
        for (const [className, label] of Object.entries(labels)) {
            if (iconClass.includes(className)) {
                return label;
            }
        }
        
        return 'Button';
    }
    
    setupFocusManagement() {
        // Focus trap for modals
        this.setupFocusTrap();
        
        // Focus restoration
        this.setupFocusRestoration();
        
        // Focus management for dynamic content
        this.setupDynamicFocus();
    }
    
    setupFocusTrap() {
        const modals = document.querySelectorAll('.modal, .dialog');
        modals.forEach(modal => {
            modal.addEventListener('show', () => {
                this.trapFocus(modal);
            });
            
            modal.addEventListener('hide', () => {
                this.removeFocusTrap(modal);
            });
        });
    }
    
    trapFocus(element) {
        const focusableElements = element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        if (focusableElements.length === 0) return;
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        element.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            }
        });
        
        // Focus first element
        firstElement.focus();
    }
    
    removeFocusTrap(element) {
        // Remove focus trap event listeners
        // This would need to be implemented based on your event management system
    }
    
    setupFocusRestoration() {
        // Store last focused element before opening modal
        this.lastFocusedElement = null;
        
        document.addEventListener('click', (e) => {
            const target = e.target;
            if (target.closest('.modal-trigger, [data-modal]')) {
                this.lastFocusedElement = document.activeElement;
            }
        });
        
        // Restore focus when modal closes
        document.addEventListener('modal-closed', () => {
            if (this.lastFocusedElement) {
                this.lastFocusedElement.focus();
                this.lastFocusedElement = null;
            }
        });
    }
    
    setupDynamicFocus() {
        // Monitor dynamic content changes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Add focus management to new elements
                            this.addFocusManagement(node);
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    addFocusManagement(element) {
        // Add focus management to interactive elements
        const interactiveElements = element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        interactiveElements.forEach(el => {
            if (!el.hasAttribute('tabindex')) {
                el.setAttribute('tabindex', '0');
            }
        });
    }
    
    setupAriaLabels() {
        // Add ARIA labels to interactive elements
        this.addAriaToButtons();
        this.addAriaToLinks();
        this.addAriaToForms();
        this.addAriaToTables();
    }
    
    addAriaToButtons() {
        const buttons = document.querySelectorAll('button');
        buttons.forEach(button => {
            if (!button.hasAttribute('aria-label') && !button.textContent.trim()) {
                const icon = button.querySelector('svg, i');
                if (icon) {
                    const iconClass = icon.className || icon.getAttribute('class');
                    button.setAttribute('aria-label', this.getButtonLabel(iconClass));
                }
            }
        });
    }
    
    addAriaToLinks() {
        const links = document.querySelectorAll('a');
        links.forEach(link => {
            if (!link.hasAttribute('aria-label') && !link.textContent.trim()) {
                const icon = link.querySelector('svg, i');
                if (icon) {
                    const iconClass = icon.className || icon.getAttribute('class');
                    link.setAttribute('aria-label', this.getButtonLabel(iconClass));
                }
            }
            
            // Add external link indicator
            if (link.hostname !== window.location.hostname) {
                link.setAttribute('aria-label', `${link.getAttribute('aria-label') || link.textContent} (opens in new window)`);
            }
        });
    }
    
    addAriaToForms() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            if (!form.hasAttribute('aria-label')) {
                const title = form.querySelector('h1, h2, h3, legend');
                if (title) {
                    form.setAttribute('aria-label', title.textContent);
                }
            }
        });
    }
    
    addAriaToTables() {
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            if (!table.hasAttribute('role')) {
                table.setAttribute('role', 'table');
            }
            
            // Add caption if missing
            if (!table.querySelector('caption')) {
                const caption = document.createElement('caption');
                caption.textContent = 'Data table';
                caption.className = 'sr-only';
                table.insertBefore(caption, table.firstChild);
            }
        });
    }
    
    setupColorContrast() {
        // Check color contrast
        this.checkColorContrast();
        
        // Add high contrast mode support
        this.addHighContrastSupport();
    }
    
    checkColorContrast() {
        // This would implement color contrast checking
        // For now, just add a basic implementation
        const elements = document.querySelectorAll('*');
        elements.forEach(element => {
            const styles = window.getComputedStyle(element);
            const color = styles.color;
            const backgroundColor = styles.backgroundColor;
            
            // Basic contrast check (would need proper implementation)
            if (this.isLowContrast(color, backgroundColor)) {
                element.classList.add('low-contrast');
            }
        });
    }
    
    isLowContrast(color, backgroundColor) {
        // Simplified contrast check
        // In a real implementation, you would use proper contrast ratio calculation
        return false;
    }
    
    addHighContrastSupport() {
        // Detect high contrast mode
        if (window.matchMedia('(prefers-contrast: high)').matches) {
            document.body.classList.add('high-contrast');
        }
        
        // Listen for changes
        window.matchMedia('(prefers-contrast: high)').addEventListener('change', (e) => {
            if (e.matches) {
                document.body.classList.add('high-contrast');
            } else {
                document.body.classList.remove('high-contrast');
            }
        });
    }
    
    setupReducedMotion() {
        // Detect reduced motion preference
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.body.classList.add('reduced-motion');
        }
        
        // Listen for changes
        window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', (e) => {
            if (e.matches) {
                document.body.classList.add('reduced-motion');
            } else {
                document.body.classList.remove('reduced-motion');
            }
        });
        
        // Add reduced motion styles
        const style = document.createElement('style');
        style.textContent = `
            .reduced-motion * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        `;
        document.head.appendChild(style);
    }
    
    setupHighContrast() {
        // Add high contrast styles
        const style = document.createElement('style');
        style.textContent = `
            .high-contrast {
                background: white !important;
                color: black !important;
            }
            
            .high-contrast * {
                background: white !important;
                color: black !important;
                border-color: black !important;
            }
            
            .high-contrast img {
                filter: grayscale(100%);
            }
        `;
        document.head.appendChild(style);
    }
    
    closeModals() {
        const modals = document.querySelectorAll('.modal.show, .dialog.open');
        modals.forEach(modal => {
            modal.classList.remove('show', 'open');
        });
    }
    
    closeDropdowns() {
        const dropdowns = document.querySelectorAll('.dropdown.show, .menu.open');
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('show', 'open');
        });
    }
}

// Initialize accessibility manager
const accessibilityManager = new AccessibilityManager();

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AccessibilityManager;
}
