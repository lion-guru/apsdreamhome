document.addEventListener('DOMContentLoaded', function() {
    const filterSection = document.querySelector('.filter-section');
    const filterWrapper = document.querySelector('.filter-section-wrapper');
    const header = document.querySelector('header');
    
    if (filterSection && filterWrapper && header) {
        const headerHeight = header.offsetHeight;
        const filterOffset = filterSection.offsetTop - headerHeight;
        const filterHeight = filterSection.offsetHeight;
        
        // Add margin to the next section to prevent content jump
        const nextSection = filterWrapper.nextElementSibling;
        if (nextSection) {
            nextSection.style.paddingTop = `${filterHeight}px`;
        }
        
        function updateFilterSticky() {
            if (window.pageYOffset > filterOffset) {
                filterSection.classList.add('sticky');
                filterWrapper.style.height = `${filterHeight}px`;
            } else {
                filterSection.classList.remove('sticky');
                filterWrapper.style.height = 'auto';
            }
        }
        
        // Initial check
        updateFilterSticky();
        
        // Update on scroll
        window.addEventListener('scroll', function() {
            updateFilterSticky();
        });
        
        // Update on window resize
        window.addEventListener('resize', function() {
            const newFilterHeight = filterSection.offsetHeight;
            if (nextSection) {
                nextSection.style.paddingTop = `${newFilterHeight}px`;
            }
            filterWrapper.style.height = `${newFilterHeight}px`;
        });
    }
});
