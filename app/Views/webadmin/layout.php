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
    
    <!-- Global Stylesheets Bundle (mandatory for all pages) -->
    <link href="<?= base_url('assets/plugins/global/plugins.bundle.css') ?>" rel="stylesheet" type="text/css"/>
    <link href="<?= base_url('assets/css/style.bundle.css') ?>" rel="stylesheet" type="text/css"/>
    <link href="<?= base_url('assets/plugins/custom/datatables/datatables.bundle.css') ?>" rel="stylesheet" type="text/css"/>
    
    <!-- Page-specific CSS -->
    <?= $this->renderSection('pageStyles') ?>
    
    <script>
        var baseUrl = '<?= base_url() ?>';
        var csrfToken = '<?= csrf_hash() ?>';
        var csrfName = '<?= csrf_token() ?>';
    </script>
</head>
<!--begin::Body-->
<body id="kt_app_body" 
    data-kt-app-layout="light-sidebar"
    data-kt-app-header-fixed="true"
    data-kt-app-sidebar-enabled="true"
    data-kt-app-sidebar-fixed="true"
    data-kt-app-sidebar-hoverable="true"
    data-kt-app-sidebar-push-header="true"
    data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true"
    data-kt-app-toolbar-enabled="true"
    class="app-default">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup on page load-->
    <!--begin::App-->
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <!--begin::Page-->
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            <!--begin::Header-->
            <div id="kt_app_header" class="app-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}" data-kt-sticky-name="app-header-minimize" data-kt-sticky-offset="{default: '200px', lg: '0'}" data-kt-sticky-animation="false">
                <!--begin::Header container-->
                <div class="app-container container-fluid d-flex align-items-stretch justify-content-between" id="kt_app_header_container">
                    <!--begin::Sidebar mobile toggle-->
                    <div class="d-flex align-items-center d-lg-none ms-n3 me-1 me-md-2" title="Show sidebar menu">
                        <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
                            <i class="ki-outline ki-abstract-14 fs-2 fs-md-1"></i>
                        </div>
                    </div>
                    <!--end::Sidebar mobile toggle-->
                    <!--begin::Mobile logo-->
                    <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
                        <a href="<?= base_url('admin') ?>" class="d-lg-none">
                            <img alt="Logo" src="<?= base_url('assets/media/logos/default-small.svg') ?>" class="h-30px" />
                        </a>
                    </div>
                    <!--end::Mobile logo-->
                    <!--begin::Header wrapper-->
                    <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
                        <!--begin::Header menu-->
                        <div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="{default: 'append', lg: 'prepend'}" data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
                            <!--begin::Menu-->
                            <div class="menu menu-rounded menu-column menu-lg-row my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0" id="kt_app_header_menu" data-kt-menu="true">
                                <!--begin::User menu-->
                                <div class="menu-item me-0 me-lg-2" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-end">
                                    <!--begin::Menu link-->
                                    <span class="menu-link">
                                        <span class="symbol symbol-25px" id="kt_header_user_menu_toggle">
                                            <img alt="User" src="<?= base_url('assets/media/avatars/300-1.jpg') ?>" class="theme-light-show"/>
                                            <img alt="User" src="<?= base_url('assets/media/avatars/300-1.jpg') ?>" class="theme-dark-show"/>
                                        </span>
                                    </span>
                                    <!--end::Menu link-->
                                </div>
                                <!--end::User menu-->
                            </div>
                            <!--end::Menu-->
                        </div>
                        <!--end::Header menu-->
                    </div>
                    <!--end::Header wrapper-->
                </div>
                <!--end::Header container-->
            </div>
            <!--end::Header-->
            <!--begin::Wrapper-->
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                <!--begin::Sidebar-->
                <div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
                    <!--begin::Logo-->
                    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
                        <!--begin::Logo image-->
                        <a href="<?= base_url('admin') ?>">
                            <img alt="Logo" src="<?= base_url('assets/media/logos/default.svg') ?>" class="h-25px app-sidebar-logo-default theme-light-show" />
                            <img alt="Logo" src="<?= base_url('assets/media/logos/default-dark.svg') ?>" class="h-25px app-sidebar-logo-default theme-dark-show" />
                            <img alt="Logo" src="<?= base_url('assets/media/logos/default-small.svg') ?>" class="h-20px app-sidebar-logo-minimize" />
                        </a>
                        <!--end::Logo image-->
                        <!--begin::Sidebar toggle-->
                        <div id="kt_app_sidebar_toggle" class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate" data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body" data-kt-toggle-name="app-sidebar-minimize">
                            <i class="ki-outline ki-black-left-line fs-3 rotate-180"></i>
                        </div>
                        <!--end::Sidebar toggle-->
                    </div>
                    <!--end::Logo-->
                    <!--begin::sidebar menu-->
                    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
                        <!--begin::Menu wrapper-->
                        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
                            <!--begin::Scroll wrapper-->
                            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer" data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
                                <!--begin::Menu-->
                                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">
                                    <!--begin::Menu item - Dashboard-->
                                    <div class="menu-item">
                                        <a class="menu-link" href="<?= base_url('admin') ?>">
                                            <span class="menu-icon">
                                                <i class="ki-outline ki-element-11 fs-2"></i>
                                            </span>
                                            <span class="menu-title">Dashboard</span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                    
                                    <!--begin::Menu item - Users-->
                                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                        <span class="menu-link">
                                            <span class="menu-icon">
                                                <i class="ki-outline ki-users fs-2"></i>
                                            </span>
                                            <span class="menu-title">Users</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        <div class="menu-sub menu-sub-accordion">
                                            <div class="menu-item">
                                                <a class="menu-link" href="<?= base_url('admin/users') ?>">
                                                    <span class="menu-bullet">
                                                        <span class="bullet bullet-dot"></span>
                                                    </span>
                                                    <span class="menu-title">List</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Menu item-->
                                    
                                    <!--begin::Menu item - Roles & Permissions-->
                                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                        <span class="menu-link">
                                            <span class="menu-icon">
                                                <i class="ki-outline ki-shield fs-2"></i>
                                            </span>
                                            <span class="menu-title">Access Control</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        <div class="menu-sub menu-sub-accordion">
                                            <div class="menu-item">
                                                <a class="menu-link" href="<?= base_url('admin/roles') ?>">
                                                    <span class="menu-bullet">
                                                        <span class="bullet bullet-dot"></span>
                                                    </span>
                                                    <span class="menu-title">Roles</span>
                                                </a>
                                            </div>
                                            <div class="menu-item">
                                                <a class="menu-link" href="<?= base_url('admin/permissions') ?>">
                                                    <span class="menu-bullet">
                                                        <span class="bullet bullet-dot"></span>
                                                    </span>
                                                    <span class="menu-title">Permissions</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Menu item-->
                                    
                                    <!--begin::Menu item - Conversations-->
                                    <div class="menu-item">
                                        <a class="menu-link" href="<?= base_url('admin/conversations') ?>">
                                            <span class="menu-icon">
                                                <i class="ki-outline ki-chat fs-2"></i>
                                            </span>
                                            <span class="menu-title">Conversations</span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                    
                                    <!--begin::Menu item - Settings-->
                                    <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                                        <span class="menu-link">
                                            <span class="menu-icon">
                                                <i class="ki-outline ki-gear fs-2"></i>
                                            </span>
                                            <span class="menu-title">System</span>
                                            <span class="menu-arrow"></span>
                                        </span>
                                        <div class="menu-sub menu-sub-accordion">
                                            <div class="menu-item">
                                                <a class="menu-link" href="<?= base_url('admin/settings') ?>">
                                                    <span class="menu-bullet">
                                                        <span class="bullet bullet-dot"></span>
                                                    </span>
                                                    <span class="menu-title">Settings</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::Menu-->
                            </div>
                            <!--end::Scroll wrapper-->
                        </div>
                        <!--end::Menu wrapper-->
                    </div>
                    <!--end::sidebar menu-->
                    <!--begin::Sidebar footer-->
                    <div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
                        <a href="<?= base_url('api/auth/login') ?>" class="btn btn-flex btn-color-primary w-100" target="_blank">
                            <span class="d-flex align-items-center justify-content-center">
                                <i class="ki-outline ki-exit-up fs-2 me-2"></i>
                                <span class="fw-bold">API Documentation</span>
                            </span>
                        </a>
                    </div>
                    <!--end::Sidebar footer-->
                </div>
                <!--end::Sidebar-->
                
                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <!--begin::Header (already defined above)-->
                    
                    <!--begin::Content-->
                    <div class="content d-flex flex-column flex-column-fluid" id="kt_app_content">
                        <div id="kt_app_content_container" class="container-fluid">
                            <?= $this->renderSection('content') ?>
                        </div>
                    </div>
                    <!--end::Content-->
                    
                    <!--begin::Footer-->
                    <div class="footer" id="kt_app_footer">
                        <div class="container-fluid">
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
                    <!--end::Footer-->
                </div>
                <!--end::Main-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::App-->
    
    <!--begin::Global JS(mandatory for all pages)-->
    <script src="<?= base_url('assets/plugins/global/plugins.bundle.js') ?>"></script>
    <script src="<?= base_url('assets/js/scripts.bundle.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/custom/datatables/datatables.bundle.js') ?>"></script>
    <!--end::Global JS-->
    
    <!-- Page-specific JS -->
    <?= $this->renderSection('pageScripts') ?>
    
    <!-- Custom Admin JS -->
    <script src="<?= base_url('assets/js/custom/admin.js') ?>"></script>
</body>
</html>