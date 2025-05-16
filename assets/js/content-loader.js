document.addEventListener('DOMContentLoaded', function() {
    // Initialize loading states for dynamic content
    const loadingStates = {
        show: function(element) {
            element.classList.add('loading');
            const loader = document.createElement('div');
            loader.className = 'loader';
            element.appendChild(loader);
        },
        hide: function(element) {
            element.classList.remove('loading');
            const loader = element.querySelector('.loader');
            if (loader) loader.remove();
        }
    };

    // Handle dynamic content loading
    document.querySelectorAll('[data-load-content]').forEach(element => {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('data-target');
            const url = this.getAttribute('data-load-content');
            const targetElement = document.querySelector(target);

            if (targetElement && url) {
                loadContent(url, targetElement);
            }
        });
    });

    // Handle infinite scroll for paginated content
    document.querySelectorAll('[data-infinite-scroll]').forEach(container => {
        let page = 1;
        let loading = false;
        const url = container.getAttribute('data-infinite-scroll');

        window.addEventListener('scroll', function() {
            if (loading) return;

            const rect = container.getBoundingClientRect();
            const bottomOffset = rect.bottom - window.innerHeight;

            if (bottomOffset < 100) {
                loading = true;
                page++;
                loadMore(url, page, container);
            }
        });
    });

    // Load content via AJAX
    function loadContent(url, targetElement) {
        loadingStates.show(targetElement);

        fetch(url)
            .then(response => response.text())
            .then(html => {
                targetElement.innerHTML = html;
                initializeNewContent(targetElement);
            })
            .catch(error => {
                console.error('Error loading content:', error);
                targetElement.innerHTML = '<div class="error">Failed to load content. Please try again.</div>';
            })
            .finally(() => {
                loadingStates.hide(targetElement);
            });
    }

    // Load more content for infinite scroll
    function loadMore(url, page, container) {
        const loadingElement = document.createElement('div');
        loadingElement.className = 'loading-more';
        container.appendChild(loadingElement);

        fetch(`${url}?page=${page}`)
            .then(response => response.text())
            .then(html => {
                loadingElement.remove();
                if (html.trim()) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const newContent = tempDiv.children;
                    Array.from(newContent).forEach(child => {
                        container.appendChild(child);
                    });
                    initializeNewContent(container);
                    loading = false;
                }
            })
            .catch(error => {
                console.error('Error loading more content:', error);
                loadingElement.innerHTML = '<div class="error">Failed to load more content. Please try again.</div>';
                loading = false;
            });
    }

    // Initialize dynamic content features
    function initializeNewContent(container) {
        // Re-initialize any necessary features for new content
        const scrollableElements = container.querySelectorAll('.scrollable');
        scrollableElements.forEach(element => {
            // Check if PerfectScrollbar is available
            if (typeof PerfectScrollbar !== 'undefined') {
                // Destroy existing instance if any
                if (element.perfectScrollbar) {
                    element.perfectScrollbar.destroy();
                }
                // Create new instance
                element.perfectScrollbar = new PerfectScrollbar(element, {
                    wheelSpeed: 2,
                    wheelPropagation: true,
                    minScrollbarLength: 20
                });
            }
        });

        // Re-initialize other dynamic features as needed
        initializeEventListeners(container);
    }

    // Initialize event listeners for dynamic content
    function initializeEventListeners(container) {
        // Add any additional event listeners for dynamic content here
        const clickableElements = container.querySelectorAll('[data-action]');
        clickableElements.forEach(element => {
            element.addEventListener('click', handleDynamicAction);
        });
    }

    // Handle dynamic actions
    function handleDynamicAction(event) {
        const action = event.currentTarget.getAttribute('data-action');
        if (action) {
            // Handle different actions based on the data-action attribute
            switch(action) {
                case 'refresh':
                    // Handle refresh action
                    break;
                // Add more cases as needed
            }
        }
    }
});