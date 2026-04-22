@extends('layouts.marketing')

@section('title', 'Contacts | '.config('app.name', 'Budget App'))

@section('content')
<section class="showcase-layout">
    <div class="showcase-copy">
        <span class="showcase-badge">Contacts</span>
        <h1>Reach your budget workspace fast and keep your flow moving.</h1>
        <p>
            This local system is centered on direct access to your planner, dashboard, categories, and downloadable reports.
            Sign in, continue a cycle, or create a fresh account if you want a clean budgeting workspace.
        </p>

        <div class="showcase-actions">
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="primary-link">
                {{ auth()->check() ? 'Go to Dashboard' : 'Sign In' }}
            </a>
            <a href="{{ auth()->check() ? route('dashboard.report.excel') : route('register') }}" class="alt-link">
                {{ auth()->check() ? 'Download Excel Report' : 'Create Account' }}
            </a>
        </div>

        <p class="auth-note">
            Local support path: if you forget your password, the system lets you verify your username and valid email
            before going directly to the reset screen.
        </p>
    </div>

    <div class="showcase-visual">
        <div class="illustration-stage">
            <div class="float-dot tiny" style="top: 82px; left: 120px;"></div>
            <div class="ring" style="top: 42px; right: 178px;"></div>
            <div class="plus-shape" style="top: 242px; left: 8px;"></div>
            <div class="float-dot small" style="top: 428px; right: 20px;"></div>
            <div class="plus-shape" style="bottom: 96px; left: 198px;"></div>

            <div class="device-laptop"></div>
            <div class="camera-bar"></div>
            <div class="device-phone"></div>
            <div class="card-graphic"></div>
            <div class="coin"></div>
            <div class="cash"></div>
            <div class="wallet"></div>

            <div class="detail-grid">
                <article class="detail-card">
                    <strong>Workspace access</strong>
                    <p>Use your existing account to return to the planner, category manager, and transaction tables.</p>
                </article>
                <article class="detail-card">
                    <strong>Account setup</strong>
                    <p>Create a new local account and the app will prepare starter categories for your first cycle.</p>
                </article>
                <article class="detail-card">
                    <strong>Password recovery</strong>
                    <p>Forgot-password verification checks both username and email before allowing a reset.</p>
                </article>
                <article class="detail-card">
                    <strong>Consistent local routes</strong>
                    <p>All public entry pages follow the same branding and connect back into the main app flow.</p>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
