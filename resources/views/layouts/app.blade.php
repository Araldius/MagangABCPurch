<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'ProcureX' }} | PT. Dunia Kimia Jaya</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        :root {
            --sidebar-bg: #111827;
            --sidebar-hover: rgba(255,255,255,0.07);
            --sidebar-active: rgba(255,255,255,0.10);
            --sidebar-text: #9ca3af;
            --sidebar-text-active: #f9fafb;
            --primary: #3b5bdb;
            --primary-hover: #3451c7;
            --primary-light: #eef2ff;
            --surface: #ffffff;
            --bg: #f8fafc;
            --border: #e5e7eb;
            --border-strong: #d1d5db;
            --text: #111827;
            --text-muted: #6b7280;
            --text-light: #9ca3af;
            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow: 0 4px 16px rgba(0,0,0,0.06);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.08);
            --status-approved-bg: #f0fdf4; --status-approved-text: #15803d; --status-approved-dot: #22c55e;
            --status-completed-bg: #eff6ff; --status-completed-text: #1d4ed8; --status-completed-dot: #3b82f6;
            --status-inprocess-bg: #f0f9ff; --status-inprocess-text: #0369a1; --status-inprocess-dot: #0ea5e9;
            --status-cancelled-bg: #fef2f2; --status-cancelled-text: #b91c1c; --status-cancelled-dot: #ef4444;
            --status-vendor-bg: #fdf4ff; --status-vendor-text: #7c3aed; --status-vendor-dot: #a855f7;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { -webkit-font-smoothing: antialiased; }
        body { font-family: 'Inter', system-ui, sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; }

        /* ── Sidebar ── */
        .sidebar {
            width: 220px;
            min-height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            padding: 24px 0;
            flex-shrink: 0;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
        }
        .sidebar-brand {
            padding: 0 20px 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .sidebar-logo {
            width: 36px; height: 36px;
            background: var(--primary);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 800; color: white;
            flex-shrink: 0;
        }
        .sidebar-brand-name { font-size: 15px; font-weight: 700; color: #f9fafb; line-height: 1.2; }
        .sidebar-brand-sub { font-size: 10px; color: var(--sidebar-text); line-height: 1.3; margin-top: 1px; }

        .sidebar-nav { padding: 16px 12px; display: flex; flex-direction: column; gap: 2px; flex: 1; }
        .sidebar-label { font-size: 10px; font-weight: 600; color: var(--sidebar-text); letter-spacing: .08em; text-transform: uppercase; padding: 8px 8px 4px; margin-top: 8px; }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 10px;
            border-radius: var(--radius-sm);
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: background .15s, color .15s;
        }
        .sidebar-link svg { flex-shrink: 0; opacity: .7; }
        .sidebar-link:hover { background: var(--sidebar-hover); color: var(--sidebar-text-active); }
        .sidebar-link:hover svg { opacity: 1; }
        .sidebar-link.active { background: var(--sidebar-active); color: var(--sidebar-text-active); }
        .sidebar-link.active svg { opacity: 1; }

        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .sidebar-logout {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 10px;
            border-radius: var(--radius-sm);
            color: #f87171;
            font-size: 13.5px; font-weight: 500;
            background: none; border: none;
            cursor: pointer; width: 100%;
            text-align: left;
            transition: background .15s;
        }
        .sidebar-logout:hover { background: rgba(239,68,68,0.08); }

        /* ── Main ── */
        .main-wrap { margin-left: 220px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Topbar ── */
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-breadcrumb {
            display: flex; align-items: center; gap: 6px;
            font-size: 13px; color: var(--text-muted);
        }
        .topbar-breadcrumb a { color: var(--text-muted); text-decoration: none; }
        .topbar-breadcrumb a:hover { color: var(--text); }
        .topbar-breadcrumb .sep { color: var(--border-strong); }
        .topbar-breadcrumb .current { color: var(--text); font-weight: 500; }
        .topbar-user {
            display: flex; align-items: center; gap: 10px;
            font-size: 13px;
        }
        .topbar-avatar {
            width: 32px; height: 32px;
            background: var(--primary);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700; color: white;
        }
        .topbar-name { font-weight: 600; color: var(--text); }
        .topbar-role { color: var(--text-muted); font-size: 12px; }

        /* ── Page content ── */
        .page-content { padding: 28px 32px; flex: 1; }
        .page-header { margin-bottom: 24px; }
        .page-title { font-size: 22px; font-weight: 700; color: var(--text); }
        .page-desc { font-size: 13.5px; color: var(--text-muted); margin-top: 4px; }

        /* ── Cards ── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }
        .card-body { padding: 24px; }
        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
        }
        .card-title { font-size: 15px; font-weight: 600; color: var(--text); }
        .card-desc { font-size: 13px; color: var(--text-muted); margin-top: 2px; }

        /* ── Stat cards ── */
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px 22px;
        }
        .stat-label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .06em; }
        .stat-value { font-size: 28px; font-weight: 800; color: var(--text); margin-top: 8px; line-height: 1; }
        .stat-value.blue { color: #2563eb; }
        .stat-value.orange { color: #ea580c; }
        .stat-value.green { color: #16a34a; }
        .stat-value.yellow { color: #d97706; }
        .stat-sub { font-size: 12px; color: var(--text-muted); margin-top: 6px; }

        /* ── Table ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        thead th {
            text-align: left; padding: 10px 16px;
            font-size: 11px; font-weight: 600;
            color: var(--text-muted); text-transform: uppercase; letter-spacing: .06em;
            background: #f9fafb;
            border-bottom: 1px solid var(--border);
        }
        thead th:first-child { border-radius: 8px 0 0 0; }
        thead th:last-child { border-radius: 0 8px 0 0; }
        tbody tr { border-bottom: 1px solid var(--border); transition: background .1s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #f9fafb; }
        tbody td { padding: 13px 16px; color: var(--text); vertical-align: middle; }
        .td-doc { font-weight: 600; font-size: 13px; color: var(--text); font-family: 'Courier New', monospace; }
        .td-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

        /* ── Status pills ── */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px; font-weight: 600;
            white-space: nowrap;
        }
        .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
        .badge-awaiting { background: #fff7ed; color: #c2410c; }
        .badge-awaiting::before { background: #f97316; }
        .badge-approved { background: var(--status-approved-bg); color: var(--status-approved-text); }
        .badge-approved::before { background: var(--status-approved-dot); }
        .badge-completed { background: var(--status-completed-bg); color: var(--status-completed-text); }
        .badge-completed::before { background: var(--status-completed-dot); }
        .badge-inprocess { background: var(--status-inprocess-bg); color: var(--status-inprocess-text); }
        .badge-inprocess::before { background: var(--status-inprocess-dot); }
        .badge-cancelled { background: var(--status-cancelled-bg); color: var(--status-cancelled-text); }
        .badge-cancelled::before { background: var(--status-cancelled-dot); }
        .badge-vendor { background: var(--status-vendor-bg); color: var(--status-vendor-text); }
        .badge-vendor::before { background: var(--status-vendor-dot); }
        .badge-rfq { background: #e0f2fe; color: #0369a1; }
        .badge-rfq::before { background: #0ea5e9; }
        .tag { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 600; }
        .tag-blue { background: #dbeafe; color: #1d4ed8; }
        .tag-green { background: #dcfce7; color: #15803d; }
        .tag-orange { background: #ffedd5; color: #c2410c; }
        .tag-gray { background: #f1f5f9; color: #475569; }
        .tag-purple { background: #f5f3ff; color: #7c3aed; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 13.5px; font-weight: 600;
            cursor: pointer; border: none;
            text-decoration: none; transition: background .15s, box-shadow .15s;
            white-space: nowrap;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-hover); box-shadow: 0 4px 12px rgba(59,91,219,.25); }
        .btn-outline { background: white; color: var(--text); border: 1px solid var(--border-strong); }
        .btn-outline:hover { background: var(--bg); }
        .btn-ghost { background: transparent; color: var(--text-muted); border: none; }
        .btn-ghost:hover { background: var(--bg); color: var(--text); }
        .btn-danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .btn-danger:hover { background: #fee2e2; }
        .btn-sm { padding: 5px 12px; font-size: 12.5px; }
        .btn-icon { padding: 7px; width: 32px; height: 32px; }

        /* ── Forms ── */
        .form-section { margin-bottom: 24px; }
        .form-section-header {
            display: flex; align-items: center; gap: 10px;
            padding-bottom: 14px; margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
        }
        .form-section-icon {
            width: 34px; height: 34px;
            background: var(--primary-light);
            border-radius: var(--radius-sm);
            display: flex; align-items: center; justify-content: center;
            color: var(--primary);
        }
        .form-section-title { font-size: 14px; font-weight: 600; color: var(--text); }
        .form-section-desc { font-size: 12.5px; color: var(--text-muted); }

        .form-row { display: grid; gap: 16px; }
        .form-row-2 { grid-template-columns: repeat(2, 1fr); }
        .form-row-3 { grid-template-columns: repeat(3, 1fr); }
        .form-row-4 { grid-template-columns: repeat(4, 1fr); }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-label {
            font-size: 12px; font-weight: 600;
            color: var(--text); letter-spacing: .01em;
        }
        .form-label .req { color: #ef4444; margin-left: 2px; }
        .form-control {
            width: 100%; padding: 9px 12px;
            border: 1px solid var(--border-strong);
            border-radius: var(--radius-sm);
            font-size: 13.5px; color: var(--text);
            background: white;
            transition: border-color .15s, box-shadow .15s;
            font-family: inherit;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59,91,219,.12); }
        .form-control:disabled, .form-control[readonly] { background: #f9fafb; color: var(--text-muted); cursor: default; }
        textarea.form-control { min-height: 88px; resize: vertical; }
        .form-hint { font-size: 11.5px; color: var(--text-muted); }
        .form-error { font-size: 11.5px; color: #dc2626; }

        /* ── Alert / Flash ── */
        .alert { padding: 12px 16px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 13.5px; border: 1px solid; }
        .alert-success { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
        .alert-danger { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
        .alert ul { margin: 6px 0 0 16px; }

        /* ── Modal ── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.45);
            display: flex; align-items: center; justify-content: center;
            z-index: 200; opacity: 0; pointer-events: none;
            transition: opacity .2s;
        }
        .modal-overlay.open { opacity: 1; pointer-events: auto; }
        .modal {
            background: white;
            border-radius: var(--radius-lg);
            width: 100%; max-width: 580px;
            max-height: 85vh;
            overflow: hidden;
            display: flex; flex-direction: column;
            box-shadow: var(--shadow-lg);
            transform: translateY(12px);
            transition: transform .2s;
        }
        .modal-overlay.open .modal { transform: translateY(0); }
        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
        }
        .modal-title { font-size: 15px; font-weight: 700; }
        .modal-desc { font-size: 13px; color: var(--text-muted); margin-top: 2px; }
        .modal-close { background: none; border: none; cursor: pointer; color: var(--text-muted); padding: 4px; border-radius: 6px; }
        .modal-close:hover { background: var(--bg); color: var(--text); }
        .modal-body { padding: 20px 24px; overflow-y: auto; flex: 1; }
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex; justify-content: flex-end; gap: 10px;
        }

        /* ── Footer ── */
        .page-footer {
            padding: 16px 32px;
            font-size: 12px;
            color: var(--text-muted);
            border-top: 1px solid var(--border);
            background: white;
            display: flex; justify-content: space-between; align-items: center;
        }
        .page-footer a { color: var(--text-muted); text-decoration: none; }
        .page-footer a:hover { color: var(--text); }

        /* ── Utilities ── */
        .flex { display: flex; }
        .flex-between { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .flex-center { display: flex; align-items: center; gap: 8px; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }
        .mt-6 { margin-top: 24px; }
        .text-muted { color: var(--text-muted); }
        .text-sm { font-size: 12.5px; }
        .font-mono { font-family: 'Courier New', monospace; }

        /* ── Divider ── */
        .divider { border: none; border-top: 1px solid var(--border); margin: 20px 0; }

        /* ── Responsive ── */
        @media (max-width: 1100px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform .25s; }
            .sidebar.open { transform: translateX(0); }
            .main-wrap { margin-left: 0; }
            .page-content { padding: 20px 16px; }
            .topbar { padding: 0 16px; }
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .form-row-2, .form-row-3, .form-row-4 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-logo">DK</div>
        <div>
            <div class="sidebar-brand-name">PT. Dunia Kimia Jaya</div>
            <div class="sidebar-brand-sub">Procurement Portal</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <span class="sidebar-label">Main Menu</span>

        @auth
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            Dashboard
        </a>
        <a href="{{ route('purchase_requests.create') }}" class="sidebar-link {{ request()->routeIs('purchase_requests.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
            New Request
        </a>
        <a href="{{ route('pr.list') }}" class="sidebar-link {{ request()->routeIs('pr.list') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            PR List
        </a>
        <a href="{{ route('history.index') }}" class="sidebar-link {{ request()->routeIs('history.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Procurement History
        </a>
        <a href="{{ route('vendors.list') }}" class="sidebar-link {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Vendor Selection
        </a>
        @endauth
    </nav>

    @auth
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit" class="sidebar-logout">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Logout
            </button>
        </form>
    </div>
    @endauth
</aside>

<!-- MAIN -->
<div class="main-wrap">
    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar-breadcrumb">
            <a href="{{ route('dashboard') }}">Portal</a>
            <span class="sep">/</span>
            <span class="current">{{ $pageTitle ?? 'Dashboard' }}</span>
        </div>
        @auth
        <div class="topbar-user">
            <div class="topbar-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
            <div>
                <div class="topbar-name">{{ Auth::user()->name }}</div>
                <div class="topbar-role">{{ Auth::user()->role === 'purchasing' ? 'Purchasing' : 'IT Staff' }}</div>
            </div>
        </div>
        @endauth
    </header>

    <!-- CONTENT -->
    <main class="page-content">
        @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        @yield('content')
    </main>

    <footer class="page-footer">
        <span>© 2026 ProcureX &middot; Employee Portal</span>
        <div class="flex-center gap-2">
            <a href="#">Help</a>
            <a href="#">Privacy</a>
            <a href="#">Contact IT</a>
        </div>
    </footer>
</div>

</body>
</html>