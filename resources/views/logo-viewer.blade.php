@extends('layouts.marketing')

@section('title', 'Logo | KUYA ALLEN TECH SOLUTIONS')

@section('content')
@php
    $brandName = 'KUYA ALLEN TECH SOLUTIONS';
    $brandLogoFile = file_exists(public_path('images/kuya-allen-logo.png'))
        ? 'images/kuya-allen-logo.png'
        : (file_exists(public_path('images/wowlogo.png')) ? 'images/wowlogo.png' : 'icons/icon.svg');
    $brandLogo = asset($brandLogoFile).'?v='.(file_exists(public_path($brandLogoFile)) ? filemtime(public_path($brandLogoFile)) : time());
@endphp

<section class="showcase-layout">
    <div class="showcase-copy">
        <span class="showcase-badge">Brand Logo</span>
        <h1>{{ $brandName }}</h1>
        <p>
            This is the full logo preview for your system. It stays inside the same visual family as the welcome page,
            so opening the logo still feels like part of the site instead of a separate screen.
        </p>

        <div class="showcase-actions">
            <a href="{{ route('welcome') }}" class="primary-link">Back to Welcome Page</a>
            <a href="{{ route('about') }}" class="alt-link">About Page</a>
        </div>
    </div>

    <div class="showcase-visual">
        <div class="media-frame">
            <img src="{{ $brandLogo }}" alt="{{ $brandName }} logo">
        </div>
    </div>
</section>
@endsection
