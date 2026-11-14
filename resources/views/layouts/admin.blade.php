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
        <link href="{{ asset('assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/custom.css') }}?v={{ filemtime(public_path('assets/css/custom.css')) }}" rel="stylesheet" type="text/css" />
        @stack('styles')
    </head>

    <body class="loading @yield('body_class')" data-layout='{"mode": "light", "width": "fluid", "menuPosition": "fixed", "sidebar": { "color": "dark", "size": "default", "showuser": false}, "topbar": {"color": "light"}, "showRightSidebarOnPageLoad": true}'>

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

                            @can('complete visits')
                            <li>
                                <a href="{{ route('route.index') }}" class="waves-effect">
                                    <i class="mdi mdi-map-marker-path"></i>
                                    <span> Today's Route </span>
                                </a>
                            </li>
                            @endcan

                            @canany(['manage customers', 'manage schedules', 'manage invoices'])
                            <li class="menu-title">Operations</li>
                            @endcanany

                            @can('manage customers')
                            <li>
                                <a href="{{ route('customers.index') }}" class="waves-effect">
                                    <i class="mdi mdi-account-tie-outline"></i>
                                    <span> Customers </span>
                                </a>
                            </li>
                            @endcan

                            @can('manage schedules')
                            <li>
                                <a href="{{ route('visits.index') }}" class="waves-effect">
                                    <i class="mdi mdi-calendar-check"></i>
                                    <span> Visits </span>
                                </a>
                            </li>
                            @endcan

                            @can('manage invoices')
                            <li>
                                <a href="{{ route('invoices.index') }}" class="waves-effect">
                                    <i class="mdi mdi-file-document-outline"></i>
                                    <span> Invoices </span>
                                </a>
                            </li>
                            @endcan

                            @php
                                $reportToday = \Illuminate\Support\Carbon::today()->toDateString();
                                $reportYesterday = \Illuminate\Support\Carbon::yesterday()->toDateString();
                                $reportWeekFrom = \Illuminate\Support\Carbon::today()->startOfWeek()->toDateString();
                                $reportWeekTo = \Illuminate\Support\Carbon::today()->endOfWeek()->toDateString();
                                $reportMonthFrom = \Illuminate\Support\Carbon::today()->startOfMonth()->toDateString();
                                $reportMonthTo = \Illuminate\Support\Carbon::today()->endOfMonth()->toDateString();
                                $reportOverdueFrom = \Illuminate\Support\Carbon::today()->subDays(30)->toDateString();
                                $reportYear = (int) now()->year;
                                $reportMonth = (int) now()->month;
                            @endphp

                            @canany(['view reports', 'manage schedules', 'manage invoices', 'view activity log', 'manage customers', 'manage team'])
                            <li class="menu-title">Reports</li>
                            @endcanany

                            @can('manage schedules')
                            <li>
                                <a href="#sidebarVisitReports" data-toggle="collapse">
                                    <i class="mdi mdi-calendar-check"></i>
                                    <span> Visit reports </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse {{ request()->routeIs('visits.*') ? 'show' : '' }}" id="sidebarVisitReports">
                                    <ul class="nav-second-level">
                                        <li><a href="{{ route('visits.index', ['date_from' => $reportToday, 'date_to' => $reportToday]) }}">Today</a></li>
                                        <li><a href="{{ route('visits.index', ['date_from' => $reportWeekFrom, 'date_to' => $reportWeekTo]) }}">This week</a></li>
                                        <li><a href="{{ route('visits.index', ['date_from' => $reportMonthFrom, 'date_to' => $reportMonthTo, 'status' => 'completed']) }}">Completed this month</a></li>
                                        <li><a href="{{ route('visits.index', ['status' => 'in_progress']) }}">In progress</a></li>
                                        <li><a href="{{ route('visits.index', ['status' => 'skipped']) }}">Skipped</a></li>
                                        <li><a href="{{ route('visits.index', ['date_from' => $reportOverdueFrom, 'date_to' => $reportYesterday, 'status' => 'scheduled']) }}">Overdue / missed</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            @can('manage invoices')
                            <li>
                                <a href="#sidebarInvoiceReports" data-toggle="collapse">
                                    <i class="mdi mdi-file-chart-outline"></i>
                                    <span> Invoice reports </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse {{ request()->routeIs('invoices.*') ? 'show' : '' }}" id="sidebarInvoiceReports">
                                    <ul class="nav-second-level">
                                        <li><a href="{{ route('invoices.index', ['status' => 'draft']) }}">Drafts</a></li>
                                        <li><a href="{{ route('invoices.index', ['status' => 'sent']) }}">Unpaid / sent</a></li>
                                        <li><a href="{{ route('invoices.index', ['status' => 'paid']) }}">Paid</a></li>
                                        <li><a href="{{ route('invoices.index', ['period_year' => $reportYear, 'period_month' => $reportMonth]) }}">This month's period</a></li>
                                        <li><a href="{{ route('invoices.index', ['status' => 'paid', 'period_year' => $reportYear, 'period_month' => $reportMonth]) }}">Paid this period</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            @can('view reports')
                            <li>
                                <a href="#sidebarEmailReports" data-toggle="collapse">
                                    <i class="mdi mdi-email-outline"></i>
                                    <span> Email reports </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse {{ request()->routeIs('email-logs.*') || request()->routeIs('visits.report') ? 'show' : '' }}" id="sidebarEmailReports">
                                    <ul class="nav-second-level">
                                        <li><a href="{{ route('email-logs.index') }}">All email log</a></li>
                                        <li><a href="{{ route('email-logs.index', ['type' => 'service_report']) }}">Service reports</a></li>
                                        <li><a href="{{ route('email-logs.index', ['type' => 'invoice']) }}">Invoice emails</a></li>
                                        <li><a href="{{ route('email-logs.index', ['status' => 'sent']) }}">Sent</a></li>
                                        <li><a href="{{ route('email-logs.index', ['status' => 'failed']) }}">Failed</a></li>
                                        <li><a href="{{ route('email-logs.index', ['status' => 'skipped']) }}">Skipped</a></li>
                                        <li><a href="{{ route('email-logs.index', ['status' => 'queued']) }}">Queued</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            @can('manage customers')
                            <li>
                                <a href="#sidebarCustomerReports" data-toggle="collapse">
                                    <i class="mdi mdi-account-tie-outline"></i>
                                    <span> Customer reports </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse {{ request()->routeIs('customers.*') ? 'show' : '' }}" id="sidebarCustomerReports">
                                    <ul class="nav-second-level">
                                        <li><a href="{{ route('customers.index', ['status' => 'active']) }}">Active customers</a></li>
                                        <li><a href="{{ route('customers.index', ['status' => 'inactive']) }}">Turned off</a></li>
                                        <li><a href="{{ route('customers.index') }}">All customers</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            @can('manage team')
                            <li>
                                <a href="#sidebarTeamReports" data-toggle="collapse">
                                    <i class="mdi mdi-account-group-outline"></i>
                                    <span> Team reports </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse {{ request()->routeIs('team.*') ? 'show' : '' }}" id="sidebarTeamReports">
                                    <ul class="nav-second-level">
                                        <li><a href="{{ route('team.index', ['role' => \App\Models\User::ROLE_TECHNICIAN, 'status' => 'active']) }}">Active technicians</a></li>
                                        <li><a href="{{ route('team.index', ['role' => \App\Models\User::ROLE_ADMIN]) }}">Admins</a></li>
                                        <li><a href="{{ route('team.index', ['status' => 'inactive']) }}">Turned off</a></li>
                                        <li><a href="{{ route('team.index') }}">Full team</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            @can('view activity log')
                            <li>
                                <a href="#sidebarActivityReports" data-toggle="collapse">
                                    <i class="mdi mdi-clipboard-text-outline"></i>
                                    <span> Activity reports </span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <div class="collapse {{ request()->routeIs('activity.*') ? 'show' : '' }}" id="sidebarActivityReports">
                                    <ul class="nav-second-level">
                                        <li><a href="{{ route('activity.index', ['date_from' => $reportToday, 'date_to' => $reportToday]) }}">Today</a></li>
                                        <li><a href="{{ route('activity.index', ['date_from' => $reportWeekFrom, 'date_to' => $reportWeekTo]) }}">This week</a></li>
                                        <li><a href="{{ route('activity.index', ['action' => 'created']) }}">Created</a></li>
                                        <li><a href="{{ route('activity.index', ['action' => 'updated']) }}">Updated</a></li>
                                        <li><a href="{{ route('activity.index', ['action' => 'deleted']) }}">Deleted</a></li>
                                        <li><a href="{{ route('activity.index', ['action' => 'login']) }}">Logins</a></li>
                                        <li><a href="{{ route('activity.index') }}">Full activity log</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

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
                                            'route.' => 'mdi-map-marker-path',
                                            'customers.pools.' => 'mdi-pool',
                                            'pools.schedule.' => 'mdi-calendar-clock',
                                            'pools.' => 'mdi-pool',
                                            'visits.' => 'mdi-calendar-check',
                                            'email-logs.' => 'mdi-email-outline',
                                            'invoices.' => 'mdi-file-document-outline',
                                            'customers.' => 'mdi-account-tie-outline',
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
        <script src="{{ asset('assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>

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
                        dropdownParent: $(document.body),
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

                function pad2(n) { return String(n).padStart(2, '0'); }
                function toIso(date) {
                    return date.getFullYear() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate());
                }

                // Calendar date pickers — display MM/DD/YYYY, submit Y-m-d for the server.
                $('[data-datepicker]').each(function () {
                    var $input = $(this);
                    var $iso = $('#' + $input.data('iso-target'));
                    var $filterForm = $input.closest('form.filters-bar');

                    $input.datepicker({
                        format: 'mm/dd/yyyy',
                        autoclose: true,
                        todayHighlight: true,
                        clearBtn: true,
                        assumeNearbyYear: true,
                        orientation: 'bottom auto',
                        container: 'body'
                    }).on('changeDate', function (e) {
                        $iso.val(e.date ? toIso(e.date) : '');
                        if ($filterForm.length) { $filterForm[0].submit(); }
                    }).on('clearDate', function () {
                        $iso.val('');
                        if ($filterForm.length) { $filterForm[0].submit(); }
                    }).on('change', function () {
                        // Typed values: keep hidden ISO in sync when possible.
                        var raw = $.trim($input.val());
                        if (!raw) {
                            $iso.val('');
                            return;
                        }
                        var parsed = $input.datepicker('getDate');
                        $iso.val(parsed ? toIso(parsed) : $iso.val());
                    });
                });

                // List filters apply themselves — no Filter button needed.
                $('form.filters-bar').each(function () {
                    var $form = $(this);
                    var timer = null;
                    var ready = false;

                    function applyFilters() {
                        if (!ready) { return; }
                        $form[0].submit();
                    }

                    $form.on('change', 'select', applyFilters);
                    $form.on('input', 'input[type="text"]:not([data-datepicker]), input[type="search"], input[type="number"]', function () {
                        clearTimeout(timer);
                        timer = setTimeout(applyFilters, 400);
                    });

                    // Ignore Select2/datepicker setup noise on first paint.
                    setTimeout(function () { ready = true; }, 150);
                });

                // Friendly "are you sure?" box (delegated so infinite-scroll rows work too).
                $(document).on('submit', 'form[data-confirm]', function (event) {
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

        <script src="{{ asset('assets/js/infinite-scroll.js') }}?v={{ filemtime(public_path('assets/js/infinite-scroll.js')) }}"></script>

        @stack('scripts')

        <!-- App js -->
        <script src="{{asset('assets/js/app.min.js')}}"></script>

    </body>
</html>
