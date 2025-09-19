document.addEventListener('DOMContentLoaded', function() {
    // Initialize perfect scrollbar for elements that need custom scrolling
    const scrollableElements = document.querySelectorAll('.scrollable');
    
    scrollableElements.forEach(function(element) {
        const ps = new PerfectScrollbar(element, {
            wheelSpeed: 2,
            wheelPropagation: true,
            minScrollbarLength: 20
        });
        
        // Update perfect scrollbar on content changes
        window.addEventListener('resize', function() {
            ps.update();
        });
    });
});