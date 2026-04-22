@extends('layouts.marketing')

@section('title', 'Forgot Password | '.config('app.name', 'Budget App'))

@section('content')
<section class="showcase-layout">
    <div class="showcase-copy">
        <span class="showcase-badge">Reset Access</span>
        <h1>Verify your account to change your password.</h1>
        <p>
            Enter your username and valid email. If they match a real account in your database, the system will send
            you straight to the password change screen.
        </p>

        <form method="POST" action="{{ route('password.email') }}" class="showcase-form">
            @csrf

            <label class="field">
                <span>Username</span>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Username" required>
            </label>

            <label class="field">
                <span>Valid Email</span>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Valid email" required>
            </label>

            <button type="submit" class="primary-button">Continue to Reset Password</button>
            <a href="{{ route('login') }}" class="secondary-button">Back to Login</a>
        </form>
    </div>

    <div class="showcase-visual">
        <div class="illustration-stage">
            <div class="float-dot tiny" style="top: 64px; left: 126px;"></div>
            <div class="ring" style="top: 34px; right: 132px;"></div>
            <div class="plus-shape" style="top: 198px; left: 14px;"></div>
            <div class="float-dot small" style="top: 426px; right: 20px;"></div>
            <div class="plus-shape" style="bottom: 104px; left: 246px;"></div>

            <div class="device-laptop"></div>
            <div class="camera-bar"></div>
            <div class="device-phone"></div>
            <div class="card-graphic"></div>
            <div class="coin"></div>
            <div class="cash"></div>
            <div class="wallet"></div>

            <div class="detail-grid">
                <article class="detail-card">
                    <strong>Username check</strong>
                    <p>The reset flow confirms the username belongs to the same account as the email address entered.</p>
                </article>
                <article class="detail-card">
                    <strong>Database validation</strong>
                    <p>If the record does not exist, the form stays here and shows a clear validation message.</p>
                </article>
                <article class="detail-card">
                    <strong>Direct reset path</strong>
                    <p>A valid match takes you directly to the change-password screen without manual link copying.</p>
                </article>
                <article class="detail-card">
                    <strong>Same visual flow</strong>
                    <p>Password recovery now feels like part of the same banking-style product experience.</p>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
