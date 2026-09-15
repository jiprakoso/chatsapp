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
