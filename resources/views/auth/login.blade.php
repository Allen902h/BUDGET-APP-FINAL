@extends('layouts.marketing')

@section('title', 'Login | '.config('app.name', 'Budget App'))

@section('content')
<section class="showcase-layout">
    <div class="showcase-copy">
        <span class="showcase-badge">Welcome Back</span>
        <h1>Return to your online budget workspace.</h1>
        <p>
            Sign in to continue your current pay cycle, review spending, import statements, and keep your category
            limits under control from the same dashboard.
        </p>

        <form method="POST" action="{{ route('login.attempt') }}" class="showcase-form">
            @csrf

            <label class="field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
            </label>

            <label class="field">
                <span>Password</span>
                <input id="login_password" class="native-password-field" type="password" name="password" placeholder="Password" required>
            </label>

            <label class="inline-check">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span>Remember me</span>
            </label>

            <div class="inline-links">
                <a href="{{ route('password.request') }}">Forgot Password?</a>
            </div>

            <div class="auth-actions">
                <button type="submit" class="primary-button">Login</button>
                <a href="{{ route('register') }}" class="secondary-button">Create account</a>
            </div>
        </form>
    </div>

    <div class="showcase-visual">
        <div class="illustration-stage">
            <div class="float-dot tiny" style="top: 56px; left: 124px;"></div>
            <div class="ring" style="top: 24px; right: 138px;"></div>
            <div class="plus-shape" style="top: 202px; left: 16px;"></div>
            <div class="float-dot small" style="top: 238px; right: -2px;"></div>
            <div class="plus-shape" style="bottom: 102px; left: 244px;"></div>

            <div class="device-laptop"></div>
            <div class="camera-bar"></div>
            <div class="device-phone"></div>
            <div class="card-graphic"></div>
            <div class="coin"></div>
            <div class="cash"></div>
            <div class="wallet"></div>

            <div class="detail-grid">
                <article class="detail-card">
                    <strong>Cycle dashboard</strong>
                    <p>Open projected savings, burn-rate warnings, and active cycle summaries after sign-in.</p>
                </article>
                <article class="detail-card">
                    <strong>Category control</strong>
                    <p>Keep fixed and variable budgets organized from the same planner workspace.</p>
                </article>
                <article class="detail-card">
                    <strong>Excel reports</strong>
                    <p>Download polished reports quickly while keeping your spending history organized in one place.</p>
                </article>
                <article class="detail-card">
                    <strong>Offline-ready access</strong>
                    <p>The app supports installable PWA behavior for smoother mobile expense logging.</p>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
