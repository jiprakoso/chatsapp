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

    // Kalau sudah ada token valid, langsung ke dashboard
    var storedToken = localStorage.getItem('admin_token');
    if (storedToken) {
        $.ajax({
            url: baseUrl + 'api/admin/users/me',
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + storedToken },
            success: function(response) {
                if (response.success) {
                    window.location.href = baseUrl;
                } else {
                    localStorage.removeItem('admin_token');
                }
            },
            error: function() {
                localStorage.removeItem('admin_token');
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
