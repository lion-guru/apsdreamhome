
// Theme Switcher
class ThemeSwitcher {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'light';
        this.init();
    }
    
    init() {
        this.applyTheme(this.currentTheme);
        this.createThemeToggle();
        this.setupEventListeners();
    }
    
    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        this.currentTheme = theme;
        this.updateToggleIcon();
    }
    
    toggleTheme() {
        const newTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(newTheme);
    }
    
    createThemeToggle() {
        const toggle = document.createElement('button');
        toggle.id = 'theme-toggle';
        toggle.className = 'btn btn-outline-secondary btn-sm';
        toggle.innerHTML = '<i class="fas fa-moon"></i>';
        toggle.style.position = 'fixed';
        toggle.style.top = '20px';
        toggle.style.right = '20px';
        toggle.style.zIndex = '1000';
        toggle.title = 'Toggle dark mode';
        
        document.body.appendChild(toggle);
    }
    
    updateToggleIcon() {
        const toggle = document.getElementById('theme-toggle');
        if (toggle) {
            const icon = this.currentTheme === 'light' ? 'fa-moon' : 'fa-sun';
            toggle.innerHTML = `<i class="fas "></i>`;
        }
    }
    
    setupEventListeners() {
        const toggle = document.getElementById('theme-toggle');
        if (toggle) {
            toggle.addEventListener('click', () => this.toggleTheme());
        }
        
        // Listen for system theme changes
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                    if (!localStorage.getItem('theme')) {
                        this.applyTheme(e.matches ? 'dark' : 'light');
                    }
                });
        }
    }
}

// Initialize theme switcher
document.addEventListener('DOMContentLoaded', () => {
    new ThemeSwitcher();
});
