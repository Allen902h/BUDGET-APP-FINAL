@extends('layouts.marketing')

@section('title', 'Reset Password | '.config('app.name', 'Budget App'))

@section('content')
<section class="showcase-layout">
    <div class="showcase-copy">
        <span class="showcase-badge">Set New Password</span>
        <h1>Choose a fresh password and secure your workspace again.</h1>
        <p>
            Confirm the username and valid email tied to this reset request, then choose a new password with letters
            and numbers so your account stays protected.
        </p>

        <form method="POST" action="{{ route('password.update') }}" class="showcase-form stack-form" style="width:min(100%, 420px);">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label class="field">
                <span>Username</span>
                <input type="text" name="name" value="{{ old('name', $name) }}" placeholder="Username" required>
            </label>

            <label class="field">
                <span>Valid Email</span>
                <input type="email" name="email" value="{{ old('email', $email) }}" placeholder="Valid email" required>
            </label>

            <div class="form-grid-two">
                <label class="field">
                    <span>New Password</span>
                    <input id="reset_password" type="password" name="password" placeholder="New password" required>
                </label>

                <label class="field">
                    <span>Confirm Password</span>
                    <input id="reset_password_confirmation" type="password" name="password_confirmation" placeholder="Confirm password" required>
                </label>
            </div>

            <div class="auth-actions">
                <button type="submit" class="primary-button">Reset Password</button>
                <a href="{{ route('login') }}" class="secondary-button">Back to Login</a>
            </div>
        </form>
    </div>

    <div class="showcase-visual">
        <div class="illustration-stage">
            <div class="float-dot tiny" style="top: 52px; left: 134px;"></div>
            <div class="ring" style="top: 38px; right: 134px;"></div>
            <div class="plus-shape" style="top: 208px; left: 12px;"></div>
            <div class="float-dot small" style="top: 426px; right: 16px;"></div>
            <div class="plus-shape" style="bottom: 98px; left: 238px;"></div>

            <div class="device-laptop"></div>
            <div class="camera-bar"></div>
            <div class="device-phone"></div>
            <div class="card-graphic"></div>
            <div class="coin"></div>
            <div class="cash"></div>
            <div class="wallet"></div>

            <div class="detail-grid">
                <article class="detail-card">
                    <strong>Verified account path</strong>
                    <p>The reset screen keeps the username and email tied to the current password token flow.</p>
                </article>
                <article class="detail-card">
                    <strong>Safer password rules</strong>
                    <p>Your new password must include letters and numbers before the app will accept it.</p>
                </article>
                <article class="detail-card">
                    <strong>Fast return to login</strong>
                    <p>Once reset is complete, the app sends you back to sign in with your updated credentials.</p>
                </article>
                <article class="detail-card">
                    <strong>Consistent design</strong>
                    <p>The final step now matches the same warm, polished product theme as the other public pages.</p>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
