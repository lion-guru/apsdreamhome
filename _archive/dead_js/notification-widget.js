(function() {
  'use strict';
  window.NotificationWidget = {
    ws: null,
    reconnectInterval: 5000,
    heartbeatInterval: 30000,
    heartbeatTimer: null,
    reconnectTimer: null,
    isConnected: false,
    isAuthenticating: false,
    badge: null,
    dropdown: null,
    list: null,
    channel: 'global',
    userId: null,
    lastId: 0,

    init: function(options) {
      options = options || {};
      this.badge = document.querySelector(options.badgeSelector || '.notification-badge');
      this.dropdown = document.querySelector(options.dropdownSelector || '.notification-dropdown');
      this.list = document.querySelector(options.listSelector || '.notification-list');
      this.channel = options.channel || 'global';
      this.userId = options.userId || null;
      this.lastId = 0;

      if (this.dropdown) {
        this.dropdown.addEventListener('show.bs.dropdown', () => this.loadAll());
      }

      this.connect();
    },

    connect: function() {
      const wsProtocol = window.location.protocol === 'https:' ? 'wss' : 'ws';
      const wsUrl = `${wsProtocol}://${window.location.host}/websocket_server.php`;
      
      try {
        this.ws = new WebSocket(wsUrl);
        
        this.ws.onopen = () => {
          this.isConnected = true;
          this.isAuthenticating = true;
          console.warn("WebSocket connected");
          
          // Send auth message if we have user info
          if (this.userId) {
            this.ws.send(JSON.stringify({
              type: 'auth',
              token: this.getAuthToken()
            }));
          }
          
          // Start heartbeat
          this.startHeartbeat();
        };
        
        this.ws.onmessage = (event) => {
          const data = JSON.parse(event.data);
          this.handleMessage(data);
        };
        
        this.ws.onclose = () => {
          this.isConnected = false;
          console.warn("WebSocket disconnected");
          this.stopHeartbeat();
          this.scheduleReconnect();
        };
        
        this.ws.onerror = (error) => {
          console.warn("WebSocket error: ", error);
          this.isConnected = false;
          this.ws.close();
        };
      } catch (error) {
        console.warn("WebSocket connection error: ", error);
        this.scheduleReconnect();
      }
    },

    handleMessage: function(data) {
      switch (data.type) {
        case 'auth':
          if (data.status === 'success') {
            this.isAuthenticating = false;
            console.warn("WebSocket authenticated");
          } else {
            console.warn("WebSocket auth failed: ", data.message);
          }
          break;
          
        case 'notification':
          if (data.data) {
            this.showToast(data.data);
            this.updateUnreadCount(1); // Increment badge
          }
          break;
          
        case 'mark_read_result':
          // Handle mark read confirmation if needed
          break;
          
        case 'pong':
          // Heartbeat response
          break;
          
        case 'connection':
          console.warn("WebSocket connection established");
          break;
          
        case 'error':
          console.warn("WebSocket error: ", data.message);
          break;
      }
    },

    getAuthToken: function() {
      // Try to get token from meta tag or cookie
      const tokenMeta = document.querySelector('meta[name="csrf-token"]');
      if (tokenMeta) {
        return tokenMeta.getAttribute('content');
      }
      
      // Fallback to session storage or return empty
      return sessionStorage.getItem('auth_token') || '';
    },

    startHeartbeat: function() {
      this.heartbeatTimer = setInterval(() => {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
          this.ws.send(JSON.stringify({
            type: 'ping',
            timestamp: Date.now()
          }));
        }
      }, this.heartbeatInterval);
    },

    stopHeartbeat: function() {
      if (this.heartbeatTimer) {
        clearInterval(this.heartbeatTimer);
        this.heartbeatTimer = null;
      }
    },

    scheduleReconnect: function() {
      if (!this.reconnectTimer) {
        this.reconnectTimer = setTimeout(() => {
          this.reconnectTimer = null;
          this.connect();
        }, this.reconnectInterval);
      }
    },

    disconnect: function() {
      if (this.ws) {
        this.ws.close();
        this.ws = null;
      }
      this.stopHeartbeat();
      if (this.reconnectTimer) {
        clearInterval(this.reconnectTimer);
        this.reconnectTimer = null;
      }
      this.isConnected = false;
    },

    showToast: function(n) {
      const payload = this.parsePayload(n.payload);
      const message = payload.message || payload.title || 'New notification';
      const type = payload.type || 'info';
      const container = document.getElementById('toast-container') || this.createToastContainer();
      const toast = document.createElement('div');
      toast.className = 'toast align-items-center text-white bg-' + (type === 'success' ? 'success' : type === 'error' ? 'danger' : type === 'warning' ? 'warning' : 'info') + ' border-0 show';
      toast.setAttribute('role', 'alert');
      toast.innerHTML = '<div class="d-flex"><div class="toast-body">' + this.escapeHtml(message) + '</div>' +
        '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
      container.appendChild(toast);
      setTimeout(() => toast.remove(), 5000);
    },

    createToastContainer: function() {
      const div = document.createElement('div');
      div.id = 'toast-container';
      div.className = 'toast-container position-fixed top-0 end-0 p-3';
      div.style.zIndex = '9999';
      document.body.appendChild(div);
      return div;
    },

    updateUnreadCount: function(change) {
      // Get current count from badge
      let current = 0;
      if (this.badge && this.badge.style.display !== 'none') {
        const text = this.badge.textContent;
        if (text === '99+') {
          current = 99;
        } else {
          current = parseInt(text) || 0;
        }
      }
      
      const newCount = Math.max(0, current + change);
      this.updateBadge(newCount);
    },

    loadAll: async function() {
      // For backward compatibility, we can still use AJAX for initial load
      try {
        const res = await fetch((window.BASE_URL || '/apsdreamhome') + '/api/v2/notifications/poll?channel=' + this.channel + '&since_id=0', { credentials: 'same-origin' });
        const data = await res.json();
        if (this.list) {
          if (!data.notifications.length) {
            this.list.innerHTML = '<div class="text-center text-muted p-3">No notifications</div>';
          } else {
            this.list.innerHTML = data.notifications.slice(0, 10).map(n => this.renderItem(n)).join('');
          }
          
          // Update badge with total unread count
          if (data.unread_count !== undefined) {
            this.updateBadge(data.unread_count);
          }
        }
      } catch (e) {
        console.warn("Error loading notifications: ", e);
      }
    },

    renderItem: function(n) {
      const payload = this.parsePayload(n.payload);
      return '<div class="notification-item p-2 border-bottom" data-id="' + n.id + '">' +
        '<div class="d-flex justify-content-between">' +
        '<strong>' + this.escapeHtml(n.event_type) + '</strong>' +
        '<small class="text-muted">' + this.timeAgo(n.created_at) + '</small>' +
        '</div>' +
        '<div class="small text-muted">' + this.escapeHtml(payload.message || payload.title || JSON.stringify(payload)) + '</div>' +
        '</div>';
    },

    markRead: async function(ids) {
      try {
        await fetch((window.BASE_URL || '/apsdreamhome') + '/api/v2/notifications/read', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ ids: ids })
        });
        
        // Also notify WebSocket server to mark as read on server side
        if (this.ws && this.ws.readyState === WebSocket.OPEN && !this.isAuthenticating) {
          this.ws.send(JSON.stringify({
            type: 'mark_read',
            ids: ids
          }));
        }
      } catch (e) {
        console.warn("Error marking notifications as read: ", e);
      }
    },

    updateBadge: function(count) {
      if (!this.badge) return;
      if (count > 0) {
        this.badge.textContent = count > 99 ? '99+' : count;
        this.badge.style.display = '';
      } else {
        this.badge.style.display = 'none';
      }
    },

    parsePayload: function(payload) {
      try { return typeof payload === 'string' ? JSON.parse(payload) : (payload || {}); }
      catch (e) { return {}; }
    },

    timeAgo: function(date) {
      const seconds = Math.floor((new Date() - new Date(date)) / 1000);
      if (seconds < 60) return 'just now';
      if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
      if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
      return Math.floor(seconds / 86400) + 'd ago';
    },

    escapeHtml: function(s) {
      if (s == null) return '';
      return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }
  };

  document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.notification-badge')) {
      // Get user ID from meta tag or JS variable
      const userIdMeta = document.querySelector('meta[name="user-id"]');
      const userId = userIdMeta ? parseInt(userIdMeta.getAttribute('content')) : null;
      
      window.NotificationWidget.init({ userId: userId });
    }
  });
})();
