/**
 * APS Dream Home — Live Chat Widget v2.0
 *
 * Improved version with:
 *  - File/image upload support
 *  - Emoji picker
 *  - Read receipts (✓✓)
 *  - Offline message queue
 *  - Better mobile UX (swipe to close, virtual keyboard handling)
 *  - Connection quality indicator
 *  - Smooth animations & transitions
 *  - Loading states & error recovery
 *  - Image preview in chat
 *  - Agent avatar & online status
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'lcw_session';
  const QUEUE_KEY = 'lcw_offline_queue';
  const POLL_MS = 4000;
  const WS_RECONNECT_MS = 3000;
  const MAX_RETRIES = 10;
  const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
  const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
  const EMOJI_LIST = [
    '😀',
    '😂',
    '😍',
    '🤔',
    '👍',
    '👎',
    '❤️',
    '🔥',
    '🎉',
    '🙏',
    '📞',
    '📍',
    '💰',
    '🏠',
    '✅',
    '⏰',
    '📋',
    '💪',
    '🤝',
    '😊',
  ];

  const BASE = (window.BASE_URL || '/apsdreamhome').replace(/\/+$/, '');
  const API = {
    start: BASE + '/api/chat/start',
    send: BASE + '/api/chat/send',
    poll: BASE + '/api/chat/poll',
    config: BASE + '/api/chat/widget',
    upload: BASE + '/api/chat/upload',
  };
  const WS_URL = window.WS_URL || 'ws://' + (location.hostname || 'localhost') + ':8080';

  const $ = sel => document.querySelector(sel);
  const $$ = sel => document.querySelectorAll(sel);
  const escape = s =>
    String(s || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  const fmtTime = ts => {
    if (!ts) return '';
    const d = new Date(ts.replace(' ', 'T'));
    if (isNaN(d.getTime())) return '';
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };
  const uid = () => '_' + Math.random().toString(36).slice(2, 9);

  /* ─── Emoji Picker ─── */
  class EmojiPicker {
    constructor(onSelect) {
      this.onSelect = onSelect;
      this.el = null;
      this.build();
    }
    build() {
      this.el = document.createElement('div');
      this.el.className = 'lcw-emoji-picker';
      this.el.hidden = true;
      const grid = document.createElement('div');
      grid.className = 'lcw-emoji-grid';
      EMOJI_LIST.forEach(e => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'lcw-emoji-btn';
        btn.textContent = e;
        btn.addEventListener('click', ev => {
          ev.stopPropagation();
          this.onSelect(e);
          this.hide();
        });
        grid.appendChild(btn);
      });
      this.el.appendChild(grid);
    }
    toggle(anchor) {
      if (!this.el.hidden) {
        this.hide();
        return;
      }
      document.body.appendChild(this.el);
      this.el.hidden = false;
      const r = anchor.getBoundingClientRect();
      this.el.style.bottom = window.innerHeight - r.top + 8 + 'px';
      this.el.style.right = window.innerWidth - r.right + 'px';
      const close = ev => {
        if (!this.el.contains(ev.target)) {
          this.hide();
          document.removeEventListener('click', close);
        }
      };
      setTimeout(() => document.addEventListener('click', close), 10);
    }
    hide() {
      this.el.hidden = true;
      if (this.el.parentNode) this.el.parentNode.removeChild(this.el);
    }
  }

  /* ─── Main Widget ─── */
  class LiveChatWidget {
    constructor() {
      this.session = this.loadSession();
      this.opened = false;
      this.pollTimer = null;
      this.lastId = 0;
      this.sending = false;
      this.agentTypingUntil = 0;
      this.ws = null;
      this.wsRetry = 0;
      this.wsHeartbeatTimer = null;
      this.wsSubscribedChannel = null;
      this.offlineQueue = [];
      this.emojiPicker = null;
      this.fileInput = null;
      this.connectionQuality = 'good'; // good | slow | offline
      this.pendingReads = new Set();
    }

    init() {
      this.cacheDom();
      this.loadOfflineQueue();
      this.bindEvents();
      this.fetchConfig();
      this.createFileInput();
      this.emojiPicker = new EmojiPicker(emoji => {
        this.input.value += emoji;
        this.input.dispatchEvent(new Event('input'));
        this.input.focus();
      });
      if (this.session && this.session.token) {
        this.showThread();
        this.fetchHistory();
        this.connectWs();
      }
      this.updateConnectionIndicator();
    }

    cacheDom() {
      this.root = $('#lcw-root');
      this.launcher = $('#lcw-launcher');
      this.launcherIcon = this.launcher ? this.launcher.querySelector('.lcw-launcher-icon') : null;
      this.launcherClose = this.launcher ? this.launcher.querySelector('.lcw-launcher-close') : null;
      this.badge = $('#lcw-launcher-badge');
      this.win = $('#lcw-window');
      this.closeBtn = $('#lcw-close');
      this.prechat = $('#lcw-prechat');
      this.prechatForm = $('#lcw-prechat-form');
      this.prechatError = $('#lcw-prechat-error');
      this.startBtn = $('#lcw-start-btn');
      this.thread = $('#lcw-thread');
      this.msgsList = $('#lcw-msgs-list');
      this.typing = $('#lcw-typing');
      this.inputBar = $('#lcw-input-bar');
      this.input = $('#lcw-input');
      this.sendBtn = $('#lcw-send');
      this.titleEl = $('#lcw-header-title');
      this.subtitleEl = $('#lcw-header-subtitle');
    }

    createFileInput() {
      this.fileInput = document.createElement('input');
      this.fileInput.type = 'file';
      this.fileInput.accept = ALLOWED_TYPES.join(',');
      this.fileInput.hidden = true;
      this.fileInput.addEventListener('change', e => this.handleFileSelect(e));
      document.body.appendChild(this.fileInput);
    }

    bindEvents() {
      if (this.launcher) this.launcher.addEventListener('click', () => this.toggle());
      if (this.closeBtn) this.closeBtn.addEventListener('click', () => this.close());
      if (this.prechatForm) this.prechatForm.addEventListener('submit', e => this.onStartSubmit(e));
      if (this.sendBtn) this.sendBtn.addEventListener('click', () => this.sendMessage());

      if (this.input) {
        this.input.addEventListener('keydown', e => {
          if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            this.sendMessage();
          }
        });

        this.input.addEventListener('input', () => {
          if (this.sendBtn) this.sendBtn.disabled = !this.input.value.trim() || this.sending;
          this.autoResizeInput();
        });
      }

      // Emoji button (add to input bar)
      this.addEmojiButton();
      // Attachment button (add to input bar)
      this.addButton('fa-face-smile', 'Emoji', () => this.emojiPicker.toggle(this.emojiBtn));
      this.addButton('fa-paperclip', 'Attach file', () => this.fileInput.click());

      // Mobile swipe to close
      this.bindTouchGestures();

      // Handle virtual keyboard on mobile
      if ('visualViewport' in window) {
        window.visualViewport.addEventListener('resize', () => this.handleViewportResize());
      }
    }

    addButton(iconClass, label, handler) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'lcw-action-btn';
      btn.title = label;
      btn.innerHTML = `<i class="fas ${iconClass}"></i>`;
      btn.addEventListener('click', handler);
      if (iconClass === 'fa-face-smile') this.emojiBtn = btn;
      if (this.inputBar) this.inputBar.insertBefore(btn, this.input);
    }

    addEmojiButton() {
      /* handled by addButton */
    }

    bindTouchGestures() {
      let startY = 0;
      if (!this.win) return;
      this.win.addEventListener(
        'touchstart',
        e => {
          startY = e.touches[0].clientY;
        },
        { passive: true }
      );
      this.win.addEventListener(
        'touchend',
        e => {
          const diff = startY - e.changedTouches[0].clientY;
          if (diff > 80) this.close(); // swipe up to close
        },
        { passive: true }
      );
    }

    handleViewportResize() {
      if (!this.win || this.win.hidden) return;
      const vh = window.visualViewport.height;
      this.win.style.maxHeight = vh + 'px';
    }

    autoResizeInput() {
      if (!this.input) return;
      this.input.style.height = 'auto';
      this.input.style.height = Math.min(this.input.scrollHeight, 90) + 'px';
    }

    /* ─── Connection Quality ─── */
    updateConnectionIndicator() {
      let indicator = this.root ? this.root.querySelector('.lcw-connection') : null;
      if (!indicator && this.root) {
        indicator = document.createElement('div');
        indicator.className = 'lcw-connection';
        this.root.appendChild(indicator);
      }
      if (!indicator) return;
      if (this.connectionQuality === 'offline') {
        indicator.innerHTML = '<i class="fas fa-wifi-slash"></i> Offline — messages will send when connected';
        indicator.classList.add('show');
      } else if (this.connectionQuality === 'slow') {
        indicator.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Slow connection...';
        indicator.classList.add('show');
      } else {
        indicator.classList.remove('show');
      }
    }

    setConnectionQuality(q) {
      this.connectionQuality = q;
      this.updateConnectionIndicator();
    }

    /* ─── Offline Queue ─── */
    loadOfflineQueue() {
      try {
        const raw = localStorage.getItem(QUEUE_KEY);
        this.offlineQueue = raw ? JSON.parse(raw) : [];
      } catch (_) {
        this.offlineQueue = [];
      }
    }

    saveOfflineQueue() {
      try {
        localStorage.setItem(QUEUE_KEY, JSON.stringify(this.offlineQueue));
      } catch (_) {}
    }

    enqueueMessage(text) {
      this.offlineQueue.push({ id: uid(), text, ts: Date.now() });
      this.saveOfflineQueue();
      this.renderSystem("Message queued — will send when you're back online.", 'info');
    }

    async flushOfflineQueue() {
      if (!this.offlineQueue.length || !this.session || !this.session.token) return;
      const queue = [...this.offlineQueue];
      this.offlineQueue = [];
      this.saveOfflineQueue();
      for (const item of queue) {
        try {
          const form = new FormData();
          form.append('token', this.session.token);
          form.append('message', item.text);
          await fetch(API.send, { method: 'POST', body: form, credentials: 'same-origin' });
        } catch (_) {
          this.offlineQueue.push(item);
          this.saveOfflineQueue();
          break;
        }
      }
    }

    /* ─── Config ─── */
    async fetchConfig() {
      try {
        const r = await fetch(API.config, { credentials: 'same-origin' });
        if (!r.ok) return;
        const data = await r.json();
        if (data && data.settings) {
          if (data.settings.title && this.titleEl) this.titleEl.textContent = data.settings.title;
          if (data.settings.subtitle && this.subtitleEl) {
            this.subtitleEl.innerHTML =
              '<span class="lcw-dot lcw-dot-online"></span> ' + escape(data.settings.subtitle);
          }
        }
        if (data && data.user && data.user.id) {
          const nameEl = $('#lcw-name');
          const emailEl = $('#lcw-email');
          if (nameEl && !nameEl.value && data.user.name) nameEl.value = data.user.name;
          if (emailEl && !emailEl.value && data.user.email) emailEl.value = data.user.email;
        }
      } catch (_) {}
    }

    /* ─── Open / Close ─── */
    toggle() {
      this.opened ? this.close() : this.open();
    }

    open() {
      this.opened = true;
      if (this.win) this.win.hidden = false;
      if (this.root) this.root.classList.add('lcw-open');
      if (this.launcher) this.launcher.classList.add('lcw-launcher-active');
      this.clearBadge();
      this.scrollToBottom();
      if (this.session && this.session.token) {
        this.startPolling();
        this.connectWs();
        this.flushOfflineQueue();
      }
    }

    close() {
      this.opened = false;
      if (this.win) this.win.hidden = true;
      if (this.root) this.root.classList.remove('lcw-open');
      if (this.launcher) this.launcher.classList.remove('lcw-launcher-active');
      this.stopPolling();
      this.disconnectWs();
    }

    /* ─── Session Persistence ─── */
    loadSession() {
      try {
        const raw = localStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
      } catch (_) {
        return null;
      }
    }

    saveSession() {
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(this.session));
      } catch (_) {}
    }

    clearSession() {
      try {
        localStorage.removeItem(STORAGE_KEY);
      } catch (_) {}
      this.session = null;
    }

    /* ─── Pre-chat Form ─── */
    showPrechatError(msg) {
      this.prechatError.textContent = msg;
      this.prechatError.classList.remove('d-none');
    }

    clearPrechatError() {
      this.prechatError.textContent = '';
      this.prechatError.classList.add('d-none');
    }

    onStartSubmit(e) {
      e.preventDefault();
      if (this.sending) return;
      this.clearPrechatError();
      const name = $('#lcw-name').value.trim();
      const email = $('#lcw-email').value.trim();
      const phone = $('#lcw-phone').value.trim();
      const msg = $('#lcw-first-message').value.trim();
      if (!name || name.length < 2) {
        this.showPrechatError('Please enter your name.');
        return;
      }
      if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
        this.showPrechatError('Please enter a valid email.');
        return;
      }
      this.sending = true;
      this.startBtn.disabled = true;
      this.startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Connecting...';
      this.startSession(name, email, phone, msg);
    }

    async startSession(name, email, phone, message) {
      try {
        const form = new FormData();
        form.append('name', name);
        form.append('email', email);
        form.append('phone', phone);
        if (message) form.append('message', message);
        form.append('page_url', location.href);
        form.append('referrer_url', document.referrer || '');

        const r = await fetch(API.start, {
          method: 'POST',
          body: form,
          credentials: 'same-origin',
        });
        const data = await r.json();
        if (data && data.token) {
          this.session = { id: data.id, token: data.token, name: name };
          this.saveSession();
          this.showThread();
          this.fetchHistory();
          this.connectWs();
        } else {
          this.showPrechatError((data && data.error) || 'Could not start chat. Please try again.');
        }
      } catch (e) {
        this.showPrechatError('Network error. Please try again.');
      } finally {
        this.sending = false;
        this.startBtn.disabled = false;
        this.startBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Start chat';
      }
    }

    showThread() {
      if (this.prechat) this.prechat.hidden = true;
      if (this.thread) this.thread.hidden = false;
      if (this.inputBar) this.inputBar.hidden = false;
      setTimeout(() => this.input && this.input.focus(), 50);
    }

    /* ─── History ─── */
    async fetchHistory() {
      if (!this.session || !this.session.token) return;
      try {
        const r = await fetch(API.poll + '?token=' + encodeURIComponent(this.session.token) + '&last_id=0', {
          credentials: 'same-origin',
        });
        if (!r.ok) return;
        const data = await r.json();
        if (data && Array.isArray(data.messages)) {
          data.messages.forEach(m => {
            this.renderMessage(m, false);
            this.lastId = Math.max(this.lastId, m.id);
            if (m.read_at) this.pendingReads.delete(m.id);
          });
          this.scrollToBottom();
        }
        this.startPolling();
      } catch (_) {
        this.schedulePoll();
      }
    }

    /* ─── HTTP Polling ─── */
    async pollMessages() {
      if (!this.session || !this.session.token) return;
      try {
        this.setConnectionQuality('slow');
        const r = await fetch(
          API.poll + '?token=' + encodeURIComponent(this.session.token) + '&last_id=' + this.lastId,
          {
            credentials: 'same-origin',
          }
        );
        if (!r.ok) {
          this.schedulePoll();
          this.setConnectionQuality('slow');
          return;
        }
        const data = await r.json();
        this.setConnectionQuality('good');
        if (data && Array.isArray(data.messages) && data.messages.length) {
          data.messages.forEach(m => {
            this.renderMessage(m, true);
            this.lastId = Math.max(this.lastId, m.id);
            if (m.read_at) this.pendingReads.delete(m.id);
          });
          if (!this.opened) this.bumpBadge(data.messages.length);
        }
        this.schedulePoll();
      } catch (_) {
        this.setConnectionQuality('offline');
        this.schedulePoll();
      }
    }

    startPolling() {
      this.stopPolling();
      this.schedulePoll();
    }
    stopPolling() {
      if (this.pollTimer) {
        clearTimeout(this.pollTimer);
        this.pollTimer = null;
      }
    }
    schedulePoll() {
      this.stopPolling();
      this.pollTimer = setTimeout(() => this.pollMessages(), POLL_MS);
    }

    /* ─── WebSocket ─── */
    connectWs() {
      if (!this.session || !this.session.token) return;
      if (this.ws && (this.ws.readyState === WebSocket.OPEN || this.ws.readyState === WebSocket.CONNECTING)) return;
      try {
        this.ws = new WebSocket(WS_URL);
      } catch (_) {
        this.scheduleWsReconnect();
        return;
      }
      this.ws.onopen = () => {
        this.wsRetry = 0;
        this.setConnectionQuality('good');
        try {
          this.ws.send(JSON.stringify({ type: 'auth', token: this.session.token, userId: 0, userRole: 'visitor' }));
        } catch (_) {}
        this.subscribeToChannel();
        this.startWsHeartbeat();
        this.flushOfflineQueue();
      };
      this.ws.onmessage = ev => {
        let data;
        try {
          data = JSON.parse(ev.data);
        } catch (_) {
          return;
        }
        if (!data || !data.type) return;
        if (data.type === 'channel' && data.channel === this.wsSubscribedChannel && data.payload) {
          this.handleChannelPayload(data.payload);
        }
        // Read receipt from server
        if (data.type === 'read_receipt' && data.message_ids) {
          data.message_ids.forEach(id => this.markMessageRead(id));
        }
        // Agent typing indicator
        if (data.type === 'typing' && data.channel === this.wsSubscribedChannel) {
          this.showAgentTyping();
        }
      };
      this.ws.onerror = () => {};
      this.ws.onclose = () => {
        this.stopWsHeartbeat();
        this.wsSubscribedChannel = null;
        this.setConnectionQuality('offline');
        this.scheduleWsReconnect();
      };
    }

    subscribeToChannel() {
      if (!this.session || !this.session.id) return;
      const channel = 'chat_' + this.session.id;
      try {
        this.ws.send(JSON.stringify({ type: 'subscribe', channel: channel }));
        this.wsSubscribedChannel = channel;
      } catch (_) {}
    }

    handleChannelPayload(payload) {
      if (!payload || payload.event !== 'message') return;
      if (payload.sender_type === 'visitor') return;
      if (payload.message_id && payload.message_id <= this.lastId) return;
      this.renderMessage(payload, true);
      if (payload.message_id) this.lastId = Math.max(this.lastId, payload.message_id);
      if (!this.opened) this.bumpBadge(1);
      this.scrollToBottom();
    }

    startWsHeartbeat() {
      this.stopWsHeartbeat();
      this.wsHeartbeatTimer = setInterval(() => {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
          try {
            this.ws.send(JSON.stringify({ type: 'ping', ts: Date.now() }));
          } catch (_) {}
        }
      }, 30000);
    }

    stopWsHeartbeat() {
      if (this.wsHeartbeatTimer) {
        clearInterval(this.wsHeartbeatTimer);
        this.wsHeartbeatTimer = null;
      }
    }

    scheduleWsReconnect() {
      if (this.wsRetry > MAX_RETRIES) return;
      this.wsRetry++;
      const delay = Math.min(WS_RECONNECT_MS * Math.pow(2, this.wsRetry - 1), 30000);
      setTimeout(() => this.connectWs(), delay);
    }

    disconnectWs() {
      this.stopWsHeartbeat();
      if (this.ws) {
        try {
          this.ws.close();
        } catch (_) {}
        this.ws = null;
        this.wsSubscribedChannel = null;
      }
    }

    /* ─── Typing Indicator ─── */
    showAgentTyping() {
      if (!this.typing) return;
      this.typing.hidden = false;
      clearTimeout(this._typingTimer);
      this._typingTimer = setTimeout(() => {
        this.typing.hidden = true;
      }, 3000);
    }

    hideAgentTyping() {
      if (this.typing) this.typing.hidden = true;
    }

    /* ─── Send Message ─── */
    async sendMessage() {
      if (this.sending) return;
      const text = ((this.input && this.input.value) || '').trim();
      if (!text) return;
      if (!this.session || !this.session.token) return;

      // Offline queue
      if (!navigator.onLine) {
        this.enqueueMessage(text);
        if (this.input) this.input.value = '';
        this.autoResizeInput();
        return;
      }

      this.sending = true;
      if (this.sendBtn) this.sendBtn.disabled = true;
      if (this.input) this.input.value = '';
      this.autoResizeInput();

      const msgId = uid();
      this.renderMessage(
        {
          id: msgId,
          sender_type: 'visitor',
          sender_name: this.session.name || 'You',
          message: text,
          created_at: new Date().toISOString().slice(0, 19).replace('T', ' '),
          _pending: true,
        },
        true
      );
      this.scrollToBottom();

      try {
        const form = new FormData();
        form.append('token', this.session.token);
        form.append('message', text);
        const r = await fetch(API.send, { method: 'POST', body: form, credentials: 'same-origin' });
        const data = await r.json();
        if (!data || !data.success) {
          this.renderSystem('Failed to send. Retrying...', 'error');
          this.enqueueMessage(text);
        } else {
          // Mark message as sent (remove pending indicator)
          this.markMessageSent(msgId);
        }
      } catch (_) {
        this.renderSystem('Connection lost. Message queued.', 'error');
        this.enqueueMessage(text);
      } finally {
        this.sending = false;
        if (this.sendBtn) {
          this.sendBtn.disabled = false;
          this.sendBtn.disabled = !(this.input && this.input.value.trim());
        }
      }
    }

    /* ─── File Upload ─── */
    handleFileSelect(e) {
      const file = e.target.files[0];
      if (!file) return;
      if (file.size > MAX_FILE_SIZE) {
        this.renderSystem('File too large. Max 5MB allowed.', 'error');
        return;
      }
      if (!ALLOWED_TYPES.includes(file.type)) {
        this.renderSystem('File type not supported.', 'error');
        return;
      }
      this.uploadFile(file);
      this.fileInput.value = '';
    }

    async uploadFile(file) {
      if (!this.session || !this.session.token) return;
      this.renderSystem('Uploading ' + file.name + '...', 'info');
      try {
        const form = new FormData();
        form.append('token', this.session.token);
        form.append('file', file);
        const r = await fetch(API.upload, { method: 'POST', body: form, credentials: 'same-origin' });
        const data = await r.json();
        if (data && data.success && data.url) {
          const isImage = file.type.startsWith('image/');
          const text = isImage ? '' : '📎 ' + file.name;
          await this.sendFileMessage(data.url, text, isImage);
        } else {
          this.renderSystem('Upload failed. Please try again.', 'error');
        }
      } catch (_) {
        this.renderSystem('Upload failed. Network error.', 'error');
      }
    }

    async sendFileMessage(url, caption, isImage) {
      if (!this.session || !this.session.token) return;
      try {
        const form = new FormData();
        form.append('token', this.session.token);
        form.append('message', caption || '');
        form.append('file_url', url);
        form.append('file_type', isImage ? 'image' : 'file');
        const r = await fetch(API.send, { method: 'POST', body: form, credentials: 'same-origin' });
        const data = await r.json();
        if (data && data.success) {
          this.renderMessage(
            {
              sender_type: 'visitor',
              sender_name: this.session.name || 'You',
              message: caption || '',
              file_url: url,
              file_type: isImage ? 'image' : 'file',
              created_at: new Date().toISOString().slice(0, 19).replace('T', ' '),
            },
            true
          );
          this.scrollToBottom();
        }
      } catch (_) {
        this.renderSystem('Failed to send file.', 'error');
      }
    }

    /* ─── Render Messages ─── */
    renderMessage(m, animate) {
      if (!m) return;
      const type = m.sender_type || 'system';
      const text = m.message || '';
      const name = m.sender_name || (type === 'visitor' ? 'You' : type === 'bot' ? 'Bot' : 'Support');
      const time = fmtTime(m.created_at);
      const div = document.createElement('div');
      div.className = 'lcw-msg lcw-msg-' + type + (animate ? ' lcw-msg-enter' : '');
      if (m._pending) div.classList.add('lcw-msg-pending');
      div.dataset.msgId = m.id || '';

      let inner = '';
      if (type !== 'visitor' && name) {
        inner += '<div class="lcw-msg-name">' + escape(name) + '</div>';
      }

      // File/image rendering
      if (m.file_url) {
        if (m.file_type === 'image' || /\.(jpe?g|png|gif|webp)$/i.test(m.file_url)) {
          inner +=
            '<div class="lcw-msg-image"><img src="' +
            escape(m.file_url) +
            '" alt="Image" loading="lazy" onclick="window.open(this.src)"></div>';
        } else {
          inner +=
            '<div class="lcw-msg-file"><a href="' +
            escape(m.file_url) +
            '" target="_blank"><i class="fas fa-file me-1"></i>' +
            escape(m.file_url.split('/').pop()) +
            '</a></div>';
        }
      }

      if (text) {
        inner += '<div class="lcw-msg-bubble">' + escape(text).replace(/\n/g, '<br>') + '</div>';
      }

      // Read receipt for visitor messages
      let receipt = '';
      if (type === 'visitor') {
        if (m.read_at || m._read) {
          receipt = '<span class="lcw-receipt lcw-receipt-read">✓✓</span>';
        } else if (!m._pending) {
          receipt = '<span class="lcw-receipt lcw-receipt-sent">✓</span>';
        } else {
          receipt = '<span class="lcw-receipt lcw-receipt-pending"><i class="fas fa-clock"></i></span>';
        }
      }

      if (time || receipt)
        inner +=
          '<div class="lcw-msg-meta">' +
          (time ? '<span class="lcw-msg-time">' + escape(time) + '</span>' : '') +
          receipt +
          '</div>';
      div.innerHTML = inner;
      this.msgsList.appendChild(div);
    }

    renderSystem(text, kind) {
      const div = document.createElement('div');
      div.className = 'lcw-system lcw-system-' + (kind || 'info');
      div.textContent = text;
      this.msgsList.appendChild(div);
      this.scrollToBottom();
    }

    markMessageSent(msgId) {
      const el = this.msgsList.querySelector(`[data-msg-id="${msgId}"]`);
      if (el) {
        el.classList.remove('lcw-msg-pending');
        const receipt = el.querySelector('.lcw-receipt');
        if (receipt) {
          receipt.className = 'lcw-receipt lcw-receipt-sent';
          receipt.innerHTML = '✓';
        }
      }
    }

    markMessageRead(msgId) {
      const el = this.msgsList.querySelector(`[data-msg-id="${msgId}"]`);
      if (el) {
        const receipt = el.querySelector('.lcw-receipt');
        if (receipt) {
          receipt.className = 'lcw-receipt lcw-receipt-read';
          receipt.innerHTML = '✓✓';
        }
      }
    }

    scrollToBottom() {
      const c = this.msgsList || this.thread;
      if (!c) return;
      requestAnimationFrame(() => {
        c.scrollTop = c.scrollHeight;
      });
    }

    bumpBadge(n) {
      if (!this.badge) return;
      const cur = parseInt(this.badge.textContent || '0', 10) || 0;
      const next = cur + n;
      this.badge.textContent = next > 99 ? '99+' : String(next);
      this.badge.hidden = false;
    }

    clearBadge() {
      if (!this.badge) return;
      this.badge.textContent = '0';
      this.badge.hidden = true;
    }
  }

  /* ─── Boot ─── */
  function boot() {
    if (window.LiveChatWidgetInited) return;
    window.LiveChatWidgetInited = true;
    const w = new LiveChatWidget();
    window.LiveChat = w;
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => w.init());
    } else {
      w.init();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
