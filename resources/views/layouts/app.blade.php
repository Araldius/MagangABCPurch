<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'ProcureX' }} | PT. Dunia Kimia Jaya</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{-webkit-font-smoothing:antialiased;font-size:13px}
        body{font-family:'Inter',system-ui,sans-serif;background:#f3f4f6;color:#111827;min-height:100vh;display:flex}
 
        /* SIDEBAR */
        .sidebar{width:200px;min-height:100vh;background:#111827;display:flex;flex-direction:column;padding:20px 0;flex-shrink:0;position:fixed;top:0;left:0;bottom:0;z-index:100}
        .sidebar-brand{padding:0 16px 20px;display:flex;align-items:center;gap:9px;border-bottom:1px solid rgba(255,255,255,.07)}
        .sidebar-logo{width:32px;height:32px;background:#3b5bdb;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0}
        .sidebar-brand-name{font-size:13px;font-weight:700;color:#f9fafb;line-height:1.2}
        .sidebar-brand-sub{font-size:10px;color:#6b7280;margin-top:1px}
        .sidebar-nav{padding:12px 10px;display:flex;flex-direction:column;gap:2px;flex:1}
        .sidebar-label{font-size:10px;font-weight:600;color:#6b7280;letter-spacing:.08em;text-transform:uppercase;padding:8px 8px 4px;margin-top:6px}
        .sidebar-link{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:7px;color:#9ca3af;text-decoration:none;font-size:12.5px;font-weight:500;transition:background .15s,color .15s}
        .sidebar-link svg{flex-shrink:0;opacity:.7}
        .sidebar-link:hover{background:rgba(255,255,255,.07);color:#f9fafb}
        .sidebar-link:hover svg{opacity:1}
        .sidebar-link.active{background:rgba(255,255,255,.1);color:#f9fafb}
        .sidebar-link.active svg{opacity:1}
        .sidebar-footer{padding:10px;border-top:1px solid rgba(255,255,255,.07)}
        .sidebar-logout{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:7px;color:#f87171;font-size:12.5px;font-weight:500;background:none;border:none;cursor:pointer;width:100%;text-align:left;transition:background .15s}
        .sidebar-logout:hover{background:rgba(239,68,68,.08)}
 
        /* MAIN */
        .main-wrap{margin-left:200px;flex:1;display:flex;flex-direction:column;min-height:100vh}
 
        /* TOPBAR */
        .topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:0 28px;height:54px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
        .topbar-breadcrumb{display:flex;align-items:center;gap:5px;font-size:12px;color:#6b7280}
        .topbar-breadcrumb a{color:#6b7280;text-decoration:none}
        .topbar-breadcrumb a:hover{color:#111827}
        .topbar-breadcrumb .sep{color:#d1d5db}
        .topbar-breadcrumb .current{color:#111827;font-weight:500}
        .topbar-user{display:flex;align-items:center;gap:9px;font-size:12px}
        .topbar-avatar{width:30px;height:30px;background:#3b5bdb;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff}
        .topbar-name{font-weight:600;color:#111827;font-size:12.5px}
        .topbar-role{color:#9ca3af;font-size:11px}
 
        /* PAGE */
        .page-content{padding:24px 28px;flex:1}
        .page-footer{padding:14px 28px;font-size:11.5px;color:#9ca3af;border-top:1px solid #e5e7eb;background:#fff;display:flex;justify-content:space-between}
        .page-footer a{color:#9ca3af;text-decoration:none}
 
        /* ALERTS */
        .alert{padding:11px 14px;border-radius:8px;margin-bottom:16px;font-size:13px;border:1px solid}
        .alert-success{background:#f0fdf4;color:#15803d;border-color:#bbf7d0}
        .alert-danger{background:#fef2f2;color:#b91c1c;border-color:#fecaca}
        .alert ul{margin:4px 0 0 16px}
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="{{ asset('img/Logo_DKJ.jpeg') }}" alt="DKJ Logo" class="sidebar-logo">
        <div>
            <div class="sidebar-brand-name">PT. Dunia Kimia Jaya</div>
            <div class="sidebar-brand-sub">Procurement Portal</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <span class="sidebar-label">Main Menu</span>
        @auth
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            Dashboard
        </a>
        <a href="{{ route('purchase_requests.create') }}" class="sidebar-link {{ request()->routeIs('purchase_requests.create') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
            New Request
        </a>
        <a href="{{ route('pr.list') }}" class="sidebar-link {{ request()->routeIs('pr.list') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            PR List
        </a>
        <div style="margin-bottom:2px">
            <div class="sidebar-link" style="color:#9ca3af;cursor:default;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Procurement History
            </div>
            <div style="display:flex;flex-direction:column;gap:2px;padding-left:26px;margin-top:2px;">
                <a href="{{ route('history.orders') }}" class="sidebar-link {{ request()->routeIs('history.orders') ? 'active' : '' }}" style="padding:6px 10px;font-size:11.5px">Order Records</a>
                <a href="{{ route('history.items') }}" class="sidebar-link {{ request()->routeIs('history.items') ? 'active' : '' }}" style="padding:6px 10px;font-size:11.5px">Item Catalog</a>
                <a href="{{ route('history.vendors') }}" class="sidebar-link {{ request()->routeIs('history.vendors') ? 'active' : '' }}" style="padding:6px 10px;font-size:11.5px">Vendor Directory</a>
            </div>
        </div>
        <a href="{{ route('vendors.list') }}" class="sidebar-link {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Vendor Selection
        </a>
        @endauth
    </nav>
    @auth
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="post">@csrf
            <button type="submit" class="sidebar-logout">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Logout
            </button>
        </form>
    </div>
    @endauth
</aside>
 
<div class="main-wrap">
    <header class="topbar">
        <div class="topbar-breadcrumb">
            <a href="{{ route('dashboard') }}">Portal</a>
            <span class="sep">/</span>
            <span class="current">{{ $pageTitle ?? 'Dashboard' }}</span>
        </div>
        @auth
        <div class="topbar-user">
            <div class="topbar-avatar">{{ strtoupper(substr(Auth::user()->name,0,2)) }}</div>
            <div>
                <div class="topbar-name">{{ Auth::user()->name }}</div>
                <div class="topbar-role">{{ Auth::user()->role === 'purchasing' ? 'Purchasing' : 'IT Staff' }}</div>
            </div>
        </div>
        @endauth
    </header>
 
    <main class="page-content">
        @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix:</strong>
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif
        @yield('content')
    </main>
 
    <footer class="page-footer">
        <span>© 2026 ProcureX · Employee Portal</span>
        <div style="display:flex;gap:12px;"><a href="#">Help</a><a href="#">Privacy</a><a href="#">Contact IT</a></div>
    </footer>
</div>
</body>
</html>