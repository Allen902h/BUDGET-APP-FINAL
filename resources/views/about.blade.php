@extends('layouts.marketing')

@section('title', 'About | '.config('app.name', 'Budget App'))

@section('content')
<section class="showcase-layout">
    <div class="showcase-copy">
        <span class="showcase-badge">About</span>
        <h1>Bank-style budgeting built around your real pay cycle.</h1>
        <p>
            This system is designed for bi-weekly income planning, not vague month-only tracking. It helps you
            create cycles, organize categories, capture expenses, and watch projected savings from one clean workspace.
        </p>

        <div class="showcase-actions">
            <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="primary-link">
                {{ auth()->check() ? 'Open Dashboard' : 'Create Account' }}
            </a>
            <a href="{{ route('faq') }}" class="alt-link">View FAQ</a>
        </div>
    </div>

    <div class="showcase-visual">
        <div class="illustration-stage">
            <div class="float-dot tiny" style="top: 54px; left: 150px;"></div>
            <div class="ring" style="top: 28px; right: 132px;"></div>
            <div class="plus-shape" style="top: 178px; left: 18px;"></div>
            <div class="float-dot tiny" style="top: 388px; left: 20px;"></div>
            <div class="plus-shape" style="bottom: 84px; left: 236px;"></div>
            <div class="float-dot tiny" style="right: 50px; bottom: 50px;"></div>

            <div class="device-laptop"></div>
            <div class="camera-bar"></div>
            <div class="device-phone"></div>
            <div class="card-graphic"></div>
            <div class="coin"></div>
            <div class="cash"></div>
            <div class="wallet"></div>

            <div class="detail-grid">
                <article class="detail-card">
                    <strong>Cycle-first structure</strong>
                    <p>Income cycles, categories, and transactions follow how you actually get paid.</p>
                </article>
                <article class="detail-card">
                    <strong>Smart planning</strong>
                    <p>Projected savings and burn-rate checks help you react before spending gets away from you.</p>
                </article>
                <article class="detail-card">
                    <strong>Import-ready workflow</strong>
                    <p>CSV tools and category controls stay inside the same branded experience.</p>
                </article>
                <article class="detail-card">
                    <strong>Mobile-friendly use</strong>
                    <p>The app supports installable PWA behavior and a cleaner phone-first budgeting routine.</p>
                </article>
            </div>
        </div>
    </div>
</section>
@endsection
