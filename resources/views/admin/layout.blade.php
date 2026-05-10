<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin') | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('icons/icon-512.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    @stack('styles')
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; text-size-adjust: 100%; }
        body { background: #f8f9fa; }
        .sidebar { width: 260px; min-height: 100vh; background: #1f2937; }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 12px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #374151; color: #fff; }
        .sidebar .muted { color:#94a3b8; }
        .sidebar .brand { color:#fff; }
        .sidebar .nav-icon { width: 20px; display:inline-block; }
        .sidebar .nav-sub a { padding-left: 44px; font-size: 0.95em; }
        .sidebar .nav-parent { cursor: pointer; }
        .sidebar .nav-caret { opacity: .85; }

        .admin-topbar {
            position: sticky;
            top: 0;
            z-index: 1020;
            background: #ffffff;
            border-bottom: 1px solid rgba(0,0,0,.08);
        }

        /* Ensure modals overlay sidebar on mobile */
        .modal { z-index: 2000; }
        .modal-backdrop { z-index: 1990; }

        @media (max-width: 768px) {
            .admin-content { padding: 1rem !important; }
            .admin-content { max-width: 100%; overflow-x: hidden; }
            body { overflow-x: hidden; }
            /* Flex children must be allowed to shrink on mobile */
            .flex-grow-1 { min-width: 0 !important; }
            .admin-content { min-width: 0 !important; }
            .card, .table-responsive { max-width: 100%; }

            /* DataTables wrappers sometimes set large widths */
            .dataTables_wrapper { width: 100% !important; max-width: 100% !important; overflow-x: auto; }
            table.dataTable { width: 100% !important; }

            /* DataTables controls: clean stacked layout on mobile (no overlap) */
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                float: none !important;
                width: 100% !important;
                text-align: left !important;
            }

            .dataTables_wrapper .dataTables_length { margin-bottom: .5rem; }
            .dataTables_wrapper .dataTables_filter { margin-bottom: .5rem; }

            .dataTables_wrapper .dataTables_length label,
            .dataTables_wrapper .dataTables_filter label {
                display: flex;
                align-items: center;
                gap: .5rem;
                width: 100%;
                margin-bottom: 0;
            }

            .dataTables_wrapper .dataTables_filter input {
                flex: 1 1 auto;
                width: 100% !important;
                margin-left: 0 !important;
                max-width: none !important;
            }

            .dataTables_wrapper .dataTables_length select { width: auto; }

            .dataTables_wrapper .dataTables_info { margin-top: .5rem; }
            .dataTables_wrapper .dataTables_paginate { margin-top: .5rem; }
            .dataTables_wrapper .pagination { justify-content: center; flex-wrap: wrap; gap: .25rem; }

            /* Bootstrap sometimes adds huge scrollbar compensation on mobile */
            body.modal-open { padding-right: 0 !important; }
            .modal { padding-right: 0 !important; }

            /* Force modal truly centered */
            .modal .modal-dialog { margin-left: auto !important; margin-right: auto !important; }
            .modal .modal-dialog.modal-mobile-centered { width: 95vw; max-width: 95vw; }

            /* Global mobile sizing */
            body { font-size: 14px; }
            h1, h2, h3 { font-size: 1.25rem; }
            .card-body { padding: 0.85rem; }
            .btn { padding: .45rem .7rem; }
            .form-control, .form-select { padding: .45rem .6rem; }

            /* Tables: reduce padding and allow wrapping to avoid horizontal scroll */
            .table { font-size: 0.875rem; }
            .table > :not(caption) > * > * { padding: .5rem .5rem; }
            th, td { white-space: normal !important; }
            .text-nowrap { white-space: normal !important; }
            .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        }

        @media (max-width: 992px) {
            /* Hide static sidebar; use offcanvas instead */
            .sidebar.static-sidebar { display: none; }
        }
    </style>
</head>
<body>

@php
  $navItems = [
    ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'is' => 'admin.dashboard'],
    ['route' => 'admin.clients.index', 'label' => 'Clients', 'icon' => 'bi-people', 'is' => 'admin.clients.*'],
    ['route' => 'admin.subscriptions.index', 'label' => 'Subscriptions', 'icon' => 'bi-receipt', 'is' => 'admin.subscriptions.*'],
    ['route' => 'admin.packages.index', 'label' => 'Packages', 'icon' => 'bi-box-seam', 'is' => 'admin.packages.*'],
    ['route' => 'admin.helpdesk.index', 'label' => 'Helpdesk', 'icon' => 'bi-headset', 'is' => 'admin.helpdesk.*'],
  ];

  $affiliateItems = [
    ['route' => 'admin.affiliate.codes', 'label' => 'Affiliate Codes', 'icon' => 'bi-link-45deg', 'is' => 'admin.affiliate.codes*'],
    ['route' => 'admin.affiliate.commissions', 'label' => 'Commissions', 'icon' => 'bi-cash-stack', 'is' => 'admin.affiliate.commissions*'],
    ['route' => 'admin.affiliate.pro_requests.index', 'label' => 'Affiliate Pro Requests', 'icon' => 'bi-person-check', 'is' => 'admin.affiliate.pro_requests.*'],
  ];

  $affiliateActive = request()->routeIs('admin.affiliate.*');
@endphp

<div class="d-flex">
    {{-- Static sidebar (desktop) --}}
    <div class="sidebar static-sidebar">
        <h5 class="brand text-center py-4 border-bottom mb-0">Jodoh Murni</h5>

        <div class="px-3 py-2 border-bottom">
            <div class="muted small">Logged in as</div>
            <div class="text-white fw-semibold">{{ auth('admin')->user()->name ?? 'Admin' }}</div>
        </div>

        @foreach($navItems as $it)
          <a href="{{ route($it['route']) }}" class="{{ request()->routeIs($it['is']) ? 'active' : '' }}">
              <span class="nav-icon"><i class="bi {{ $it['icon'] }}"></i></span> {{ $it['label'] }}
          </a>
        @endforeach

        <a class="nav-parent {{ $affiliateActive ? 'active' : '' }}"
           data-bs-toggle="collapse"
           href="#sidebarAffiliateMenu"
           role="button"
           aria-expanded="{{ $affiliateActive ? 'true' : 'false' }}"
           aria-controls="sidebarAffiliateMenu">
          <span class="nav-icon"><i class="bi bi-diagram-3"></i></span> Affiliate
          <span class="float-end nav-caret"><i class="bi {{ $affiliateActive ? 'bi-chevron-up' : 'bi-chevron-down' }}"></i></span>
        </a>
        <div class="collapse {{ $affiliateActive ? 'show' : '' }} nav-sub" id="sidebarAffiliateMenu">
          @foreach($affiliateItems as $it)
            <a href="{{ route($it['route']) }}" class="{{ request()->routeIs($it['is']) ? 'active' : '' }}">
              <span class="nav-icon"><i class="bi {{ $it['icon'] }}"></i></span> {{ $it['label'] }}
            </a>
          @endforeach
        </div>

        <a href="#"><span class="nav-icon"><i class="bi bi-gear"></i></span> Settings</a>

        <hr class="text-secondary">

        <form method="POST" action="{{ route('admin.logout') }}" class="px-3 pb-3">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light w-100">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>

    <div class="flex-grow-1">
        {{-- Topbar (mobile/tablet) --}}
        <div class="admin-topbar d-lg-none">
            <div class="d-flex align-items-center justify-content-between px-3 py-2">
                <button class="btn btn-outline-secondary btn-sm" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
                    <i class="bi bi-list"></i> Menu
                </button>
                <div class="fw-semibold">Jodoh Murni</div>
                <div class="text-muted small">{{ auth('admin')->user()->name ?? 'Admin' }}</div>
            </div>
        </div>

        <div class="admin-content p-4">
            @yield('content')
        </div>
    </div>
</div>

{{-- Offcanvas sidebar (mobile/tablet) --}}
<div class="offcanvas offcanvas-start d-lg-none sidebar" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title brand" id="adminSidebarLabel">Jodoh Murni</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-0">
    <div class="px-3 py-2 border-bottom">
      <div class="muted small">Logged in as</div>
      <div class="text-white fw-semibold">{{ auth('admin')->user()->name ?? 'Admin' }}</div>
    </div>

    @foreach($navItems as $it)
      <a href="{{ route($it['route']) }}" class="{{ request()->routeIs($it['is']) ? 'active' : '' }}">
        <span class="nav-icon"><i class="bi {{ $it['icon'] }}"></i></span> {{ $it['label'] }}
      </a>
    @endforeach

    <a class="nav-parent {{ $affiliateActive ? 'active' : '' }}"
       data-bs-toggle="collapse"
       href="#offcanvasAffiliateMenu"
       role="button"
       aria-expanded="{{ $affiliateActive ? 'true' : 'false' }}"
       aria-controls="offcanvasAffiliateMenu">
      <span class="nav-icon"><i class="bi bi-diagram-3"></i></span> Affiliate
      <span class="float-end nav-caret"><i class="bi {{ $affiliateActive ? 'bi-chevron-up' : 'bi-chevron-down' }}"></i></span>
    </a>
    <div class="collapse {{ $affiliateActive ? 'show' : '' }} nav-sub" id="offcanvasAffiliateMenu">
      @foreach($affiliateItems as $it)
        <a href="{{ route($it['route']) }}" class="{{ request()->routeIs($it['is']) ? 'active' : '' }}">
          <span class="nav-icon"><i class="bi {{ $it['icon'] }}"></i></span> {{ $it['label'] }}
        </a>
      @endforeach
    </div>

    <a href="#"><span class="nav-icon"><i class="bi bi-gear"></i></span> Settings</a>

    <hr class="text-secondary">

    <form method="POST" action="{{ route('admin.logout') }}" class="px-3 pb-3">
      @csrf
      <button type="submit" class="btn btn-sm btn-outline-light w-100">
        <i class="bi bi-box-arrow-right"></i> Logout
      </button>
    </form>
  </div>
</div>

@stack('modals')

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/service-worker.js')
    .then(function(reg) {
        console.log('SW registered', reg);
    })
    .catch(function(err) {
        console.log('SW failed', err);
    });
}
</script>
</body>
</html>
