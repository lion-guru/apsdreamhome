/**
 * APS Dream Home Notification System
 * Handles notifications, category filtering, popups, and sound chimes for all user types
 */

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  if (meta) return meta.getAttribute('content');
  const m = document.cookie.match(/(^|; )csrf_token=([^;]*)/);
  return m ? decodeURIComponent(m[2]) : '';
}

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

class NotificationSystem {
  constructor() {
    this.userId = null;
    this.userRole = null;
    this.currentPage = this.getCurrentPage();
    this.notifications = [];
    this.popups = [];
    this.unreadCount = 0;
    this.activeFilter = 'all';
    this.soundEnabled = localStorage.getItem('aps_notif_sound') !== 'false';
    this.initialLoadDone = false;

    this.init();
  }

  /**
   * Initialize the notification system
   */
  init() {
    this.detectUserInfo();
    this.createNotificationUI();
    this.loadNotifications();
    this.loadPopups();
    this.setupPeriodicUpdates();
  }

  /**
   * Detect current user information
   */
  detectUserInfo() {
    if (typeof window.NOTIFY_USER !== 'undefined' && window.NOTIFY_USER) {
      this.userId = window.NOTIFY_USER.id;
      this.userRole = (window.NOTIFY_USER.role || 'guest').toLowerCase();
    } else if (typeof window.currentUser !== 'undefined' && window.currentUser) {
      this.userId = window.currentUser.id;
      this.userRole = (window.currentUser.role || 'guest').toLowerCase();
    } else {
      this.userId = null;
      this.userRole = window.location.pathname.includes('/admin') ? 'admin' : 'guest';
    }
  }

  /**
   * Calculate role-based notification center link
   */
  getViewAllUrl() {
    const role = (this.userRole || '').toLowerCase();
    const isAdmin = ['admin', 'super_admin', 'manager', 'ceo', 'coo', 'cfo', 'director'].includes(role) || window.location.pathname.includes('/admin');
    const isEmployee = ['employee', 'staff'].includes(role) || window.location.pathname.includes('/employee');

    if (isAdmin) {
      return this.getBaseUrl() + '/admin/notifications';
    }
    if (isEmployee) {
      return this.getBaseUrl() + '/employee/notifications';
    }
    if (this.userId) {
      return this.getBaseUrl() + '/user/notifications';
    }
    return this.getBaseUrl() + '/notifications';
  }

  /**
   * Calculate role-based notification preferences link
   */
  getPreferencesUrl() {
    const role = (this.userRole || '').toLowerCase();
    const isAdmin = ['admin', 'super_admin', 'manager', 'ceo', 'coo', 'cfo', 'director'].includes(role) || window.location.pathname.includes('/admin');
    if (isAdmin) {
      return this.getBaseUrl() + '/admin/notification-preferences';
    }
    if (this.userId) {
      return this.getBaseUrl() + '/user/notification-preferences';
    }
    return this.getBaseUrl() + '/login?redirect=notification-preferences';
  }

