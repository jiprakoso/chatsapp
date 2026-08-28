<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?= esc($title ?? 'Chatsapp Admin') ?></title>
    <meta name="description" content="Chatsapp Admin Panel"/>
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>"/>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Global CSS (Metronic) -->
    <link href="<?= base_url('assets/plugins/global/plugins.bundle.css') ?>" rel="stylesheet" type="text/css"/>
    <link href="<?= base_url('assets/css/style.bundle.css') ?>" rel="stylesheet" type="text/css"/>
    
    <!-- Page-specific CSS -->
    <?= $this->renderSection('pageStyles') ?>
    
    <script>
        var baseUrl = '<?= base_url() ?>';
        var csrfToken = '<?= csrf_hash() ?>';
        var csrfName = '<?= csrf_token() ?>';
    </script>
</head>
<body id="kt_app_body" class="app-default">
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!-- Page -->
        <div class="d-flex flex-row flex-column-fluid page" id="kt_app_page">
            <!-- Sidebar -->
            <div class="sidebar sidebar-fixed sidebar-bg-primary" id="kt_app_sidebar" data-kt-drawer="true" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default: '200px', '300px': '250px'}" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
                <div class="sidebar-logo" id="kt_app_sidebar_logo">
                    <a href="<?= base_url('admin') ?>">
                        <img alt="Logo" src="<?= base_url('assets/media/logos/logo-dark.png') ?>" class="h-30px theme-light-show"/>
                        <img alt="Logo" src="<?= base_url('assets/media/logos/logo-light.png') ?>" class="h-30px theme-dark-show"/>
                    </a>
                    <div class="sidebar-toggle btn btn-icon btn-flex btn-active-color-primary w-30px h-30px" data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="kt_app_sidebar" data-kt-toggle-name="sidebar-minimize">
                        <i class="ki-duotone ki-element-11 fs-2"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                </div>
                <div class="sidebar-wrapper scroll-y my-5 mx-3" id="kt_app_sidebar_wrapper">
                    <div class="sidebar-divider mt-0"></div>
                    <div class="menu menu-column menu-rounded menu-sub-indents fw-semibold fs-6" id="kt_app_sidebar_menu" data-kt-menu="true">
                        <div class="menu-item">
                            <a class="menu-link" href="<?= base_url('admin') ?>">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-home fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                </span>
                                <span class="menu-title">Dashboard</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= base_url('admin/users') ?>">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-users fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                                <span class="menu-title">Users</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= base_url('admin/roles') ?>">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-shield fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                                <span class="menu-title">Roles</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= base_url('admin/permissions') ?>">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-key fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                                <span class="menu-title">Permissions</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= base_url('admin/conversations') ?>">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-chat fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                                <span class="menu-title">Conversations</span>
                            </a>
                        </div>
                        <div class="menu-item">
                            <a class="menu-link" href="<?= base_url('admin/settings') ?>">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-gear fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                                <span class="menu-title">Settings</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="sidebar-footer py-5" id="kt_app_sidebar_footer">
                    <div class="d-flex flex-center">
                        <a href="<?= base_url('api/auth/login') ?>" class="btn btn-light-primary" target="_blank">
                            <i class="ki-duotone ki-box-arrow-up-left me-2"></i>API Docs
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Wrapper -->
            <div class="d-flex flex-column flex-row-fluid wrapper" id="kt_app_wrapper">
                <!-- Header -->
                <div id="kt_app_header" class="header header-fixed">
                    <div class="container-xl" id="kt_app_header_container">
                        <div class="d-flex align-items-stretch justify-content-between">
                            <div class="d-flex align-items-center" id="kt_app_header_left">
                                <button class="btn btn-icon btn-flex btn-active-light-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
                                    <i class="ki-duotone ki-abstract-11 fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                                <div class="d-flex align-items-center ms-1 ms-lg-3">
                                    <a href="<?= base_url('admin') ?>" class="text-gray-900 text-hover-primary fs-4 fw-bold">Chatsapp Admin</a>
                                </div>
                            </div>
                            <div class="d-flex align-items-center" id="kt_app_header_right">
                                <div class="d-flex align-items-center ms-2">
                                    <div class="btn btn-icon btn-flex btn-active-light-primary w-35px h-35px" data-kt-menu="true" data-kt-menu-placement="bottom-end" data-kt-menu-trigger="click">
                                        <span class="symbol symbol-35px" id="kt_header_user_menu_toggle">
                                            <img alt="User" src="<?= base_url('assets/media/avatars/300-1.jpg') ?>" class="theme-light-show"/>
                                            <img alt="User" src="<?= base_url('assets/media/avatars/300-1.jpg') ?>" class="theme-dark-show"/>
                                        </span>
                                    </div>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold w-275px py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <div class="d-flex flex-column">
                                                <div class="fw-bolder fs-6">Admin User</div>
                                                <div class="fw-semibold text-muted fs-7">admin@chatsapp.local</div>
                                            </div>
                                        </div>
                                        <div class="separator my-2"></div>
                                        <div class="menu-item px-3">
                                            <a href="<?= base_url('api/auth/login') ?>" class="menu-link px-3" target="_blank">
                                                <span class="menu-title">API Documentation</span>
                                            </a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" id="btnLogout">
                                                <span class="menu-title">Logout</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="content d-flex flex-column flex-column-fluid" id="kt_app_content">
                    <div id="kt_app_content_container" class="container-xl">
                        <?= $this->renderSection('content') ?>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="footer" id="kt_app_footer">
                    <div class="container-xl">
                        <div class="d-flex flex-stack flex-wrap gap-3">
                            <div class="text-muted fw-semibold">
                                2026&copy; Chatsapp Admin
                            </div>
                            <div class="text-muted">
                                Built with CodeIgniter 4 + Metronic 8
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Global JS (Metronic) -->
    <script src="<?= base_url('assets/plugins/global/plugins.bundle.js') ?>"></script>
    <script src="<?= base_url('assets/js/scripts.bundle.js') ?>"></script>
    
    <!-- Page-specific JS -->
    <?= $this->renderSection('pageScripts') ?>
    
    <!-- Custom Admin JS -->
    <script src="<?= base_url('assets/js/custom/admin.js') ?>"></script>
</body>
</html>