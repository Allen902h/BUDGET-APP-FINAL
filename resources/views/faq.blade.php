@extends('layouts.marketing')

@section('title', 'FAQ | '.config('app.name', 'Budget App'))

@section('content')
<section class="showcase-layout">
    <div class="showcase-copy">
        <span class="showcase-badge">FAQ</span>
        <h1>Useful answers for how this budget system works day to day.</h1>
        <p>
            The app is built to answer practical questions quickly: how to budget by cycle, how imports work,
            how mobile access behaves, and how to stay ahead of overspending before the cycle ends.
        </p>

        <div class="showcase-actions">
            <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="primary-link">
                {{ auth()->check() ? 'Review My Budget' : 'Create Account' }}
            </a>
            <a href="{{ route('about') }}" class="alt-link">Learn More</a>
        </div>
    </div>

    <div class="showcase-visual">
        <div class="illustration-stage">
            <div class="float-dot tiny" style="top: 68px; left: 108px;"></div>
            <div class="ring" style="top: 18px; right: 148px;"></div>
            <div class="float-dot small" style="top: 214px; right: 8px;"></div>
            <div class="plus-shape" style="top: 180px; left: 26px;"></div>
            <div class="plus-shape" style="bottom: 112px; left: 220px;"></div>
            <div class="float-dot tiny" style="right: 36px; bottom: 48px;"></div>

            <div class="device-laptop"></div>
            <div class="camera-bar"></div>
            <div class="device-phone"></div>
            <div class="card-graphic"></div>
            <div class="coin"></div>
            <div class="cash"></div>
            <div class="wallet"></div>

            <div class="detail-grid">
                <article class="detail-card">
                    <strong>How does budgeting work?</strong>
                    <p>The app uses income cycles with start and end dates so spending is measured per pay period.</p>
                </article>
                <article class="detail-card">
                    <strong>Can I import bank data?</strong>
                    <p>Yes. CSV import lets you bring statement data into the app and organize it by category.</p>
                </article>
                <article class="detail-card">
                    <strong>Will it work on mobile?</strong>
                    <p>Yes. The PWA setup supports install prompts and smoother expense logging on phones.</p>
                </article>
                <article class="detail-card">
                    <strong>Does it warn me early?</strong>
                    <p>Yes. Burn-rate checks and category pacing highlight risk before your cycle runs out.</p>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