  /**
   * Update the footer links dynamically
   */
  updateFooterLink() {
    const link = document.querySelector('#notificationDropdown .notif-view-all');
    if (link) {
      link.href = this.getViewAllUrl();
    }
    const prefLink = document.querySelector('#notificationDropdown .notif-pref-link');
    if (prefLink) {
      prefLink.href = this.getPreferencesUrl();
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

  getBaseUrl() {
    if (typeof window !== 'undefined' && window.BASE_URL) {
      return window.BASE_URL.replace(/\/+$/, '');
    }
    if (typeof window !== 'undefined' && window.location.pathname.startsWith('/apsdreamhome')) {
      return '/apsdreamhome';
    }
    return '';
  }

  /**
   * Load notifications from API
   */
  async loadNotifications() {
    try {
      const response = await fetch(this.getBaseUrl() + '/api/notifications', {
        method: 'GET',
        headers: {
          'X-CSRF-Token': getCsrfToken(),
          'Content-Type': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          const raw = data.notifications || data.data || [];
          this.notifications = raw
            .filter(n => n && (n.title || n.message))
            .map(n => ({
              ...n,
              title: (n.title || 'Notification').replace(/\?\?\?/g, '₹'),
              message: (n.message || '').replace(/\?\?\?/g, '₹'),
              status: (n.is_read == 1 || n.status === 'read') ? 'read' : 'unread',
            }));

          const prevCount = this.unreadCount;
          this.unreadCount = typeof data.unread_count !== 'undefined'
            ? Number(data.unread_count)
            : this.notifications.filter(n => n.status === 'unread').length;

          // Play subtle chime on new incoming unread notifications
          if (this.initialLoadDone && this.unreadCount > prevCount) {
            this.playNotificationSound();
          }
          this.initialLoadDone = true;

          if (data.role) {
            this.userRole = String(data.role).toLowerCase();
            this.updateFooterLink();
          }

          this.updateNotificationBadge();
          this.renderNotificationsList();
        }
      }
    } catch (error) {
      console.error('Error loading notifications:', error);
    }
  }

  /**
   * Play crystal chime synthesizer using Web Audio API
   */
  playNotificationSound() {
    if (!this.soundEnabled) return;
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      if (ctx.state === 'suspended') {
        ctx.resume();
      }

      const now = ctx.currentTime;
      const osc1 = ctx.createOscillator();
      const osc2 = ctx.createOscillator();
      const gain = ctx.createGain();

      osc1.type = 'sine';
      osc1.frequency.setValueAtTime(587.33, now); // D5
      osc1.frequency.exponentialRampToValueAtTime(880, now + 0.14); // A5

      osc2.type = 'triangle';
      osc2.frequency.setValueAtTime(1174.66, now + 0.04); // D6 harmonic

      gain.gain.setValueAtTime(0, now);
      gain.gain.linearRampToValueAtTime(0.12, now + 0.04);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.55);

      osc1.connect(gain);
      osc2.connect(gain);
      gain.connect(ctx.destination);

      osc1.start(now);
      osc2.start(now + 0.04);
      osc1.stop(now + 0.55);
      osc2.stop(now + 0.55);
    } catch (e) {
      // Browser autoplay policy catch
    }
  }

  /**
   * Toggle sound alerts preference
   */
  toggleSound() {
    this.soundEnabled = !this.soundEnabled;
    localStorage.setItem('aps_notif_sound', this.soundEnabled ? 'true' : 'false');
    const btn = document.getElementById('notifSoundToggle');
    if (btn) {
      btn.innerHTML = `<i class="fas ${this.soundEnabled ? 'fa-volume-up' : 'fa-volume-mute'}"></i>`;
      btn.title = this.soundEnabled ? 'Sound alerts enabled (click to mute)' : 'Sound alerts muted (click to unmute)';
    }
    if (this.soundEnabled) {
      this.playNotificationSound();
    }
  }

  /**
   * Convert base64url string to Uint8Array for PushManager
   */
  urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = window.atob(base64);
    const out = new Uint8Array(raw.length);
    for (let i = 0; i < raw.length; ++i) out[i] = raw.charCodeAt(i);
    return out;
  }

