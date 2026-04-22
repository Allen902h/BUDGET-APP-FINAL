@extends('layouts.app')

@section('title', 'Dashboard | '.config('app.name', 'Budget App'))

@section('body_class', 'app-shell dashboard-shell-body')

@section('content')
@php
    $supportedCurrencies = config('currencies.supported', []);
    $defaultCurrency = config('currencies.default', 'USD');
    $currency = strtoupper(auth()->user()->currency_pref ?: $defaultCurrency);
    $currencyDetails = $supportedCurrencies[$currency] ?? ['label' => $currency, 'symbol' => $currency.' '];
    $currencySymbol = $currencyDetails['symbol'];
    $user = auth()->user();
    $savingsProgressValue = isset($summary) && $summary
        ? max(min((float) ($summary['savingsProgress'] ?? 0), 100), 0)
        : 0;
    $savingsRingValues = [$savingsProgressValue, max(100 - $savingsProgressValue, 0)];
    $brandName = 'KUYA ALLEN TECH SOLUTIONS';
    $brandTagline = 'Smart budget and tech workflow';
    $brandLogoFile = file_exists(public_path('images/kuya-allen-logo.png'))
        ? 'images/kuya-allen-logo.png'
        : (file_exists(public_path('images/wowlogo.png')) ? 'images/wowlogo.png' : 'icons/icon.svg');
    $brandLogo = asset($brandLogoFile).'?v='.(file_exists(public_path($brandLogoFile)) ? filemtime(public_path($brandLogoFile)) : time());
    $profilePhotoUrl = $user->profile_photo_path ? asset($user->profile_photo_path).'?v='.(file_exists(public_path($user->profile_photo_path)) ? filemtime(public_path($user->profile_photo_path)) : time()) : null;
@endphp

