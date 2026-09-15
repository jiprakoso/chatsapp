<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <title>Admin Login - Chatsapp</title>
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>"/>

    <!-- Tabler CSS (lokal, vendor/) -->
    <link href="<?= base_url('assets/vendor/tabler/tabler.min.css') ?>" rel="stylesheet"/>
    <link href="<?= base_url('assets/vendor/icons/tabler-icons.min.css') ?>" rel="stylesheet"/>
    <!-- Toastr -->
    <link href="<?= base_url('assets/vendor/toastr/toastr.min.css') ?>" rel="stylesheet"/>
</head>
<body class="d-flex flex-column">
<div class="page page-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <a href="<?= base_url('/') ?>" class="navbar-brand navbar-brand-autodark">
                <i class="ti ti-messages me-2"></i>Chatsapp Admin
            </a>
        </div>
        <div class="card card-md">
            <div class="card-body">
                <h2 class="h2 text-center mb-4">Sign In</h2>
                <div class="text-secondary text-center mb-4">Enter your admin credentials</div>

                <form id="loginForm" autocomplete="off" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Username or Email</label>
                        <input type="text" name="username_or_email" id="username_or_email" class="form-control" placeholder="admin@chatsapp.local" autocomplete="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group input-group-flat">
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" autocomplete="current-password" required>
                            <span class="input-group-text">
                                <a href="#" class="link-secondary" title="Show password" data-bs-toggle="tooltip" onclick="togglePassword('password'); return false;">
                                    <i class="ti ti-eye" id="passwordIcon"></i>
                                </a>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <span class="form-check-label">Remember me</span>
                        </label>
                    </div>
                    <div class="form-footer">
                        <button type="submit" id="btnLogin" class="btn btn-primary w-100">Sign In</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center text-secondary mt-3">Chatsapp Admin v1.0</div>
    </div>
</div>

<!-- jQuery -->
<script src="<?= base_url('assets/vendor/jquery/jquery.min.js') ?>"></script>
<!-- Tabler JS (sudah termasuk Bootstrap 5) -->
<script src="<?= base_url('assets/vendor/tabler/tabler.min.js') ?>"></script>
<!-- Toastr -->
<script src="<?= base_url('assets/vendor/toastr/toastr.min.js') ?>"></script>

<script>
var baseUrl = '<?= base_url() ?>';

$(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        var btn = $('#btnLogin');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Please wait...');

        var formData = {
            username_or_email: $('#username_or_email').val(),
            password: $('#password').val(),
            remember: $('#remember').is(':checked') ? '1' : ''
        };

        $.ajax({
            url: baseUrl + 'login',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    localStorage.setItem('admin_token', response.token);
                    toastr.success('Login successful');
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 500);
                } else {
                    toastr.error(response.message || 'Login failed');
                }
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Login failed. Please try again.';
                toastr.error(msg);
            },
            complete: function() {
                btn.prop('disabled', false).text('Sign In');
            }
        });
    });

    // Baru saja logout: buang token sisa supaya tidak auto-redirect dan loop.
    var params = new URLSearchParams(window.location.search);
    if (params.has('logged_out')) {
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        sessionStorage.removeItem('auto_login_ts');
        // Bersihkan query agar refresh tidak mengulang
        window.history.replaceState({}, '', baseUrl + 'login');
    }

    // Kalau sudah ada token valid, langsung ke dashboard.
    // Pemutus loop: kalau kita baru saja auto-redirect (<5 detik lalu) tapi
    // terlempar kembali ke /login (sesi server mati), token dianggap basi.
    var storedToken = localStorage.getItem('admin_token');
    var lastAuto = parseInt(sessionStorage.getItem('auto_login_ts') || '0', 10);
    if (storedToken && (Date.now() - lastAuto < 5000)) {
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        sessionStorage.removeItem('auto_login_ts');
        storedToken = null;
        toastr.warning('Session ended, please sign in again.');
    }
    // Jalur 1: session server masih hidup -> minta tokennya, langsung masuk.
    // Ini yang memulihkan kondisi "session hidup tapi token hilang".
    $.ajax({
        url: baseUrl + 'login/token',
        method: 'GET',
        success: function(response) {
            if (response.success && response.token) {
                localStorage.setItem('admin_token', response.token);
                sessionStorage.setItem('auto_login_ts', String(Date.now()));
                window.location.href = baseUrl;
            } else {
                tryStoredToken(storedToken);
            }
        },
        error: function() {
            tryStoredToken(storedToken);
        }
    });

    // Jalur 2: tidak ada session -> coba bangun dari JWT simpanan.
    function tryStoredToken(token) {
        if (!token) return;
        // Lewat refresh: token valid -> session server dibangun ulang dulu,
        // baru redirect. Tidak mungkin terlempar balik ke /login.
        $.ajax({
            url: baseUrl + 'login/refresh',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ token: token }),
            success: function(response) {
                if (response.success) {
                    sessionStorage.setItem('auto_login_ts', String(Date.now()));
                    window.location.href = response.redirect;
                } else {
                    localStorage.removeItem('admin_token');
                    localStorage.removeItem('admin_user');
                }
            },
            error: function() {
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user');
            }
        });
    }
});

function togglePassword(inputId) {
    var input = $('#' + inputId);
    var icon = $('#' + inputId + 'Icon');
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('ti-eye').addClass('ti-eye-off');
    } else {
        input.attr('type', 'password');
        icon.removeClass('ti-eye-off').addClass('ti-eye');
    }
}
</script>
</body>
</html>
