/**
 * APS Dream Home — Notification Widget v2.0
 *
 * WebSocket-based real-time push notifications.
 * Complements notification-system.js (HTTP polling + bell UI).
 * This widget adds:
 *  - Instant WebSocket delivery (no 30s delay)
 *  - Toast notifications with action buttons
 *  - Notification grouping (same type within 30s)
 *  - Optional sound alerts (browser notification API)
 *  - Mark-read via both WebSocket + HTTP
 *  - Reconnection with exponential backoff
 *  - Badge auto-sync with notification-system.js
 *
 * Does NOT conflict with notification-system.js — uses its own
 * WebSocket connection, only touches toast container & badge.
 */
(function () {
  'use strict';

  const TOAST_DURATION = 6000;
  const GROUP_WINDOW_MS = 30000;
  const MAX_TOASTS = 4;

  const BASE = (window.BASE_URL || '/apsdreamhome').replace(/\/+$/, '');
  const API = {
    poll: BASE + '/api/v2/notifications/poll',
    read: BASE + '/api/v2/notifications/read',
  };
  const WS_URL =
    window.WS_URL || (location.protocol === 'https:' ? 'wss' : 'ws') + '://' + location.host + '/apsdreamhome/websocket_server.php';

  const escape = s =>
    String(s || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');

  class NotificationWidget {
    constructor() {
      this.ws = null;
      this.reconnectDelay = 2000;
      this.reconnectMax = 30000;
      this.reconnectAttempts = 0;
      this.heartbeatTimer = null;
      this.isConnected = false;
      this.isAuth = false;
      this.userId = null;
      this.lastId = 0;
      this.toastContainer = null;
      this.activeToasts = [];
      this.groupBuffer = [];
      this.groupTimer = null;
      this.soundEnabled = this.loadSoundPref();
      this.audioCtx = null;
    }

    init(options) {
      options = options || {};
      this.userId = options.userId || this.detectUserId();
      this.toastContainer = this.getOrCreateToastContainer();
      this.connect();
      this.requestNotificationPermission();
    }

    detectUserId() {
      const meta = document.querySelector('meta[name="user-id"]');
      return meta ? parseInt(meta.getAttribute('content')) || null : null;
    }

    /* ─── WebSocket ─── */
    connect() {
      if (this.ws && (this.ws.readyState === WebSocket.OPEN || this.ws.readyState === WebSocket.CONNECTING)) return;
      try {
        this.ws = new WebSocket(WS_URL);
      } catch (_) {
        this.scheduleReconnect();
        return;
      }

      this.ws.onopen = () => {
        this.isConnected = true;
        this.reconnectAttempts = 0;
        this.reconnectDelay = 2000;
        this.authenticate();
        this.startHeartbeat();
      };

      this.ws.onmessage = ev => {
        let data;
        try {
          data = JSON.parse(ev.data);
        } catch (_) {
          return;
        }
        this.handleMessage(data);
      };

      this.ws.onerror = () => {};

      this.ws.onclose = () => {
        this.isConnected = false;
        this.isAuth = false;
        this.stopHeartbeat();
        this.scheduleReconnect();
      };
    }

    authenticate() {
      if (!this.ws || this.ws.readyState !== WebSocket.OPEN) return;
      const token = this.getAuthToken();
      this.ws.send(
        JSON.stringify({
          type: 'auth',
          token: token,
          userId: this.userId,
          userRole: this.getUserRole(),
        })
      );
    }

    handleMessage(data) {
      if (!data || !data.type) return;
      switch (data.type) {
        case 'auth':
          this.isAuth = data.status === 'success';
          break;
        case 'notification':
          if (data.data) this.onNotification(data.data);
          break;
        case 'mark_read_result':
          break;
        case 'pong':
          break;
        case 'connection':
          break;
        case 'error':
          break;
      }
    }

    onNotification(n) {
      // Parse payload
      const payload = this.parsePayload(n.payload);
      const title = payload.title || payload.message || 'New notification';
      const body = payload.body || '';
      const type = payload.type || n.event_type || 'info';
      const notifId = n.id || null;
      const actionUrl = payload.url || payload.action_url || null;

      // Group similar notifications
      this.addToGroup({ title, body, type, notifId, actionUrl, ts: Date.now() });

      // Update badge (sync with notification-system.js if present)
      this.incrementBadge();

      // Browser notification (if permitted)
      this.sendBrowserNotification(title, body, actionUrl);

      // Audio alert
      if (this.soundEnabled) this.playNotificationSound();
    }

    /* ─── Notification Grouping ─── */
    addToGroup(item) {
      this.groupBuffer.push(item);
      if (!this.groupTimer) {
        this.groupTimer = setTimeout(() => this.flushGroup(), GROUP_WINDOW_MS);
      }
    }

    flushGroup() {
      this.groupTimer = null;
      if (!this.groupBuffer.length) return;

      const group = [...this.groupBuffer];
      this.groupBuffer = [];

      if (group.length === 1) {
        this.showToast(group[0]);
      } else {
        // Group by type
        const byType = {};
        group.forEach(item => {
          const key = item.type || 'info';
          if (!byType[key]) byType[key] = [];
          byType[key].push(item);
        });
        Object.keys(byType).forEach(type => {
          const items = byType[type];
          if (items.length === 1) {
            this.showToast(items[0]);
          } else {
            this.showToast({
              title: items[0].title,
              body: `${items.length} new notifications`,
              type: type,
              actionUrl: items[0].actionUrl,
              grouped: true,
              count: items.length,
              items: items,
            });
          }
        });
      }
    }

    /* ─── Toast Rendering ─── */
    showToast(item) {
      // Limit active toasts
      while (this.activeToasts.length >= MAX_TOASTS) {
        const old = this.activeToasts.shift();
        if (old && old.parentNode) old.remove();
      }

      const toast = document.createElement('div');
      toast.className = 'lcw-toast lcw-toast-' + (item.type || 'info');
      toast.setAttribute('role', 'alert');

      const iconMap = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle',
        campaign: 'fa-bullhorn',
        system: 'fa-cog',
      };
      const icon = iconMap[item.type] || 'fa-info-circle';

      let bodyHtml = escape(item.body || '');
      if (item.grouped && item.count > 1) {
        bodyHtml = `<strong>${item.count} notifications</strong><br>` + bodyHtml;
      }

      toast.innerHTML = `
                <div class="lcw-toast-icon"><i class="fas ${icon}"></i></div>
                <div class="lcw-toast-content">
                    <div class="lcw-toast-title">${escape(item.title)}</div>
                    ${bodyHtml ? '<div class="lcw-toast-body">' + bodyHtml + '</div>' : ''}
                    <div class="lcw-toast-time">${this.timeAgo(item.ts)}</div>
                </div>
                <div class="lcw-toast-actions">
                    ${item.actionUrl ? '<button class="lcw-toast-btn lcw-toast-view" title="View"><i class="fas fa-arrow-right"></i></button>' : ''}
                    <button class="lcw-toast-btn lcw-toast-dismiss" title="Dismiss"><i class="fas fa-times"></i></button>
                </div>
            `;

      // Event handlers
      const dismissBtn = toast.querySelector('.lcw-toast-dismiss');
      if (dismissBtn) dismissBtn.addEventListener('click', () => this.dismissToast(toast));

      const viewBtn = toast.querySelector('.lcw-toast-view');
      if (viewBtn && item.actionUrl) {
        viewBtn.addEventListener('click', () => {
          if (item.notifId) this.markRead([item.notifId]);
          window.location.href = item.actionUrl;
        });
      }

      // Click on toast body to view
      toast.addEventListener('click', e => {
        if (e.target.closest('.lcw-toast-btn')) return;
        if (item.actionUrl) {
          if (item.notifId) this.markRead([item.notifId]);
          window.location.href = item.actionUrl;
        } else if (item.notifId) {
          this.markRead([item.notifId]);
        }
      });

      // Auto-dismiss
      setTimeout(() => this.dismissToast(toast), TOAST_DURATION);

      // Animate in
      this.toastContainer.appendChild(toast);
      this.activeToasts.push(toast);
      requestAnimationFrame(() => toast.classList.add('show'));
    }

    dismissToast(toast) {
      if (!toast || !toast.parentNode) return;
      toast.classList.remove('show');
      setTimeout(() => {
        if (toast.parentNode) toast.remove();
        this.activeToasts = this.activeToasts.filter(t => t !== toast);
      }, 300);
    }

    /* ─── Badge ─── */
    incrementBadge() {
      // Sync with notification-system.js badge if present
      const badge = document.getElementById('notificationBadge');
      if (badge) {
        const current = parseInt(badge.textContent || '0', 10) || 0;
        badge.textContent = current + 1;
        badge.style.display = 'block';
      }
      // Also update notification-system.js unread count
      if (window.notificationSystem && typeof window.notificationSystem.loadNotifications === 'function') {
        // Don't call loadNotifications here — let the 30s periodic update handle it
      }
    }

    /* ─── Mark Read ─── */
    async markRead(ids) {
      if (!ids || !ids.length) return;
      // HTTP
      try {
        await fetch(API.read, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ ids: ids }),
        });
      } catch (_) {}
      // WebSocket
      if (this.ws && this.ws.readyState === WebSocket.OPEN && this.isAuth) {
        try {
          this.ws.send(JSON.stringify({ type: 'mark_read', ids: ids }));
        } catch (_) {}
      }
    }

    /* ─── Browser Notifications ─── */
    requestNotificationPermission() {
      if (!('Notification' in window)) return;
      if (Notification.permission === 'default') {
        Notification.requestPermission();
      }
    }

    sendBrowserNotification(title, body, url) {
      if (!('Notification' in window) || Notification.permission !== 'granted') return;
      try {
        const n = new Notification(title, {
          body: body || '',
          icon: (window.BASE_URL || '/apsdreamhome') + '/assets/images/logo.png',
          badge: (window.BASE_URL || '/apsdreamhome') + '/assets/images/logo.png',
          tag: 'aps-' + Date.now(),
        });
        if (url) {
          n.onclick = () => {
            window.focus();
            window.location.href = url;
            n.close();
          };
        }
        setTimeout(() => n.close(), 8000);
      } catch (_) {}
    }

    /* ─── Sound ─── */
    loadSoundPref() {
      try {
        return localStorage.getItem('lcw_notif_sound') !== 'off';
      } catch (_) {
        return true;
      }
    }

    toggleSound() {
      this.soundEnabled = !this.soundEnabled;
      try {
        localStorage.setItem('lcw_notif_sound', this.soundEnabled ? 'on' : 'off');
      } catch (_) {}
    }

    playNotificationSound() {
      try {
        if (!this.audioCtx) this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const ctx = this.audioCtx;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.setValueAtTime(800, ctx.currentTime);
        osc.frequency.setValueAtTime(600, ctx.currentTime + 0.1);
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.3);
      } catch (_) {}
    }

    /* ─── Heartbeat ─── */
    startHeartbeat() {
      this.stopHeartbeat();
      this.heartbeatTimer = setInterval(() => {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
          try {
            this.ws.send(JSON.stringify({ type: 'ping', timestamp: Date.now() }));
          } catch (_) {}
        }
      }, 30000);
    }

    stopHeartbeat() {
      if (this.heartbeatTimer) {
        clearInterval(this.heartbeatTimer);
        this.heartbeatTimer = null;
      }
    }

    /* ─── Reconnect ─── */
    scheduleReconnect() {
      this.reconnectAttempts++;
      const delay = Math.min(this.reconnectDelay * Math.pow(1.5, this.reconnectAttempts - 1), this.reconnectMax);
      setTimeout(() => this.connect(), delay);
    }

    disconnect() {
      this.stopHeartbeat();
      if (this.groupTimer) {
        clearTimeout(this.groupTimer);
        this.groupTimer = null;
      }
      if (this.ws) {
        try {
          this.ws.close();
        } catch (_) {}
        this.ws = null;
      }
      this.isConnected = false;
      this.isAuth = false;
    }

    /* ─── Helpers ─── */
    getAuthToken() {
      const meta = document.querySelector('meta[name="csrf-token"]');
      if (meta) return meta.getAttribute('content');
      return sessionStorage.getItem('auth_token') || '';
    }

    getUserRole() {
      const meta = document.querySelector('meta[name="user-role"]');
      return meta ? meta.getAttribute('content') : 'customer';
    }

    getOrCreateToastContainer() {
      let c = document.getElementById('lcw-toast-container');
      if (!c) {
        c = document.createElement('div');
        c.id = 'lcw-toast-container';
        c.className = 'lcw-toast-container';
        document.body.appendChild(c);
      }
      return c;
    }

    parsePayload(payload) {
      try {
        return typeof payload === 'string' ? JSON.parse(payload) : payload || {};
      } catch (_) {
        return {};
      }
    }

    timeAgo(ts) {
      const seconds = Math.floor((Date.now() - ts) / 1000);
      if (seconds < 60) return 'just now';
      if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
      if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
      return Math.floor(seconds / 86400) + 'd ago';
    }
  }

  /* ─── Auto-init ─── */
  document.addEventListener('DOMContentLoaded', function () {
    // Only init if user is logged in (meta tag present)
    const userIdMeta = document.querySelector('meta[name="user-id"]');
    if (!userIdMeta) return;

    const widget = new NotificationWidget();
    widget.init({ userId: parseInt(userIdMeta.getAttribute('content')) || null });
    window.NotificationWidget = widget;
  });
})();
