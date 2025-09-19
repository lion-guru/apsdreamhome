// sidebar-ajax.js
// Load main content area via AJAX when sidebar menu is clicked

document.addEventListener('DOMContentLoaded', function() {
    const sidebarLinks = document.querySelectorAll('.nav-link');
    const mainContent = document.querySelector('.main-content');
    if (!mainContent) return;

    sidebarLinks.forEach(link => {
        // Only handle local links
        if (link.getAttribute('href').startsWith('http')) return;
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const url = link.getAttribute('href');
            // Highlight active link
            sidebarLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            // Show loading spinner
            mainContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    // Extract only the main-content from the response
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const newContent = tempDiv.querySelector('.main-content');
                    if (newContent) {
                        mainContent.innerHTML = newContent.innerHTML;
                    } else {
                        mainContent.innerHTML = '<div class="alert alert-danger">Failed to load content.</div>';
                    }
                })
                .catch(() => {
                    mainContent.innerHTML = '<div class="alert alert-danger">Failed to load content.</div>';
                });
        });
    });
});
