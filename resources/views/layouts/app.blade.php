<!doctype html>
<html lang="en">
<head>
    <title>@yield('title', 'Dashboard') | نظام أثر الالكتروني </title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="نظام أثر الالكتروني   - نظام إدارة متكامل" />
    <meta name="author" content="Safe Tech" />

    <link rel="icon" href="{{ asset('logo-primary.png') }}" type="image/x-icon" />
    
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Changa:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/fonts/inter/inter.css') }}" id="main-font-link" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/phosphor/duotone/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link" />
    <link rel="stylesheet" href="{{ asset('assets/css/style-preset.css') }}" />

    <style>
        body, .pc-sidebar, .pc-header, .card, .btn, .dropdown-item, .nav-link, h1, h2, h3, h4, h5, h6 {
            font-family: 'Changa', sans-serif;
        }
        
        /* RTL Support for Arabic text */
        .arabic-text {
            direction: rtl;
            text-align: right;
        }
        
        /* Improve font rendering for Arabic */
        .pc-navbar li a span,
        .pc-user-links a span,
        .dropdown-item span {
            font-weight: 400;
        }


    /* توحيد الأيقونة داخل pc-micon حتى لو كانت <i> */
      .pc-micon { width: 46px; display:inline-flex; align-items:center; justify-content:center; }
      .pc-micon .pc-icon { font-size: 20px; line-height: 1; } /* لـ <i class="fa ... pc-icon"> */
      .pc-item > .pc-link .pc-mtext { font-weight: 500; }      /* توحيد الوزن */
      .pc-submenu { list-style: none; padding: 0; margin: 0; }  /* إزالة النقاط */

    </style>

    @stack('styles')

    @vite('resources/js/app.js')

</head>