<style>
    .dashboard-shell-body .shell {
        width: calc(100% - 24px);
        max-width: none;
    }

    .dashboard-shell-body .topbar {
        display: none;
    }

    .dashboard-shell-body .page-content {
        padding-top: 8px;
        padding-bottom: 14px;
    }

    .dashboard-shell {
        display: grid;
        grid-template-columns: 240px minmax(0, 1fr);
        grid-template-rows: auto 1fr;
        gap: 0;
        overflow: hidden;
        min-height: calc(100vh - 118px);
        border-radius: 34px;
        border: 1px solid rgba(255, 255, 255, 0.34);
        background:
            linear-gradient(145deg, rgba(255, 250, 246, 0.98), rgba(249, 244, 255, 0.98));
        box-shadow: 0 22px 56px rgba(44, 36, 72, 0.12);
    }

    .dashboard-frame-header {
        grid-column: 1 / -1;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 16px 18px;
        background:
            linear-gradient(135deg, rgba(255, 152, 0, 0.9), rgba(255, 140, 109, 0.86) 46%, rgba(120, 101, 255, 0.82));
        border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.12);
        overflow: hidden;
    }

    .dashboard-frame-header::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(115deg, transparent 18%, rgba(255, 255, 255, 0.16) 46%, transparent 72%);
        transform: translateX(-120%);
        animation: dashboardHeaderSheen 8s ease-in-out infinite;
        pointer-events: none;
    }

    .dashboard-brand {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
        color: #fffaf5;
    }

    .dashboard-brand-logo {
        width: 92px;
        height: 92px;
        border-radius: 24px;
        object-fit: contain;
        background: rgba(255, 255, 255, 0.96);
        padding: 8px;
        box-shadow: 0 16px 30px rgba(72, 40, 108, 0.18);
        flex-shrink: 0;
    }

    .dashboard-brand-copy strong {
        display: block;
        font-size: 1.7rem;
        line-height: 1;
        color: #fffaf5;
        letter-spacing: -0.04em;
    }

    .dashboard-brand-copy span {
        display: block;
        margin-top: 4px;
        color: rgba(255, 250, 245, 0.84);
        font-size: 1rem;
    }

    .dashboard-frame-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .dashboard-frame-nav a,
    .dashboard-frame-nav button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 16px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: rgba(255, 255, 255, 0.08);
        color: #fffaf5;
        font-weight: 700;
        cursor: pointer;
        transition:
            background var(--motion-fast) var(--ease-standard),
            transform var(--motion-fast) var(--ease-standard),
            border-color var(--motion-fast) var(--ease-standard),
            box-shadow var(--motion-fast) var(--ease-standard);
    }

    .dashboard-frame-nav a:hover,
    .dashboard-frame-nav button:hover {
        background: rgba(255, 255, 255, 0.16);
        border-color: rgba(255, 255, 255, 0.24);
        transform: var(--lift-soft);
        box-shadow: 0 14px 28px rgba(72, 40, 108, 0.16);
    }

    .dashboard-nav-coin-image {
        width: 54px;
        height: 54px;
        flex-shrink: 0;
        object-fit: contain;
        filter: drop-shadow(0 12px 18px rgba(106, 43, 0, 0.24));
        transition:
            transform var(--motion-fast) var(--ease-standard),
            filter var(--motion-fast) var(--ease-standard);
        animation: dashboardCoinBounce 2.6s ease-in-out infinite;
    }

    .dashboard-nav-coin-shell {
        position: relative;
        display: inline-grid;
        place-items: center;
        width: 60px;
        height: 60px;
        flex-shrink: 0;
    }

    .dashboard-nav-coin-orbit {
        position: absolute;
        inset: 4px;
        border-radius: 999px;
        border: 1px dashed rgba(255, 240, 214, 0.44);
        animation: dashboardCoinOrbitSpin 7s linear infinite;
    }

    .dashboard-frame-nav:hover .dashboard-nav-coin-image,
    .dashboard-nav-coin-image:hover {
        transform: translateY(-2px) scale(1.04);
        filter: drop-shadow(0 16px 24px rgba(106, 43, 0, 0.28));
    }

    .dashboard-sidebar {
        position: sticky;
        top: 12px;
        padding: 28px 22px;
        background:
            linear-gradient(180deg, #ff9a18 0%, #f58d4a 24%, #a46be3 72%, #795fe0 100%);
        color: #f8fafc;
        display: grid;
        align-content: start;
        gap: 26px;
        border-right: 1px solid rgba(255, 255, 255, 0.08);
        isolation: isolate;
    }

    .dashboard-sidebar::before {
        content: "";
        position: absolute;
        inset: 14px 12px auto auto;
        width: 120px;
        height: 120px;
        border-radius: 999px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0));
        pointer-events: none;
        z-index: -1;
    }

    .sidebar-panel {
        position: relative;
        padding: 16px;
        border-radius: 22px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.11), rgba(255, 255, 255, 0.06));
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        overflow: hidden;
    }

    .sidebar-panel::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 18%, rgba(255, 255, 255, 0.14) 46%, transparent 74%);
        transform: translateX(-135%);
        transition: transform 760ms var(--ease-standard);
        pointer-events: none;
    }

    .sidebar-panel:hover::before {
        transform: translateX(135%);
    }

    .dashboard-avatar {
        width: 88px;
        height: 88px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        background: linear-gradient(180deg, #fffdf8, #ffe8c9);
        color: #7b341e;
        font-size: 2rem;
        font-weight: 900;
        box-shadow: 0 14px 30px rgba(8, 15, 27, 0.28);
        overflow: hidden;
    }

    .dashboard-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .dashboard-identity strong,
    .dashboard-sidebar .sidebar-section-title {
        display: block;
    }

    .dashboard-identity {
        display: grid;
        justify-items: start;
        padding: 18px;
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.08));
        box-shadow: 0 16px 30px rgba(52, 25, 84, 0.16);
        color: inherit;
        text-decoration: none;
        transition:
            transform var(--motion-base) var(--ease-standard),
            opacity var(--motion-base) var(--ease-standard),
            box-shadow var(--motion-base) var(--ease-standard);
    }

    .dashboard-identity:hover {
        transform: translateY(-3px);
        opacity: 0.98;
    }

    .dashboard-identity strong {
        font-size: 1.4rem;
        letter-spacing: -0.03em;
    }

    .dashboard-identity span {
        display: block;
        margin-top: 6px;
        color: rgba(248, 250, 252, 0.72);
        font-size: 0.92rem;
        word-break: break-word;
    }

    .sidebar-section-title {
        margin-bottom: 12px;
        color: rgba(248, 250, 252, 0.56);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .sidebar-nav,
    .sidebar-mini-stats {
        display: grid;
        gap: 10px;
    }

    .sidebar-link,
    .sidebar-mini-card {
        border-radius: 18px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.08);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
    }

    .sidebar-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 52px;
        padding: 0 14px;
        color: #f8fafc;
        font-weight: 700;
        transition:
            background var(--motion-fast) var(--ease-standard),
            transform var(--motion-fast) var(--ease-standard),
            border-color var(--motion-fast) var(--ease-standard),
            box-shadow var(--motion-fast) var(--ease-standard);
    }

    .sidebar-link-label {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .sidebar-link-icon {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        display: inline-grid;
        place-items: center;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
        flex-shrink: 0;
    }

    .sidebar-link-icon::before,
    .sidebar-link-icon::after {
        content: "";
        position: absolute;
    }

    .sidebar-link.active .sidebar-link-icon {
        background: rgba(255, 255, 255, 0.22);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .sidebar-link-icon {
        position: relative;
    }

    .sidebar-link-icon.overview::before {
        width: 14px;
        height: 14px;
        border-radius: 4px;
        border: 2px solid #fffaf5;
    }

    .sidebar-link-icon.planner::before {
        width: 14px;
        height: 10px;
        border: 2px solid #fffaf5;
        border-radius: 3px;
        top: 8px;
    }

    .sidebar-link-icon.planner::after {
        width: 10px;
        height: 2px;
        background: #fffaf5;
        top: 17px;
        left: 10px;
        box-shadow: 0 -4px 0 #fffaf5;
    }

    .sidebar-link-icon.history::before {
        width: 14px;
        height: 14px;
        border-radius: 999px;
        border: 2px solid #fffaf5;
    }

    .sidebar-link-icon.history::after {
        width: 2px;
        height: 6px;
        background: #fffaf5;
        top: 9px;
        left: 14px;
        box-shadow: 4px 4px 0 0 #fffaf5;
        transform: rotate(0deg);
        transform-origin: bottom center;
    }

    .sidebar-link-icon.goals::before {
        width: 14px;
        height: 12px;
        border-radius: 10px 10px 8px 8px;
        border: 2px solid #fffaf5;
        top: 9px;
    }

    .sidebar-link-icon.goals::after {
        width: 4px;
        height: 4px;
        border-radius: 999px;
        background: #fffaf5;
        top: 13px;
        left: 13px;
        box-shadow: -8px -6px 0 -1px #fffaf5, 8px -6px 0 -1px #fffaf5;
    }

    .sidebar-link-icon.settings::before {
        width: 14px;
        height: 14px;
        border-radius: 999px;
        border: 2px solid #fffaf5;
    }

    .sidebar-link-icon.settings::after {
        width: 4px;
        height: 4px;
        border-radius: 999px;
        background: #fffaf5;
        top: 13px;
        left: 13px;
        box-shadow:
            0 -10px 0 -1px #fffaf5,
            0 10px 0 -1px #fffaf5,
            -10px 0 0 -1px #fffaf5,
            10px 0 0 -1px #fffaf5;
    }

    .sidebar-link-icon.reports::before {
        width: 14px;
        height: 10px;
        border-radius: 2px;
        border-left: 2px solid #fffaf5;
        border-bottom: 2px solid #fffaf5;
        left: 9px;
        top: 10px;
    }

    .sidebar-link-icon.reports::after {
        width: 10px;
        height: 10px;
        border-right: 2px solid #fffaf5;
        border-top: 2px solid #fffaf5;
        right: 9px;
        top: 9px;
        transform: skew(-28deg);
    }

    .sidebar-link:hover {
        background: rgba(255, 255, 255, 0.14);
        border-color: rgba(255, 255, 255, 0.18);
        transform: translateX(4px) scale(1.01);
    }

    .sidebar-link.active {
        background: linear-gradient(135deg, rgba(255, 248, 239, 0.34), rgba(255, 255, 255, 0.14));
        border-color: rgba(255, 239, 214, 0.42);
        box-shadow: 0 12px 24px rgba(49, 27, 85, 0.16);
    }

    .dashboard-section-anchor {
        scroll-margin-top: 28px;
    }

    .sidebar-link code {
        min-height: 28px;
        padding: 0 10px;
        background: rgba(255, 255, 255, 0.14);
        border: none;
        color: #f8fafc;
        font-size: 0.78rem;
        border-radius: 999px;
    }

    .sidebar-mini-card {
        position: relative;
        overflow: hidden;
        padding: 14px;
    }

    .sidebar-mini-card::after {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: linear-gradient(180deg, rgba(255, 216, 132, 0.92), rgba(255, 255, 255, 0.2));
    }

    .sidebar-mini-card span {
        display: block;
        color: rgba(248, 250, 252, 0.66);
        font-size: 0.82rem;
    }

    .sidebar-mini-card strong {
        display: block;
        margin-top: 6px;
        font-size: 1.3rem;
    }

    .sidebar-footer-note {
        padding: 14px 16px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.08);
        border: 1px dashed rgba(255, 255, 255, 0.18);
        color: rgba(248, 250, 252, 0.76);
        font-size: 0.86rem;
        line-height: 1.55;
    }

    .dashboard-main {
        padding: 30px 26px 26px;
        display: grid;
        gap: 22px;
        min-width: 0;
        align-content: start;
        background:
            linear-gradient(180deg, rgba(255, 253, 250, 0.96), rgba(246, 241, 255, 0.96));
    }

    .dashboard-panel-section {
        display: none;
        gap: 18px;
        align-content: start;
        animation: dashboardPanelIn var(--motion-base) var(--ease-emphasis);
    }

    .dashboard-panel-section.is-active {
        display: grid;
    }

    .dashboard-main-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        position: sticky;
        top: 0;
        z-index: 18;
        padding: 14px 14px 20px;
        margin: -8px -6px 0;
        border-bottom: 1px solid rgba(120, 101, 255, 0.08);
        background: linear-gradient(180deg, rgba(255, 253, 250, 0.98), rgba(248, 244, 255, 0.96));
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 24px rgba(72, 40, 108, 0.06);
        border-radius: 24px;
    }

    .dashboard-main-header h1 {
        margin: 0;
        font-size: clamp(1.6rem, 2.4vw, 2.2rem);
        line-height: 1.1;
        color: #1e293b;
    }

    .dashboard-main-header p {
        margin: 6px 0 0;
        color: rgba(30, 41, 59, 0.68);
    }

    .header-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: end;
    }


    .insights-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 48px;
        padding: 0 18px;
        border-radius: 14px;
        border: 1px solid rgba(120, 101, 255, 0.14);
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.1), rgba(120, 101, 255, 0.12));
        color: #2c2151;
        font-weight: 800;
        transition:
            transform var(--motion-fast) var(--ease-standard),
            box-shadow var(--motion-fast) var(--ease-standard),
            background var(--motion-fast) var(--ease-standard),
            border-color var(--motion-fast) var(--ease-standard);
    }

    .insights-toggle:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(72, 40, 108, 0.12);
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.18), rgba(120, 101, 255, 0.18));
    }

    .sidebar-link-button {
        width: 100%;
        cursor: pointer;
        font: inherit;
        text-align: left;
    }

    .dashboard-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .dashboard-kpi {
        position: relative;
        padding: 18px 18px 16px;
        border-radius: 18px;
        border: 1px solid rgba(120, 101, 255, 0.12);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 247, 241, 0.94));
        box-shadow: 0 10px 22px rgba(72, 40, 108, 0.08);
        transition:
            transform var(--motion-base) var(--ease-standard),
            box-shadow var(--motion-base) var(--ease-standard),
            border-color var(--motion-base) var(--ease-standard);
        overflow: hidden;
    }

    .dashboard-kpi::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 18%, rgba(255, 255, 255, 0.28) 48%, transparent 78%);
        transform: translateX(-145%);
        transition: transform 760ms var(--ease-standard);
        pointer-events: none;
    }

    .dashboard-kpi:hover,
    .board-panel:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 28px rgba(72, 40, 108, 0.1);
    }

    .dashboard-kpi:hover::before,
    .board-panel:hover::before {
        transform: translateX(145%);
    }

    .dashboard-kpi.primary {
        background: linear-gradient(180deg, #ff9a18, #f06a3f 52%, #8658d5 100%);
        color: #fff;
    }

    .dashboard-kpi span {
        display: block;
        font-size: 0.82rem;
        color: inherit;
        opacity: 0.76;
    }

    .dashboard-kpi strong {
        display: block;
        margin-top: 10px;
        font-size: clamp(1.7rem, 2.6vw, 2.3rem);
        line-height: 1;
    }

    .dashboard-kpi small {
        display: block;
        margin-top: 10px;
        color: inherit;
        opacity: 0.7;
    }

    .board-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) 260px;
        gap: 18px;
    }

    .board-stack {
        display: grid;
        gap: 18px;
        min-width: 0;
    }

    .board-panel {
        position: relative;
        padding: 20px;
        border-radius: 22px;
        border: 1px solid rgba(120, 101, 255, 0.12);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.97), rgba(255, 250, 246, 0.94));
        box-shadow: 0 10px 24px rgba(72, 40, 108, 0.08);
        min-width: 0;
        transition:
            transform var(--motion-base) var(--ease-standard),
            box-shadow var(--motion-base) var(--ease-standard),
            border-color var(--motion-base) var(--ease-standard);
        overflow: hidden;
    }

    .board-panel::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, transparent 20%, rgba(255, 255, 255, 0.22) 48%, transparent 76%);
        transform: translateX(-140%);
        transition: transform 760ms var(--ease-standard);
        pointer-events: none;
    }

    .board-panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid rgba(120, 101, 255, 0.08);
    }

    .board-panel-header h2 {
        font-size: 1.08rem;
        letter-spacing: -0.02em;
        color: #1e293b;
    }

    .board-panel-header p {
        margin: 4px 0 0;
        color: rgba(30, 41, 59, 0.62);
        font-size: 0.86rem;
    }

    .board-panel canvas {
        width: 100% !important;
        height: 210px !important;
    }

    .mini-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 14px;
        border-radius: 999px;
        background: linear-gradient(135deg, #ff9a18, #f06a3f);
        color: white;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .board-secondary {
        display: grid;
        grid-template-columns: 1.3fr 0.7fr;
        gap: 18px;
    }

    .dashboard-ring-wrap {
        display: grid;
        place-items: center;
        gap: 14px;
        text-align: center;
    }

    .dashboard-ring-wrap canvas {
        height: 170px !important;
        max-width: 170px;
    }

    .alert-list,
    .compact-list {
        display: grid;
        gap: 10px;
    }

    .alert-row,
    .compact-row {
        padding: 10px 12px;
        border-radius: 14px;
        border: 1px solid rgba(120, 101, 255, 0.08);
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.08), rgba(120, 101, 255, 0.08));
    }

    .alert-row strong,
    .compact-row strong {
        display: block;
        font-size: 0.95rem;
        color: #1e293b;
    }

    .alert-row span,
    .compact-row span {
        display: block;
        margin-top: 4px;
        color: rgba(30, 41, 59, 0.62);
        font-size: 0.84rem;
    }

    .board-forms {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .board-forms .board-panel {
        height: 100%;
    }

    .stack-form.compact-form {
        gap: 12px;
    }

    .compact-form input,
    .compact-form select,
    .compact-form textarea {
        min-height: 44px;
        padding: 10px 14px;
        border-radius: 12px;
        background: linear-gradient(180deg, #ffffff, #fff7f0);
    }

    .compact-form textarea {
        min-height: 92px;
    }

    .settings-compact-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        align-items: end;
    }

    .settings-compact-form .field {
        margin: 0;
    }

    .settings-compact-form .field:last-of-type {
        grid-column: 1 / -1;
    }

    .settings-submit {
        min-width: 180px;
        justify-self: start;
    }

    .dashboard-main .field input,
    .dashboard-main .field select,
    .dashboard-main .field textarea {
        border: 1px solid rgba(120, 101, 255, 0.12);
        box-shadow: none;
    }

    .dashboard-main .field input:focus,
    .dashboard-main .field select:focus,
    .dashboard-main .field textarea:focus {
        border-color: rgba(120, 101, 255, 0.32);
        box-shadow: 0 0 0 4px rgba(120, 101, 255, 0.08);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        align-items: end;
    }

    .history-toolbar {
        display: grid;
        gap: 14px;
        margin-bottom: 18px;
        padding: 18px;
        border-radius: 20px;
        border: 1px solid rgba(120, 101, 255, 0.1);
        background: linear-gradient(180deg, rgba(255, 247, 240, 0.72), rgba(247, 242, 255, 0.72));
    }

    .history-search-row {
        display: grid;
        grid-template-columns: minmax(0, 1.8fr) repeat(2, minmax(0, 0.75fr)) auto;
        gap: 12px;
        align-items: end;
    }

    .history-search-row .field,
    .filter-grid .field {
        margin: 0;
    }

    .search-input-wrap {
        position: relative;
    }

    .search-input-wrap input {
        padding-left: 44px;
    }

    .search-input-wrap::before {
        content: "";
        position: absolute;
        left: 16px;
        top: 50%;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(44, 33, 81, 0.45);
        border-radius: 999px;
        transform: translateY(-60%);
    }

    .search-input-wrap::after {
        content: "";
        position: absolute;
        left: 29px;
        top: 58%;
        width: 8px;
        height: 2px;
        background: rgba(44, 33, 81, 0.45);
        transform: rotate(45deg);
        border-radius: 999px;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    .filter-summary {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        min-height: 32px;
        padding: 0 12px;
        border-radius: 999px;
        background: rgba(120, 101, 255, 0.08);
        border: 1px solid rgba(120, 101, 255, 0.1);
        color: #4b3d7d;
        font-size: 0.84rem;
        font-weight: 700;
    }

    .history-meta {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        color: rgba(27, 35, 64, 0.65);
        font-size: 0.9rem;
    }

    .category-action-cell {
        min-width: 460px;
    }

    .category-inline-edit {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(92px, 0.85fr) 84px auto auto;
        gap: 8px;
        align-items: center;
    }

    .settings-grid .category-inline-edit input {
        width: 100%;
        min-width: 0;
        min-height: 40px;
    }

    .settings-grid .category-inline-edit .inline-check {
        gap: 8px;
        white-space: nowrap;
        justify-self: start;
    }

    .settings-grid .category-inline-edit .inline-check input {
        width: 16px;
        min-height: 16px;
    }

    .settings-grid .category-inline-edit .table-action,
    .settings-grid .category-delete-form .danger-button {
        min-height: 40px;
        padding: 0 14px;
        border-radius: 12px;
    }

    .settings-grid .category-delete-form {
        margin-top: 8px;
    }

    .table-panel table {
        min-width: 760px;
    }

    .table-panel tbody tr:hover {
        background: rgba(255, 152, 0, 0.05);
    }

    .goals-grid,
    .settings-grid {
        display: grid;
        grid-template-columns: minmax(280px, 0.82fr) minmax(0, 1.18fr);
        gap: 18px;
    }

    .profile-card {
        grid-column: 1 / -1;
    }

    .profile-layout {
        display: grid;
        grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
        gap: 18px;
        align-items: start;
    }

    .profile-photo-panel {
        display: grid;
        gap: 14px;
        padding: 18px;
        border-radius: 24px;
        background: linear-gradient(145deg, rgba(255, 247, 236, 0.9), rgba(242, 236, 255, 0.86));
        border: 1px solid rgba(121, 95, 224, 0.12);
    }

    .profile-photo-frame {
        width: 144px;
        height: 144px;
        margin: 0 auto;
        border-radius: 32px;
        overflow: hidden;
        background: linear-gradient(180deg, #fffdf8, #ffe8c9);
        box-shadow: 0 16px 32px rgba(72, 40, 108, 0.16);
        display: grid;
        place-items: center;
        color: #7b341e;
        font-size: 3rem;
        font-weight: 900;
    }

    .profile-photo-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-photo-actions {
        display: grid;
        gap: 10px;
    }

    .profile-photo-note {
        margin: 0;
        color: #6b5d87;
        font-size: 0.88rem;
        line-height: 1.5;
    }

    .password-field {
        position: relative;
    }

    .password-field input {
        padding-right: 52px;
    }

    .password-field input[type="password"]::-ms-reveal,
    .password-field input[type="password"]::-ms-clear {
        display: none;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 12px;
        width: 34px;
        height: 34px;
        border: 1px solid rgba(121, 95, 224, 0.18);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.88);
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 160ms ease, border-color 160ms ease, transform 160ms ease;
    }

    .password-toggle:hover {
        border-color: rgba(121, 95, 224, 0.34);
        background: #ffffff;
        transform: translateY(-50%) scale(1.03);
    }

    .password-toggle::before {
        content: '';
        width: 16px;
        height: 10px;
        border: 2px solid #795fe0;
        border-radius: 14px 14px 10px 10px / 11px 11px 9px 9px;
        display: block;
    }

    .password-toggle::after {
        content: '';
        position: absolute;
        width: 4px;
        height: 4px;
        border-radius: 999px;
        background: #795fe0;
    }

    .password-toggle span {
        display: none;
    }

    .password-toggle[data-visible="true"]::before {
        opacity: 0.72;
    }

    .password-toggle[data-visible="true"]::after {
        width: 20px;
        height: 2px;
        border-radius: 999px;
        transform: rotate(-36deg);
        background: #f58d4a;
    }

    .goal-card {
        padding: 14px;
        border-radius: 16px;
        border: 1px solid rgba(120, 101, 255, 0.08);
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.07), rgba(120, 101, 255, 0.07));
    }

    .goal-card + .goal-card {
        margin-top: 12px;
    }

    .goal-card-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: flex-start;
    }

    .goal-card-title {
        font-size: 1.16rem;
        font-weight: 800;
        color: #1b2340;
    }

    .goal-card-subtitle {
        margin-top: 4px;
        color: rgba(27, 35, 64, 0.74);
        font-size: 0.92rem;
    }

    .goal-progress-stack {
        margin-top: 12px;
        display: grid;
        gap: 10px;
    }

    .goal-progress-meta {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .goal-progress-value {
        text-align: right;
    }

    .goal-progress-value strong {
        display: block;
        font-size: 1.02rem;
        color: #2d2150;
    }

    .goal-progress-value span {
        display: block;
        margin-top: 2px;
        color: rgba(27, 35, 64, 0.72);
        font-size: 0.84rem;
    }

    .goal-progress-wrap {
        position: relative;
    }

    .goal-progress-wrap .progress-bar {
        margin: 0;
    }

    .goal-progress-wrap .progress-bar.is-complete span {
        background: linear-gradient(135deg, #0f766e, #22c55e);
    }

    .goal-progress-markers {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }

    .goal-progress-markers span {
        position: absolute;
        top: -2px;
        bottom: -2px;
        width: 1px;
        background: rgba(45, 33, 80, 0.12);
    }

    .goal-progress-markers span:nth-child(1) { left: 25%; }
    .goal-progress-markers span:nth-child(2) { left: 50%; }
    .goal-progress-markers span:nth-child(3) { left: 75%; }

    .goal-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .goal-summary-item {
        padding: 10px 12px;
        border-radius: 14px;
        border: 1px solid rgba(120, 101, 255, 0.08);
        background: rgba(255, 255, 255, 0.7);
    }

    .goal-summary-item span {
        display: block;
        color: rgba(27, 35, 64, 0.62);
        font-size: 0.78rem;
    }

    .goal-summary-item strong {
        display: block;
        margin-top: 6px;
        font-size: 0.98rem;
        color: #1b2340;
    }

    .goal-editor {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid rgba(120, 101, 255, 0.08);
    }

    .goal-editor-header {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        margin-bottom: 12px;
    }

    .goal-editor-header strong {
        font-size: 0.98rem;
    }

    .goal-editor-status {
        color: rgba(27, 35, 64, 0.72);
        font-size: 0.82rem;
        font-weight: 700;
    }

    .goal-editor-grid {
        display: grid;
        gap: 12px;
    }

    .goal-readonly-field {
        min-height: 44px;
        padding: 10px 14px;
        border-radius: 12px;
        border: 1px solid rgba(120, 101, 255, 0.12);
        background: rgba(255, 255, 255, 0.72);
        color: #2d2150;
        font-weight: 700;
    }

    .goal-timeline-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 2px;
    }

    .goal-timeline-item {
        padding: 10px 12px;
        border-radius: 14px;
        border: 1px solid rgba(120, 101, 255, 0.08);
        background: rgba(255, 255, 255, 0.74);
    }

    .goal-timeline-item span {
        display: block;
        color: rgba(27, 35, 64, 0.62);
        font-size: 0.78rem;
    }

    .goal-timeline-item strong {
        display: block;
        margin-top: 6px;
        font-size: 0.94rem;
        color: #1b2340;
    }

    .goal-editor-grid .field {
        margin: 0;
    }

    .goal-editor-grid .two-field-grid {
        gap: 12px;
    }

    .goal-helper-text {
        margin-top: 6px;
        color: rgba(27, 35, 64, 0.78);
        font-size: 0.8rem;
    }

    .goal-warning {
        margin-top: 8px;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid rgba(217, 119, 6, 0.16);
        background: rgba(254, 243, 199, 0.8);
        color: #9a3412;
        font-size: 0.84rem;
    }

    .goal-warning[hidden] {
        display: none;
    }

    .goal-actions {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .goal-update-button {
        min-width: 170px;
    }

    .goal-update-button[disabled] {
        opacity: 0.55;
        cursor: not-allowed;
        transform: none;
    }

    .goal-delete-button {
        min-height: 42px;
        padding: 0 18px;
    }

    .dashboard-empty {
        padding: 30px;
        border-radius: 26px;
        border: 1px solid rgba(120, 101, 255, 0.12);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.97), rgba(255, 248, 242, 0.94));
        box-shadow: 0 16px 28px rgba(72, 40, 108, 0.1);
    }

    .overview-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .overview-card {
        position: relative;
        overflow: hidden;
        padding: 20px;
        border-radius: 22px;
        border: 1px solid rgba(120, 101, 255, 0.12);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 247, 241, 0.94));
        box-shadow: 0 16px 28px rgba(72, 40, 108, 0.1);
    }

    .overview-card::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: 0;
        height: 4px;
        background: linear-gradient(90deg, #ff9800, #ff8c6d 55%, #7865ff);
        opacity: 0.88;
    }

    .overview-card span {
        display: block;
        color: rgba(27, 35, 64, 0.62);
        font-size: 0.82rem;
    }

    .overview-card strong {
        display: block;
        margin-top: 12px;
        font-size: 1.7rem;
        color: #1b2340;
    }

    .overview-card small {
        display: block;
        margin-top: 10px;
        color: rgba(27, 35, 64, 0.62);
    }

    .table-wrap {
        overflow: auto;
        padding: 8px;
        border-radius: 20px;
        background: linear-gradient(180deg, rgba(255, 250, 244, 0.78), rgba(247, 242, 255, 0.7));
        border: 1px solid rgba(120, 101, 255, 0.08);
    }

    .table-wrap table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 720px;
    }

    .table-wrap thead th {
        position: sticky;
        top: 0;
        background: rgba(255, 255, 255, 0.94);
        z-index: 1;
    }

    @keyframes dashboardPanelIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .insights-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(24, 18, 44, 0.4);
        backdrop-filter: blur(4px);
        opacity: 0;
        pointer-events: none;
        transition:
            opacity var(--motion-base) var(--ease-standard),
            backdrop-filter var(--motion-base) var(--ease-standard);
        z-index: 60;
    }

    .insights-drawer {
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        padding: 28px;
        border-radius: 0;
        border: none;
        background:
            linear-gradient(180deg, rgba(255, 250, 246, 0.98), rgba(246, 240, 255, 0.98));
        box-shadow: none;
        transform: translateY(26px) scale(0.985);
        transition:
            transform var(--motion-slow) var(--ease-emphasis),
            opacity var(--motion-base) var(--ease-standard);
        z-index: 70;
        display: grid;
        grid-template-rows: auto 1fr;
        gap: 16px;
        opacity: 0;
        pointer-events: none;
    }

    .insights-drawer.is-open,
    .insights-backdrop.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    .insights-drawer.is-open {
        transform: translateY(0) scale(1);
    }

    .insights-drawer-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 0 4px 18px;
        margin-bottom: 18px;
        border-bottom: 1px solid rgba(120, 101, 255, 0.1);
    }

    .insights-drawer-header h2 {
        margin: 0;
        color: #1b2340;
    }

    .insights-drawer-header p {
        margin: 6px 0 0;
        color: rgba(27, 35, 64, 0.68);
    }

    .insights-drawer-close {
        min-width: 42px;
        min-height: 42px;
        border-radius: 14px;
        border: 1px solid rgba(120, 101, 255, 0.12);
        background: rgba(255, 255, 255, 0.74);
        color: #2c2151;
        font-size: 1.4rem;
        line-height: 1;
        cursor: pointer;
    }

    .insights-drawer-body {
        overflow-y: auto;
        padding-right: 6px;
        display: grid;
        gap: 22px;
    }

    .insights-summary {
        display: grid;
        grid-template-columns: minmax(0, 1.25fr) auto;
        gap: 18px;
        align-items: center;
        padding: 18px 20px;
        border-radius: 22px;
        border: 1px solid rgba(120, 101, 255, 0.12);
        background: linear-gradient(135deg, rgba(255, 152, 0, 0.12), rgba(120, 101, 255, 0.12));
    }

    .insights-summary strong {
        display: block;
        color: #1b2340;
        font-size: 1rem;
    }

    .insights-summary span {
        display: block;
        margin-top: 6px;
        color: rgba(27, 35, 64, 0.7);
        font-size: 0.92rem;
    }

    .insights-summary-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 16px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(120, 101, 255, 0.12);
        color: #4b3d7d;
        font-weight: 800;
        white-space: nowrap;
    }

    .insights-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(320px, 0.9fr);
        gap: 22px;
        align-items: start;
    }

    .insights-stack,
    .insights-side-stack {
        display: grid;
        gap: 22px;
    }

    .reports-panel {
        padding: 22px;
        border-radius: 24px;
        border: 1px solid rgba(120, 101, 255, 0.12);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 248, 242, 0.94));
        box-shadow: 0 14px 32px rgba(72, 40, 108, 0.08);
    }

    .reports-panel .board-panel-header {
        margin-bottom: 18px;
    }

    .dashboard-main h1,
    .dashboard-main h2,
    .dashboard-main strong {
        color: #1b2340;
    }

    .dashboard-main p,
    .dashboard-main small,
    .dashboard-main .field span {
        color: rgba(27, 35, 64, 0.66);
    }

    .dashboard-main table th {
        color: rgba(27, 35, 64, 0.52);
    }

    .dashboard-main table td {
        color: #25314f;
    }

    @media (max-width: 1180px) {
        .dashboard-shell {
            grid-template-columns: 1fr;
        }

        .dashboard-frame-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .dashboard-frame-nav {
            justify-content: flex-start;
        }

        .dashboard-sidebar {
            position: static;
            max-height: none;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .dashboard-identity {
            grid-column: 1 / -1;
        }

        .profile-layout {
            grid-template-columns: 1fr;
        }

        .dashboard-kpi-grid,
        .board-forms,
        .goals-grid,
        .settings-grid,
        .overview-grid,
        .insights-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .board-grid,
        .board-secondary {
            grid-template-columns: 1fr;
        }

        .history-search-row,
        .filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .goal-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .goal-timeline-grid {
            grid-template-columns: 1fr;
        }

        .settings-compact-form {
            grid-template-columns: 1fr;
        }

        .settings-compact-form .field:last-of-type {
            grid-column: auto;
        }

        .category-action-cell {
            min-width: 0;
        }

        .category-inline-edit {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 760px) {
        .dashboard-shell-body .shell {
            width: calc(100% - 14px);
        }

        .dashboard-shell {
            min-height: auto;
            border-radius: 24px;
        }

        .dashboard-frame-header {
            padding: 14px;
            gap: 14px;
        }

        .dashboard-sidebar,
        .dashboard-kpi-grid,
        .board-forms,
        .goals-grid,
        .settings-grid,
        .overview-grid,
        .insights-grid,
        .filter-grid,
        .history-search-row,
        .settings-compact-form {
            grid-template-columns: 1fr;
        }

        .goal-summary-grid,
        .goal-editor-grid .two-field-grid,
        .goal-timeline-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-brand {
            width: 100%;
            flex-direction: row;
            align-items: center;
            gap: 12px;
        }

        .dashboard-brand-logo {
            width: 68px;
            height: 68px;
            border-radius: 18px;
            padding: 6px;
        }

        .dashboard-brand-copy strong {
            font-size: 1.2rem;
        }

        .dashboard-brand-copy span {
            font-size: 0.9rem;
        }

        .dashboard-frame-nav {
            width: 100%;
            justify-content: stretch;
            gap: 10px;
        }

        .dashboard-frame-nav form,
        .dashboard-nav-coin-shell,
        .dashboard-frame-nav button {
            width: 100%;
        }

        .dashboard-frame-nav button {
            justify-content: center;
        }

        .dashboard-nav-coin-shell {
            height: 56px;
        }

        .dashboard-main {
            padding: 14px;
        }

        .dashboard-main-header {
            flex-direction: column;
            align-items: flex-start;
            top: 0;
            margin: -2px -2px 0;
            padding: 12px 10px 16px;
        }

        .category-inline-edit {
            grid-template-columns: 1fr;
        }

        .header-actions {
            justify-content: flex-start;
        }

        .insights-drawer {
            inset: 0;
            width: 100vw;
            height: 100vh;
            padding: 18px 14px;
            border-radius: 0;
        }

        .insights-summary {
            grid-template-columns: 1fr;
        }

        .dashboard-sidebar {
            gap: 14px;
            padding: 14px;
        }

        .profile-photo-frame {
            width: 124px;
            height: 124px;
        }

        .sidebar-panel {
            padding: 14px;
        }

        .sidebar-link {
            min-height: 48px;
            padding: 0 12px;
        }

        .sidebar-link code {
            font-size: 0.72rem;
            padding: 0 8px;
        }

        .dashboard-kpi {
            padding: 16px;
        }

        .board-panel,
        .reports-panel {
            padding: 16px;
            border-radius: 18px;
        }

        .board-panel-header {
            align-items: flex-start;
            gap: 10px;
        }

        .board-panel canvas {
            height: 180px !important;
        }
    }

    @media (max-width: 540px) {
        .dashboard-shell-body .shell {
            width: calc(100% - 8px);
        }

        .dashboard-shell {
            border-radius: 18px;
        }

        .dashboard-frame-header {
            padding: 12px;
        }

        .dashboard-brand {
            align-items: flex-start;
        }

        .dashboard-brand-logo {
            width: 58px;
            height: 58px;
        }

        .dashboard-brand-copy strong {
            font-size: 1rem;
            line-height: 1.1;
        }

        .dashboard-brand-copy span {
            font-size: 0.84rem;
        }

        .dashboard-main {
            padding: 10px;
            gap: 14px;
        }

        .dashboard-main-header h1 {
            font-size: 1.35rem;
        }

        .dashboard-main-header p {
            font-size: 0.9rem;
        }

        .dashboard-sidebar {
            padding: 10px;
        }

        .dashboard-identity {
            padding: 14px;
            border-radius: 18px;
        }

        .dashboard-avatar {
            width: 64px;
            height: 64px;
            font-size: 1.5rem;
        }

        .dashboard-identity strong {
            font-size: 1.05rem;
        }

        .sidebar-panel {
            padding: 12px;
            border-radius: 18px;
        }

        .sidebar-section-title {
            margin-bottom: 10px;
        }

        .sidebar-link {
            min-height: 44px;
            border-radius: 14px;
        }

        .sidebar-link-label {
            gap: 8px;
        }

        .sidebar-link-icon {
            width: 26px;
            height: 26px;
        }

        .sidebar-mini-card {
            padding: 12px;
        }

        .sidebar-mini-card strong {
            font-size: 1.05rem;
        }

        .dashboard-kpi-grid {
            gap: 12px;
        }

        .dashboard-kpi {
            padding: 14px;
            border-radius: 16px;
        }

        .dashboard-kpi strong {
            font-size: 1.4rem;
        }

        .board-grid,
        .board-stack,
        .board-secondary,
        .insights-grid,
        .insights-stack,
        .insights-side-stack {
            gap: 14px;
        }

        .board-panel,
        .reports-panel {
            padding: 14px;
            border-radius: 16px;
        }

        .board-panel-header {
            margin-bottom: 14px;
            padding-bottom: 10px;
        }

        .board-panel-header h2 {
            font-size: 1rem;
        }

        .board-panel-header p {
            font-size: 0.82rem;
        }

        .insights-drawer {
            padding: 14px 10px;
        }

        .insights-drawer-header {
            padding: 0 2px 12px;
            margin-bottom: 12px;
        }

        .insights-summary-badge {
            min-height: 34px;
            padding: 0 12px;
            white-space: normal;
            text-align: center;
        }

        .reports-panel table,
        .board-panel table {
            min-width: 640px;
        }

    }

    @keyframes dashboardHeaderSheen {
        0%,
        100% {
            transform: translateX(-120%);
        }

        32%,
        52% {
            transform: translateX(130%);
        }
    }

    @keyframes dashboardCoinOrbitSpin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    @keyframes dashboardCoinBounce {
        0%,
        100% {
            transform: translateY(0) scale(1);
        }

        40% {
            transform: translateY(-4px) scale(1.05);
        }

        60% {
            transform: translateY(-1px) scale(1.02);
        }
    }
</style>

<div class="dashboard-shell">
    <header class="dashboard-frame-header">
        <div class="dashboard-brand">
            <a href="{{ route('logo.viewer') }}" aria-label="View {{ $brandName }} logo">
                <img class="dashboard-brand-logo" src="{{ $brandLogo }}" alt="{{ $brandName }} logo">
            </a>
            <div class="dashboard-brand-copy">
                <strong>{{ $brandName }}</strong>
                <span>{{ $brandTagline }}</span>
            </div>
        </div>

        <nav class="dashboard-frame-nav">
            <span class="dashboard-nav-coin-shell" aria-hidden="true">
                <span class="dashboard-nav-coin-orbit"></span>
                <img class="dashboard-nav-coin-image" src="{{ asset('images/Coin.gif') }}" alt="" aria-hidden="true">
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </nav>
    </header>

    <aside class="dashboard-sidebar">
        <a class="dashboard-identity dashboard-nav-link" href="#settings" data-target="settings">
            <div class="dashboard-avatar">
                @if($profilePhotoUrl)
                    <img src="{{ $profilePhotoUrl }}" alt="{{ $user->name }} profile photo">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </div>
            <div style="margin-top:18px;">
                <strong>{{ strtoupper($user->name) }}</strong>
                <span>{{ $user->email }}</span>
            </div>
        </a>

        <div class="sidebar-panel">
            <span class="sidebar-section-title">Navigation</span>
            <div class="sidebar-nav">
                <a class="sidebar-link active dashboard-nav-link" href="#overview" data-target="overview"><span class="sidebar-link-label"><span class="sidebar-link-icon overview" aria-hidden="true"></span>Overview</span><code>Home</code></a>
                <a class="sidebar-link dashboard-nav-link" href="#planner" data-target="planner"><span class="sidebar-link-label"><span class="sidebar-link-icon planner" aria-hidden="true"></span>Planner</span><code>Cycle</code></a>
                <a class="sidebar-link dashboard-nav-link" href="#history" data-target="history"><span class="sidebar-link-label"><span class="sidebar-link-icon history" aria-hidden="true"></span>History</span><code>Logs</code></a>
                <a class="sidebar-link dashboard-nav-link" href="#goals" data-target="goals"><span class="sidebar-link-label"><span class="sidebar-link-icon goals" aria-hidden="true"></span>Goals</span><code>Save</code></a>
                <a class="sidebar-link dashboard-nav-link" href="#settings" data-target="settings"><span class="sidebar-link-label"><span class="sidebar-link-icon settings" aria-hidden="true"></span>Settings</span><code>Edit</code></a>
                <button type="button" class="sidebar-link sidebar-link-button insights-toggle" data-insights-open>
                    <span class="sidebar-link-label"><span class="sidebar-link-icon reports" aria-hidden="true"></span>Reports</span><code>Stats</code>
                </button>
            </div>
        </div>

        <div class="sidebar-panel">
            <span class="sidebar-section-title">Snapshot</span>
            <div class="sidebar-mini-stats">
                <div class="sidebar-mini-card">
                    <span>Currency</span>
                    <strong>{{ $currencySymbol }} {{ $currency }}</strong>
                </div>
                <div class="sidebar-mini-card">
                    <span>Categories</span>
                    <strong>{{ $allCategories->count() }}</strong>
                </div>
                <div class="sidebar-mini-card">
                    <span>Goals</span>
                    <strong>{{ $allSavingsGoals->count() }}</strong>
                </div>
            </div>
        </div>

        <div class="sidebar-footer-note">
            Use the sidebar to jump between sections quickly. Your reports stay separate so the planner area stays easier to reach.
        </div>

    </aside>

    <section class="dashboard-main">
        <div class="dashboard-main-header">
            <div>
                <h1>Budget Workspace</h1>
                <p>Budget tracking, reports, categories, alerts, and savings controls in one polished control center.</p>
            </div>

            <div class="header-actions">
                <form method="GET" action="{{ route('dashboard') }}" class="cycle-switcher">
                    <label class="field compact-field">
                        <span>View cycle</span>
                        <select name="cycle" onchange="this.form.submit()">
                            <option value="">Current active cycle</option>
                            @foreach($allCycles as $item)
                                <option value="{{ $item->id }}" {{ $cycle && $cycle->id === $item->id ? 'selected' : '' }}>
                                    {{ $item->start_date->format('M d') }} - {{ $item->end_date->format('M d') }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </form>
                <button type="button" class="insights-toggle" data-insights-open>Open Reports</button>
                <a href="{{ route('dashboard.report.excel') }}" class="secondary-button">Download Excel Report</a>
            </div>
        </div>

        <section class="dashboard-panel-section is-active" id="overview">
            @if($cycle && $summary)
                <div class="overview-grid">
                    <article class="overview-card">
                        <span>Active cycle</span>
                        <strong>{{ $cycle->start_date->format('M d') }} - {{ $cycle->end_date->format('M d') }}</strong>
                        <small>{{ number_format($cycle->amount, 2) }} planned income</small>
                    </article>
                    <article class="overview-card">
                        <span>Transactions</span>
                        <strong>{{ $filteredTransactions->count() }}</strong>
                        <small>Visible records in the selected cycle</small>
                    </article>
                    <article class="overview-card">
                        <span>Categories</span>
                        <strong>{{ $allCategories->count() }}</strong>
                        <small>Budget groups and bill categories ready</small>
                    </article>
                    <article class="overview-card">
                        <span>Alerts</span>
                        <strong>{{ ($budgetAlerts->count() ?? 0) + ($dueBillAlerts->count() ?? 0) + (!empty($summary['warning']) ? 1 : 0) }}</strong>
                        <small>Open Reports for charts, alerts, and statistics</small>
                    </article>
                </div>

                <article class="dashboard-empty">
                    <span class="eyebrow">Overview</span>
                    <h2 style="margin-top:14px;">Each section is now separated inside the dashboard.</h2>
                    <p>Use the sidebar to switch between Planner, History, Goals, and Settings. Open the Reports button when you want charts, alerts, and statistics without making the page too long.</p>
                </article>
            @else
                <div class="dashboard-empty">
                    <span class="eyebrow">Start Here</span>
                    <h2 style="margin-top:14px;">No cycle exists yet.</h2>
                    <p>Create your first income cycle in Planner to unlock transaction tracking, reports, alerts, goals, and the rest of the budget tools.</p>
                </div>
            @endif
        </section>

        <section class="dashboard-panel-section" id="planner">
            <div class="board-forms">
            <article class="board-panel">
                <div class="board-panel-header">
                    <div>
                        <h2>Create Income Cycle</h2>
                        <p>Set the main pay period for reporting and unlock the full dashboard.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('income-cycles.store') }}" class="stack-form compact-form">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ route('dashboard') }}#planner">
                    <label class="field">
                        <span>Income amount</span>
                        <input type="number" step="0.01" name="amount" placeholder="6000.00" required>
                    </label>
                    <label class="field">
                        <span>Start date</span>
                        <input type="date" name="start_date" required>
                    </label>
                    <label class="field">
                        <span>End date</span>
                        <input type="date" name="end_date" required>
                    </label>
                    <button type="submit" class="primary-button full-width">Create Cycle</button>
                </form>
            </article>

            @if($cycle && $summary)
                <article class="board-panel">
                    <div class="board-panel-header">
                        <div>
                            <h2>Add income or expense</h2>
                            <p>Track every entry from one compact form.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('transactions.store') }}" class="stack-form compact-form offline-transaction-form" data-sync-url="{{ route('transactions.sync') }}">
                        @csrf
                        <input type="hidden" name="cycle_id" value="{{ $cycle->id }}">
                        <input type="hidden" name="return_to" value="{{ route('dashboard', ['cycle' => $cycle->id]) }}#history">
                        <label class="field">
                            <span>Entry type</span>
                            <select name="transaction_type" class="transaction-type-select" required>
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </label>
                        <label class="field transaction-category-field">
                            <span>Category</span>
                            <select name="category_id">
                                <option value="">Select category</option>
                                @foreach($allCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="field">
                            <span>Amount</span>
                            <input type="number" step="0.01" name="amount" placeholder="65.20" required>
                        </label>
                        <label class="field">
                            <span>Timestamp</span>
                            <input type="datetime-local" name="timestamp" value="{{ now()->format('Y-m-d\TH:i') }}">
                        </label>
                        <label class="field">
                            <span>Note</span>
                            <textarea name="note" placeholder="Salary, groceries, rent, tuition, fuel."></textarea>
                        </label>
                        <button type="submit" class="primary-button full-width">Save Entry</button>
                    </form>
                </article>
            @else
                <article class="board-panel">
                    <div class="board-panel-header">
                        <div>
                            <h2>Next Step</h2>
                            <p>Create your first cycle first, then income, expenses, charts, and reports will appear here.</p>
                        </div>
                    </div>
                    <div class="compact-list">
                        <div class="compact-row">
                            <strong>1. Create an income cycle</strong>
                            <span>Set amount, start date, and end date.</span>
                        </div>
                        <div class="compact-row">
                            <strong>2. Add categories and entries</strong>
                            <span>Once a cycle exists, transaction forms and tracking tools become available.</span>
                        </div>
                        <div class="compact-row">
                            <strong>3. View reports and alerts</strong>
                            <span>The dashboard overview will unlock automatically after creation.</span>
                        </div>
                    </div>
                </article>
            @endif

            <article class="board-panel">
                <div class="board-panel-header">
                    <div>
                        <h2>Category + Budget Setup</h2>
                        <p>Maintain limits and due dates.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('categories.store') }}" class="stack-form compact-form">
                    @csrf
                    <input type="hidden" name="return_to" value="{{ route('dashboard') }}#planner">
                    <label class="field">
                        <span>Category name</span>
                        <input type="text" name="name" placeholder="Food, Rent, Bills" required>
                    </label>
                    <label class="field">
                        <span>Budget limit</span>
                        <input type="number" step="0.01" name="budget_limit" placeholder="800.00">
                    </label>
                    <label class="field">
                        <span>Bill due day</span>
                        <input type="number" min="1" max="31" name="due_day" placeholder="15">
                    </label>
                    <label class="inline-check">
                        <input type="checkbox" name="is_fixed" value="1">
                        <span>Fixed expense / bill</span>
                    </label>
                    <button type="submit" class="secondary-button full-width">Create Category</button>
                </form>
            </article>
            </div>

        @if($cycle && $summary)
            <article class="board-panel">
                <div class="board-panel-header">
                    <div>
                        <h2>Income Cycle Management</h2>
                        <p>Full CRUD for your budgeting cycles.</p>
                    </div>
                </div>
                <form method="GET" action="{{ route('dashboard') }}" class="history-toolbar" style="margin-bottom:16px;">
                    @if($cycle)
                        <input type="hidden" name="cycle" value="{{ $cycle->id }}">
                    @endif
                    <div class="history-search-row">
                        <label class="field search-input-wrap">
                            <span>Search cycles</span>
                            <input type="text" name="cycles_search" value="{{ request('cycles_search') }}" placeholder="Search by amount or date">
                        </label>
                        <label class="field">
                            <span>Start from</span>
                            <input type="date" name="cycles_date_from" value="{{ request('cycles_date_from') }}">
                        </label>
                        <label class="field">
                            <span>End to</span>
                            <input type="date" name="cycles_date_to" value="{{ request('cycles_date_to') }}">
                        </label>
                        <div class="filter-actions">
                            <button type="submit" class="secondary-button">Apply</button>
                            <a href="{{ route('dashboard', ['cycle' => $cycle?->id]) }}#planner" class="secondary-button">Reset</a>
                        </div>
                    </div>
                    <div class="history-meta">
                            <span>{{ $cycles->count() }} cycle{{ $cycles->count() === 1 ? '' : 's' }} shown out of {{ $allCycles->count() }} total.</span>
                    </div>
                </form>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Amount</th>
                                <th>Transactions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cycles as $item)
                                <tr>
                                    <td>
                                        {{ $item->start_date->format('M d, Y') }} - {{ $item->end_date->format('M d, Y') }}
                                    </td>
                                    <td>{{ number_format($item->amount, 2) }}</td>
                                    <td>{{ $item->transactions_count }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('income-cycles.update', $item) }}" class="inline-edit-grid">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="return_to" value="{{ route('dashboard', ['cycle' => $cycle?->id ?: $item->id]) }}#planner">
                                            <input type="number" step="0.01" name="amount" value="{{ $item->amount }}" required>
                                            <input type="date" name="start_date" value="{{ $item->start_date->format('Y-m-d') }}" required>
                                            <input type="date" name="end_date" value="{{ $item->end_date->format('Y-m-d') }}" required>
                                            <button type="submit" class="table-action">Update</button>
                                        </form>
                                        <form method="POST" action="{{ route('income-cycles.destroy', $item) }}" onsubmit="return confirm('Delete this income cycle?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="return_to" value="{{ route('dashboard', ['cycle' => $cycle?->id]) }}#planner">
                                            <button type="submit" class="danger-button">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No income cycles yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        @endif
        </section>

        <section class="dashboard-panel-section" id="history">
            @if($cycle && $summary)
            <article class="board-panel table-panel">
                <div class="board-panel-header">
                    <div>
                        <h2>Transaction history</h2>
                        <p>Search and filter full income and expense records.</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('dashboard') }}" class="history-toolbar">
                    <input type="hidden" name="cycle" value="{{ $cycle->id }}">

                    <div class="history-search-row">
                        <label class="field search-input-wrap">
                            <span>Search transactions</span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search note, category, or description">
                        </label>
                        <label class="field">
                            <span>Type</span>
                            <select name="transaction_type">
                                <option value="">All</option>
                                <option value="expense" {{ request('transaction_type') === 'expense' ? 'selected' : '' }}>Expense</option>
                                <option value="income" {{ request('transaction_type') === 'income' ? 'selected' : '' }}>Income</option>
                            </select>
                        </label>
                        <label class="field">
                            <span>Category</span>
                            <select name="category_id">
                                <option value="">All</option>
                                @foreach($allCategories as $category)
                                    <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <div class="filter-actions">
                            <button type="submit" class="secondary-button">Apply</button>
                            <a href="{{ route('dashboard', ['cycle' => $cycle->id]) }}#history" class="secondary-button">Reset</a>
                        </div>
                    </div>

                    <div class="filter-grid">
                        <label class="field">
                            <span>From</span>
                            <input type="date" name="date_from" value="{{ request('date_from') }}">
                        </label>
                        <label class="field">
                            <span>To</span>
                            <input type="date" name="date_to" value="{{ request('date_to') }}">
                        </label>
                        <label class="field">
                            <span>Min amount</span>
                            <input type="number" step="0.01" name="amount_min" value="{{ request('amount_min') }}">
                        </label>
                        <label class="field">
                            <span>Max amount</span>
                            <input type="number" step="0.01" name="amount_max" value="{{ request('amount_max') }}">
                        </label>
                    </div>

                    <div class="history-meta">
                        <span>{{ $filteredTransactions->count() }} result{{ $filteredTransactions->count() === 1 ? '' : 's' }} found in this cycle.</span>
                        <div class="filter-summary">
                            @if(request('search'))
                                <span class="filter-chip">Search: {{ request('search') }}</span>
                            @endif
                            @if(request('transaction_type'))
                                <span class="filter-chip">Type: {{ ucfirst(request('transaction_type')) }}</span>
                            @endif
                            @if(request('category_id'))
                                <span class="filter-chip">Category: {{ optional($allCategories->firstWhere('id', (int) request('category_id')))->name ?? 'Selected' }}</span>
                            @endif
                            @if(request('date_from') || request('date_to'))
                                <span class="filter-chip">Date: {{ request('date_from') ?: 'Any' }} to {{ request('date_to') ?: 'Any' }}</span>
                            @endif
                            @if(request('amount_min') || request('amount_max'))
                                <span class="filter-chip">Amount: {{ request('amount_min') ?: '0' }} to {{ request('amount_max') ?: 'Any' }}</span>
                            @endif
                            @if(! request('search') && ! request('transaction_type') && ! request('category_id') && ! request('date_from') && ! request('date_to') && ! request('amount_min') && ! request('amount_max'))
                                <span class="filter-chip">No active filters</span>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Note</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($filteredTransactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->timestamp?->format('M d, Y H:i') }}</td>
                                    <td>{{ ucfirst($transaction->transaction_type) }}</td>
                                    <td>{{ $transaction->category?->name ?? 'General income' }}</td>
                                    <td>{{ number_format($transaction->amount, 2) }}</td>
                                    <td>{{ $transaction->note }}</td>
                                    <td class="action-row">
                                        <a class="table-action" href="{{ route('transactions.edit', $transaction) }}">Edit</a>
                                        <form method="POST" action="{{ route('transactions.destroy', $transaction) }}" onsubmit="return confirm('Delete this transaction?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="danger-button">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No matching transactions found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
            @else
            <article class="dashboard-empty">
                <span class="eyebrow">History</span>
                <h2 style="margin-top:14px;">Transaction history will appear here.</h2>
                <p>Create a cycle and save at least one income or expense entry in Planner to start using filters and full history logs.</p>
            </article>
            @endif
        </section>

        <section class="dashboard-panel-section" id="goals">
            <div class="goals-grid">
                <article class="board-panel">
                    <div class="board-panel-header">
                        <div>
                            <h2>Create Savings Goal</h2>
                            <p>Emergency fund, travel, school fees, and more.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('savings-goals.store') }}" class="stack-form compact-form">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ route('dashboard', ['cycle' => $cycle?->id]) }}#goals">
                        <label class="field">
                            <span>Goal name</span>
                            <input type="text" name="name" placeholder="Emergency fund" required>
                        </label>
                        <label class="field">
                            <span>Target amount</span>
                            <input type="number" step="0.01" min="0.01" inputmode="decimal" name="target_amount" placeholder="{{ $currencySymbol }}1,000.00" required>
                        </label>
                        <label class="field">
                            <span>Current saved</span>
                            <input type="number" step="0.01" min="0" inputmode="decimal" name="current_amount" value="0">
                        </label>
                        <label class="field">
                            <span>Target date</span>
                            <input type="date" name="target_date">
                        </label>
                        <label class="field">
                            <span>Notes</span>
                            <textarea name="notes" placeholder="Anything important about this goal."></textarea>
                        </label>
                        <button type="submit" class="primary-button full-width">Add Goal</button>
                    </form>
                </article>

                <article class="board-panel">
                    <div class="board-panel-header">
                        <div>
                            <h2>Edit Goal</h2>
                            <p>Savings progress is shown first, then you can update the selected goal with clearer fields and safer actions.</p>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('dashboard') }}" class="history-toolbar" style="margin-bottom:16px;">
                        @if($cycle)
                            <input type="hidden" name="cycle" value="{{ $cycle->id }}">
                        @endif
                        <div class="history-search-row">
                            <label class="field search-input-wrap">
                                <span>Search goals</span>
                                <input type="text" name="goals_search" value="{{ request('goals_search') }}" placeholder="Search name or notes">
                            </label>
                            <label class="field">
                                <span>Status</span>
                                <select name="goals_status">
                                    <option value="">All</option>
                                    <option value="active" {{ request('goals_status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="completed" {{ request('goals_status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </label>
                            <div class="filter-actions">
                                <button type="submit" class="secondary-button">Apply</button>
                                <a href="{{ route('dashboard', ['cycle' => $cycle?->id]) }}#goals" class="secondary-button">Reset</a>
                            </div>
                        </div>
                        <div class="history-meta">
                            <span>{{ $savingsGoals->count() }} goal{{ $savingsGoals->count() === 1 ? '' : 's' }} shown out of {{ $allSavingsGoals->count() }} total.</span>
                        </div>
                    </form>
                    @forelse($savingsGoals as $goal)
                        @php
                            $goalProgress = min(($goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0), 100);
                            $goalRemaining = max($goal->target_amount - $goal->current_amount, 0);
                            $daysLeft = $goal->target_date ? now()->startOfDay()->diffInDays($goal->target_date->startOfDay(), false) : null;
                            $deadlineStatus = $goal->target_date
                                ? ($daysLeft < 0
                                    ? 'Past due by '.abs($daysLeft).' day'.(abs($daysLeft) === 1 ? '' : 's')
                                    : ($daysLeft === 0
                                        ? 'Due today'
                                        : $daysLeft.' day'.($daysLeft === 1 ? '' : 's').' left'))
                                : 'No deadline set';
                            $monthsLeft = $goal->target_date
                                ? max(now()->startOfDay()->diffInMonths($goal->target_date->startOfDay(), false), 1)
                                : null;
                            $suggestedMonthly = $goalRemaining > 0
                                ? ($monthsLeft ? $goalRemaining / $monthsLeft : $goalRemaining / 3)
                                : 0;
                            $trackedSince = $goal->created_at?->format('M d, Y h:i A') ?? 'Not available';
                            $lastUpdated = $goal->updated_at?->format('M d, Y h:i A') ?? 'Not available';
                        @endphp
                        <div class="goal-card">
                            <div class="goal-card-header">
                                <div>
                                    <div class="goal-card-title">{{ $goal->name }}</div>
                                    <div class="goal-card-subtitle">{{ $goal->target_date ? 'Target '.$goal->target_date->format('M d, Y') : 'No deadline set' }}</div>
                                </div>
                                <div class="goal-progress-value">
                                    <strong>{{ $currencySymbol }}{{ number_format($goal->current_amount, 2) }} / {{ $currencySymbol }}{{ number_format($goal->target_amount, 2) }}</strong>
                                    <span>{{ number_format($goalProgress, 2) }}% saved</span>
                                </div>
                            </div>

                            <div class="goal-progress-stack">
                                <div class="goal-progress-meta">
                                    <small>{{ $deadlineStatus }}</small>
                                    <small>{{ $goal->is_completed ? 'Completed' : 'In progress' }}</small>
                                </div>
                                <div class="goal-progress-wrap">
                                    <div class="progress-bar {{ $goal->is_completed ? 'is-complete' : '' }}">
                                        <span style="width: {{ $goalProgress }}%"></span>
                                    </div>
                                    <div class="goal-progress-markers" aria-hidden="true">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>
                            </div>

                            <div class="goal-summary-grid">
                                <div class="goal-summary-item">
                                    <span>Remaining</span>
                                    <strong>{{ $currencySymbol }}{{ number_format($goalRemaining, 2) }}</strong>
                                </div>
                                <div class="goal-summary-item">
                                    <span>Saved</span>
                                    <strong>{{ number_format($goalProgress, 2) }}%</strong>
                                </div>
                                <div class="goal-summary-item">
                                    <span>Deadline</span>
                                    <strong>{{ $deadlineStatus }}</strong>
                                </div>
                                <div class="goal-summary-item">
                                    <span>Suggested monthly</span>
                                    <strong>{{ $currencySymbol }}{{ number_format($suggestedMonthly, 2) }}/month</strong>
                                </div>
                            </div>

                            <div class="goal-editor">
                                <div class="goal-editor-header">
                                    <strong>Selected Goal Details</strong>
                                    <span class="goal-editor-status">Changes update the live summary below.</span>
                                </div>

                            <form method="POST"
                                action="{{ route('savings-goals.update', $goal) }}"
                                class="stack-form compact-form goal-editor-grid"
                                data-goal-editor
                                data-currency-symbol="{{ $currencySymbol }}"
                                data-goal-name="{{ $goal->name }}"
                                data-goal-created-at="{{ $goal->created_at?->toIso8601String() }}"
                                data-goal-updated-at="{{ $goal->updated_at?->toIso8601String() }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="return_to" value="{{ route('dashboard', ['cycle' => $cycle?->id]) }}#goals">
                                <input type="hidden" name="name" value="{{ $goal->name }}">

                                <label class="field">
                                    <span>Goal name</span>
                                    <div class="goal-readonly-field">{{ $goal->name }}</div>
                                    <small class="goal-helper-text">Goal name is locked after creation to keep history consistent.</small>
                                </label>

                                <div class="two-field-grid">
                                    <label class="field">
                                        <span>Target amount</span>
                                        <input type="number" step="0.01" min="0.01" inputmode="decimal" name="target_amount" value="{{ number_format($goal->target_amount, 2, '.', '') }}" required>
                                    </label>
                                    <label class="field">
                                        <span>Current saved</span>
                                        <input type="number" step="0.01" min="0" inputmode="decimal" name="current_amount" value="{{ number_format($goal->current_amount, 2, '.', '') }}">
                                    </label>
                                </div>

                                <label class="field">
                                    <span>Target date</span>
                                    <input type="date" name="target_date" value="{{ $goal->target_date?->format('Y-m-d') }}" min="{{ now()->format('Y-m-d') }}">
                                    <small class="goal-helper-text" data-deadline-text>{{ $deadlineStatus }}</small>
                                </label>

                                <div class="goal-timeline-grid">
                                    <div class="goal-timeline-item">
                                        <span>Started tracking</span>
                                        <strong data-goal-created-text>{{ $trackedSince }}</strong>
                                    </div>
                                    <div class="goal-timeline-item">
                                        <span>Last savings update</span>
                                        <strong data-goal-updated-text>{{ $lastUpdated }}</strong>
                                    </div>
                                    <div class="goal-timeline-item">
                                        <span>Time until target</span>
                                        <strong data-goal-live-timer>{{ $deadlineStatus }}</strong>
                                    </div>
                                </div>

                                <label class="field">
                                    <span>Notes</span>
                                    <textarea name="notes">{{ $goal->notes }}</textarea>
                                </label>

                                <div class="goal-warning" data-goal-warning hidden></div>
                                <div class="goal-summary-grid" data-live-summary>
                                    <div class="goal-summary-item">
                                        <span>Remaining</span>
                                        <strong data-summary-remaining>{{ $currencySymbol }}{{ number_format($goalRemaining, 2) }}</strong>
                                    </div>
                                    <div class="goal-summary-item">
                                        <span>Saved</span>
                                        <strong data-summary-saved>{{ number_format($goalProgress, 2) }}%</strong>
                                    </div>
                                    <div class="goal-summary-item">
                                        <span>Status</span>
                                        <strong data-summary-status>{{ $goal->is_completed ? 'Completed' : 'In progress' }}</strong>
                                    </div>
                                    <div class="goal-summary-item">
                                        <span>Suggested monthly</span>
                                        <strong data-summary-monthly>{{ $currencySymbol }}{{ number_format($suggestedMonthly, 2) }}/month</strong>
                                    </div>
                                </div>

                                <div class="goal-actions">
                                    <button type="submit" class="primary-button goal-update-button" data-goal-submit>Update Goal</button>
                                </div>
                            </form>
                            </div>

                            <form method="POST" action="{{ route('savings-goals.destroy', $goal) }}" class="category-delete-form" data-delete-confirm data-delete-label="{{ $goal->name }}" style="margin-top:14px;">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="return_to" value="{{ route('dashboard', ['cycle' => $cycle?->id]) }}#goals">
                                <button type="submit" class="danger-button goal-delete-button">Delete Goal</button>
                            </form>
                        </div>
                    @empty
                        <p class="soft-note">No savings goals yet. Create one to start tracking progress.</p>
                    @endforelse
                </article>
            </div>
        </section>

        <section class="dashboard-panel-section" id="settings">
            <div class="settings-grid">
                <article class="board-panel profile-card">
                    <div class="board-panel-header">
                        <div>
                            <h2>Edit Profile</h2>
                            <p>Update your picture, account details, and password in one place.</p>
                        </div>
                    </div>

                    <div class="profile-layout">
                        <div class="profile-photo-panel">
                            <div class="profile-photo-frame">
                                @if($profilePhotoUrl)
                                    <img src="{{ $profilePhotoUrl }}" alt="{{ $user->name }} profile photo">
                                @else
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                @endif
                            </div>

                            <p class="profile-photo-note">Upload a JPG, PNG, or WEBP profile photo up to 2MB. Your dashboard photo updates automatically after saving.</p>

                            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="stack-form compact-form">
                                @csrf
                                <input type="hidden" name="return_to" value="{{ route('dashboard') }}#settings">
                                <input type="hidden" name="name" value="{{ old('name', $user->name) }}">
                                <input type="hidden" name="email" value="{{ old('email', $user->email) }}">
                                <label class="field">
                                    <span>Profile photo</span>
                                    <input type="file" name="profile_photo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                </label>
                                <div class="profile-photo-actions">
                                    <button type="submit" class="secondary-button full-width">Upload Photo</button>
                                </div>
                            </form>

                            @if($user->profile_photo_path)
                                <form method="POST" action="{{ route('profile.photo.destroy') }}">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="return_to" value="{{ route('dashboard') }}#settings">
                                    <button type="submit" class="danger-button full-width">Remove Photo</button>
                                </form>
                            @endif
                        </div>

                        <div style="display:grid; gap:18px;">
                            <form method="POST" action="{{ route('profile.update') }}" class="stack-form compact-form settings-compact-form">
                                @csrf
                                <input type="hidden" name="return_to" value="{{ route('dashboard') }}#settings">
                                <label class="field">
                                    <span>Full name</span>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                                </label>
                                <label class="field">
                                    <span>Email address</span>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                                </label>
                                <button type="submit" class="secondary-button settings-submit">Save Profile Info</button>
                            </form>

                            <form method="POST" action="{{ route('profile.password.update') }}" class="stack-form compact-form settings-compact-form">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="return_to" value="{{ route('dashboard') }}#settings">
                                <label class="field">
                                    <span>Current password</span>
                                    <div class="password-field">
                                        <input id="profile_current_password" type="password" name="current_password" placeholder="Current password" required>
                                        <button type="button" class="password-toggle" data-password-toggle="profile_current_password" data-visible="false" aria-label="Show password"><span></span></button>
                                    </div>
                                </label>
                                <label class="field">
                                    <span>New password</span>
                                    <div class="password-field">
                                        <input id="profile_new_password" type="password" name="password" placeholder="New password" required>
                                        <button type="button" class="password-toggle" data-password-toggle="profile_new_password" data-visible="false" aria-label="Show password"><span></span></button>
                                    </div>
                                </label>
                                <label class="field">
                                    <span>Confirm new password</span>
                                    <div class="password-field">
                                        <input id="profile_new_password_confirmation" type="password" name="password_confirmation" placeholder="Confirm new password" required>
                                        <button type="button" class="password-toggle" data-password-toggle="profile_new_password_confirmation" data-visible="false" aria-label="Show password"><span></span></button>
                                    </div>
                                </label>
                                <button type="submit" class="primary-button settings-submit">Change Password</button>
                            </form>
                        </div>
                    </div>
                </article>

                <article class="board-panel">
                    <div class="board-panel-header">
                        <div>
                            <h2>Account Budget Settings</h2>
                            <p>Set currency, monthly limits, and savings target.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('dashboard.settings.update') }}" class="stack-form compact-form settings-compact-form">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ route('dashboard') }}#settings">
                        <label class="field">
                            <span>Currency</span>
                            <select name="currency_pref" required>
                                @foreach($supportedCurrencies as $code => $details)
                                    <option value="{{ $code }}" {{ old('currency_pref', $user->currency_pref) === $code ? 'selected' : '' }}>
                                        {{ $code }} - {{ $details['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="field">
                            <span>Savings goal %</span>
                            <input type="number" step="0.01" min="0" max="100" name="savings_goal_percentage" value="{{ old('savings_goal_percentage', $user->savings_goal_percentage) }}" required>
                        </label>
                        <label class="field">
                            <span>Monthly budget limit</span>
                            <input type="number" step="0.01" min="0" name="monthly_budget_limit" value="{{ old('monthly_budget_limit', $user->monthly_budget_limit) }}" placeholder="5000.00">
                        </label>
                        <button type="submit" class="secondary-button settings-submit">Save Settings</button>
                    </form>
                </article>

                <article class="board-panel">
                    <div class="board-panel-header">
                        <div>
                            <h2>Category Settings</h2>
                            <p>Edit budgets, bill days, and category types.</p>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('dashboard') }}" class="history-toolbar" style="margin-bottom:16px;">
                        @if($cycle)
                            <input type="hidden" name="cycle" value="{{ $cycle->id }}">
                        @endif
                        <div class="history-search-row">
                            <label class="field search-input-wrap">
                                <span>Search categories</span>
                                <input type="text" name="categories_search" value="{{ request('categories_search') }}" placeholder="Search category name">
                            </label>
                            <label class="field">
                                <span>Type</span>
                                <select name="categories_type">
                                    <option value="">All</option>
                                    <option value="fixed" {{ request('categories_type') === 'fixed' ? 'selected' : '' }}>Fixed</option>
                                    <option value="variable" {{ request('categories_type') === 'variable' ? 'selected' : '' }}>Variable</option>
                                </select>
                            </label>
                            <div class="filter-actions">
                                <button type="submit" class="secondary-button">Apply</button>
                                <a href="{{ route('dashboard', ['cycle' => $cycle?->id]) }}#settings" class="secondary-button">Reset</a>
                            </div>
                        </div>
                        <div class="history-meta">
                            <span>{{ $categories->count() }} categor{{ $categories->count() === 1 ? 'y' : 'ies' }} shown out of {{ $allCategories->count() }} total.</span>
                        </div>
                    </form>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Budget</th>
                                    <th>Due</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $category->name }}<br><small>{{ $category->is_fixed ? 'Fixed' : 'Variable' }}</small></td>
                                        <td>{{ $category->budget_limit ? number_format($category->budget_limit, 2) : 'Not set' }}</td>
                                        <td>{{ $category->due_day ?: 'N/A' }}</td>
                                        <td class="category-action-cell">
                                            <form method="POST" action="{{ route('categories.update', $category) }}" class="inline-edit-grid category-inline-edit">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="return_to" value="{{ route('dashboard', ['cycle' => $cycle?->id]) }}#settings">
                                                <input type="text" name="name" value="{{ $category->name }}" required>
                                                <input type="number" step="0.01" name="budget_limit" value="{{ $category->budget_limit }}">
                                                <input type="number" min="1" max="31" name="due_day" value="{{ $category->due_day }}">
                                                <label class="inline-check">
                                                    <input type="checkbox" name="is_fixed" value="1" {{ $category->is_fixed ? 'checked' : '' }}>
                                                    <span>Fixed</span>
                                                </label>
                                                <button type="submit" class="table-action">Update</button>
                                            </form>
                                            <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Delete this category?');" class="category-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="return_to" value="{{ route('dashboard', ['cycle' => $cycle?->id]) }}#settings">
                                                <button type="submit" class="danger-button">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No categories yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>
        </section>
    </section>
