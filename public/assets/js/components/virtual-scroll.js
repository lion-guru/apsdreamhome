// Virtual Scroll Component
class VirtualScroll {
    constructor(container, options = {}) {
        this.container = container;
        this.options = {
            itemHeight: options.itemHeight || 50,
            bufferSize: options.bufferSize || 10,
            threshold: options.threshold || 100,
            renderItem: options.renderItem || this.defaultRenderItem,
            ...options
        };
        
        this.items = [];
        this.visibleItems = [];
        this.scrollTop = 0;
        this.containerHeight = 0;
        this.totalHeight = 0;
        
        this.init();
    }
    
    init() {
        this.container.style.overflow = 'auto';
        this.container.style.position = 'relative';
        
        // Create spacer elements
        this.topSpacer = document.createElement('div');
        this.topSpacer.style.position = 'absolute';
        this.topSpacer.style.top = '0';
        this.topSpacer.style.left = '0';
        this.topSpacer.style.right = '0';
        this.topSpacer.style.height = '0';
        this.topSpacer.style.pointerEvents = 'none';
        
        this.bottomSpacer = document.createElement('div');
        this.bottomSpacer.style.position = 'absolute';
        this.bottomSpacer.style.left = '0';
        this.bottomSpacer.style.right = '0';
        this.bottomSpacer.style.height = '0';
        this.bottomSpacer.style.pointerEvents = 'none';
        
        this.container.appendChild(this.topSpacer);
        this.container.appendChild(this.bottomSpacer);
        
        // Create viewport
        this.viewport = document.createElement('div');
        this.viewport.style.position = 'relative';
        this.viewport.style.top = '0';
        this.viewport.style.left = '0';
        this.viewport.style.right = '0';
        this.viewport.style.minHeight = '100%';
        
        this.container.appendChild(this.viewport);
        
        // Bind events
        this.container.addEventListener('scroll', this.handleScroll.bind(this));
        this.container.addEventListener('resize', this.handleResize.bind(this));
        
        // Initial setup
        this.updateContainerHeight();
        this.updateVisibleItems();
    }
    
    setItems(items) {
        this.items = items;
        this.totalHeight = items.length * this.options.itemHeight;
        this.updateVisibleItems();
    }
    
    handleScroll() {
        this.scrollTop = this.container.scrollTop;
        this.updateVisibleItems();
    }
    
    handleResize() {
        this.updateContainerHeight();
        this.updateVisibleItems();
    }
    
    updateContainerHeight() {
        this.containerHeight = this.container.clientHeight;
    }
    
    updateVisibleItems() {
        const startIndex = Math.floor(this.scrollTop / this.options.itemHeight);
        const endIndex = Math.min(
            startIndex + Math.ceil(this.containerHeight / this.options.itemHeight) + this.options.bufferSize,
            this.items.length - 1
        );
        
        const visibleStartIndex = Math.max(0, startIndex - this.options.bufferSize);
        const visibleEndIndex = Math.min(endIndex + this.options.bufferSize, this.items.length - 1);
        
        // Update spacers
        this.topSpacer.style.height = `${visibleStartIndex * this.options.itemHeight}px`;
        this.bottomSpacer.style.height = `${(this.items.length - visibleEndIndex - 1) * this.options.itemHeight}px`;
        
        // Update viewport content
        this.viewport.innerHTML = '';
        
        for (let i = visibleStartIndex; i <= visibleEndIndex; i++) {
            if (this.items[i]) {
                const itemElement = this.options.renderItem(this.items[i], i);
                itemElement.style.position = 'absolute';
                itemElement.style.top = `${i * this.options.itemHeight}px`;
                itemElement.style.left = '0';
                itemElement.style.right = '0';
                itemElement.style.height = `${this.options.itemHeight}px`;
                
                this.viewport.appendChild(itemElement);
            }
        }
        
        this.visibleItems = this.items.slice(visibleStartIndex, visibleEndIndex + 1);
    }
    
    scrollToItem(index) {
        const scrollTop = index * this.options.itemHeight;
        this.container.scrollTop = scrollTop;
    }
    
    scrollToTop() {
        this.container.scrollTop = 0;
    }
    
    scrollToBottom() {
        this.container.scrollTop = this.totalHeight;
    }
    
    getItemHeight() {
        return this.options.itemHeight;
    }
    
    getVisibleItems() {
        return this.visibleItems;
    }
    
    getTotalHeight() {
        return this.totalHeight;
    }
    
    defaultRenderItem(item, index) {
        const element = document.createElement('div');
        element.className = 'virtual-scroll-item';
        element.style.borderBottom = '1px solid #eee';
        element.style.padding = '12px';
        element.style.boxSizing = 'border-box';
        
        element.innerHTML = `
            <div style="font-weight: bold;">${item.title || 'Item ' + index}</div>
            <div style="color: #666; font-size: 14px;">${item.description || ''}</div>
        `;
        
        return element;
    }
    
    destroy() {
        this.container.removeEventListener('scroll', this.handleScroll);
        this.container.removeEventListener('resize', this.handleResize);
        this.container.innerHTML = '';
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VirtualScroll;
}
