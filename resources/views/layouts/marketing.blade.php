<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ff9800">
    <title>@yield('title', config('app.name', 'Budget App'))</title>
    <style>
        :root {
            --ink: #2f2458;
            --ink-soft: rgba(255, 245, 240, 0.88);
            --line: #49286c;
            --line-soft: rgba(255, 255, 255, 0.22);
            --gold: #ffbf3d;
            --gold-deep: #f48d15;
            --panel: rgba(255, 255, 255, 0.1);
            --panel-strong: rgba(255, 255, 255, 0.14);
            --success: #0f766e;
            --danger: #c2410c;
        }

        * { box-sizing: border-box; }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            background:
                linear-gradient(135deg, rgba(255, 205, 84, 0.16), transparent 30%),
                linear-gradient(315deg, rgba(122, 101, 255, 0.18), transparent 26%),
                linear-gradient(135deg, #ff9800 0%, #ff9421 36%, #ff8c6d 36%, #c46bb8 72%, #7865ff 100%);
        }

        .app-loading-overlay {
            position: fixed;
            inset: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at top, rgba(255, 214, 118, 0.24), rgba(255, 214, 118, 0) 35%),
                linear-gradient(145deg, rgba(255, 184, 61, 0.18), rgba(120, 101, 255, 0.24)),
                rgba(47, 36, 88, 0.62);
            backdrop-filter: blur(18px);
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 260ms cubic-bezier(.2,.8,.2,1), visibility 260ms linear;
            z-index: 1400;
        }

        .app-loading-overlay.is-active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .app-loading-card {
            position: relative;
            display: grid;
            justify-items: center;
            gap: 14px;
            width: min(100%, 320px);
            padding: 28px 24px;
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            background: rgba(255, 255, 255, 0.14);
            box-shadow: 0 24px 56px rgba(72, 40, 108, 0.22);
            text-align: center;
            transform: translateY(14px) scale(0.98);
            transition: transform 260ms cubic-bezier(.2,.8,.2,1), opacity 260ms cubic-bezier(.2,.8,.2,1);
            opacity: 0;
            overflow: hidden;
        }

        .app-loading-overlay.is-active .app-loading-card {
            transform: translateY(0) scale(1);
            opacity: 1;
            animation: marketingLoadingCardFloat 3.2s ease-in-out infinite;
        }

        .app-loading-card::before {
            content: "";
            position: absolute;
            inset: -30% auto auto -20%;
            width: 160px;
            height: 160px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(255, 217, 108, 0.42), rgba(255, 217, 108, 0));
            filter: blur(10px);
            animation: marketingLoadingAuraDrift 3.6s ease-in-out infinite;
            pointer-events: none;
        }

        .app-loading-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 10%, rgba(255, 255, 255, 0.36) 45%, transparent 72%);
            transform: translateX(-130%);
            animation: marketingLoadingSheen 2.3s ease-in-out infinite;
            pointer-events: none;
        }

        .app-loading-coin {
            width: 86px;
            height: 86px;
            object-fit: contain;
            image-rendering: auto;
            filter: drop-shadow(0 18px 24px rgba(72, 40, 108, 0.26));
            transform: translateZ(0);
            animation: marketingLoadingCoinFloat 1.8s ease-in-out infinite;
        }

        .app-loading-dots {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .app-loading-dots span {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: linear-gradient(135deg, #ffcf57, #9c82ff);
            box-shadow: 0 6px 14px rgba(120, 101, 255, 0.22);
            animation: marketingLoadingDotPulse 1.05s cubic-bezier(.22,1,.36,1) infinite;
        }

        .app-loading-dots span:nth-child(2) {
            animation-delay: 120ms;
        }

        .app-loading-dots span:nth-child(3) {
            animation-delay: 240ms;
        }

        .app-loading-title {
            margin: 0;
            color: #fffdf8;
            font-size: 1.08rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .app-loading-text {
            margin: 0;
            color: rgba(255, 245, 240, 0.86);
            line-height: 1.6;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .shell {
            width: min(1240px, calc(100% - 28px));
            margin: 0 auto;
            padding: 22px 0;
        }

        .hero {
            position: relative;
            overflow: hidden;
            min-height: calc(100vh - 44px);
            border: 2px solid rgba(255, 193, 66, 0.55);
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.07), transparent 30%),
                linear-gradient(225deg, rgba(83, 62, 154, 0.1), transparent 36%);
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: auto auto -18% -10%;
            width: 46%;
            height: 68%;
            background: linear-gradient(45deg, rgba(255, 182, 90, 0.22), rgba(255, 255, 255, 0));
            transform: rotate(45deg);
            pointer-events: none;
            animation: driftGlowOne 16s ease-in-out infinite;
        }

        .hero::after {
            content: "";
            position: absolute;
            inset: -18% -12% auto auto;
            width: 42%;
            height: 70%;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0));
            transform: rotate(45deg);
            pointer-events: none;
            animation: driftGlowTwo 18s ease-in-out infinite;
        }

        .nav {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 26px 54px 10px;
            animation: marketingRise 520ms ease both;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .brand-with-logo span {
            display: block;
            max-width: 260px;
            line-height: 1.05;
        }

        .marketing-brand-logo {
            width: 138px;
            height: 138px;
            object-fit: contain;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.96);
            padding: 8px;
            border: 1px solid rgba(255, 255, 255, 0.32);
            box-shadow: 0 18px 34px rgba(55, 28, 90, 0.2);
            animation: logoFloat 8s ease-in-out infinite;
        }

        .menu {
            display: flex;
            gap: 48px;
            flex-wrap: wrap;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .menu a {
            opacity: 0.96;
            transition: transform 180ms ease, opacity 180ms ease;
        }

        .menu a:hover {
            transform: translateY(-1px);
            opacity: 1;
        }

        .menu a.active {
            text-decoration: underline;
            text-underline-offset: 6px;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        .system-popup-overlay {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(47, 36, 88, 0.44);
            backdrop-filter: blur(12px);
            z-index: 1200;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 220ms ease;
        }

        .system-popup-overlay.is-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .system-popup {
            width: min(100%, 520px);
            padding: 24px;
            border-radius: 28px;
            border: 2px solid rgba(255, 255, 255, 0.22);
            background:
                linear-gradient(135deg, rgba(255, 216, 110, 0.98), rgba(255, 184, 61, 0.96) 26%, rgba(255, 140, 109, 0.96) 58%, rgba(120, 101, 255, 0.96));
            box-shadow: 0 24px 64px rgba(72, 40, 108, 0.24);
            color: white;
            animation: popupRise 240ms cubic-bezier(.2,.8,.2,1);
        }

        .system-popup-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
        }

        .system-popup-badge {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            font-size: 1.35rem;
            font-weight: 900;
            color: var(--ink);
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 14px 28px rgba(72, 40, 108, 0.18);
            flex-shrink: 0;
        }

        .system-popup-title {
            margin: 0;
            font-size: 1.35rem;
            line-height: 1.1;
            color: white;
        }

        .system-popup-subtitle {
            margin: 6px 0 0;
            color: rgba(255, 245, 240, 0.88);
            line-height: 1.5;
        }

        .system-popup-close {
            width: 40px;
            height: 40px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
        }

        .system-popup-list {
            margin: 18px 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 10px;
        }

        .system-popup-item {
            padding: 12px 14px;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background: rgba(255, 255, 255, 0.14);
            line-height: 1.55;
        }

        .system-popup-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 18px;
        }

        .system-popup-button {
            min-height: 46px;
            padding: 0 18px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.92);
            color: var(--ink);
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(72, 40, 108, 0.16);
            transition: transform 180ms ease, box-shadow 180ms ease, opacity 180ms ease;
        }

        .system-popup-close:hover,
        .system-popup-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(72, 40, 108, 0.16);
        }

        .showcase-layout {
            display: grid;
            grid-template-columns: 0.9fr 1.15fr;
            align-items: center;
            gap: 34px;
            padding: 34px 54px 64px;
            animation: marketingReveal 620ms cubic-bezier(.2,.8,.2,1) both;
        }

        .showcase-copy {
            max-width: 470px;
        }

        .showcase-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 2px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 18px;
            font-weight: 700;
        }

        .showcase-copy h1 {
            margin: 0 0 16px;
            font-size: clamp(2.7rem, 5.8vw, 5rem);
            line-height: 0.94;
            letter-spacing: -0.05em;
        }

        .showcase-copy p,
        .detail-card p,
        .detail-card li,
        .field span,
        .inline-links a,
        .auth-note {
            color: var(--ink-soft);
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .showcase-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .primary-link,
        .alt-link,
        .primary-button,
        .secondary-button {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 58px;
            padding: 0 22px;
            border-radius: 999px;
            font-size: 1.05rem;
            font-weight: 800;
            cursor: pointer;
            transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease, opacity 180ms ease;
            overflow: hidden;
        }

        .primary-link::after,
        .alt-link::after,
        .primary-button::after,
        .secondary-button::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 18%, rgba(255, 255, 255, 0.34) 50%, transparent 82%);
            transform: translateX(-150%);
            transition: transform 620ms cubic-bezier(.22,1,.36,1);
            pointer-events: none;
        }

        .primary-link,
        .primary-button {
            border: 4px solid var(--line);
            background: var(--gold);
            color: var(--ink);
            box-shadow: 0 12px 20px rgba(72, 40, 108, 0.12);
        }

        .alt-link,
        .secondary-button {
            border: 2px solid rgba(255, 255, 255, 0.28);
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .showcase-form {
            display: grid;
            gap: 14px;
            width: min(100%, 380px);
            margin-top: 24px;
        }

        .stack-form {
            display: grid;
            gap: 14px;
        }

        .form-grid-two {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        .field span {
            font-size: 0.95rem;
            font-weight: 700;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            min-height: 54px;
            padding: 0 16px;
            border: none;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.95);
            color: var(--ink);
            font-size: 1rem;
            box-shadow: 0 12px 28px rgba(112, 39, 17, 0.08);
            transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease;
        }

        .field textarea {
            min-height: 112px;
            padding: 14px 16px;
            resize: vertical;
        }

        .field input::placeholder,
        .field textarea::placeholder {
            color: rgba(47, 36, 88, 0.42);
        }

        .field input[type="password"]::-ms-reveal,
        .field input[type="password"]::-ms-clear {
            display: none;
        }

        .field input.native-password-field[type="password"]::-ms-reveal,
        .field input.native-password-field[type="password"]::-ms-clear {
            display: block;
        }

        .field input.native-password-field {
            padding-right: 16px;
        }

        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 72px;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            min-width: 34px;
            min-height: 34px;
            padding: 0;
            border: 1px solid rgba(73, 40, 108, 0.14);
            border-radius: 999px;
            background: linear-gradient(180deg, rgba(255, 206, 93, 0.98), rgba(247, 171, 38, 0.96));
            color: var(--ink);
            font-size: 0;
            box-shadow: 0 6px 12px rgba(72, 40, 108, 0.1);
            cursor: pointer;
            transform: translateY(-50%);
            transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease, color 180ms ease;
        }

        .password-toggle::before {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            width: 16px;
            height: 10px;
            border: 1.8px solid currentColor;
            border-radius: 999px;
            transform: translate(-50%, -50%);
        }

        .password-toggle::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            width: 4px;
            height: 4px;
            border-radius: 999px;
            background: currentColor;
            transform: translate(-50%, -50%);
        }

        .password-toggle span {
            position: absolute;
            inset: 0;
            display: block;
        }

        .auth-actions {
            display: grid;
            gap: 12px;
            margin-top: 6px;
        }

        .auth-actions .primary-button,
        .auth-actions .secondary-button {
            width: 100%;
        }

        .password-toggle:hover {
            background: linear-gradient(180deg, #ffd86e, #ffb43d);
            box-shadow: 0 10px 18px rgba(72, 40, 108, 0.14);
        }

        .password-toggle[data-visible="true"] {
            background: linear-gradient(180deg, rgba(120, 101, 255, 0.94), rgba(91, 68, 206, 0.96));
            border-color: rgba(73, 40, 108, 0.24);
            color: white;
        }

        .password-toggle[data-visible="true"] span::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            width: 18px;
            height: 1.8px;
            background: currentColor;
            border-radius: 999px;
            transform: translate(-50%, -50%) rotate(-28deg);
        }

        .inline-check {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: white;
        }

        .inline-check input {
            width: 20px;
            height: 20px;
            accent-color: var(--gold-deep);
        }

        .inline-links {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .inline-links a {
            text-decoration: underline;
            text-underline-offset: 5px;
        }

        .showcase-visual {
            position: relative;
            min-height: 620px;
        }

        .illustration-stage {
            position: relative;
            width: 100%;
            min-height: 620px;
        }

        .float-dot,
        .ring,
        .plus-shape {
            position: absolute;
            border-radius: 999px;
            border: 4px solid var(--line);
            background: rgba(255, 255, 255, 0.88);
            animation: artFloat 9s ease-in-out infinite;
        }

        .float-dot.tiny {
            width: 16px;
            height: 16px;
            border-width: 3px;
        }

        .float-dot.small {
            width: 24px;
            height: 24px;
        }

        .ring {
            width: 26px;
            height: 26px;
            background: transparent;
        }

        .plus-shape {
            width: 40px;
            height: 14px;
        }

        .plus-shape::before {
            content: "";
            position: absolute;
            inset: -13px auto auto 11px;
            width: 14px;
            height: 40px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.88);
            border: 4px solid var(--line);
        }

        .device-laptop {
            position: absolute;
            top: 124px;
            left: 52px;
            width: 508px;
            height: 292px;
            border-radius: 22px;
            border: 4px solid var(--line);
            background: linear-gradient(180deg, #ffcf55, #f9b937);
            box-shadow: 0 24px 50px rgba(72, 40, 108, 0.14);
            animation: artFloat 9s ease-in-out infinite;
        }

        .device-laptop::before {
            content: "";
            position: absolute;
            inset: 20px 20px 42px;
            border-radius: 18px;
            border: 4px solid var(--line);
            background: linear-gradient(135deg, #efefff 0%, #d2d4f5 100%);
        }

        .device-laptop::after {
            content: "";
            position: absolute;
            left: 44px;
            right: 44px;
            bottom: -18px;
            height: 34px;
            border-radius: 0 0 26px 26px;
            border: 4px solid var(--line);
            background: linear-gradient(180deg, #ffb842, #f48d15);
        }

        .camera-bar {
            position: absolute;
            top: 10px;
            left: 172px;
            width: 140px;
            height: 18px;
            border-radius: 999px;
            border: 4px solid var(--line);
            background: rgba(255, 255, 255, 0.95);
        }

        .camera-bar::before {
            content: "";
            position: absolute;
            left: -28px;
            top: -4px;
            width: 18px;
            height: 18px;
            border-radius: 999px;
            border: 4px solid var(--line);
            background: #fff3d1;
        }

        .device-phone {
            position: absolute;
            top: 170px;
            right: 34px;
            width: 164px;
            height: 240px;
            border-radius: 26px;
            border: 4px solid var(--line);
            background: linear-gradient(180deg, #ffcf55, #f8bb3f);
            box-shadow: 0 24px 44px rgba(72, 40, 108, 0.16);
            animation: artFloat 10.5s ease-in-out infinite;
        }

        .device-phone::before {
            content: "";
            position: absolute;
            inset: 18px;
            border-radius: 16px;
            border: 4px solid var(--line);
            background: linear-gradient(135deg, #efefff 0%, #d2d4f5 100%);
        }

        .device-phone::after {
            content: "";
            position: absolute;
            left: 66px;
            bottom: 10px;
            width: 20px;
            height: 20px;
            border-radius: 999px;
            border: 4px solid var(--line);
            background: rgba(255, 255, 255, 0.9);
        }

        .card-graphic {
            position: absolute;
            top: 178px;
            left: 134px;
            width: 160px;
            height: 92px;
            border-radius: 18px;
            border: 4px solid var(--line);
            background: linear-gradient(135deg, #df8bf4 0%, #b959d4 100%);
            transform: rotate(-14deg);
            box-shadow: 0 18px 32px rgba(72, 40, 108, 0.18);
            animation: artFloatTilt 9.5s ease-in-out infinite;
        }

        .card-graphic::before {
            content: "";
            position: absolute;
            top: 20px;
            left: 20px;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            border: 4px solid var(--line);
            background: rgba(255, 255, 255, 0.95);
        }

        .card-graphic::after {
            content: "";
            position: absolute;
            right: 16px;
            bottom: 22px;
            width: 72px;
            height: 16px;
            border-radius: 999px;
            background: rgba(86, 39, 134, 0.35);
        }

        .coin {
            position: absolute;
            top: 178px;
            left: 334px;
            width: 116px;
            height: 116px;
            border-radius: 999px;
            border: 4px solid var(--line);
            background: radial-gradient(circle at 35% 32%, #ffe9a8, #ffc541 56%, #f09c12 100%);
            box-shadow: 0 18px 32px rgba(72, 40, 108, 0.14);
            animation: artFloat 8.5s ease-in-out infinite;
        }

        .coin::before {
            content: "$";
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            color: #883c00;
            font-size: 3.5rem;
            font-weight: 900;
        }

        .cash {
            position: absolute;
            top: 218px;
            right: 56px;
            width: 90px;
            height: 54px;
            border-radius: 10px;
            border: 4px solid var(--line);
            background: linear-gradient(135deg, #ffd16a, #ffb948);
            transform: rotate(18deg);
            animation: artFloatCash 10s ease-in-out infinite;
        }

        .cash::before,
        .cash::after {
            content: "";
            position: absolute;
            inset: 8px 12px;
            border-radius: 8px;
            border: 4px solid rgba(86, 39, 134, 0.72);
        }

        .cash::after {
            inset: 12px 22px;
            border-radius: 999px;
        }

        .wallet {
            position: absolute;
            left: 102px;
            bottom: 88px;
            width: 66px;
            height: 112px;
            border-radius: 12px;
            border: 4px solid var(--line);
            background: linear-gradient(180deg, #e37cec, #b239ca);
            animation: artFloat 8.5s ease-in-out infinite;
        }

        .wallet::before {
            content: "";
            position: absolute;
            top: 14px;
            left: 12px;
            width: 8px;
            height: 42px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.9);
        }

        .wallet::after {
            content: "";
            position: absolute;
            top: 22px;
            right: 12px;
            width: 18px;
            height: 30px;
            border-radius: 6px;
            border: 4px solid var(--line);
            background: rgba(255, 255, 255, 0.88);
        }

        .detail-grid {
            position: absolute;
            right: 10px;
            bottom: 0;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
            width: min(100%, 420px);
        }

        .detail-card {
            position: relative;
            padding: 18px;
            border-radius: 22px;
            border: 2px solid var(--line-soft);
            background: var(--panel);
            backdrop-filter: blur(10px);
            box-shadow: 0 16px 32px rgba(72, 40, 108, 0.12);
            animation: itemRise 680ms ease both;
            overflow: hidden;
        }

        .detail-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent 20%, rgba(255, 255, 255, 0.18) 48%, transparent 76%);
            transform: translateX(-140%);
            transition: transform 760ms cubic-bezier(.22,1,.36,1);
            pointer-events: none;
        }

        .detail-card strong {
            display: block;
            margin-bottom: 6px;
            font-size: 1.1rem;
        }

        .detail-card ul {
            margin: 0;
            padding-left: 18px;
        }

        .media-frame {
            display: grid;
            place-items: center;
            padding: 34px;
            border-radius: 28px;
            border: 2px solid rgba(255, 255, 255, 0.24);
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            min-height: 420px;
            box-shadow: 0 20px 42px rgba(72, 40, 108, 0.14);
        }

        .media-frame img {
            width: min(100%, 440px);
            height: auto;
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.96);
            padding: 14px;
            box-shadow: 0 22px 44px rgba(72, 40, 108, 0.18);
            animation: mediaFloat 9s ease-in-out infinite;
        }

        .detail-card:nth-child(2) { animation-delay: 90ms; }
        .detail-card:nth-child(3) { animation-delay: 180ms; }
        .detail-card:nth-child(4) { animation-delay: 270ms; }

        .primary-link:hover::after,
        .alt-link:hover::after,
        .primary-button:hover::after,
        .secondary-button:hover::after,
        .detail-card:hover::before {
            transform: translateX(145%);
        }

        @keyframes popupRise {
            from { opacity: 0; transform: translateY(18px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes marketingRise {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes marketingReveal {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes itemRise {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes driftGlowOne {
            0%, 100% { transform: rotate(45deg) translate3d(0, 0, 0); }
            50% { transform: rotate(45deg) translate3d(14px, -18px, 0); }
        }

        @keyframes driftGlowTwo {
            0%, 100% { transform: rotate(45deg) translate3d(0, 0, 0); }
            50% { transform: rotate(45deg) translate3d(-18px, 16px, 0); }
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes artFloat {
            0%, 100% { transform: translate3d(0, 0, 0); }
            50% { transform: translate3d(0, -8px, 0); }
        }

        @keyframes artFloatTilt {
            0%, 100% { transform: rotate(-14deg) translate3d(0, 0, 0); }
            50% { transform: rotate(-14deg) translate3d(0, -8px, 0); }
        }

        @keyframes artFloatCash {
            0%, 100% { transform: rotate(18deg) translate3d(0, 0, 0); }
            50% { transform: rotate(18deg) translate3d(0, -7px, 0); }
        }

        @keyframes mediaFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        @keyframes marketingLoadingCoinFloat {
            0%, 100% { transform: translateY(0) scale(1); filter: drop-shadow(0 18px 24px rgba(72, 40, 108, 0.26)); }
            50% { transform: translateY(-6px) scale(1.06); filter: drop-shadow(0 24px 30px rgba(72, 40, 108, 0.3)); }
        }

        @keyframes marketingLoadingCardFloat {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-4px) scale(1.01); }
        }

        @keyframes marketingLoadingAuraDrift {
            0%, 100% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(26px, 18px, 0) scale(1.16); }
        }

        @keyframes marketingLoadingSheen {
            0%, 100% { transform: translateX(-140%); }
            48%, 60% { transform: translateX(140%); }
        }

        @keyframes marketingLoadingDotPulse {
            0%, 100% { transform: translateY(0) scale(0.92); opacity: 0.7; }
            50% { transform: translateY(-5px) scale(1.12); opacity: 1; }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                transition-delay: 0ms !important;
            }
        }

        @media (max-width: 1100px) {
            .showcase-layout {
                grid-template-columns: 1fr;
            }

            .showcase-copy {
                max-width: none;
            }

            .showcase-visual,
            .illustration-stage {
                min-height: 720px;
            }

            .detail-grid {
                position: relative;
                right: auto;
                bottom: auto;
                margin-top: 460px;
                width: 100%;
            }
        }

        @media (max-width: 760px) {
            .nav {
                padding: 22px 22px 0;
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .menu {
                gap: 18px;
                width: 100%;
            }

            .marketing-notices,
            .showcase-layout {
                padding-left: 22px;
                padding-right: 22px;
            }

            .showcase-layout {
                padding-top: 24px;
                padding-bottom: 40px;
                gap: 24px;
            }

            .form-grid-two,
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .showcase-form,
            .primary-link,
            .alt-link,
            .primary-button,
            .secondary-button {
                width: 100%;
            }

            .brand-with-logo span {
                max-width: none;
            }

            .marketing-brand-logo {
                width: 96px;
                height: 96px;
                border-radius: 20px;
            }

            .showcase-copy h1 {
                font-size: clamp(2.2rem, 10vw, 3.4rem);
                line-height: 1;
            }

            .showcase-visual,
            .illustration-stage {
                min-height: 560px;
            }

            .device-laptop {
                left: 4px;
                width: calc(100% - 64px);
                height: 220px;
            }

            .camera-bar {
                left: calc(50% - 70px);
            }

            .device-phone {
                top: 170px;
                right: 0;
                width: 116px;
                height: 184px;
            }

            .card-graphic {
                top: 176px;
                left: 48px;
                width: 108px;
                height: 72px;
            }

            .coin {
                top: 176px;
                left: 170px;
                width: 74px;
                height: 74px;
            }

            .cash {
                top: 214px;
                right: 18px;
                width: 60px;
                height: 40px;
            }

            .wallet {
                left: 18px;
                bottom: 122px;
                width: 54px;
                height: 92px;
            }

            .detail-grid {
                position: relative;
                margin-top: 336px;
                width: 100%;
            }
        }

        @media (max-width: 540px) {
            .nav,
            .marketing-notices,
            .showcase-layout {
                padding-left: 14px;
                padding-right: 14px;
            }

            .menu {
                gap: 10px;
                font-size: 0.95rem;
            }

            .menu a {
                width: 100%;
            }

            .showcase-layout {
                padding-top: 18px;
                padding-bottom: 26px;
            }

            .showcase-badge {
                margin-bottom: 12px;
                font-size: 0.85rem;
            }

            .showcase-copy p,
            .detail-card p,
            .detail-card li,
            .field span,
            .inline-links a,
            .auth-note {
                font-size: 0.95rem;
                line-height: 1.55;
            }

            .showcase-visual,
            .illustration-stage {
                min-height: 470px;
            }

            .device-laptop {
                left: 0;
                width: calc(100% - 42px);
                height: 178px;
                border-radius: 16px;
            }

            .device-laptop::before {
                inset: 14px 14px 30px;
                border-radius: 12px;
            }

            .device-laptop::after {
                left: 28px;
                right: 28px;
                height: 24px;
            }

            .camera-bar {
                top: 8px;
                left: calc(50% - 50px);
                width: 100px;
                height: 14px;
            }

            .device-phone {
                top: 142px;
                width: 92px;
                height: 152px;
                border-radius: 18px;
            }

            .device-phone::before {
                inset: 12px;
                border-radius: 12px;
            }

            .device-phone::after {
                left: 34px;
                width: 16px;
                height: 16px;
            }

            .card-graphic {
                top: 146px;
                left: 26px;
                width: 88px;
                height: 58px;
                border-radius: 14px;
            }

            .coin {
                top: 144px;
                left: 128px;
                width: 58px;
                height: 58px;
            }

            .coin::before {
                font-size: 2.2rem;
            }

            .cash {
                top: 184px;
                right: 14px;
                width: 46px;
                height: 30px;
            }

            .wallet {
                left: 10px;
                bottom: 106px;
                width: 42px;
                height: 76px;
            }

            .float-dot,
            .ring,
            .plus-shape {
                transform: scale(0.75);
                transform-origin: center;
            }

            .detail-grid {
                margin-top: 272px;
                gap: 12px;
            }

            .detail-card {
                padding: 14px;
                border-radius: 18px;
            }

            .media-frame {
                min-height: 280px;
                padding: 18px;
                border-radius: 22px;
            }

            .media-frame img {
                padding: 8px;
                border-radius: 18px;
            }

            .showcase-form,
            .field input,
            .field select,
            .field textarea,
            .primary-link,
            .alt-link,
            .primary-button,
            .secondary-button {
                min-height: 48px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }

            *,
            *::before,
            *::after {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="app-loading-overlay is-active" data-loading-overlay aria-hidden="true">
        <div class="app-loading-card" role="status" aria-live="polite">
            <img class="app-loading-coin" src="{{ asset('images/Coin.gif') }}" alt="" aria-hidden="true">
            <div class="app-loading-dots" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <p class="app-loading-title">Loading your workspace</p>
            <p class="app-loading-text">Please wait while the system prepares your page.</p>
        </div>
    </div>

    @php
        $brandName = 'KUYA ALLEN TECH SOLUTIONS';
        $brandLogoFile = file_exists(public_path('images/kuya-allen-logo.png'))
            ? 'images/kuya-allen-logo.png'
            : (file_exists(public_path('images/wowlogo.png')) ? 'images/wowlogo.png' : 'icons/icon.svg');
        $brandLogo = asset($brandLogoFile).'?v='.(file_exists(public_path($brandLogoFile)) ? filemtime(public_path($brandLogoFile)) : time());
    @endphp

    <main class="shell">
        <section class="hero">
            <nav class="nav">
                <div class="brand brand-with-logo">
                    <a href="{{ route('logo.viewer') }}" aria-label="View {{ $brandName }} logo">
                        <img class="marketing-brand-logo" src="{{ $brandLogo }}" alt="{{ $brandName }} logo">
                    </a>
                    <a href="{{ route('welcome') }}">
                        <span>{{ $brandName }}</span>
                    </a>
                </div>
                <div class="menu">
                    <a href="{{ route('welcome') }}" class="{{ request()->routeIs('welcome') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About</a>
                    <a href="{{ route('contacts') }}" class="{{ request()->routeIs('contacts') ? 'active' : '' }}">Contacts</a>
                    <a href="{{ route('faq') }}" class="{{ request()->routeIs('faq') ? 'active' : '' }}">FAQ</a>
                </div>
            </nav>

            <div class="content">
                @yield('content')
            </div>
        </section>
    </main>

    @php
        $popupMessages = [];
        $popupTone = null;
        $popupTitle = null;
        $popupSubtitle = null;

        if ($errors->any()) {
            $popupMessages = $errors->all();
            $popupTone = 'error';
            $popupTitle = 'Please check these errors';
            $popupSubtitle = 'Some information needs to be corrected before the page can continue.';
        } elseif (session('error')) {
            $popupMessages = [session('error')];
            $popupTone = 'error';
            $popupTitle = 'Something went wrong';
            $popupSubtitle = 'The system could not finish that request.';
        } elseif (session('success')) {
            $popupMessages = [session('success')];
            $popupTone = 'success';
            $popupTitle = 'Action completed';
            $popupSubtitle = 'Your latest action finished successfully.';
        }
    @endphp

    @if(! empty($popupMessages))
        <div class="system-popup-overlay is-visible" data-system-popup-overlay>
            <div class="system-popup" data-system-popup data-popup-tone="{{ $popupTone }}" role="alertdialog" aria-modal="true" aria-labelledby="system-popup-title">
                <div class="system-popup-header">
                    <div style="display:flex; gap:14px; align-items:flex-start;">
                        <div class="system-popup-badge">{{ $popupTone === 'error' ? '!' : 'OK' }}</div>
                        <div>
                            <h2 class="system-popup-title" id="system-popup-title">{{ $popupTitle }}</h2>
                            <p class="system-popup-subtitle">{{ $popupSubtitle }}</p>
                        </div>
                    </div>
                    <button type="button" class="system-popup-close" aria-label="Close popup" data-system-popup-close>&times;</button>
                </div>

                <ul class="system-popup-list">
                    @foreach($popupMessages as $message)
                        <li class="system-popup-item">{{ $message }}</li>
                    @endforeach
                </ul>

                <div class="system-popup-actions">
                    <button type="button" class="system-popup-button" data-system-popup-close>Okay</button>
                </div>
            </div>
        </div>
    @endif

    <script src="{{ asset('js/app.js') }}?v={{ file_exists(public_path('js/app.js')) ? filemtime(public_path('js/app.js')) : time() }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const target = document.getElementById(button.dataset.passwordToggle);

                    if (!target) {
                        return;
                    }

                    const isHidden = target.type === 'password';
                    target.type = isHidden ? 'text' : 'password';
                    button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                    button.dataset.visible = isHidden ? 'true' : 'false';
                });
            });

            const overlay = document.querySelector('[data-system-popup-overlay]');

            if (overlay) {
                const closePopup = () => {
                    overlay.classList.remove('is-visible');
                };

                overlay.querySelectorAll('[data-system-popup-close]').forEach((button) => {
                    button.addEventListener('click', closePopup);
                });

                overlay.addEventListener('click', (event) => {
                    if (event.target === overlay) {
                        closePopup();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closePopup();
                    }
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
