<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'AdminLTE 3',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>PT. Oceanindo</b> Prima Sarana',
    'logo_img' => 'logo-oceanindo.png',
    'logo_img_class' => 'brand-image',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Logo PT. Oceanindo Prima Sarana',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'logo-oceanindo-full.png',
            'alt' => 'Logo PT. Oceanindo Prima Sarana',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'logo-oceanindo-full.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

   'menu' => [

        /*
        |--------------------------------------------------------------------------
        | WORKSPACE — satu menu per modul, submenu mengikuti tab konsep
        | OPS Fleet Management. Akses tiap item dicek lewat 'can' (gate).
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'WORKSPACE',
        ],

        [
            'text'    => 'Fleet Overview',
            'icon'    => 'fas fa-th-large',
            'submenu' => [
                [
                    'text' => 'Executive Dashboard',
                    'url'  => 'admin/dashboard',
                    'icon' => 'fas fa-home',
                ],
                [
                    'text' => 'Master Data',
                    'url'  => 'admin/master-location',
                    'icon' => 'fas fa-ship',
                    'can'  => 'super-admin',
                ],
                [
                    'text' => 'User Management',
                    'url'  => 'admin/users',
                    'icon' => 'fas fa-users-cog',
                    'can'  => 'super-admin',
                ],
            ],
        ],

        [
            'text'    => 'QHSE',
            'icon'    => 'fas fa-shield-alt',
            'can'     => 'qhse-access',
            'submenu' => [
                [
                    'text'    => 'QHSE Statistics',
                    'icon'    => 'fas fa-chart-line',
                    'submenu' => [
                        [
                            'text' => 'Man Hours',
                            'url'  => 'admin/man-hours',
                            'icon' => 'fas fa-clock',
                        ],
                        [
                            'text' => 'HSSE Statistics',
                            'url'  => 'admin/hsse-statistics',
                            'icon' => 'fas fa-chart-line',
                        ],
                        [
                            'text' => 'Incident Resume',
                            'url'  => 'admin/incident-resume',
                            'icon' => 'fas fa-exclamation-triangle',
                        ],
                    ],
                ],
                [
                    'text' => 'Annual Work Plan',
                    'url'  => 'admin/annual-work-plans',
                    'icon' => 'fas fa-calendar',
                ],
                [
                    'text' => 'Management Review',
                    'url'  => 'admin/management-reviews',
                    'icon' => 'fas fa-users-cog',
                ],
                [
                    'text' => 'Management Visit',
                    'url'  => 'admin/management-visits',
                    'icon' => 'fas fa-clipboard-check',
                ],
                [
                    'text'    => 'Audit & NC Register',
                    'icon'    => 'fas fa-search',
                    'submenu' => [
                        [
                            'text' => 'Checklist Template',
                            'url'  => 'admin/audit-checklist-templates',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Internal Audit',
                            'url'  => 'admin/internal-audits',
                            'icon' => 'fas fa-search',
                        ],
                        [
                            'text' => 'Audit Findings (NC)',
                            'url'  => 'admin/audit-findings',
                            'icon' => 'fas fa-exclamation-circle',
                        ],
                    ],
                ],
                [
                    'text'    => 'Dokumen QHSE',
                    'icon'    => 'fas fa-folder-open',
                    'submenu' => [
                        [
                            'text' => 'Manual',
                            'url'  => 'admin/documents/manual',
                            'icon' => 'fas fa-book',
                        ],
                        [
                            'text' => 'Procedure',
                            'url'  => 'admin/documents/procedure',
                            'icon' => 'fas fa-file-alt',
                        ],
                        [
                            'text' => 'Work Instruction',
                            'url'  => 'admin/documents/work-instruction',
                            'icon' => 'fas fa-file-signature',
                        ],
                        [
                            'text' => 'Form/Checklist',
                            'url'  => 'admin/documents/form-checklist',
                            'icon' => 'fas fa-tasks',
                        ],
                    ],
                ],
            ],
        ],

        [
            'text'    => 'Operation',
            'icon'    => 'fas fa-ship',
            'can'     => 'operation-access',
            'submenu' => [
                [
                    'text' => 'Certificate Register',
                    'url'  => 'admin/vessel-certificates',
                    'icon' => 'fas fa-certificate',
                ],
            ],
        ],

        // Admin Operation tetap melihat Technical untuk PMS & Work Orders;
        // item lain khusus Super Admin.
        [
            'text'    => 'Technical',
            'icon'    => 'fas fa-cogs',
            'can'     => 'operation-access',
            'submenu' => [
                [
                    'text' => 'Docking Plan',
                    'url'  => 'admin/vessel-surveys',
                    'icon' => 'fas fa-anchor',
                    'can'  => 'super-admin',
                ],
                [
                    'text' => 'Defect Register',
                    'url'  => 'admin/damage-reports',
                    'icon' => 'fas fa-exclamation-triangle',
                    'can'  => 'super-admin',
                ],
                [
                    'text' => 'PMS & Work Orders',
                    'url'  => 'admin/maintenance-checklists',
                    'icon' => 'fas fa-tools',
                ],
                [
                    'text'    => 'Budget Planning & Monitoring',
                    'icon'    => 'fas fa-wallet',
                    'can'     => 'super-admin',
                    'submenu' => [
                        [
                            'text' => 'Monitoring',
                            'url'  => 'admin/budgets',
                            'icon' => 'fas fa-chart-bar',
                        ],
                        [
                            'text' => 'Atur Anggaran',
                            'url'  => 'admin/budgets/plan',
                            'icon' => 'fas fa-edit',
                        ],
                        [
                            'text' => 'Dokumen Budget',
                            'url'  => 'admin/documents/budget',
                            'icon' => 'fas fa-folder-open',
                        ],
                    ],
                ],
                [
                    'text' => 'Dokumen Technical',
                    'url'  => 'admin/documents/technical',
                    'icon' => 'fas fa-folder-open',
                    'can'  => 'super-admin',
                ],
            ],
        ],

        [
            'text'    => 'Crewing',
            'icon'    => 'fas fa-user-friends',
            'can'     => 'super-admin',
            'submenu' => [
                [
                    'text' => 'Crew Register',
                    'url'  => 'admin/crews',
                    'icon' => 'fas fa-id-card',
                ],
                [
                    'text' => 'Dokumen Crewing',
                    'url'  => 'admin/documents/crewing',
                    'icon' => 'fas fa-folder-open',
                ],
            ],
        ],

        [
            'text'    => 'Purchasing',
            'icon'    => 'fas fa-shopping-cart',
            'can'     => 'super-admin',
            'submenu' => [
                [
                    'text' => 'Vessel Requisitions',
                    'url'  => 'admin/purchase-requests',
                    'icon' => 'fas fa-file-invoice',
                ],
                [
                    'text' => 'Dokumen Purchasing',
                    'url'  => 'admin/documents/purchasing',
                    'icon' => 'fas fa-folder-open',
                ],
            ],
        ],

        [
            'text' => 'Laporan',
            'url'  => 'admin/laporan',
            'icon' => 'fas fa-file-alt',
        ],

        /*
        |--------------------------------------------------------------------------
        | TOP NAV
        |--------------------------------------------------------------------------
        */

        [
            'topnav_right' => true,
            'type' => 'fullscreen-widget',
        ],

        [
            'text' => 'Logout',
            'url'  => 'force-logout',
            'icon' => 'fas fa-sign-out-alt',
            'topnav_right' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'DesignSystem' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'css/design-tokens.css',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'css/adminlte-theme.css',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'js/theme-toggle.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'js/responsive-tables.js',
                ],
            ],
        ],
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
