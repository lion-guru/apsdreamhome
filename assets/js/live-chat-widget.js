/* APS Dream Home — Public Live Chat Widget */
(function () {
    'use strict';

    const STORAGE_KEY = 'lcw_session';
    const POLL_MS = 4000;
    const WS_RECONNECT_MS = 3000;
    const BASE = (window.BASE_URL || '/apsdreamhome').replace(/\/+$/, '');

    const API = {
        start:   BASE + '/api/chat/start',
        send:    BASE + '/api/chat/send',
        poll:    BASE + '/api/chat/poll',
        config:  BASE + '/api/chat/widget',
    };
    const WS_URL = (window.WS_URL || ('ws://' + (location.hostname || 'localhost') + ':8080'));

    const $ = (sel) => document.querySelector(sel);
    const escape = (s) => String(s || '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    const fmtTime = (ts) => {
        if (!ts) return '';
        const d = new Date(ts.replace(' ', 'T'));
        if (isNaN(d.getTime())) return '';
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    };

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
        }

        init() {
            this.cacheDom();
            this.bindEvents();
            this.fetchConfig();
            if (this.session && this.session.token) {
                this.showThread();
                this.fetchHistory();
                this.connectWs();
            }
        }

        cacheDom() {
            this.root         = $('#lcw-root');
            this.launcher     = $('#lcw-launcher');
            this.launcherIcon = this.launcher ? this.launcher.querySelector('.lcw-launcher-icon') : null;
            this.launcherClose= this.launcher ? this.launcher.querySelector('.lcw-launcher-close') : null;
            this.badge        = $('#lcw-launcher-badge');
            this.win          = $('#lcw-window');
            this.closeBtn     = $('#lcw-close');
            this.prechat      = $('#lcw-prechat');
            this.prechatForm  = $('#lcw-prechat-form');
            this.prechatError = $('#lcw-prechat-error');
            this.startBtn     = $('#lcw-start-btn');
            this.thread       = $('#lcw-thread');
            this.msgsList     = $('#lcw-msgs-list');
            this.typing       = $('#lcw-typing');
            this.inputBar     = $('#lcw-input-bar');
            this.input        = $('#lcw-input');
            this.sendBtn      = $('#lcw-send');
            this.titleEl      = $('#lcw-header-title');
            this.subtitleEl   = $('#lcw-header-subtitle');
        }

        bindEvents() {
            this.launcher.addEventListener('click', () => this.toggle());
            this.closeBtn.addEventListener('click', () => this.close());
            this.prechatForm.addEventListener('submit', (e) => this.onStartSubmit(e));
            this.sendBtn.addEventListener('click', () => this.sendMessage());
            this.input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
            this.input.addEventListener('input', () => {
                this.sendBtn.disabled = !this.input.value.trim() || this.sending;
                this.input.style.height = 'auto';
                this.input.style.height = Math.min(this.input.scrollHeight, 90) + 'px';
            });
        }

        async fetchConfig() {
            try {
                const r = await fetch(API.config, { credentials: 'same-origin' });
                if (!r.ok) return;
                const data = await r.json();
                if (data && data.settings) {
                    if (data.settings.title && this.titleEl) this.titleEl.textContent = data.settings.title;
                    if (data.settings.subtitle && this.subtitleEl) {
                        this.subtitleEl.innerHTML = '<span class="lcw-dot lcw-dot-online"></span> ' + escape(data.settings.subtitle);
                    }
                }
                if (data && data.user && data.user.id) {
                    const nameEl  = $('#lcw-name');
                    const emailEl = $('#lcw-email');
                    if (!nameEl.value && data.user.name)  nameEl.value  = data.user.name;
                    if (!emailEl.value && data.user.email) emailEl.value = data.user.email;
                }
            } catch (_) { /* silent */ }
        }

        toggle() {
            this.opened ? this.close() : this.open();
        }

        open() {
            this.opened = true;
            this.win.hidden = false;
            this.root.classList.add('lcw-open');
            this.launcher.classList.add('lcw-launcher-active');
            this.clearBadge();
            this.scrollToBottom();
            if (this.session && this.session.token) {
                this.startPolling();
                this.connectWs();
            }
        }

        close() {
            this.opened = false;
            this.win.hidden = true;
            this.root.classList.remove('lcw-open');
            this.launcher.classList.remove('lcw-launcher-active');
            this.stopPolling();
            this.disconnectWs();
        }

        loadSession() {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                return raw ? JSON.parse(raw) : null;
            } catch (_) { return null; }
        }

        saveSession() {
            try { localStorage.setItem(STORAGE_KEY, JSON.stringify(this.session)); } catch (_) {}
        }

        clearSession() {
            try { localStorage.removeItem(STORAGE_KEY); } catch (_) {}
            this.session = null;
        }

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
            const name  = $('#lcw-name').value.trim();
            const email = $('#lcw-email').value.trim();
            const phone = $('#lcw-phone').value.trim();
            const msg   = $('#lcw-first-message').value.trim();
            if (!name || name.length < 2) { this.showPrechatError('Please enter your name.'); return; }
            if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
                this.showPrechatError('Please enter a valid email.'); return;
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
                    method: 'POST', body: form, credentials: 'same-origin'
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
            this.prechat.hidden = true;
            this.thread.hidden = false;
            this.inputBar.hidden = false;
            setTimeout(() => this.input && this.input.focus(), 50);
        }

        async fetchHistory() {
            if (!this.session || !this.session.token) return;
            try {
                const r = await fetch(API.poll + '?token=' + encodeURIComponent(this.session.token) + '&last_id=0', {
                    credentials: 'same-origin'
                });
                if (!r.ok) return;
                const data = await r.json();
                if (data && Array.isArray(data.messages)) {
                    data.messages.forEach((m) => {
                        this.renderMessage(m, false);
                        this.lastId = Math.max(this.lastId, m.id);
                    });
                    this.scrollToBottom();
                }
                this.startPolling();
            } catch (_) { this.schedulePoll(); }
        }

        async pollMessages() {
            if (!this.session || !this.session.token) return;
            try {
                const r = await fetch(API.poll + '?token=' + encodeURIComponent(this.session.token) + '&last_id=' + this.lastId, {
                    credentials: 'same-origin'
                });
                if (!r.ok) { this.schedulePoll(); return; }
                const data = await r.json();
                if (data && Array.isArray(data.messages) && data.messages.length) {
                    data.messages.forEach((m) => {
                        this.renderMessage(m, true);
                        this.lastId = Math.max(this.lastId, m.id);
                    });
                    if (!this.opened) this.bumpBadge(data.messages.length);
                }
                this.schedulePoll();
            } catch (_) { this.schedulePoll(); }
        }

        startPolling() {
            this.stopPolling();
            this.schedulePoll();
        }

        stopPolling() {
            if (this.pollTimer) { clearTimeout(this.pollTimer); this.pollTimer = null; }
        }

        schedulePoll() {
            this.stopPolling();
            this.pollTimer = setTimeout(() => this.pollMessages(), POLL_MS);
        }

        // === WebSocket transport (real-time) ===
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
                try {
                    this.ws.send(JSON.stringify({ type: 'auth', token: this.session.token, userId: 0, userRole: 'visitor' }));
                } catch (_) {}
                this.subscribeToChannel();
                this.startWsHeartbeat();
            };
            this.ws.onmessage = (ev) => {
                let data;
                try { data = JSON.parse(ev.data); } catch (_) { return; }
                if (!data || !data.type) return;
                if (data.type === 'channel' && data.channel === this.wsSubscribedChannel && data.payload) {
                    this.handleChannelPayload(data.payload);
                }
            };
            this.ws.onerror = () => { /* will trigger onclose */ };
            this.ws.onclose = () => {
                this.stopWsHeartbeat();
                this.wsSubscribedChannel = null;
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
            // Filter out the visitor's own optimistic messages (already shown immediately)
            if (payload.sender_type === 'visitor') return;
            // Avoid double-rendering messages we already got via polling
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
                    try { this.ws.send(JSON.stringify({ type: 'ping', ts: Date.now() })); } catch (_) {}
                }
            }, 30000);
        }

        stopWsHeartbeat() {
            if (this.wsHeartbeatTimer) { clearInterval(this.wsHeartbeatTimer); this.wsHeartbeatTimer = null; }
        }

        scheduleWsReconnect() {
            if (this.wsRetry > 10) return; // give up after 10 retries
            this.wsRetry++;
            const delay = Math.min(WS_RECONNECT_MS * Math.pow(2, this.wsRetry - 1), 30000);
            setTimeout(() => this.connectWs(), delay);
        }

        disconnectWs() {
            this.stopWsHeartbeat();
            if (this.ws) {
                try { this.ws.close(); } catch (_) {}
                this.ws = null;
                this.wsSubscribedChannel = null;
            }
        }

        async sendMessage() {
            if (this.sending) return;
            const text = (this.input.value || '').trim();
            if (!text) return;
            if (!this.session || !this.session.token) return;
            this.sending = true;
            this.sendBtn.disabled = true;
            this.input.value = '';
            this.input.style.height = 'auto';
            this.renderMessage({
                sender_type: 'visitor',
                sender_name: this.session.name || 'You',
                message: text,
                created_at: new Date().toISOString().slice(0, 19).replace('T', ' ')
            }, true);
            this.scrollToBottom();
            try {
                const form = new FormData();
                form.append('token', this.session.token);
                form.append('message', text);
                const r = await fetch(API.send, { method: 'POST', body: form, credentials: 'same-origin' });
                const data = await r.json();
                if (!data || !data.success) {
                    this.renderSystem('Failed to send. Retrying...', 'error');
                }
            } catch (_) {
                this.renderSystem('Connection lost. Will retry shortly.', 'error');
            } finally {
                this.sending = false;
                this.sendBtn.disabled = false;
            }
        }

        renderMessage(m, animate) {
            if (!m) return;
            const type = m.sender_type || 'system';
            const text = m.message || '';
            const name = m.sender_name || (type === 'visitor' ? 'You' : (type === 'bot' ? 'Bot' : 'Support'));
            const time = fmtTime(m.created_at);
            const div = document.createElement('div');
            div.className = 'lcw-msg lcw-msg-' + type + (animate ? ' lcw-msg-enter' : '');
            let inner = '';
            if (type !== 'visitor' && name) {
                inner += '<div class="lcw-msg-name">' + escape(name) + '</div>';
            }
            inner += '<div class="lcw-msg-bubble">' + escape(text).replace(/\n/g, '<br>') + '</div>';
            if (time) inner += '<div class="lcw-msg-time">' + escape(time) + '</div>';
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

        scrollToBottom() {
            const c = this.msgsList || this.thread;
            if (!c) return;
            requestAnimationFrame(() => { c.scrollTop = c.scrollHeight; });
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
