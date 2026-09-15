<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Admin Login - Chatsapp</title>
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>"/>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Global CSS (Metronic) -->
    <link href="<?= base_url('assets/plugins/global/plugins.bundle.css') ?>" rel="stylesheet" type="text/css"/>
    <link href="<?= base_url('assets/css/style.bundle.css') ?>" rel="stylesheet" type="text/css"/>
</head>
<body class="bg-body">
    <div class="d-flex flex-column flex-center flex-column-fluid" id="kt_app_body">
        <div class="d-flex flex-center flex-column flex-column-fluid">
            <!-- Logo -->
            <a href="<?= base_url('admin') ?>" class="mb-10">
                <img alt="Logo" src="<?= base_url('assets/media/logos/default.svg') ?>" class="h-50px theme-light-show"/>
                <img alt="Logo" src="<?= base_url('assets/media/logos/default-dark.svg') ?>" class="h-50px theme-dark-show"/>
            </a>
            
            <!-- Card -->
            <div class="w-400px rounded card card-flush">
                <div class="card-body py-15 px-10">
                    <h1 class="text-gray-900 fw-bolder fs-2hx mb-2">Sign In</h1>
                    <div class="text-muted fw-semibold fs-6 mb-10">Enter your admin credentials</div>
                    
                    <form id="loginForm" class="fv-plugins-bootstrap5 fv-plugins-framework">
                        <div class="fv-row mb-8">
                            <label class="form-label fw-semibold fs-6">Username or Email</label>
                            <input type="text" name="username_or_email" id="username_or_email" class="form-control form-control-lg form-control-solid" placeholder="admin@chatsapp.local" autocomplete="username" required>
                        </div>
                        
                        
                        <div class="fv-row mb-5">
                            <div class="d-flex flex-stack">
                                <label class="form-label fw-semibold fs-6 mb-0">Password</label>
                                <a href="#" class="text-primary fw-semibold fs-6">Forgot Password?</a>
                            </div>
                            <div class="input-group mt-2">
                                <input type="password" name="password" id="password" class="form-control form-control-lg form-control-solid" placeholder="Enter password" autocomplete="current-password" required>
                                <button type="button" class="btn btn-icon btn-flex btn-active-light-primary" onclick="togglePassword('password')">
                                    <i class="ki-duotone ki-eye fs-3" id="passwordIcon"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="fv-row mb-10">
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                                <label class="form-check-label fw-semibold" for="remember">Remember me</label>
                            </div>
                        </div>
                        
                        <div class="fv-row mb-10">
                            <button type="submit" id="btnLogin" class="btn btn-primary w-100 py-3">
                                <span class="indicator-label">Sign In</span>
                                <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center text-muted fs-6">
                        <span>Chatsapp Admin v1.0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Global JS (Metronic) -->
    <script src="<?= base_url('assets/plugins/global/plugins.bundle.js') ?>"></script>
    <script src="<?= base_url('assets/js/scripts.bundle.js') ?>"></script>
    
    <script>
    $(document).ready(function() {
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            var btn = $('#btnLogin');
            btn.addClass('loading').prop('disabled', true);
            
            var formData = {
                username_or_email: $('#username_or_email').val(),
                password: $('#password').val(),
                remember: $('#remember').is(':checked') ? '1' : ''
            };
            
            $.ajax({
                url: baseUrl + 'admin/login',
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
                    var msg = xhr.responseJSON?.message || 'Login failed. Please try again.';
                    toastr.error(msg);
                },
                complete: function() {
                    btn.removeClass('loading').prop('disabled', false);
                }
            });
        });
        
        // Check for stored token
        var storedToken = localStorage.getItem('admin_token');
        if (storedToken) {
            // Verify token
            $.ajax({
                url: baseUrl + 'api/admin/users/me',
                method: 'GET',
                headers: { 'Authorization': 'Bearer ' + storedToken },
                success: function(response) {
                    if (response.success) {
                        window.location.href = baseUrl + 'admin';
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
            icon.removeClass('ki-eye').addClass('ki-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('ki-eye-slash').addClass('ki-eye');
        }
    }
    
    // Base URL helper
    var baseUrl = '<?= base_url() ?>';
    </script>
</body>
</html>