<body
data-pc-preset="preset-1"
data-pc-direction="rtl"
  data-pc-theme="light"
  data-pc-sidebar-caption="true"
  data-pc-layout="vertical">
    <!-- [ Pre-loader ] start -->
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <!-- [ Pre-loader ] End -->

    <!-- [ Sidebar Menu ] start -->
    <nav class="pc-sidebar">
        <div class="navbar-wrapper">
            <div class="m-header justify-content-center">
                <a href="{{ url('/sections') }}" class="b-brand text-primary">
                    <img src="{{ asset('logo-primary.png') }}" width="150" class="img-fluid" alt="logo" />
                </a>
            </div>
            <div class="navbar-content">
                <div class="card pc-user-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <img src="{{ asset('assets/images/user/avatar-1.jpg') }}" alt="user-image" class="user-avtar wid-45 rounded-circle" />
                            </div>
                            <div class="flex-grow-1 ms-3 me-2">
                                <h6 class="mb-0">{{ auth()->user()->name ?? 'User' }}</h6>
                                <small>Administrator</small>
                            </div>
                            <a class="btn btn-icon btn-link-secondary avtar" data-bs-toggle="collapse" href="#pc_sidebar_userlink">
                                <svg class="pc-icon">
                                    <use xlink:href="#custom-sort-outline"></use>
                                </svg>
                            </a>
                        </div>
                        <div class="collapse pc-user-links" id="pc_sidebar_userlink">
                            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-link p-0" style="text-decoration: none; color: inherit;">
                                    <i class="ti ti-power"></i>
                                    <span>تسجيل الخروج</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <ul class="pc-navbar">
                    @include('layouts.menus.'.get_area_name())
                </ul>
            </div>
        </div>
    </nav>
    <!-- [ Sidebar Menu ] end -->

    <!-- [ Header Topbar ] start -->
    <header class="pc-header">
        <div class="header-wrapper">
            <!-- [Mobile Media Block] start -->
            <div class="me-auto pc-mob-drp">
                <ul class="list-unstyled">
                    <li class="pc-h-item pc-sidebar-collapse">
                        <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                    <li class="pc-h-item pc-sidebar-popup">
                        <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- [Mobile Media Block end] -->
            
            <div class="ms-auto">
                <ul class="list-unstyled">
                    <!-- Theme Toggle -->
                    <li class="dropdown pc-h-item">
                        <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button">
                            <svg class="pc-icon">
                                <use xlink:href="#custom-sun-1"></use>
                            </svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                            <a href="#!" class="dropdown-item" onclick="setTheme('dark')">
                                <svg class="pc-icon">
                                    <use xlink:href="#custom-moon"></use>
                                </svg>
                                <span>الوضع الداكن</span>
                            </a>
                            <a href="#!" class="dropdown-item" onclick="setTheme('light')">
                                <svg class="pc-icon">
                                    <use xlink:href="#custom-sun-1"></use>
                                </svg>
                                <span>الوضع الفاتح</span>
                            </a>
                        </div>
                    </li>

                    <!-- User Profile -->
                    <li class="dropdown pc-h-item header-user-profile">
                        <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button">
                            <img src="{{ asset('assets/images/user/avatar-2.jpg') }}" alt="user-image" class="user-avtar" />
                        </a>
                        <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                            <div class="dropdown-header">
                                <h5 class="m-0">الملف الشخصي</h5>
                            </div>
                            <div class="dropdown-body">
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <img src="{{ asset('assets/images/user/avatar-2.jpg') }}" alt="user-image" class="user-avtar wid-35" />
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">{{ auth()->user()->name ?? 'المستخدم' }}</h6>
                                        <span>{{ auth()->user()->email ?? 'user@example.com' }}</span>
                                    </div>
                                </div>
                                
                            
                                <hr class="border-secondary border-opacity-50" />
                                
                                <div class="d-grid mb-3">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            <svg class="pc-icon me-2">
                                                <use xlink:href="#custom-logout-1-outline"></use>
                                            </svg>
                                            تسجيل الخروج
                                        </button>
                                    </form>
                                </div>                                
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </header>
    <!-- [ Header ] end -->

    <!-- [ Main Content ] start -->
    <div class="pc-container">
        <div class="">
            <div class="row">
                <div class="col-md-12">
                    @include('layouts.messages')
                </div>
            </div>
        </div>
        <div class="pc-content">
            @yield('content')
        </div>
    </div>
    <!-- [ Main Content ] end -->

    <!-- Footer -->
    <footer class="pc-footer">
        <div class="footer-wrapper container-fluid">
            <div class="row">
                <div class="col my-1">
                    <p class="m-0">تم التطوير بواسطة <a href="#" target="_blank"> فريق اثر </a></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/tech-stack.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/fonts/custom-font.js') }}"></script>
    <script src="{{ asset('assets/js/pcoded.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>

    <script>
        // Theme Management with LocalStorage
        function setTheme(theme) {
            localStorage.setItem('theme', theme);
            document.body.setAttribute('data-pc-theme', theme);
            
            // Update icon based on theme
            const themeIcon = document.querySelector('.pc-head-link svg use');
            if (theme === 'dark') {
                themeIcon.setAttribute('xlink:href', '#custom-moon');
            } else {
                themeIcon.setAttribute('xlink:href', '#custom-sun-1');
            }
        }

        // Load theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            setTheme(savedTheme);
        });

        // Initialize layout
        layout_change('light');
        change_box_container('true');
        layout_caption_change('true');
        layout_rtl_change('false');
        preset_change('preset-1');
        main_layout_change('vertical');
        localStorage.setItem('layout', 'vertical');
    </script>
    <script>
      const savedTheme = localStorage.getItem('theme') || 'light';
      document.body.setAttribute('data-pc-theme', savedTheme);
      layout_change('light');
      change_box_container('true');
      layout_caption_change('true');
      layout_rtl_change('true');       // مهم للـ RTL
      preset_change('preset-1');
      main_layout_change('vertical');

    </script>

    @stack('scripts')
</body>
</html>