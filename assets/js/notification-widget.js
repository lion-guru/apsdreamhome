(function() {
  'use strict';
  window.NotificationWidget = {
    pollInterval: 15000,
    timer: null,
    badge: null,
    dropdown: null,
    list: null,
    channel: 'global',

    init: function(options) {
      options = options || {};
      this.badge = document.querySelector(options.badgeSelector || '.notification-badge');
      this.dropdown = document.querySelector(options.dropdownSelector || '.notification-dropdown');
      this.list = document.querySelector(options.listSelector || '.notification-list');
      this.channel = options.channel || 'global';
      this.lastId = 0;

      if (this.dropdown) {
        this.dropdown.addEventListener('show.bs.dropdown', () => this.loadAll());
      }

      this.start();
    },

    start: function() {
      this.poll();
      this.timer = setInterval(() => this.poll(), this.pollInterval);
    },

    stop: function() {
      if (this.timer) clearInterval(this.timer);
    },

    poll: async function() {
      try {
        const res = await fetch((window.BASE_URL || '/apsdreamhome') + '/api/v2/notifications/poll?channel=' + this.channel + '&since_id=' + this.lastId, {
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.unread_count !== undefined) this.updateBadge(data.unread_count);
        if (data.notifications && data.notifications.length) {
          data.notifications.forEach(n => this.showToast(n));
          if (data.last_id) this.lastId = data.last_id;
        }
      } catch (e) { /* silent */ }
    },

    loadAll: async function() {
      try {
        const res = await fetch((window.BASE_URL || '/apsdreamhome') + '/api/v2/notifications/poll?channel=' + this.channel + '&since_id=0', { credentials: 'same-origin' });
        const data = await res.json();
        if (this.list) {
          if (!data.notifications.length) {
            this.list.innerHTML = '<div class="text-center text-muted p-3">No notifications</div>';
          } else {
            this.list.innerHTML = data.notifications.slice(0, 10).map(n => this.renderItem(n)).join('');
          }
        }
      } catch (e) {}
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

    markRead: async function(ids) {
      try {
        await fetch((window.BASE_URL || '/apsdreamhome') + '/api/v2/notifications/read', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ ids: ids })
        });
      } catch (e) {}
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
      window.NotificationWidget.init();
    }
  });
})();
