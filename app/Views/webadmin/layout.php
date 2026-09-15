<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <title><?= esc($title ?? 'Chatsapp Admin') ?></title>
    <meta name="description" content="Chatsapp Admin Panel"/>
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>"/>

    <!-- Tabler CSS (lokal, vendor/) -->
    <link href="<?= base_url('assets/vendor/tabler/tabler.min.css') ?>" rel="stylesheet"/>
    <link href="<?= base_url('assets/vendor/tabler/tabler-vendors.min.css') ?>" rel="stylesheet"/>
    <!-- Tabler Icons (lokal, vendor/) -->
    <link href="<?= base_url('assets/vendor/icons/tabler-icons.min.css') ?>" rel="stylesheet"/>
    <!-- DataTables (Bootstrap 5 styling) -->
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap5.min.css') ?>" rel="stylesheet"/>
    <!-- Toastr -->
    <link href="<?= base_url('assets/vendor/toastr/toastr.min.css') ?>" rel="stylesheet"/>

    <!-- Page-specific CSS -->
    <?= $this->renderSection('pageStyles') ?>

    <script>
        var baseUrl = '<?= base_url() ?>';
        var csrfToken = '<?= csrf_hash() ?>';
        var csrfName = '<?= csrf_token() ?>';
    </script>
</head>
<body>
<div class="page">
    <!-- Sidebar -->
    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark">
                <a href="<?= base_url('/') ?>">
                    <i class="ti ti-messages me-2"></i>Chatsapp
                </a>
            </h1>
            <div class="collapse navbar-collapse" id="sidebar-menu">
                <ul class="navbar-nav pt-lg-3">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('/') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-dashboard"></i></span>
                            <span class="nav-link-title">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('users') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-users"></i></span>
                            <span class="nav-link-title">Users</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#sidebar-access" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-shield-lock"></i></span>
                            <span class="nav-link-title">Access Control</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="<?= base_url('roles') ?>">Roles</a>
                            <a class="dropdown-item" href="<?= base_url('permissions') ?>">Permissions</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('conversations') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-messages"></i></span>
                            <span class="nav-link-title">Conversations</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= base_url('chat') ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-message-chatbot"></i></span>
                            <span class="nav-link-title">Live Chat</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#sidebar-system" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="false">
                            <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-settings"></i></span>
                            <span class="nav-link-title">System</span>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="<?= base_url('settings') ?>">Settings</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </aside>
    <!-- Topbar (tanpa navbar-expand-*: agar tidak memicu aturan Tabler
         yang menyembunyikan .navbar-vertical) -->
    <header class="navbar d-print-none">
        <div class="container-xl">
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <i class="ti ti-messages me-2"></i>Chatsapp
            </h1>
            <div class="navbar-nav flex-row order-md-last">
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                        <span class="avatar avatar-sm" id="headerUserAvatar">A</span>
                        <div class="d-none d-xl-block ps-2">
                            <div id="headerUserName">Admin</div>
                            <div class="mt-1 small text-secondary">Administrator</div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                        <a href="<?= base_url('settings') ?>" class="dropdown-item">
                            <i class="ti ti-settings me-2"></i>Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item text-danger" id="btnLogout">
                            <i class="ti ti-logout me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="page-wrapper">
        <!-- Page content (setiap view membawa page-header + page-body sendiri) -->
        <?= $this->renderSection('content') ?>
        <footer class="footer footer-transparent d-print-none">
            <div class="container-xl">
                <div class="row text-center align-items-center">
                    <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                        <ul class="list-inline list-inline-dots mb-0">
                            <li class="list-inline-item">2026 &copy; Chatsapp Admin</li>
                            <li class="list-inline-item">CodeIgniter 4 + Tabler</li>
                        </ul>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</div>

<!-- jQuery -->
<script src="<?= base_url('assets/vendor/jquery/jquery.min.js') ?>"></script>
<!-- Tabler JS (sudah termasuk Bootstrap 5, diekspos sebagai tabler.bootstrap) -->
<script src="<?= base_url('assets/vendor/tabler/tabler.min.js') ?>"></script>
<script>window.bootstrap = window.tabler.bootstrap;</script>
<!-- DataTables -->
<script src="<?= base_url('assets/vendor/datatables/dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap5.min.js') ?>"></script>
<!-- Toastr -->
<script src="<?= base_url('assets/vendor/toastr/toastr.min.js') ?>"></script>

<!-- Page-specific JS -->
<?= $this->renderSection('pageScripts') ?>

<!-- Custom Admin JS -->
<script src="<?= base_url('assets/js/custom/admin.js') ?>"></script>
</body>
</html>
