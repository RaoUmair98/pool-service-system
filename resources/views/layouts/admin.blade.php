<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>@yield('title', 'Dashboard') | {{ config('app.name') }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- App favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <meta name="theme-color" content="#0369a1">

		<!-- App css -->
		<link href="{{asset('assets/css/bootstrap-corporate.min.css')}}" rel="stylesheet" type="text/css" id="bs-default-stylesheet" />
		<link href="{{asset('assets/css/app-corporate.min.css')}}" rel="stylesheet" type="text/css" id="app-default-stylesheet" />

		<link href="{{asset('assets/css/bootstrap-corporate-dark.min.css')}}" rel="stylesheet" type="text/css" id="bs-dark-stylesheet" />
		<link href="{{asset('assets/css/app-corporate-dark.min.css')}}" rel="stylesheet" type="text/css" id="app-dark-stylesheet" />

		<!-- icons -->
		<link href="{{asset('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />

        <link href="{{ asset('assets/libs/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/custom.css') }}?v={{ filemtime(public_path('assets/css/custom.css')) }}" rel="stylesheet" type="text/css" />
        @stack('styles')
    </head>

    <body class="loading" data-layout='{"mode": "light", "width": "fluid", "menuPosition": "fixed", "sidebar": { "color": "dark", "size": "default", "showuser": false}, "topbar": {"color": "light"}, "showRightSidebarOnPageLoad": true}'>

        <!-- Begin page -->
        <div id="wrapper">

            <!-- ========== Left Sidebar Start ========== -->
            <div class="left-side-menu">

                <!-- LOGO -->
                <div class="logo-box">
                    <a href="{{ route('home') }}" class="logo logo-dark text-center">
                        <span class="logo-sm">
                            <span class="logo-lg-text-dark"><span class="brand-icon"><i class="mdi mdi-pool"></i></span></span>
                        </span>
                        <span class="logo-lg">
                            <span class="logo-lg-text-dark"><span class="brand-icon"><i class="mdi mdi-pool"></i></span>{{ config('app.name') }}</span>
                        </span>
                    </a>

                    <a href="{{ route('home') }}" class="logo logo-light text-center">
                        <span class="logo-sm">
                            <span class="logo-lg-text-light"><span class="brand-icon"><i class="mdi mdi-pool"></i></span></span>
                        </span>
                        <span class="logo-lg">
                            <span class="logo-lg-text-light"><span class="brand-icon"><i class="mdi mdi-pool"></i></span>{{ config('app.name') }}</span>
                        </span>
                    </a>
                </div>

                <div class="h-100" data-simplebar>

                    <!--- Sidemenu -->
                    <div id="sidebar-menu">

                        <ul id="side-menu">

                            <li class="menu-title">Navigation</li>

                            <li>
                                <a href="{{ route('dashboard') }}" class="waves-effect">
                                    <i class="ri-dashboard-line"></i>
                                    <span> Dashboard </span>
                                </a>
                            </li>

                            {{-- Pool service sections (customers, pools, jobs, reports...) are added here as they are built. --}}

                            @canany(['manage team', 'manage company'])
                            <li class="menu-title">Company</li>
                            @endcanany

                            @can('manage team')
                            <li>
                                <a href="{{ route('team.index') }}" class="waves-effect">
                                    <i class="mdi mdi-account-group-outline"></i>
                                    <span> Team </span>
                                </a>
                            </li>
                            @endcan

                            @can('manage company')
                            <li>
                                <a href="{{ route('company.edit') }}" class="waves-effect">
                                    <i class="mdi mdi-domain"></i>
                                    <span> Company Settings </span>
                                </a>
                            </li>
                            @endcan

                            @can('view activity log')
                            <li>
                                <a href="{{ route('activity.index') }}" class="waves-effect">
                                    <i class="mdi mdi-clipboard-text-outline"></i>
                                    <span> Activity Log </span>
                                </a>
                            </li>
                            @endcan

                        </ul>

                    </div>
                    <!-- End Sidebar -->

                    <div class="clearfix"></div>

                </div>
                <!-- Sidebar -left -->

            </div>
            <!-- Left Sidebar End -->

            <!-- ============================================================== -->
            <!-- Start Page Content here -->
            <!-- ============================================================== -->

            <div class="content-page">
                <div class="content">

                    <!-- Topbar Start -->
                    <div class="navbar-custom">
                        <div class="container-fluid">

                            <ul class="list-unstyled topnav-menu float-right mb-0">

                                <li class="dropdown d-none d-lg-inline-block">
                                    <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light" data-toggle="fullscreen" href="#">
                                        <i class="fe-maximize noti-icon"></i>
                                    </a>
                                </li>

                                <li class="dropdown notification-list topbar-dropdown">
                                    <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect waves-light" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                        <i class="ri-account-circle-line noti-icon"></i>
                                        <span class="pro-user-name ml-1">
                                            {{ auth()->user()->name }} <i class="mdi mdi-chevron-down"></i>
                                        </span>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                                        <!-- item-->
                                        <div class="dropdown-header noti-title">
                                            <h6 class="text-overflow m-0">{{ auth()->user()->company?->name }}</h6>
                                            <small class="text-muted">{{ auth()->user()->role_label }}</small>
                                        </div>

                                        <!-- item-->
                                        <a href="{{ route('profile.edit') }}" class="dropdown-item notify-item">
                                            <i class="ri-account-circle-line"></i>
                                            <span>My Account</span>
                                        </a>

                                        <div class="dropdown-divider"></div>

                                        <!-- item-->
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item notify-item">
                                                <i class="ri-logout-box-line"></i>
                                                <span>Logout</span>
                                            </button>
                                        </form>

                                    </div>
                                </li>

                            </ul>

                            <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
                                <li>
                                    <button class="button-menu-mobile waves-effect waves-light">
                                        <i class="fe-menu"></i>
                                    </button>
                                </li>

                                <li>
                                    <!-- Mobile menu toggle (Horizontal Layout)-->
                                    <a class="navbar-toggle nav-link" data-toggle="collapse" data-target="#topnav-menu-content">
                                        <div class="lines">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                        </div>
                                    </a>
                                    <!-- End mobile menu toggle-->
                                </li>

                                @if (auth()->user()->company)
                                <li class="d-none d-md-block">
                                    <span class="topbar-company"><i class="mdi mdi-domain"></i> {{ auth()->user()->company->name }}</span>
                                </li>
                                @endif

                            </ul>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <!-- end Topbar -->

                    <!-- Start Content-->
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box">
                                    @php
                                        // Page icon follows the section of the app the current route belongs to.
                                        $pageIcon = collect([
                                            'dashboard' => 'mdi-view-dashboard-outline',
                                            'team.' => 'mdi-account-group-outline',
                                            'company.' => 'mdi-domain',
                                            'activity.' => 'mdi-clipboard-text-outline',
                                            'profile.' => 'mdi-account-circle-outline',
                                        ])->first(fn ($icon, $prefix) => str_starts_with((string) request()->route()?->getName(), $prefix));
                                    @endphp
                                    <h4 class="page-title">@if ($pageIcon)<span class="page-icon"><i class="mdi {{ $pageIcon }}"></i></span>@endif @yield('title', 'Dashboard')</h4>
                                    <div class="page-title-right d-print-none">
                                        @yield('actions')
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        @include('partials.toasts')

                        @hasSection('help')
                            <div class="alert help-box d-print-none">
                                <i class="mdi mdi-lightbulb-on-outline mr-1"></i> @yield('help')
                            </div>
                        @endif

                        @yield('content')

                    </div> <!-- container -->

                </div> <!-- content -->

                <!-- Footer Start -->
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6">
                                {{ config('app.name') }} &mdash; Pool Service Management
                            </div>
                        </div>
                    </div>
                </footer>
                <!-- end Footer -->

            </div>

            <!-- ============================================================== -->
            <!-- End Page content -->
            <!-- ============================================================== -->

        </div>
        <!-- END wrapper -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>

        <!-- Vendor js -->
        <script src="{{asset('assets/js/vendor.min.js')}}"></script>
        <script src="{{ asset('assets/libs/select2/js/select2.min.js') }}"></script>
        <script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

        <script>
            // Dependent dropdowns: <select data-parent="#customer_id" data-url="..."> reloads
            // its options whenever the parent select changes.
            $(function () {
                function load($select) {
                    var parentId = $($select.data('parent')).val();
                    var selected = $select.data('selected');

                    $select.data('selected', '');
                    $select.html($('<option>').val('').text($select.data('placeholder') || 'Select'));

                    if (!parentId) {
                        $select.trigger('change');
                        return;
                    }

                    $.getJSON($select.data('url'), { parent_id: parentId, include: selected }, function (rows) {
                        rows.forEach(function (row) {
                            $select.append(
                                $('<option>').val(row.id).text(row.text).data('row', row).prop('selected', String(row.id) === String(selected))
                            );
                        });
                        $select.trigger('change');
                    });
                }

                // Every dropdown is a searchable Select2. The width is fixed at 100% so dropdowns inside
                // collapsed filter panels do not render with zero width.
                // Each one has a search box and shows the icon of what it lists.
                var optionIcons = {
                    status: 'mdi-toggle-switch-outline', user_id: 'mdi-account-outline'
                };

                $('select.form-control').each(function () {
                    var icon = optionIcons[this.name];

                    function withIcon(option) {
                        // The empty "Select..." / "All..." choice stays plain text.
                        if (!icon || !option.id) {
                            return option.text;
                        }

                        return $('<span>').append($('<i>').addClass('mdi ' + icon + ' option-icon'), document.createTextNode(' ' + option.text));
                    }

                    $(this).select2({
                        width: '100%',
                        minimumResultsForSearch: 0,
                        templateResult: withIcon,
                        templateSelection: withIcon,
                        language: { noResults: function () { return 'Nothing found'; } }
                    });
                });

                // Typing should work as soon as a dropdown opens.
                $(document).on('select2:open', function () {
                    var field = document.querySelector('.select2-container--open .select2-search__field');
                    if (field) { field.focus(); }
                });

                var $dependents = $('select[data-parent]');

                $dependents.each(function () {
                    var $select = $(this);
                    $($select.data('parent')).on('change', function () { load($select); });
                });

                // Top-level dependents load first; their change event loads the ones below them.
                $dependents.each(function () {
                    var $select = $(this);
                    if (!$($select.data('parent')).is('[data-parent]')) {
                        load($select);
                    }
                });

                // Friendly "are you sure?" box instead of the browser's plain one.
                $('form[data-confirm]').on('submit', function (event) {
                    var form = this;

                    event.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Are you sure?',
                        text: $(form).data('confirm'),
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete',
                        cancelButtonText: 'No, keep it',
                        confirmButtonColor: '#f1556c'
                    }).then(function (result) {
                        if (result.isConfirmed || result.value) {
                            // Native submit, so this handler does not run a second time.
                            HTMLFormElement.prototype.submit.call(form);
                        }
                    });
                });

                // Floating messages close by themselves, or when the cross is pressed.
                function autoHide($toast, delay) {
                    if (delay) {
                        setTimeout(function () { $toast.addClass('leaving'); setTimeout(function () { $toast.remove(); }, 400); }, delay);
                    }
                }

                $('.app-toast').each(function () { autoHide($(this), $(this).data('autohide')); });

                // Pages that save in the background show their message with this.
                window.showToast = function (message, isError) {
                    var $toast = $('<div>').addClass('app-toast ' + (isError ? 'toast-error' : 'toast-success')).append(
                        $('<i>').addClass('mdi toast-icon ' + (isError ? 'mdi-alert-circle' : 'mdi-check-circle')),
                        $('<div>').addClass('toast-text').text(message),
                        $('<button type="button" aria-label="Close">').addClass('toast-close').html('&times;')
                    );

                    // Only the newest message stays, so quick taps do not pile up.
                    $('.toast-stack').empty().append($toast);
                    autoHide($toast, isError ? 9000 : 6000);
                };
                $(document).on('click', '.toast-close', function () { $(this).closest('.app-toast').remove(); });

                // Dashboard numbers count up from zero.
                $('[data-count]').each(function () {
                    var $number = $(this), target = parseInt($number.data('count'), 10) || 0, started = null;

                    function step(now) {
                        started = started || now;
                        var progress = Math.min(1, (now - started) / 700);

                        $number.text(Math.round(target * progress).toLocaleString());

                        if (progress < 1) { requestAnimationFrame(step); }
                    }

                    requestAnimationFrame(step);
                });
            });
        </script>

        @stack('scripts')

        <!-- App js -->
        <script src="{{asset('assets/js/app.min.js')}}"></script>

    </body>
</html>
