// sidebar_slider.js
// Enables vertical scrolling for sidebar if content overflows

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('aside');
    if (sidebar) {
        sidebar.style.overflowY = 'auto';
        sidebar.style.maxHeight = '100vh';
    }
});