  /**
   * Check Web Push subscription and update header button state
   */
  async checkWebPushStatus() {
    const btn = document.getElementById('notifPushToggle');
    if (!btn) return;
    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
      btn.style.display = 'none';
      return;
    }
    if (Notification.permission === 'granted') {
      btn.classList.add('push-active');
      btn.title = 'Desktop alerts active (click to test)';
    } else if (Notification.permission === 'denied') {
      btn.classList.add('push-blocked');
      btn.title = 'Desktop alerts blocked in browser settings';
    } else {
      btn.classList.remove('push-active', 'push-blocked');
      btn.title = 'Enable real-time desktop push alerts';
    }
  }

  /**
   * Toggle or test Web Push notifications
   */
  async toggleWebPush() {
    const btn = document.getElementById('notifPushToggle');
    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
      alert('Your browser does not support Web Push notifications.');
      return;
    }

    if (Notification.permission === 'denied') {
      alert('Browser notifications are currently blocked. Please open your browser site settings and allow notifications for APS Dream Home.');
      return;
    }

    if (Notification.permission === 'granted') {
      if (btn) btn.classList.add('push-loading');
      try {
        const resp = await fetch(this.getBaseUrl() + '/push/test', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          body: JSON.stringify({
            title: 'APS Dream Home',
            body: 'Real-time Desktop Push Notifications are connected and active!',
            url: this.getViewAllUrl()
          })
        });
        const res = await resp.json();
        if (res && res.success) {
          alert('Test push notification sent! Check your desktop notification center.');
        } else {
          await this.subscribeWebPush();
        }
      } catch (err) {
        console.warn('Push test error, re-registering:', err);
        await this.subscribeWebPush();
      } finally {
        if (btn) btn.classList.remove('push-loading');
      }
      return;
    }

    await this.subscribeWebPush();
  }

  /**
   * Subscribe to Web Push via VAPID and store subscription
   */
  async subscribeWebPush() {
    const btn = document.getElementById('notifPushToggle');
    try {
      const perm = await Notification.requestPermission();
      if (perm !== 'granted') {
        this.checkWebPushStatus();
        return;
      }

      let reg = await navigator.serviceWorker.getRegistration();
      if (!reg) {
        reg = await navigator.serviceWorker.register(this.getBaseUrl() + '/sw.js');
      }
      await navigator.serviceWorker.ready;

      const keyResp = await fetch(this.getBaseUrl() + '/push/vapid-key', { credentials: 'same-origin' });
      const keyData = await keyResp.json();
      if (!keyData || !keyData.success || !keyData.vapid_key) {
        throw new Error((keyData && keyData.error) || 'Failed to retrieve VAPID key');
      }

      const appKey = this.urlBase64ToUint8Array(keyData.vapid_key);
      const sub = await reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: appKey,
      });

      const endpoint = sub.endpoint;
      let p256dh = btoa(String.fromCharCode.apply(null, new Uint8Array(sub.getKey('p256dh'))));
      let auth = btoa(String.fromCharCode.apply(null, new Uint8Array(sub.getKey('auth'))));
      p256dh = p256dh.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
      auth = auth.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');

      const subResp = await fetch(this.getBaseUrl() + '/push/subscribe', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ endpoint, keys: { p256dh, auth } })
      });
      const subResult = await subResp.json();
      if (subResult && subResult.success) {
        this.checkWebPushStatus();
        alert('Desktop Push Notifications successfully enabled! You will now receive important updates even when the site is closed.');
      }
    } catch (e) {
      console.error('Web push subscribe error:', e);
      alert('Could not enable desktop notifications: ' + e.message);
    }
  }

  /**
   * Load popups from API
   */
  async loadPopups() {
    try {
      const response = await fetch(this.getBaseUrl() + '/api/popups?page=' + this.currentPage, {
        method: 'GET',
        headers: {
          'X-CSRF-Token': getCsrfToken(),
          'Content-Type': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          this.popups = data.popups || data.data || [];
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
    if (this.isPopupDismissed(popup.id)) return;

    const popupEl = document.createElement('div');
    popupEl.className = `notification-popup popup-${popup.position} popup-${popup.type}`;
    popupEl.setAttribute('data-popup-id', popup.id);

    popupEl.innerHTML = `
      <div class="popup-content">
        <div class="popup-header">
          <h5>${escapeHtml(popup.title)}</h5>
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

    if (popup.auto_close > 0) {
      setTimeout(() => {
        this.removePopupElement(popup.id);
      }, popup.auto_close);
    }

    setTimeout(() => {
      popupEl.classList.add('show');
    }, 100);
  }

  removePopupElement(popupId) {
    const popupEl = document.querySelector(`[data-popup-id="${popupId}"]`);
    if (popupEl) {
      popupEl.classList.remove('show');
      setTimeout(() => {
        popupEl.remove();
      }, 300);
    }
  }

  async dismissPopup(popupId) {
    try {
      const response = await fetch(this.getBaseUrl() + '/api/popups/dismiss', {
        method: 'POST',
        headers: {
          'X-CSRF-Token': getCsrfToken(),
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ popup_id: popupId }),
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
   * Create notification UI in header
   */
  createNotificationUI() {
    if (document.getElementById('notification-bell-root')) return;

    // Check if the page already has its own dedicated portal bell
    if (document.getElementById('notifBadge') && !document.getElementById('notification-bell-placeholder')) {
      return;
    }

    const placeholder =
      document.getElementById('notification-bell-placeholder') ||
      document.querySelector('.nav-icon[onclick="toggleNotifications()"]');
    const viewAllUrl = this.getViewAllUrl();

    const notificationBell = document.createElement('div');
    notificationBell.id = 'notification-bell-root';
    notificationBell.className = 'notification-bell' + (this.unreadCount > 0 ? ' has-unread' : '');
    notificationBell.innerHTML = `
      <button class="nav-icon notification-bell-btn" id="notificationBellBtn" type="button" aria-label="Notifications" title="Notifications">
        <i class="fas fa-bell"></i>
        <span class="badge notification-badge" id="notificationBadge" style="display: ${this.unreadCount > 0 ? 'flex' : 'none'};">${this.unreadCount}</span>
      </button>
      <div class="notification-dropdown" id="notificationDropdown">
        <div class="notification-header">
          <div class="notif-title-wrap">
            <h6>Notifications</h6>
            <span class="notif-unread-pill" id="notifUnreadPill">${this.unreadCount} unread</span>
          </div>
          <div class="d-flex align-items-center gap-1">
            <button class="notif-push-btn" id="notifPushToggle" type="button" onclick="notificationSystem.toggleWebPush()" title="Enable Real-Time Desktop Alerts">
              <i class="fas fa-desktop"></i>
            </button>
            <button class="notif-sound-btn" id="notifSoundToggle" type="button" onclick="notificationSystem.toggleSound()" title="${this.soundEnabled ? 'Sound alerts enabled (click to mute)' : 'Sound alerts muted (click to unmute)'}">
              <i class="fas ${this.soundEnabled ? 'fa-volume-up' : 'fa-volume-mute'}"></i>
            </button>
            <button class="notif-mark-all-btn" type="button" onclick="notificationSystem.markAllAsRead()">
              <i class="fas fa-check-double me-1"></i>Mark all
            </button>
          </div>
        </div>
        <div class="notification-tabs">
          <button class="notif-tab active" data-tab="all" type="button" onclick="notificationSystem.setFilter('all')">All</button>
          <button class="notif-tab" data-tab="leads" type="button" onclick="notificationSystem.setFilter('leads')">Leads</button>
          <button class="notif-tab" data-tab="alerts" type="button" onclick="notificationSystem.setFilter('alerts')">Alerts</button>
          <button class="notif-tab" data-tab="payments" type="button" onclick="notificationSystem.setFilter('payments')">Payments</button>
          <button class="notif-tab" data-tab="reviews" type="button" onclick="notificationSystem.setFilter('reviews')">Reviews</button>
        </div>
        <div class="notification-list" id="notificationList">
          <!-- Notifications rendered here -->
        </div>
        <div class="notification-footer d-flex justify-content-between align-items-center">
          <a href="${this.getPreferencesUrl()}" class="notif-pref-link" title="Customize Channels & Preferences">
            <i class="fas fa-sliders-h me-1"></i>Preferences
          </a>
          <a href="${viewAllUrl}" class="notif-view-all">
            View All <i class="fas fa-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
    `;

    if (placeholder) {
      placeholder.parentNode.replaceChild(notificationBell, placeholder);
    } else {
      const header = document.querySelector('.nav-right') || document.querySelector('.navbar-nav') || document.querySelector('header');
      if (header) {
        header.appendChild(notificationBell);
      }
    }

    const bellBtn = document.getElementById('notificationBellBtn');
    if (bellBtn) {
      bellBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        this.toggleNotificationDropdown();
      });
    }

    this.checkWebPushStatus();
    this.renderNotificationsList();
  }

  /**
   * Switch category filter tab
   */
  setFilter(filterName) {
    this.activeFilter = filterName;
    const tabs = document.querySelectorAll('.notif-tab');
    tabs.forEach(t => {
      if (t.getAttribute('data-tab') === filterName) {
        t.classList.add('active');
      } else {
        t.classList.remove('active');
      }
    });
    this.renderNotificationsList();
  }

  /**
   * Categorize notification for UI styling & filtering
   */
  getNotificationMeta(n) {
    const type = (n.type || '').toLowerCase();
    const title = (n.title || '').toLowerCase();
    const message = (n.message || '').toLowerCase();

    if (type.includes('lead') || title.includes('lead') || message.includes('lead')) {
      return { category: 'leads', icon: 'fa-user-plus', colorClass: 'cat-lead', label: 'Lead' };
    }
    if (type.includes('payment') || type.includes('invoice') || title.includes('payment') || title.includes('invoice')) {
      return { category: 'payments', icon: 'fa-rupee-sign', colorClass: 'cat-payment', label: 'Payment' };
    }
    if (type.includes('warning') || type.includes('error') || type.includes('alert') || title.includes('alert') || title.includes('fail')) {
      return { category: 'alerts', icon: 'fa-exclamation-triangle', colorClass: 'cat-alert', label: 'Alert' };
    }
    if (type.includes('review') || title.includes('review') || message.includes('review')) {
      return { category: 'reviews', icon: 'fa-clipboard-check', colorClass: 'cat-review', label: 'Review' };
    }
    if (type.includes('system') || type.includes('backup') || title.includes('system') || title.includes('summary')) {
      return { category: 'system', icon: 'fa-server', colorClass: 'cat-system', label: 'System' };
    }
    return { category: 'general', icon: 'fa-bell', colorClass: 'cat-info', label: 'Info' };
  }

  /**
   * Toggle notification dropdown
   */
  toggleNotificationDropdown() {
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
      dropdown.classList.toggle('show');
      if (dropdown.classList.contains('show')) {
        this.renderNotificationsList();
      }
    }
  }

  /**
   * Render notification list with active filter
   */
  renderNotificationsList() {
    const listEl = document.getElementById('notificationList');
    if (!listEl) return;

    // Filter items
    let items = this.notifications;
    if (this.activeFilter !== 'all') {
      items = items.filter(n => {
        const meta = this.getNotificationMeta(n);
        return meta.category === this.activeFilter;
      });
    }

    if (items.length === 0) {
      const isGuest = this.userRole === 'guest' || !this.userId;
      const filterLabel = this.activeFilter === 'all' ? 'notifications' : `${this.activeFilter} notifications`;
      const emptyContent = isGuest
        ? `<i class="fas fa-bullhorn mb-2 text-warning fs-3"></i><p class="mb-1">No public announcements right now.</p><a href="${this.getBaseUrl()}/login" class="btn btn-sm btn-outline-primary mt-2">Login to view alerts</a>`
        : `<i class="fas fa-bell-slash"></i><p>No ${filterLabel} found</p>`;
      listEl.innerHTML = `
        <div class="notification-empty py-4 text-center">
          ${emptyContent}
        </div>
      `;
      return;
    }

    listEl.innerHTML = items.map(n => {
      const meta = this.getNotificationMeta(n);
      const isUnread = n.status === 'unread';
      const cleanTitle = escapeHtml(n.title || 'Notification');
      const cleanMsg = escapeHtml(n.message || '');
      const timeStr = this.formatDate(n.created_at);

      return `
        <div class="notification-item ${isUnread ? 'unread' : 'read'} ${meta.colorClass}" onclick="notificationSystem.viewNotification(${n.id})">
          <div class="notification-icon-wrap">
            <i class="fas ${meta.icon}"></i>
          </div>
          <div class="notification-content">
            <h6>${cleanTitle}</h6>
            <p>${cleanMsg}</p>
            <div class="notification-meta-row">
              <span class="notification-category-badge">${meta.label}</span>
              <small><i class="far fa-clock me-1"></i>${timeStr}</small>
            </div>
          </div>
          <div class="notification-actions">
            ${isUnread ? `
              <button class="notif-item-check" title="Mark as read" type="button" onclick="event.stopPropagation(); notificationSystem.markAsRead(${n.id})">
                <i class="fas fa-check"></i>
              </button>
            ` : ''}
          </div>
        </div>
      `;
    }).join('');
  }

  /**
   * Update notification badge and bell animations
   */
  updateNotificationBadge() {
    const badge = document.getElementById('notificationBadge');
    const pill = document.getElementById('notifUnreadPill');
    const bellRoot = document.getElementById('notification-bell-root');

    if (badge) {
      badge.textContent = this.unreadCount;
      badge.style.display = this.unreadCount > 0 ? 'flex' : 'none';
    }
    if (pill) {
      pill.textContent = `${this.unreadCount} unread`;
      pill.style.display = this.unreadCount > 0 ? 'inline-block' : 'none';
    }
    if (bellRoot) {
      if (this.unreadCount > 0) {
        bellRoot.classList.add('has-unread');
      } else {
        bellRoot.classList.remove('has-unread');
      }
    }
  }

  /**
   * Format friendly relative date
   */
  formatDate(dateString) {
    if (!dateString) return 'Recent';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return String(dateString);

    const now = new Date();
    const diff = now - date;

    if (diff < 60000) return 'Just now';
    if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
    if (diff < 86400000) return Math.floor(diff / 3600000) + 'h ago';
    if (diff < 604800000) return Math.floor(diff / 86400000) + 'd ago';

    return date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
  }

  /**
   * Mark single notification as read
   */
  async markAsRead(notificationId) {
    try {
      const response = await fetch(this.getBaseUrl() + '/api/notifications/mark-read', {
        method: 'POST',
        headers: {
          'X-CSRF-Token': getCsrfToken(),
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          id: notificationId,
          notification_id: notificationId,
        }),
      });

      if (response.ok) {
        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification && notification.status !== 'read') {
          notification.status = 'read';
          notification.is_read = 1;
          this.unreadCount = Math.max(0, this.unreadCount - 1);
          this.updateNotificationBadge();
          this.renderNotificationsList();
        }
      }
    } catch (error) {
      console.error('Error marking notification as read:', error);
    }
  }

  /**
   * Mark all notifications as read in a single request
   */
  async markAllAsRead() {
    try {
      const response = await fetch(this.getBaseUrl() + '/api/notifications/mark-all-read', {
        method: 'POST',
        headers: {
          'X-CSRF-Token': getCsrfToken(),
          'Content-Type': 'application/json',
        },
      });

      if (response.ok) {
        this.notifications.forEach(n => {
          n.status = 'read';
          n.is_read = 1;
        });
        this.unreadCount = 0;
        this.updateNotificationBadge();
        this.renderNotificationsList();
      }
    } catch (error) {
      console.error('Error marking all notifications as read:', error);
    }
  }

  /**
   * View notification and optionally redirect to action_url safely
   */
  viewNotification(notificationId) {
    const notification = this.notifications.find(n => n.id === notificationId);
    this.markAsRead(notificationId);

    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
      dropdown.classList.remove('show');
    }

    if (notification && notification.action_url) {
      const rawUrl = notification.action_url.trim();
      let targetUrl = rawUrl;
      if (!rawUrl.startsWith('http://') && !rawUrl.startsWith('https://')) {
        targetUrl = this.getBaseUrl() + '/' + rawUrl.replace(/^\/+/, '');
      }
      window.location.href = targetUrl;
    }
  }

  setupPeriodicUpdates() {
    // Refresh count every 40 seconds
    setInterval(() => {
      this.loadNotifications();
    }, 40000);
  }

  isPopupDismissed(popupId) {
    return localStorage.getItem(`dismissed_popup_${popupId}`) === 'true';
  }

  markPopupAsDismissed(popupId) {
    localStorage.setItem(`dismissed_popup_${popupId}`, 'true');
  }
}

// Initialize notification system when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
  window.notificationSystem = new NotificationSystem();
});

// Close dropdown when clicking outside
document.addEventListener('click', function (event) {
  const dropdown = document.getElementById('notificationDropdown');
  const bell = document.getElementById('notification-bell-root');

  if (dropdown && bell && !bell.contains(event.target)) {
    dropdown.classList.remove('show');
  }
});
