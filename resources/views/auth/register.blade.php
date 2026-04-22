@extends('layouts.marketing')

@section('title', 'Register | '.config('app.name', 'Budget App'))

@section('content')
@php
    $supportedCurrencies = config('currencies.supported', []);
    $defaultCurrency = config('currencies.default', 'USD');
@endphp
<section class="showcase-layout">
    <div class="showcase-copy">
        <span class="showcase-badge">Create Your Workspace</span>
        <h1>Build your budget system around your real pay cycle.</h1>
        <p>
            Create an account, set your preferred currency, define your savings target, and start with a cleaner
            banking-style workspace that is ready for cycles, categories, and transaction tracking.
        </p>

        <form method="POST" action="{{ route('register.store') }}" class="showcase-form stack-form" style="width:min(100%, 420px);">
            @csrf

            <label class="field">
                <span>Name</span>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Your name" required>
            </label>

            <label class="field">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Valid email" required>
            </label>

            <div class="form-grid-two">
                <label class="field">
                    <span>Currency</span>
                    <select name="currency_pref">
                        @foreach($supportedCurrencies as $code => $details)
                            <option value="{{ $code }}" {{ old('currency_pref', $defaultCurrency) === $code ? 'selected' : '' }}>
                                {{ $code }} - {{ $details['label'] }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label class="field">
                    <span>Savings Goal %</span>
                    <input type="number" step="0.01" min="0" max="100" name="savings_goal_percentage" value="{{ old('savings_goal_percentage', 20) }}">
                </label>
            </div>

            <div class="form-grid-two">
                <label class="field">
                    <span>Password</span>
                    <div class="password-field">
                        <input id="register_password" type="password" name="password" placeholder="Password" required>
                        <button type="button" class="password-toggle" data-password-toggle="register_password" data-visible="false" aria-label="Show password"><span></span></button>
                    </div>
                </label>

                <label class="field">
                    <span>Confirm Password</span>
                    <div class="password-field">
                        <input id="register_password_confirmation" type="password" name="password_confirmation" placeholder="Confirm password" required>
                        <button type="button" class="password-toggle" data-password-toggle="register_password_confirmation" data-visible="false" aria-label="Show password"><span></span></button>
                    </div>
                </label>
            </div>

            <div class="auth-actions">
                <button type="submit" class="primary-button">Create Account</button>
                <a href="{{ route('login') }}" class="secondary-button">Back to Login</a>
            </div>
        </form>
    </div>

    <div class="showcase-visual">
        <div class="illustration-stage">
            <div class="float-dot tiny" style="top: 68px; left: 110px;"></div>
            <div class="ring" style="top: 34px; right: 136px;"></div>
            <div class="plus-shape" style="top: 220px; left: 10px;"></div>
            <div class="float-dot small" style="top: 420px; right: 30px;"></div>
            <div class="plus-shape" style="bottom: 86px; left: 216px;"></div>

            <div class="device-laptop"></div>
            <div class="camera-bar"></div>
            <div class="device-phone"></div>
            <div class="card-graphic"></div>
            <div class="coin"></div>
            <div class="cash"></div>
            <div class="wallet"></div>

            <div class="detail-grid">
                <article class="detail-card">
                    <strong>Starter setup</strong>
                    <p>Your account gets prepared for categories, cycles, and a more organized first budgeting run.</p>
                </article>
                <article class="detail-card">
                    <strong>Savings target</strong>
                    <p>Set the percentage you want the dashboard to use as your personal goal marker.</p>
                </article>
                <article class="detail-card">
                    <strong>Cycle-ready planner</strong>
                    <p>Once registered, you can create a pay period immediately and start recording expenses.</p>
                </article>
                <article class="detail-card">
                    <strong>Professional theme</strong>
                    <p>The account flow now matches the same branded visual direction as the welcome screen.</p>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