</div>

@if($cycle && $summary)
    <div class="insights-backdrop" data-insights-backdrop></div>
    <aside class="insights-drawer" data-insights-drawer aria-hidden="true">
        <div class="insights-drawer-header">
            <div>
                <h2>Statistics and Reports</h2>
                <p>Open this full-screen panel anytime to review totals, charts, alerts, and savings performance.</p>
            </div>
            <button type="button" class="insights-drawer-close" aria-label="Close reports panel" data-insights-close>&times;</button>
        </div>

        <div class="insights-drawer-body">
            <div class="insights-summary">
                <div>
                    <strong>Reports are now in one full-screen panel.</strong>
                    <span>Review totals, charts, alerts, and savings progress here without crowding the main dashboard.</span>
                </div>
                <span class="insights-summary-badge">Live cycle report</span>
            </div>

            <div class="dashboard-kpi-grid">
                <article class="dashboard-kpi primary">
                    <span>Earnings</span>
                    <strong>{{ $currency }} {{ number_format($summary['totalIncome'], 0) }}</strong>
                    <small>Cycle + extra income</small>
                </article>
                <article class="dashboard-kpi">
                    <span>Expenses</span>
                    <strong>{{ number_format($summary['totalExpenses'], 0) }}</strong>
                    <small>{{ number_format($summary['spendProgress'], 2) }}% of budget</small>
                </article>
                <article class="dashboard-kpi">
                    <span>Balance</span>
                    <strong>{{ number_format($summary['remainingBalance'], 0) }}</strong>
                    <small>Remaining after spending</small>
                </article>
                <article class="dashboard-kpi">
                    <span>Savings</span>
                    <strong>{{ $summary['savingsProgress'] !== null ? number_format(min($summary['savingsProgress'], 100), 0) : 0 }}%</strong>
                    <small>Goal progress</small>
                </article>
            </div>

            <div class="insights-grid">
                <div class="insights-stack">
                    <article class="reports-panel">
                        <div class="board-panel-header">
                            <div>
                                <h2>Budget vs Actual</h2>
                                <p>Monthly performance across your live categories.</p>
                            </div>
                            <span class="mini-action">Check Now</span>
                        </div>
                        <canvas id="budgetActualChart"
                            data-chart-type="bar"
                            data-labels='@json($summary["budgetVsActualLabels"])'
                            data-budget='@json($summary["budgetVsActualBudget"])'
                            data-spent='@json($summary["budgetVsActualSpent"])'></canvas>
                    </article>

                    <div class="insights-grid" style="grid-template-columns: minmax(0, 1.1fr) minmax(300px, 0.9fr);">
                        <article class="reports-panel">
                            <div class="board-panel-header">
                                <div>
                                    <h2>Income vs Expenses Trend</h2>
                                    <p>Daily movement inside the selected cycle.</p>
                                </div>
                            </div>
                            <canvas id="trendChart"
                                data-chart-type="line"
                                data-labels='@json($summary["trendLabels"])'
                                data-income='@json($summary["trendIncome"])'
                                data-expenses='@json($summary["trendExpenses"])'></canvas>
                        </article>

                        <article class="reports-panel">
                            <div class="board-panel-header">
                                <div>
                                    <h2>Quick Alerts</h2>
                                    <p>Overspending and bill reminders.</p>
                                </div>
                            </div>
                            <div class="alert-list">
                                @if($summary['warning'])
                                    <div class="alert-row notification-source" data-notification-title="Budget Warning" data-notification-body="{{ $summary['warning'] }}">
                                        <strong>Burn rate alert</strong>
                                        <span>{{ $summary['warning'] }}</span>
                                    </div>
                                @endif

                                @forelse($budgetAlerts->take(2) as $alert)
                                    <div class="alert-row notification-source" data-notification-title="Overspending Alert" data-notification-body="{{ $alert['name'] }} is {{ $alert['used_percentage'] }}% used.">
                                        <strong>{{ $alert['name'] }}</strong>
                                        <span>{{ $alert['used_percentage'] }}% used with {{ number_format($alert['remaining'], 2) }} left.</span>
                                    </div>
                                @empty
                                    @if($dueBillAlerts->isEmpty() && ! $summary['warning'])
                                        <div class="alert-row">
                                            <strong>No urgent alerts</strong>
                                            <span>Your current cycle is within expected range.</span>
                                        </div>
                                    @endif
                                @endforelse

                                @foreach($dueBillAlerts->take(2) as $alert)
                                    <div class="alert-row notification-source" data-notification-title="Bill Due Reminder" data-notification-body="{{ $alert['name'] }} is due in {{ $alert['days_until_due'] }} day(s).">
                                        <strong>{{ $alert['name'] }}</strong>
                                        <span>Due in {{ $alert['days_until_due'] }} day{{ $alert['days_until_due'] === 1 ? '' : 's' }}.</span>
                                    </div>
                                @endforeach
                            </div>
                        </article>
                    </div>
                </div>

                <div class="insights-side-stack">
                    <article class="reports-panel">
                        <div class="dashboard-ring-wrap">
                            <div>
                                <h2 style="margin:0;color:#1e293b;">Savings Rating</h2>
                                <p style="margin:6px 0 0;color:rgba(30,41,59,0.62);">Current cycle target progress</p>
                            </div>
                            <canvas id="categoryChart"
                                data-chart-type="doughnut"
                                data-labels='@json(["Saved", "Remaining"])'
                                data-values='@json($savingsRingValues)'></canvas>
                            <strong style="font-size:2rem;color:#1e293b;">{{ $summary['savingsProgress'] !== null ? number_format(min($summary['savingsProgress'], 100), 0) : 0 }}%</strong>
                            <div class="compact-list" style="width:100%;">
                                <div class="compact-row">
                                    <strong>Monthly limit</strong>
                                    <span>{{ $summary['monthlyLimit'] ? number_format($summary['monthlyLimit'], 2) : 'Not set' }}</span>
                                </div>
                                <div class="compact-row">
                                    <strong>Used</strong>
                                    <span>{{ $summary['monthlyBudgetUsed'] !== null ? number_format($summary['monthlyBudgetUsed'], 2).'%' : 'N/A' }}</span>
                                </div>
                                <div class="compact-row">
                                    <strong>Burn rate</strong>
                                    <span>{{ number_format($summary['burnRate'], 2) }} / day</span>
                                </div>
                            </div>
                            <a href="#settings" class="mini-action" data-insights-close>Check Now</a>
                        </div>
                    </article>

                    <article class="reports-panel">
                        <div>
                            <div class="board-panel-header" style="margin-bottom:0;">
                                <div>
                                    <h2>Report Notes</h2>
                                    <p>Quick reading guide for the values in this panel.</p>
                                </div>
                            </div>
                            <div class="compact-list" style="margin-top:18px;">
                                <div class="compact-row">
                                    <strong>Earnings</strong>
                                    <span>Total planned income plus extra income entries inside the selected cycle.</span>
                                </div>
                                <div class="compact-row">
                                    <strong>Expenses</strong>
                                    <span>All tracked expense entries compared against your current budget setup.</span>
                                </div>
                                <div class="compact-row">
                                    <strong>Savings rating</strong>
                                    <span>Progress toward the savings target based on your current income cycle and settings.</span>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </aside>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dashboardPanelStorageKey = 'budget.activeDashboardPanel';
        const navLinks = [...document.querySelectorAll('.dashboard-nav-link')];
        const panelSections = [...document.querySelectorAll('.dashboard-panel-section')];
        const insightsDrawer = document.querySelector('[data-insights-drawer]');
        const insightsBackdrop = document.querySelector('[data-insights-backdrop]');
        const insightsOpenButtons = [...document.querySelectorAll('[data-insights-open]')];
        const insightsCloseButtons = [...document.querySelectorAll('[data-insights-close]')];

        const activatePanel = (targetId, updateHash = true) => {
            const targetPanel = document.getElementById(targetId);

            if (!targetPanel) {
                return;
            }

            panelSections.forEach((section) => {
                section.classList.toggle('is-active', section.id === targetId);
            });

            navLinks.forEach((link) => {
                link.classList.toggle('active', link.dataset.target === targetId);
            });

            try {
                window.sessionStorage.setItem(dashboardPanelStorageKey, targetId);
            } catch (error) {
                console.debug('Unable to store active dashboard panel.', error);
            }

            if (updateHash) {
                window.history.replaceState(null, '', `#${targetId}`);
            }
        };

        const setInsightsState = (isOpen) => {
            if (!insightsDrawer || !insightsBackdrop) {
                return;
            }

            insightsDrawer.classList.toggle('is-open', isOpen);
            insightsBackdrop.classList.toggle('is-open', isOpen);
            insightsDrawer.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            document.body.style.overflow = isOpen ? 'hidden' : '';
        };

        navLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                const targetId = link.dataset.target;

                if (!targetId) {
                    return;
                }

                event.preventDefault();
                activatePanel(targetId);
            });
        });

        insightsOpenButtons.forEach((button) => {
            button.addEventListener('click', () => setInsightsState(true));
        });

        insightsCloseButtons.forEach((button) => {
            button.addEventListener('click', () => setInsightsState(false));
        });

        if (insightsBackdrop) {
            insightsBackdrop.addEventListener('click', () => setInsightsState(false));
        }

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setInsightsState(false);
            }
        });

        if (!panelSections.length) {
            return;
        }

        let storedTarget = '';
        try {
            storedTarget = window.sessionStorage.getItem(dashboardPanelStorageKey) || '';
        } catch (error) {
            storedTarget = '';
        }

        const initialTarget = window.location.hash.replace('#', '') || storedTarget || 'overview';
        activatePanel(initialTarget, false);

        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.passwordToggle);

                if (! target) {
                    return;
                }

                const isHidden = target.type === 'password';
                target.type = isHidden ? 'text' : 'password';
                button.dataset.visible = isHidden ? 'true' : 'false';
                button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            });
        });
    });
</script>
@endpush
