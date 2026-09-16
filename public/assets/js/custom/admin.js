// Chatsapp Admin - Common JavaScript
// Handles authentication, token storage, and common utilities

window.AdminApp = window.AdminApp || {};

(function() {
    'use strict';

    // Promise yang resolve kalau auth sudah siap (token valid tersimpan).
    // Halaman menunggu ini sebelum menembak API agar tidak terjadi race:
    // request dengan token basi -> 401 -> redirect -> loop.
    var settleReady;
    window.AdminApp.ready = new Promise(function(resolve, reject) {
        settleReady = { resolve: resolve, reject: reject };
    });
    // Cegah unhandled rejection di console saat auth gagal (redirect jalan).
    window.AdminApp.ready.catch(function() {});

    // Check authentication on page load
    $(document).ready(function() {
        initCommonHandlers();
        checkAuth();
    });

    function authReady(token) {
        localStorage.setItem('admin_token', token);
        settleReady.resolve(token);
    }

    function authFailed() {
        handleAuthFailure();
        settleReady.reject(new Error('unauthenticated'));
    }

    function checkAuth() {
        var token = localStorage.getItem('admin_token');
        var currentPath = window.location.pathname;

        // Halaman login tidak perlu token
        if (currentPath === '/login') {
            settleReady.resolve(null);
            return;
        }

        if (!token) {
            // Session server mungkin masih hidup (bukti: halaman ini lolos
            // filter webadminauth). Minta token dari session, bukan redirect.
            syncTokenFromSession();
            return;
        }

        // Validate token with API
        validateToken(token);
    }

    // Ambil JWT milik session aktif; kalau session juga mati, baru ke login.
    function syncTokenFromSession() {
        $.ajax({
            url: baseUrl + 'login/token',
            method: 'GET',
            success: function(response) {
                if (response.success && response.token) {
                    validateToken(response.token);
                } else {
                    authFailed();
                }
            },
            error: function() {
                authFailed();
            }
        });
    }

    function validateToken(token) {
        $.ajax({
            url: baseUrl + 'api/admin/users/me',
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token },
            success: function(response) {
                if (response.success && response.data) {
                    // Token valid, store user info
                    localStorage.setItem('admin_user', JSON.stringify(response.data));
                    updateUserUI(response.data);
                    authReady(token);
                    initNotifications(response.data, token);
                } else {
                    authFailed();
                }
            },
            error: function(xhr) {
                if (xhr.status === 401 || xhr.status === 403) {
                    authFailed();
                }
                // Status lain (mis. 500/network): biarkan halaman mencoba,
                // jangan redirect buta yang bisa memicu loop.
            }
        });
    }

    // ---------- Global realtime notifications ----------
    function hasNotifPermission(perm) {
        var u = window.AdminApp.getUser && window.AdminApp.getUser();
        if (!u || !Array.isArray(u.permissions)) return false;
        if (u.permissions.includes('*') || u.permissions.includes(perm)) return true;
        var prefix = perm.split('.')[0] + '.*';
        if (u.permissions.includes(prefix)) return true;
        return false;
    }
    function canViewAllForNotif() {
        return hasNotifPermission('conversations.view_all') || hasNotifPermission('conversations.manage');
    }
    function initNotifications(user, token) {
        if (!user || !token || window.location.pathname === '/login') return;
        if (window.AdminApp._notifInit) return;
        window.AdminApp._notifInit = true;

        var unreadCount = parseInt(localStorage.getItem('notif_unread') || '0', 10) || 0;
        var prevIds = new Set();
        var prevLast = {}; // id -> last_message_id
        var initialPoll = true;

        function updateBadge() {
            var badge = $('#notifBadge');
            if (!badge.length) return;
            if (unreadCount > 0) {
                badge.text(unreadCount > 99 ? '99+' : String(unreadCount)).show();
                document.title = '(' + unreadCount + ') Chatsapp';
            } else {
                badge.hide();
                document.title = 'Chatsapp Admin';
            }
            localStorage.setItem('notif_unread', String(unreadCount));
        }
        function bumpBadge() { unreadCount++; updateBadge(); }
        window.AdminApp.clearNotifBadge = function() { unreadCount = 0; updateBadge(); $('#notifList').html('<div class="p-3 text-secondary text-center small" id="notifEmpty">No new messages</div>'); };
        window.AdminApp.bumpNotifBadge = bumpBadge;
        updateBadge();

        function addNotifItem(opts) {
            var list = $('#notifList');
            var empty = $('#notifEmpty');
            if (empty.length) empty.remove();
            var time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            var html = '<a href="' + baseUrl + 'chat/' + opts.conversation_id + '" class="list-group-item list-group-item-action notif-item" data-cid="' + opts.conversation_id + '">'
                + '<div class="row g-2 align-items-center"><div class="col"><div class="text-truncate"><strong>' + escapeHtml(opts.title) + '</strong></div>'
                + '<div class="text-secondary small text-truncate">' + escapeHtml(opts.body || '') + '</div></div>'
                + '<div class="col-auto text-secondary small">' + time + '</div></div></a>';
            list.prepend(html);
            // keep max 20
            list.children().slice(20).remove();
        }
        function escapeHtml(t){ if(t==null) return ''; var m={'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}; return String(t).replace(/[&<>"']/g,function(x){return m[x];}); }
        function showToast(msg){ if(window.toastr) toastr.info(msg); }
        function playBeep(){
            try {
                var ctx = new (window.AudioContext || window.webkitAudioContext)();
                var o = ctx.createOscillator(); var g = ctx.createGain();
                o.type='sine'; o.frequency.value=880; o.connect(g); g.connect(ctx.destination);
                g.gain.setValueAtTime(0.12, ctx.currentTime);
                o.start(); g.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime+0.25); o.stop(ctx.currentTime+0.26);
            } catch(e){}
        }

        // Browser Notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            // request on first click to avoid spam
            $(document).one('click', function(){ Notification.requestPermission(); });
        }

        // Socket for realtime
        if (typeof io !== 'undefined') {
            try {
                var sock = io(window.location.protocol + '//' + window.location.hostname + ':4000');
                window.AdminApp.notifSocket = sock;
                sock.on('connect', function(){
                    sock.emit('authenticate', { token: token }, function(res){
                        // autoJoin done server-side
                    });
                });
                sock.on('new_message', function(msg){
                    try {
                        var uid = user.id;
                        if (parseInt(msg.sender_id,10) === parseInt(uid,10)) return;
                        var activeId = window.activeConversationId;
                        var isViewOnly = window.isViewOnly;
                        if (activeId && parseInt(msg.conversation_id,10) === parseInt(activeId,10) && !isViewOnly) return;
                        bumpBadge();
                        var preview = (msg.message || '').substring(0, 48) || 'New message';
                        addNotifItem({ title: preview, body: 'Conversation #' + msg.conversation_id, conversation_id: msg.conversation_id });
                        showToast(preview);
                        playBeep();
                        if ('Notification' in window && Notification.permission === 'granted') {
                            new Notification('New message', { body: preview });
                        }
                    } catch(e){}
                });
            } catch(e){}
        }

        // Poll for new conversations + new messages (REST fallback, supaya pesan via REST tetap ke-notif)
        function pollConversations(){
            var url = canViewAllForNotif() ? baseUrl + 'api/admin/conversations?per_page=200' : baseUrl + 'api/conversations';
            $.ajax({
                url: url,
                method: 'GET',
                headers: { 'Authorization': 'Bearer ' + token },
                success: function(res){
                    var list = [];
                    if (res.success && res.data) {
                        if (Array.isArray(res.data.data)) list = res.data.data;
                        else if (Array.isArray(res.data.conversations)) list = res.data.conversations;
                    }
                    var ids = list.map(function(c){ return String(c.id); });
                    if (initialPoll) {
                        ids.forEach(function(id){ prevIds.add(id); });
                        list.forEach(function(c){
                            var id = String(c.id);
                            prevLast[id] = String(c.last_message_id || '') + '|' + String(c.last_message_at || '');
                        });
                        initialPoll = false;
                        return;
                    }
                    // new conversations
                    ids.forEach(function(id){
                        if (!prevIds.has(id)) {
                            prevIds.add(id);
                            var conv = list.find(function(c){ return String(c.id)===id; });
                            var title = (conv && (conv.display_name || conv.name)) || ('Conversation #' + id);
                            addNotifItem({ title: 'New chat: ' + title, body: 'You were added', conversation_id: id });
                            bumpBadge();
                            showToast('New chat: ' + title);
                            playBeep();
                            if ('Notification' in window && Notification.permission === 'granted') {
                                new Notification('New chat', { body: title });
                            }
                        }
                    });
                    // new messages in existing conversations (via REST)
                    list.forEach(function(c){
                        var id = String(c.id);
                        var curKey = String(c.last_message_id || '') + '|' + String(c.last_message_at || '');
                        var prevKey = prevLast[id];
                        if (prevKey !== undefined && prevKey !== curKey && c.last_message_id) {
                            var senderId = c.last_sender_id;
                            if (String(senderId) === String(user.id)) { /* own message, ignore */ }
                            else {
                                var activeId = window.activeConversationId;
                                var isViewOnly = window.isViewOnly;
                                if (activeId && String(c.id) === String(activeId) && !isViewOnly) {
                                    // sedang buka room ini, jangan badge
                                } else {
                                    var preview = c.last_message_preview || 'New message';
                                    var title = (c.display_name || c.name) || ('Conversation #' + id);
                                    addNotifItem({ title: preview, body: title, conversation_id: id });
                                    bumpBadge();
                                    showToast(preview);
                                    playBeep();
                                    if ('Notification' in window && Notification.permission === 'granted') {
                                        new Notification(title, { body: preview });
                                    }
                                }
                            }
                        }
                        prevLast[id] = curKey;
                    });
                }
            });
        }
        pollConversations();
        setInterval(pollConversations, 7000);

        $(document).on('click', '#notifMarkRead', function(e){
            e.preventDefault();
            window.AdminApp.clearNotifBadge();
        });
        $(document).on('click', '#notifBell', function(){
            // optional: clear on open? keep manual
        });
        // expose
        window.AdminApp._notif = { bumpBadge: bumpBadge, addNotifItem: addNotifItem };
    }

    function handleAuthFailure() {
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        sessionStorage.removeItem('auto_login_ts');
        redirectToLogin();
    }

    function redirectToLogin() {
        if (window.location.pathname !== '/login') {
            window.location.href = baseUrl + 'login';
        }
    }

    function updateUserUI(user) {
        // Update header user info if elements exist (Tabler layout)
        var nameEl = $('#headerUserName');
        if (nameEl.length) nameEl.text(user.name || 'Admin');
        var avatarEl = $('#headerUserAvatar');
        if (avatarEl.length && user.name) avatarEl.text(user.name.charAt(0).toUpperCase());
    }

    function initCommonHandlers() {
        // Logout button
        $(document).on('click', '#btnLogout', function(e) {
            e.preventDefault();
            logout();
        });

        // Handle AJAX errors globally.
        // Abaikan 401 yang terjadi SEBELUM auth siap (halaman belum boleh
        // menembak API) dan 401 dari endpoint validasi/sync itu sendiri.
        $(document).ajaxError(function(event, xhr, settings, error) {
            var url = settings && settings.url ? settings.url : '';
            if (url.indexOf('api/admin/users/me') !== -1 || url.indexOf('login/token') !== -1) {
                return;
            }
            if (xhr.status === 401) {
                handleAuthFailure();
            } else if (xhr.status === 403) {
                toastr.error('Access denied. Insufficient permissions.');
            } else if (xhr.status >= 500) {
                toastr.error('Server error. Please try again later.');
            }
        });

        // CSRF token refresh
        $(document).ajaxSuccess(function(event, xhr, settings) {
            var newToken = xhr.getResponseHeader('X-CSRF-TOKEN');
            if (newToken) {
                csrfToken = newToken;
            }
        });
    }

    function logout() {
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        sessionStorage.removeItem('auto_login_ts');
        window.location.href = baseUrl + 'logout';
    }

    // Expose globally (ready sudah dibuat di atas sebelum IIFE ini jalan)
    window.AdminApp.getToken = function() {
        return localStorage.getItem('admin_token');
    };
    window.AdminApp.getUser = function() {
        var user = localStorage.getItem('admin_user');
        return user ? JSON.parse(user) : null;
    };
    window.AdminApp.setToken = function(token) {
        localStorage.setItem('admin_token', token);
    };
    window.AdminApp.logout = logout;
    window.AdminApp.getAuthHeaders = function() {
        var token = localStorage.getItem('admin_token');
        return token ? { 'Authorization': 'Bearer ' + token } : {};
    };
})();

// Toast configuration
toastr.options = {
    closeButton: true,
    debug: false,
    newestOnTop: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    preventDuplicates: false,
    onclick: null,
    showDuration: 300,
    hideDuration: 1000,
    timeOut: 5000,
    extendedTimeOut: 1000,
    showEasing: 'swing',
    hideEasing: 'linear',
    showMethod: 'fadeIn',
    hideMethod: 'fadeOut'
};
