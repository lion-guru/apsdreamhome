// Infinite Scroll Component
class InfiniteScroll {
    constructor(container, options = {}) {
        this.container = container;
        this.options = {
            threshold: options.threshold || 100,
            loadingText: options.loadingText || 'Loading...',
            noMoreText: options.noMoreText || 'No more items',
            loadMore: options.loadMore || this.defaultLoadMore,
            renderItem: options.renderItem || this.defaultRenderItem,
            ...options
        };
        
        this.items = [];
        this.isLoading = false;
        this.hasMore = true;
        this.page = 1;
        
        this.init();
    }
    
    init() {
        this.container.style.position = 'relative';
        
        // Create content container
        this.contentContainer = document.createElement('div');
        this.contentContainer.className = 'infinite-scroll-content';
        this.container.appendChild(this.contentContainer);
        
        // Create loading indicator
        this.loadingIndicator = document.createElement('div');
        this.loadingIndicator.className = 'infinite-scroll-loading';
        this.loadingIndicator.style.textAlign = 'center';
        this.loadingIndicator.style.padding = '20px';
        this.loadingIndicator.style.display = 'none';
        this.loadingIndicator.innerHTML = `
            <div class="spinner"></div>
            <div>${this.options.loadingText}</div>
        `;
        this.container.appendChild(this.loadingIndicator);
        
        // Create no more indicator
        this.noMoreIndicator = document.createElement('div');
        this.noMoreIndicator.className = 'infinite-scroll-no-more';
        this.noMoreIndicator.style.textAlign = 'center';
        this.noMoreIndicator.style.padding = '20px';
        this.noMoreIndicator.style.display = 'none';
        this.noMoreIndicator.style.color = '#666';
        this.noMoreIndicator.innerHTML = this.options.noMoreText;
        this.container.appendChild(this.noMoreIndicator);
        
        // Bind scroll event
        this.container.addEventListener('scroll', this.handleScroll.bind(this));
        
        // Load initial items
        this.loadMore();
    }
    
    handleScroll() {
        if (this.isLoading || !this.hasMore) return;
        
        const { scrollTop, scrollHeight, clientHeight } = this.container;
        const distanceFromBottom = scrollHeight - (scrollTop + clientHeight);
        
        if (distanceFromBottom <= this.options.threshold) {
            this.loadMore();
        }
    }
    
    async loadMore() {
        if (this.isLoading || !this.hasMore) return;
        
        this.isLoading = true;
        this.showLoading();
        
        try {
            const newItems = await this.options.loadMore(this.page);
            
            if (newItems && newItems.length > 0) {
                this.addItems(newItems);
                this.page++;
            } else {
                this.hasMore = false;
                this.showNoMore();
            }
        } catch (error) {
            console.error('Error loading more items:', error);
            this.showError();
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }
    
    addItems(items) {
        items.forEach(item => {
            const itemElement = this.options.renderItem(item);
            this.contentContainer.appendChild(itemElement);
            this.items.push(item);
        });
    }
    
    showLoading() {
        this.loadingIndicator.style.display = 'block';
        this.noMoreIndicator.style.display = 'none';
    }
    
    hideLoading() {
        this.loadingIndicator.style.display = 'none';
    }
    
    showNoMore() {
        this.noMoreIndicator.style.display = 'block';
        this.loadingIndicator.style.display = 'none';
    }
    
    showError() {
        const errorElement = document.createElement('div');
        errorElement.className = 'infinite-scroll-error';
        errorElement.style.textAlign = 'center';
        errorElement.style.padding = '20px';
        errorElement.style.color = '#dc3545';
        errorElement.innerHTML = `
            <div>Error loading items</div>
            <button onclick="this.parentElement.parentElement.infiniteScroll.retry()" style="margin-top: 10px; padding: 8px 16px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Retry</button>
        `;
        
        this.contentContainer.appendChild(errorElement);
        
        // Auto-remove error after 5 seconds
        setTimeout(() => {
            if (errorElement.parentElement) {
                errorElement.remove();
            }
        }, 5000);
    }
    
    retry() {
        this.loadMore();
    }
    
    reset() {
        this.items = [];
        this.page = 1;
        this.hasMore = true;
        this.isLoading = false;
        this.contentContainer.innerHTML = '';
        this.hideLoading();
        this.hideNoMore();
        this.loadMore();
    }
    
    getItems() {
        return this.items;
    }
    
    getItemCount() {
        return this.items.length;
    }
    
    isLoading() {
        return this.isLoading;
    }
    
    hasMore() {
        return this.hasMore;
    }
    
    defaultLoadMore(page) {
        // Default implementation - should be overridden
        return new Promise((resolve) => {
            setTimeout(() => {
                const items = Array.from({ length: 10 }, (_, i) => ({
                    id: (page - 1) * 10 + i + 1,
                    title: `Item ${(page - 1) * 10 + i + 1}`,
                    description: `Description for item ${(page - 1) * 10 + i + 1}`
                }));
                resolve(items);
            }, 1000);
        });
    }
    
    defaultRenderItem(item) {
        const element = document.createElement('div');
        element.className = 'infinite-scroll-item';
        element.style.borderBottom = '1px solid #eee';
        element.style.padding = '16px';
        element.style.background = 'white';
        
        element.innerHTML = `
            <h3 style="margin: 0 0 8px 0; color: #333;">${item.title}</h3>
            <p style="margin: 0; color: #666;">${item.description}</p>
        `;
        
        return element;
    }
    
    hideNoMore() {
        this.noMoreIndicator.style.display = 'none';
    }
    
    destroy() {
        this.container.removeEventListener('scroll', this.handleScroll);
        this.container.innerHTML = '';
    }
}

// Add styles for loading spinner
const style = document.createElement('style');
style.textContent = `
    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 0 auto 10px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = InfiniteScroll;
}
