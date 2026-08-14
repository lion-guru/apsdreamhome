document.addEventListener('DOMContentLoaded', function() {
    // Add lazy loading to images without it
    var images = document.querySelectorAll('img:not([loading])');
    images.forEach(function(img) {
        img.setAttribute('loading', 'lazy');
    });
    
    // Responsive table handling
    var tables = document.querySelectorAll('table');
    tables.forEach(function(table) {
        var wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    });
});

// Mobile menu toggle
function toggleMobileMenu() {
    var menu = document.getElementById('mobile-menu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}