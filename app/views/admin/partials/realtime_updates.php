<!-- Real-time Updates Component -->
<script>
// Real-time Dashboard Updates
class RealtimeDashboard {
    constructor() {
        this.updateInterval = 30000; // 30 seconds
        this.retryCount = 0;
        this.maxRetries = 3;
        this.isUpdating = false;
    }
    
    init() {
        this.bindEvents();
        this.startAutoUpdate();
        this.setupWebSocket();
    }
    
    bindEvents() {
        // Listen for visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopAutoUpdate();
            } else {
                this.startAutoUpdate();
            }
        });
        
        // Listen for network changes
        window.addEventListener('online', () => {
            this.showNotification('Connection restored', 'success');
            this.startAutoUpdate();
        });
        
        window.addEventListener('offline', () => {
            this.showNotification('Connection lost', 'warning');
            this.stopAutoUpdate();
        });
    }
    
    setupWebSocket() {
        // WebSocket connection for real-time updates
        if (typeof WebSocket !== 'undefined') {
            try {
                this.ws = new WebSocket(`ws://${window.location.host}/ws/dashboard`);
                
                this.ws.onopen = () => {
                    console.log('WebSocket connected');
                    this.showNotification('Live updates enabled', 'success');
                };
                
                this.ws.onmessage = (event) => {
                    const data = JSON.parse(event.data);
                    this.handleRealtimeUpdate(data);
                };
                
                this.ws.onclose = () => {
                    console.log('WebSocket disconnected');
                    this.showNotification('Live updates disabled', 'warning');
                    // Fallback to polling
                    this.startAutoUpdate();
                };
                
                this.ws.onerror = (error) => {
                    console.error('WebSocket error:', error);
                    // Fallback to polling
                    this.startAutoUpdate();
                };
            } catch (error) {
                console.error('WebSocket setup failed:', error);
                // Fallback to polling
                this.startAutoUpdate();
            }
        }
    }
    
    startAutoUpdate() {
        if (this.isUpdating || this.intervalId) return;
        
        this.intervalId = setInterval(() => {
            this.fetchDashboardData();
        }, this.updateInterval);
    }
    
    stopAutoUpdate() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }
    
    async fetchDashboardData() {
        if (this.isUpdating) return;
        
        this.isUpdating = true;
        this.showLoadingState();
        
        try {
            const response = await fetch('<?= BASE_URL ?>/api/dashboard/updates', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            this.updateDashboardStats(data);
            this.retryCount = 0;
            
        } catch (error) {
            console.error('Dashboard update failed:', error);
            this.retryCount++;
            
            if (this.retryCount < this.maxRetries) {
                this.showNotification(`Update failed, retrying... (${this.retryCount}/${this.maxRetries})`, 'warning');
            } else {
                this.showNotification('Update failed after multiple attempts', 'error');
                this.stopAutoUpdate();
            }
        } finally {
            this.isUpdating = false;
            this.hideLoadingState();
        }
    }
    
    handleRealtimeUpdate(data) {
        switch (data.type) {
            case 'stats_update':
                this.updateStats(data.stats);
                break;
            case 'new_notification':
                this.showNotification(data.message, data.level);
                break;
            case 'system_alert':
                this.showAlert(data.message, data.severity);
                break;
            case 'data_refresh':
                this.refreshDataTable(data.table);
                break;
        }
    }
    
    updateDashboardStats(data) {
        // Update stats cards
        if (data.total_leads !== undefined) {
            this.updateStatCard('totalLeads', data.total_leads);
        }
        
        if (data.active_users !== undefined) {
            this.updateStatCard('activeUsers', data.active_users);
        }
        
        if (data.revenue_today !== undefined) {
            this.updateStatCard('revenueToday', data.revenue_today);
        }
        
        if (data.pending_tasks !== undefined) {
            this.updateStatCard('pendingTasks', data.pending_tasks);
        }
        
        // Update last update timestamp
        this.updateLastRefresh();
    }
    
    updateStatCard(cardId, value) {
        const card = document.getElementById(cardId);
        if (card) {
            // Animate the change
            card.style.transition = 'all 0.3s ease';
            card.style.transform = 'scale(1.1)';
            
            setTimeout(() => {
                card.textContent = this.formatValue(value);
                card.style.transform = 'scale(1)';
            }, 150);
        }
    }
    
    formatValue(value) {
        if (typeof value === 'number') {
            if (value >= 1000000) {
                return (value / 1000000).toFixed(1) + 'L';
            } else if (value >= 1000) {
                return (value / 1000).toFixed(1) + 'K';
            } else {
                return value.toString();
            }
        } else if (typeof value === 'string' && value.includes('₹')) {
            return value;
        }
        return value.toString();
    }
    
    updateLastRefresh() {
        const timestamp = document.getElementById('lastRefresh');
        if (timestamp) {
            const now = new Date();
            timestamp.textContent = `Last updated: ${now.toLocaleTimeString()}`;
        }
    }
    
    showLoadingState() {
        const refreshBtn = document.getElementById('refreshData');
        if (refreshBtn) {
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        }
        
        // Add loading overlay to tables
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            table.style.opacity = '0.6';
            table.style.pointerEvents = 'none';
        });
    }
    
    hideLoadingState() {
        const refreshBtn = document.getElementById('refreshData');
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        }
        
        // Remove loading overlay from tables
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            table.style.opacity = '1';
            table.style.pointerEvents = 'auto';
        });
    }
    
    showNotification(message, type = 'info', duration = 3000) {
        // Use existing notification system if available
        if (typeof showNotification === 'function') {
            showNotification(message, type);
        } else {
            // Fallback notification
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, duration);
        }
    }
    
    showAlert(message, severity = 'info') {
        // Create system alert
        const alert = document.createElement('div');
        alert.className = `alert alert-${severity} alert-dismissible fade show position-fixed`;
        alert.style.cssText = 'top: 80px; right: 20px; left: 20px; z-index: 9999;';
        alert.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>${message}</strong>
                <button type="button" class="btn-close ms-auto" onclick="this.parentElement.remove()"></button>
            </div>
        `;
        
        document.body.appendChild(alert);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 5000);
    }
    
    refreshDataTable(tableId) {
        const table = document.getElementById(tableId);
        if (table) {
            // Add refresh animation
            table.style.transition = 'opacity 0.3s ease';
            table.style.opacity = '0.5';
            
            setTimeout(() => {
                table.style.opacity = '1';
                this.updateLastRefresh();
            }, 300);
        }
    }
    
    destroy() {
        this.stopAutoUpdate();
        if (this.ws) {
            this.ws.close();
        }
    }
}

// Initialize real-time updates
document.addEventListener('DOMContentLoaded', function() {
    window.realtimeDashboard = new RealtimeDashboard();
    window.realtimeDashboard.init();
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (window.realtimeDashboard) {
            window.realtimeDashboard.destroy();
        }
    });
});

// Performance optimizations
window.addEventListener('load', function() {
    // Lazy load images
    const images = document.querySelectorAll('img[data-src]');
    images.forEach(img => {
        img.src = img.dataset.src;
    });
    
    // Optimize table rendering
    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        if (table.rows.length > 100) {
            // Add pagination for large tables
            table.classList.add('large-table');
        }
    });
});

// Mobile-specific optimizations
if (window.mobileHelpers && window.mobileHelpers.isMobile()) {
    // Reduce update frequency on mobile
    if (window.realtimeDashboard) {
        window.realtimeDashboard.updateInterval = 60000; // 1 minute on mobile
    }
}
</script>
