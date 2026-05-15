/**
 * APS Dream Home Notification System
 * Handles notifications and popups for all user types
 */

class NotificationSystem {
    constructor() {
        this.userId = null;
        this.userRole = null;
        this.currentPage = this.getCurrentPage();
        this.notifications = [];
        this.popups = [];
        this.unreadCount = 0;
        
        this.init();
    }

    /**
     * Initialize the notification system
     */
    init() {
        // Detect user type and ID from session
        this.detectUserInfo();
        
        // Load notifications
        this.loadNotifications();
        
        // Load popups
        this.loadPopups();
        
        // Set up periodic updates
        this.setupPeriodicUpdates();
        
        // Create notification UI
        this.createNotificationUI();
    }

    /**
     * Detect current user information
     */
    detectUserInfo() {
        // This would be set by the backend in a real implementation
        // For now, we'll detect from page context
        if (typeof window.currentUser !== 'undefined') {
            this.userId = window.currentUser.id;
            this.userRole = window.currentUser.role;
        } else {
            // Default values for testing
            this.userId = 1;
            this.userRole = 'customer';
        }
    }

    /**
     * Get current page name
     */
    getCurrentPage() {
        const path = window.location.pathname;
        if (path.includes('home') || path === '/') return 'home';
        if (path.includes('properties')) return 'properties';
        if (path.includes('about')) return 'about';
        if (path.includes('contact')) return 'contact';
        if (path.includes('dashboard')) return 'dashboard';
        if (path.includes('employee')) return 'employee';
        if (path.includes('admin')) return 'admin';
        return 'unknown';
    }

