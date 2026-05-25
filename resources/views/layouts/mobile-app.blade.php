<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Lymity">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <title>@yield('title', 'Lymity App')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            padding-bottom: 80px; /* room for bottom nav */
        }

        /* ── Header ─────────────────────────────── */
        .app-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: #1e293b;
            border-bottom: 1px solid #334155;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .app-header-logo {
            font-size: 18px;
            font-weight: 700;
            color: #38bdf8;
            letter-spacing: -0.5px;
        }
        .app-header-title {
            font-size: 15px;
            font-weight: 600;
            color: #f1f5f9;
        }
        .app-header-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: #38bdf8;
            text-decoration: none;
        }

        /* ── Content ─────────────────────────────── */
        .app-content {
            padding: 16px;
            max-width: 600px;
            margin: 0 auto;
        }

        /* ── Cards ─────────────────────────────────── */
        .app-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
        }
        .app-card-title {
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }

        /* ── Stat grid ────────────────────────────── */
        .stat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }
        .stat-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 14px;
            padding: 14px 16px;
            text-align: center;
        }
        .stat-number {
            font-size: 28px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }
        .stat-label {
            font-size: 11px;
            font-weight: 500;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* ── Approval items ───────────────────────── */
        .approval-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #1e293b;
            text-decoration: none;
            color: inherit;
        }
        .approval-item:last-child { border-bottom: none; }
        .approval-item:hover { opacity: 0.85; }
        .approval-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }
        .approval-info { flex: 1; min-width: 0; }
        .approval-name {
            font-size: 14px;
            font-weight: 600;
            color: #f1f5f9;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .approval-meta {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        /* ── Badges ──────────────────────────────── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.03em;
        }
        .badge-pending   { background: #fef08a22; color: #fde047; border: 1px solid #fde04744; }
        .badge-approved  { background: #bbf7d022; color: #4ade80; border: 1px solid #4ade8044; }
        .badge-rejected  { background: #fecaca22; color: #f87171; border: 1px solid #f8717144; }
        .badge-changes   { background: #bfdbfe22; color: #60a5fa; border: 1px solid #60a5fa44; }
        .badge-draft     { background: #e2e8f022; color: #94a3b8; border: 1px solid #94a3b844; }
        .badge-sent      { background: #a5f3fc22; color: #22d3ee; border: 1px solid #22d3ee44; }
        .badge-accepted  { background: #bbf7d022; color: #4ade80; border: 1px solid #4ade8044; }
        .badge-active    { background: #bbf7d022; color: #4ade80; border: 1px solid #4ade8044; }
        .badge-critical  { background: #fecaca22; color: #f87171; border: 1px solid #f8717144; }
        .badge-high      { background: #fed7aa22; color: #fb923c; border: 1px solid #fb923c44; }
        .badge-medium    { background: #fef08a22; color: #fde047; border: 1px solid #fde04744; }
        .badge-low       { background: #e2e8f022; color: #94a3b8; border: 1px solid #94a3b844; }

        /* ── Buttons ─────────────────────────────── */
        .btn-app {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.15s, transform 0.1s;
        }
        .btn-app:active { transform: scale(0.97); opacity: 0.85; }
        .btn-approve  { background: #22c55e; color: #fff; }
        .btn-reject   { background: #ef4444; color: #fff; }
        .btn-changes  { background: #3b82f6; color: #fff; }
        .btn-outline  { background: transparent; color: #94a3b8; border: 1px solid #334155; }
        .btn-full     { width: 100%; }

        /* ── Section headings ────────────────────── */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #f1f5f9;
        }
        .section-link {
            font-size: 12px;
            color: #38bdf8;
            text-decoration: none;
        }

        /* ── Alert messages ───────────────────────── */
        .app-alert {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 12px;
        }
        .app-alert-success { background: #bbf7d022; color: #4ade80; border: 1px solid #4ade8044; }
        .app-alert-error   { background: #fecaca22; color: #f87171; border: 1px solid #f8717144; }

        /* ── Forms ────────────────────────────────── */
        .app-input {
            width: 100%;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
            color: #e2e8f0;
            outline: none;
            transition: border-color 0.15s;
        }
        .app-input:focus { border-color: #38bdf8; }
        .app-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .app-field { margin-bottom: 14px; }

        /* ── Bottom Navigation ───────────────────── */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: #1e293b;
            border-top: 1px solid #334155;
            display: flex;
            align-items: stretch;
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }
        .bottom-nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 4px;
            text-decoration: none;
            color: #475569;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.03em;
            gap: 3px;
            transition: color 0.15s;
        }
        .bottom-nav-item.active,
        .bottom-nav-item:hover { color: #38bdf8; }
        .bottom-nav-icon { font-size: 20px; line-height: 1; }

        /* ── Empty state ──────────────────────────── */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #475569;
        }
        .empty-icon { font-size: 40px; margin-bottom: 12px; }
        .empty-text { font-size: 14px; }

        /* ── Detail sections ───────────────────────── */
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px solid #1e293b;
            font-size: 13px;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-key { color: #64748b; flex-shrink: 0; margin-right: 12px; }
        .detail-val { color: #e2e8f0; text-align: right; word-break: break-word; }

        /* ── Action area ─────────────────────────── */
        .action-area {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }
        .action-area-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        /* Desktop override */
        @media (min-width: 600px) {
            .app-content { padding: 24px; }
            .stat-grid { grid-template-columns: repeat(3, 1fr); }
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <header class="app-header">
        <a href="{{ route('app.dashboard') }}" class="app-header-logo">Lymity</a>
        <span class="app-header-title">@yield('header-title', 'App')</span>
        <a href="{{ route('app.profile') }}" class="app-header-avatar">
            {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
        </a>
    </header>

    {{-- Content --}}
    <main class="app-content">
        @if(session('success'))
            <div class="app-alert app-alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="app-alert app-alert-error">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    {{-- Bottom Navigation --}}
    <nav class="bottom-nav">
        <a href="{{ route('app.dashboard') }}" class="bottom-nav-item {{ request()->routeIs('app.dashboard') ? 'active' : '' }}">
            <span class="bottom-nav-icon">⊞</span>
            <span>Início</span>
        </a>
        <a href="{{ route('app.approvals.index') }}" class="bottom-nav-item {{ request()->routeIs('app.approvals*') ? 'active' : '' }}">
            <span class="bottom-nav-icon">✓</span>
            <span>Aprovações</span>
        </a>
        <a href="{{ route('app.calendar') }}" class="bottom-nav-item {{ request()->routeIs('app.calendar') ? 'active' : '' }}">
            <span class="bottom-nav-icon">◈</span>
            <span>Agenda</span>
        </a>
        <a href="{{ route('app.reports') }}" class="bottom-nav-item {{ request()->routeIs('app.reports') ? 'active' : '' }}">
            <span class="bottom-nav-icon">▣</span>
            <span>Relatórios</span>
        </a>
        <a href="{{ route('app.profile') }}" class="bottom-nav-item {{ request()->routeIs('app.profile') ? 'active' : '' }}">
            <span class="bottom-nav-icon">◉</span>
            <span>Perfil</span>
        </a>
    </nav>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>

</body>
</html>
