// Chatsapp Admin - Common JavaScript
// Handles authentication, token storage, and common utilities

(function() {
    'use strict';
    
    // Check authentication on page load
    $(document).ready(function() {
        checkAuth();
        initCommonHandlers();
    });
    
    function checkAuth() {
        var token = localStorage.getItem('admin_token');
        var currentPath = window.location.pathname;

        // Halaman login tidak perlu token
        if (currentPath.startsWith('/admin/login')) {
            return;
        }
        
        if (!token) {
            redirectToLogin();
            return;
        }
        
        // Validate token with API
        validateToken(token);
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
                } else {
                    handleAuthFailure();
                }
            },
            error: function(xhr) {
                if (xhr.status === 401 || xhr.status === 403) {
                    handleAuthFailure();
                }
            }
        });
    }
    
    function handleAuthFailure() {
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        redirectToLogin();
    }
    
    function redirectToLogin() {
        if (!window.location.pathname.startsWith('/admin/login')) {
            window.location.href = baseUrl + 'admin/login';
        }
    }
    
    function updateUserUI(user) {
        // Update header user info if elements exist
        $('#kt_header_user_menu_toggle').attr('title', user.name + ' (' + user.email + ')');
    }
    
    function initCommonHandlers() {
        // Logout button
        $(document).on('click', '#btnLogout', function(e) {
            e.preventDefault();
            logout();
        });
        
        // Handle AJAX errors globally
        $(document).ajaxError(function(event, xhr, settings, error) {
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
        window.location.href = baseUrl + 'admin/login';
    }
    
    // Expose globally
    window.AdminApp = {
        getToken: function() {
            return localStorage.getItem('admin_token');
        },
        getUser: function() {
            var user = localStorage.getItem('admin_user');
            return user ? JSON.parse(user) : null;
        },
        setToken: function(token) {
            localStorage.setItem('admin_token', token);
        },
        logout: logout,
        getAuthHeaders: function() {
            var token = this.getToken();
            return token ? { 'Authorization': 'Bearer ' + token } : {};
        }
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