    /**
     * Load notifications from API
     */
    async loadNotifications() {
        try {
            const response = await fetch('/api/notifications', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.notifications = data.data || [];
                    this.unreadCount = data.unread_count || 0;
                    this.updateNotificationBadge();
                }
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    /**
     * Load popups from API
     */
    async loadPopups() {
        try {
            const response = await fetch(`/api/popups?page=${this.currentPage}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.popups = data.data || [];
                    this.showPopups();
                }
            }
        } catch (error) {
            console.error('Error loading popups:', error);
        }
    }

    /**
     * Show popups with delays
     */
    showPopups() {
        this.popups.forEach(popup => {
            setTimeout(() => {
                this.createPopupElement(popup);
            }, popup.show_delay || 0);
        });
    }

    /**
     * Create popup element
     */
    createPopupElement(popup) {
        // Check if popup was already dismissed
        if (this.isPopupDismissed(popup.id)) {
            return;
        }

        const popupEl = document.createElement('div');
        popupEl.className = `notification-popup popup-${popup.position} popup-${popup.type}`;
        popupEl.setAttribute('data-popup-id', popup.id);
        
        popupEl.innerHTML = `
            <div class="popup-content">
                <div class="popup-header">
                    <h5>${popup.title}</h5>
                    <button class="popup-close" onclick="notificationSystem.dismissPopup(${popup.id})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="popup-body">
                    ${popup.content}
                </div>
            </div>
        `;

        document.body.appendChild(popupEl);

        // Auto-close if specified
        if (popup.auto_close > 0) {
            setTimeout(() => {
                this.removePopupElement(popup.id);
            }, popup.auto_close);
        }

        // Animate in
        setTimeout(() => {
            popupEl.classList.add('show');
        }, 100);
    }

    /**
     * Remove popup element
     */
    removePopupElement(popupId) {
        const popupEl = document.querySelector(`[data-popup-id="${popupId}"]`);
        if (popupEl) {
            popupEl.classList.remove('show');
            setTimeout(() => {
                popupEl.remove();
            }, 300);
        }
    }

    /**
     * Dismiss popup
     */
    async dismissPopup(popupId) {
        try {
            const response = await fetch('/api/popups/dismiss', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    popup_id: popupId
                })
            });
            
            if (response.ok) {
                this.markPopupAsDismissed(popupId);
                this.removePopupElement(popupId);
            }
        } catch (error) {
            console.error('Error dismissing popup:', error);
        }
    }

    /**
     * Create notification UI
     */
    createNotificationUI() {
        // Create notification bell
        const notificationBell = document.createElement('div');
        notificationBell.className = 'notification-bell';
        notificationBell.innerHTML = `
            <button class="btn btn-link position-relative" onclick="notificationSystem.toggleNotificationDropdown()">
                <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationBadge">${this.unreadCount}</span>
            </button>
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">
                    <h6>Notifications</h6>
                    <button class="btn btn-sm btn-link" onclick="notificationSystem.markAllAsRead()">Mark all as read</button>
                </div>
                <div class="notification-list" id="notificationList">
                    <!-- Notifications will be loaded here -->
                </div>
                <div class="notification-footer">
                    <a href="/notifications" class="btn btn-link">View all notifications</a>
                </div>
            </div>
        `;

        // Add to header
        const header = document.querySelector('.navbar-nav') || document.querySelector('header');
        if (header) {
            header.appendChild(notificationBell);
        }

        // Load notifications into dropdown
        this.updateNotificationDropdown();
    }

    /**
     * Toggle notification dropdown
     */
    toggleNotificationDropdown() {
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown) {
            dropdown.classList.toggle('show');
            if (dropdown.classList.contains('show')) {
                this.updateNotificationDropdown();
            }
        }
    }

    /**
     * Update notification dropdown
     */
    updateNotificationDropdown() {
        const notificationList = document.getElementById('notificationList');
        if (!notificationList) return;

        if (this.notifications.length === 0) {
            notificationList.innerHTML = `
                <div class="notification-empty">
                    <i class="fas fa-inbox"></i>
                    <p>No notifications</p>
                </div>
            `;
        } else {
            notificationList.innerHTML = this.notifications.map(notification => `
                <div class="notification-item ${notification.status}" onclick="notificationSystem.viewNotification(${notification.id})">
                    <div class="notification-icon">
                        <i class="fas ${this.getNotificationIcon(notification.type)}"></i>
                    </div>
                    <div class="notification-content">
                        <h6>${notification.title}</h6>
                        <p>${notification.message}</p>
                        <small>${this.formatDate(notification.created_at)}</small>
                    </div>
                    <div class="notification-actions">
                        ${notification.status === 'unread' ? `
                            <button class="btn btn-sm btn-link" onclick="event.stopPropagation(); notificationSystem.markAsRead(${notification.id})">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        }
    }

    /**
     * Update notification badge
     */
    updateNotificationBadge() {
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            badge.textContent = this.unreadCount;
            badge.style.display = this.unreadCount > 0 ? 'block' : 'none';
        }
    }

    /**
     * Get notification icon
     */
    getNotificationIcon(type) {
        const icons = {
            'info': 'fa-info-circle',
            'success': 'fa-check-circle',
            'warning': 'fa-exclamation-triangle',
            'error': 'fa-times-circle',
            'campaign': 'fa-bullhorn',
            'system': 'fa-cog'
        };
        return icons[type] || 'fa-info-circle';
    }

    /**
     * Format date
     */
    formatDate(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'Just now';
        if (diff < 3600000) return Math.floor(diff / 60000) + ' minutes ago';
        if (diff < 86400000) return Math.floor(diff / 3600000) + ' hours ago';
        if (diff < 604800000) return Math.floor(diff / 86400000) + ' days ago';
        
        return date.toLocaleDateString();
    }

    /**
     * Mark notification as read
     */
    async markAsRead(notificationId) {
        try {
            const response = await fetch('/api/notifications/mark-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notification_id: notificationId
                })
            });
            
            if (response.ok) {
                // Update local notification
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification) {
                    notification.status = 'read';
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                    this.updateNotificationBadge();
                    this.updateNotificationDropdown();
                }
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    /**
     * Mark all notifications as read
     */
    async markAllAsRead() {
        const unreadNotifications = this.notifications.filter(n => n.status === 'unread');
        
        for (const notification of unreadNotifications) {
            await this.markAsRead(notification.id);
        }
    }

    /**
     * View notification
     */
    viewNotification(notificationId) {
        this.markAsRead(notificationId);
        // Close dropdown
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown) {
            dropdown.classList.remove('show');
        }
    }

    /**
     * Setup periodic updates
     */
    setupPeriodicUpdates() {
        // Update notifications every 30 seconds
        setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }

    /**
     * Check if popup was dismissed
     */
    isPopupDismissed(popupId) {
        const dismissed = localStorage.getItem(`dismissed_popup_${popupId}`);
        return dismissed === 'true';
    }

    /**
     * Mark popup as dismissed in localStorage
     */
    markPopupAsDismissed(popupId) {
        localStorage.setItem(`dismissed_popup_${popupId}`, 'true');
    }
}

// Initialize notification system when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.notificationSystem = new NotificationSystem();
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('notificationDropdown');
    const bell = document.querySelector('.notification-bell');
    
    if (dropdown && bell && !bell.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});