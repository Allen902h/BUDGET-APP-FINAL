@extends('layouts.marketing')

@section('title', config('app.name', 'Budget App'))

@section('content')
<style>
    .welcome-layout {
        position: relative;
        display: grid;
        grid-template-columns: minmax(320px, 0.84fr) minmax(420px, 1.16fr);
        align-items: center;
        gap: 18px;
        padding: 28px 54px 58px;
    }

    .welcome-layout::before {
        content: "";
        position: absolute;
        inset: -140px 24% -120px -90px;
        background: linear-gradient(135deg, rgba(255, 192, 94, 0.24), rgba(255, 255, 255, 0));
        transform: rotate(42deg);
        pointer-events: none;
    }

    .left {
        position: relative;
        z-index: 1;
        max-width: 420px;
        padding: 18px 0 12px;
    }

    .left h1 {
        margin: 0 0 16px;
        font-size: clamp(3.2rem, 6vw, 5rem);
        line-height: 0.9;
        letter-spacing: -0.06em;
        text-shadow: 0 8px 18px rgba(70, 36, 102, 0.14);
    }

    .left p {
        margin: 0 0 18px;
        max-width: 390px;
        color: rgba(255, 245, 240, 0.92);
        font-size: 1.02rem;
        line-height: 1.75;
    }

    .login-card {
        display: grid;
        gap: 14px;
        max-width: 340px;
    }

    .login-card input[type="email"],
    .login-card input[type="password"],
    .login-card input[type="text"],
    .login-card input[readonly] {
        min-height: 56px;
        padding: 0 18px;
        border: none;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.96);
        color: var(--ink);
        box-shadow: 0 16px 30px rgba(112, 39, 17, 0.08);
    }

    .login-card input::placeholder {
        color: rgba(47, 36, 88, 0.38);
    }

    .welcome-remember {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 2px;
        color: rgba(255, 245, 240, 0.95);
        font-size: 0.98rem;
    }

    .welcome-remember input {
        width: 18px;
        height: 18px;
        accent-color: #f7ba33;
    }

    .welcome-links {
        margin-top: 4px;
    }

    .welcome-links a {
        color: rgba(255, 245, 240, 0.98);
        font-size: 0.98rem;
        text-decoration: underline;
        text-underline-offset: 5px;
    }

    .login-card button,
    .login-card .primary-link {
        width: fit-content;
        min-width: 148px;
        min-height: 56px;
        margin-top: 12px;
        padding: 0 28px;
        border-radius: 999px;
        border: 4px solid var(--line);
        background: linear-gradient(180deg, #ffcf57, #ffbd3d);
        color: var(--ink);
        font-size: 1.08rem;
        font-weight: 900;
        box-shadow: 0 14px 24px rgba(72, 40, 108, 0.14);
    }

    .helper-links {
        margin-top: 10px;
    }

    .helper-links .alt-link {
        min-width: 176px;
        min-height: 56px;
        border-radius: 999px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.05);
        color: white;
        font-size: 1.02rem;
        font-weight: 800;
        backdrop-filter: blur(8px);
    }

    .right {
        position: relative;
        min-height: 620px;
    }

    .art-stage {
        position: relative;
        width: 100%;
        height: 100%;
        min-height: 620px;
    }

    .art-stage::before {
        content: "";
        position: absolute;
        top: -92px;
        right: 120px;
        width: 380px;
        height: 380px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0));
        transform: rotate(45deg);
        pointer-events: none;
    }

    .float-dot,
    .ring,
    .plus-shape {
        position: absolute;
        z-index: 0;
    }

    .float-dot {
        width: 14px;
        height: 14px;
        border: 4px solid var(--line);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.96);
    }

    .ring {
        width: 24px;
        height: 24px;
        border: 4px solid var(--line);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.96);
    }

    .plus-shape {
        width: 38px;
        height: 38px;
    }

    .plus-shape::before,
    .plus-shape::after {
        content: "";
        position: absolute;
        inset: 0;
        margin: auto;
        border-radius: 999px;
        border: 4px solid var(--line);
        background: rgba(255, 255, 255, 0.96);
    }

    .plus-shape::before {
        width: 38px;
        height: 10px;
    }

    .plus-shape::after {
        width: 10px;
        height: 38px;
    }

    .device-laptop {
        position: absolute;
        top: 122px;
        left: 56px;
        width: 510px;
        height: 290px;
        border: 4px solid var(--line);
        border-radius: 22px;
        background: linear-gradient(180deg, #ffca4c, #f5b332);
        box-shadow: 0 28px 50px rgba(60, 26, 107, 0.14);
    }

    .device-laptop::before {
        content: "";
        position: absolute;
        inset: 24px 20px 42px;
        border: 4px solid var(--line);
        border-radius: 18px;
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.56), rgba(255, 255, 255, 0) 44%),
            linear-gradient(145deg, #f0f0ff, #d7d8f8 74%);
    }

    .camera-bar {
        position: absolute;
        top: 10px;
        left: 0;
        right: 0;
        width: 72px;
        height: 12px;
        margin: auto;
        border: 4px solid var(--line);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.94);
    }

    .base {
        position: absolute;
        left: 44px;
        right: 44px;
        bottom: -16px;
        height: 26px;
        border: 4px solid var(--line);
        border-radius: 0 0 24px 24px;
        background: linear-gradient(180deg, #ffbf40, #ff9720);
    }

    .base::after {
        content: "";
        position: absolute;
        left: 112px;
        right: 112px;
        bottom: -18px;
        height: 24px;
        border: 4px solid var(--line);
        border-top: none;
        border-radius: 0 0 24px 24px;
        background: linear-gradient(180deg, #f28c22, #d56f15);
    }

    .card-graphic {
        position: absolute;
        top: 178px;
        left: 128px;
        width: 154px;
        height: 94px;
        border: 4px solid var(--line);
        border-radius: 18px;
        background: linear-gradient(135deg, #e786f1, #b24cc8);
        transform: rotate(-14deg);
    }

    .card-graphic::before {
        content: "";
        position: absolute;
        left: 18px;
        top: 24px;
        width: 32px;
        height: 28px;
        border: 4px solid var(--line);
        border-radius: 8px;
        background: white;
    }

    .card-graphic::after {
        content: "";
        position: absolute;
        right: 16px;
        bottom: 22px;
        width: 66px;
        height: 14px;
        border-radius: 999px;
        background: rgba(85, 24, 111, 0.38);
    }

    .coin {
        position: absolute;
        top: 174px;
        left: 332px;
        width: 120px;
        height: 120px;
        border: 4px solid var(--line);
        border-radius: 999px;
        background: radial-gradient(circle at 30% 30%, #ffeaa3, #ffc541 56%, #ef9c1f 100%);
        box-shadow: inset 0 0 0 10px rgba(255, 248, 211, 0.38);
    }

    .coin::before {
        content: "$";
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        color: #8e3c00;
        font-size: 3.3rem;
        font-weight: 900;
    }

    .device-phone {
        position: absolute;
        top: 170px;
        right: 32px;
        width: 160px;
        height: 238px;
        border: 4px solid var(--line);
        border-radius: 24px;
        background: linear-gradient(180deg, #ffca4c, #f5b332);
        box-shadow: 0 28px 50px rgba(60, 26, 107, 0.12);
    }

    .device-phone::before {
        content: "";
        position: absolute;
        inset: 18px 14px 28px;
        border: 4px solid var(--line);
        border-radius: 16px;
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.54), rgba(255, 255, 255, 0) 45%),
            linear-gradient(145deg, #f2f1ff, #d9d8fb 72%);
    }

    .device-phone::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        bottom: 10px;
        width: 16px;
        height: 16px;
        margin: auto;
        border: 4px solid var(--line);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.96);
    }

    .cash {
        position: absolute;
        top: 214px;
        right: 52px;
        width: 92px;
        height: 58px;
        border: 4px solid var(--line);
        border-radius: 12px;
        background: linear-gradient(145deg, #ffc95c, #ffaf37);
        transform: rotate(18deg);
    }

    .cash::before,
    .cash::after {
        content: "";
        position: absolute;
        border: 4px solid rgba(73, 40, 108, 0.72);
        border-radius: 10px;
    }

    .cash::before {
        inset: 10px 12px;
    }

    .cash::after {
        left: 31px;
        top: 16px;
        width: 22px;
        height: 22px;
        border-radius: 999px;
    }

    .wallet {
        position: absolute;
        left: 114px;
        bottom: 70px;
        width: 62px;
        height: 112px;
        border: 4px solid var(--line);
        border-radius: 12px;
        background: linear-gradient(180deg, #ee87ee, #b533c3);
    }

    .wallet::before {
        content: "";
        position: absolute;
        left: 11px;
        top: 16px;
        width: 8px;
        height: 44px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.94);
    }

    .wallet::after {
        content: "";
        position: absolute;
        right: 10px;
        top: 24px;
        width: 18px;
        height: 30px;
        border: 4px solid var(--line);
        border-radius: 6px;
        background: white;
    }

    .top-dot { top: 68px; left: 206px; }
    .top-ring { top: 50px; right: 138px; }
    .mid-dot { top: 252px; left: 38px; }
    .side-ring { top: 226px; right: 10px; }
    .bottom-dot { right: 36px; bottom: 62px; }
    .small-plus { left: 42px; top: 182px; }
    .mid-plus { left: 284px; bottom: 104px; }

    @media (max-width: 1100px) {
        .welcome-layout {
            grid-template-columns: 1fr;
            gap: 26px;
        }

        .left {
            max-width: 100%;
        }

        .left p,
        .login-card {
            max-width: 360px;
        }

        .right {
            min-height: 520px;
        }

        .art-stage {
            transform: scale(0.86);
            transform-origin: top center;
        }
    }

    @media (max-width: 760px) {
        .welcome-layout {
            padding: 18px 22px 36px;
        }

        .left h1 {
            font-size: 3.5rem;
        }

        .left p,
        .login-card,
        .login-card button,
        .login-card .primary-link,
        .helper-links .alt-link {
            width: 100%;
            max-width: none;
        }

        .right {
            min-height: 360px;
        }

        .art-stage {
            min-height: 360px;
            transform: scale(0.52);
            transform-origin: top left;
        }
    }
</style>

<div class="welcome-layout">
    <div class="left">
        <h1>Bank Online</h1>
        <p>
            Smart budget control for every pay cycle. Track expenses, review cash flow, and keep your money
            organized from one clean dashboard.
        </p>

        @guest
            <form method="POST" action="{{ route('login.attempt') }}" class="login-card">
                @csrf
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required>
                <input id="welcome_password" type="password" name="password" placeholder="Password" required>
                <label class="welcome-remember">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>
                <div class="welcome-links">
                    <a href="{{ route('password.request') }}">Forgot Password?</a>
                </div>
                <button type="submit">Login</button>
                <div class="helper-links">
                    <a class="alt-link" href="{{ route('register') }}">Create account</a>
                </div>
            </form>
        @else
            <div class="login-card">
                <input type="text" value="Welcome back" readonly>
                <input type="text" value="{{ auth()->user()->email }}" readonly>
                <a href="{{ route('dashboard') }}" class="primary-link">Dashboard</a>
            </div>
        @endguest
    </div>

    <div class="right" aria-hidden="true">
        <div class="art-stage">
            <span class="top-dot float-dot"></span>
            <span class="top-ring ring"></span>
            <span class="mid-dot float-dot"></span>
            <span class="side-ring ring"></span>
            <span class="bottom-dot float-dot"></span>
            <span class="small-plus plus-shape"></span>
            <span class="mid-plus plus-shape"></span>

            <div class="device-laptop">
                <span class="camera-bar"></span>
                <div class="card-graphic"></div>
                <div class="coin"></div>
                <div class="base"></div>
            </div>

            <div class="device-phone"></div>
            <div class="cash"></div>
            <div class="wallet"></div>
        </div>
    </div>
</div>
@endsection
