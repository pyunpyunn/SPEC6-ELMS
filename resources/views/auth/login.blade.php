<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NAV Employee Leave Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #e9f3ea;
            --panel: #0f2d1f;
            --panel2: #163d2a;
            --panel3: #1f593d;
            --line: rgba(255,255,255,.08);
            --text: #f6fbf7;
            --muted: #c3d8c8;
            --accent: #2d7a45;
            --accent2: #76c18c;
            --danger: #f06c6c;
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top left, rgba(45,122,69,.18), transparent 28%),
                        radial-gradient(circle at bottom right, rgba(118,193,140,.14), transparent 30%),
                        linear-gradient(180deg, #eef5ef 0%, #d8e5d5 100%);
            color: #0f2d1f;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .shell {
            width: min(1080px, 100%);
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            gap: 20px;
            align-items: stretch;
        }

        .hero, .card {
            border-radius: 28px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 28px 80px rgba(18, 38, 22, 0.18);
        }

        .hero {
            background: linear-gradient(180deg, #143c28 0%, #0e2e1f 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 44px;
            min-height: 620px;
        }

        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top left, rgba(118,193,140,.24), transparent 26%),
                        radial-gradient(circle at bottom right, rgba(45,122,69,.16), transparent 24%);
            opacity: 0.55;
            pointer-events: none;
        }

        .hero > * { position: relative; z-index: 1; }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 26px;
        }

        .mark {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            background: linear-gradient(135deg, #3fa274, #1f593d);
            display: grid;
            place-items: center;
            color: white;
            font-weight: 900;
            font-size: 18px;
            letter-spacing: -0.06em;
        }

        .brand-text h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-text span {
            display: block;
            color: rgba(255,255,255,.75);
            font-size: 14px;
            line-height: 1.6;
        }

        .hero-title {
            margin: 0;
            font-size: clamp(40px, 4vw, 52px);
            line-height: 1.02;
            letter-spacing: -0.05em;
            max-width: 11ch;
        }

        .hero-copy {
            margin: 22px 0 0;
            color: rgba(255,255,255,.8);
            font-size: 16px;
            line-height: 1.8;
            max-width: 46ch;
        }

        .hero-features {
            margin-top: 32px;
            display: grid;
            gap: 14px;
        }

        .hero-card {
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.08);
            padding: 18px 20px;
        }

        .hero-card strong {
            display: block;
            font-size: 15px;
            margin-bottom: 6px;
        }

        .hero-card span {
            color: rgba(255,255,255,.75);
            font-size: 14px;
            line-height: 1.7;
        }

        .card {
            background: white;
            padding: 38px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .tabs a {
            flex: 1;
            text-align: center;
            padding: 12px 16px;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            border: 1px solid #e4ece2;
            color: #4f6a5a;
            background: #f6faf5;
        }

        .tabs a.active {
            background: var(--accent);
            color: #f7fbf7;
            border-color: transparent;
        }

        .card h3 {
            margin: 0 0 6px;
            font-size: 32px;
            letter-spacing: -0.04em;
            color: #163d2a;
        }

        .card p {
            margin: 0 0 24px;
            color: #6d8f76;
            font-size: 15px;
            line-height: 1.7;
        }

        label {
            display: block;
            margin: 16px 0 10px;
            font-size: 14px;
            font-weight: 700;
            color: #375a42;
        }

        input {
            width: 100%;
            min-height: 48px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid #d6e4d6;
            background: #f8fcf6;
            color: #0f2d1f;
            font-size: 15px;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        input:focus {
            border-color: rgba(45,122,69,.8);
            box-shadow: 0 0 0 4px rgba(118,193,140,.18);
        }

        .field-row {
            position: relative;
        }

        .field-row button {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            cursor: pointer;
            color: #7b9f82;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: 20px;
        }

        .actions label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            font-size: 14px;
            color: #5b7f65;
            font-weight: 600;
        }

        .actions input[type="checkbox"] {
            accent-color: var(--accent);
            width: 16px;
            height: 16px;
        }

        .btn {
            min-height: 50px;
            padding: 0 18px;
            border: 0;
            border-radius: 16px;
            background: var(--accent);
            color: #f7fbf7;
            font-weight: 800;
            font-size: 15px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform .15s ease, opacity .15s ease;
        }

        .btn:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }

        .err {
            margin-top: 14px;
            padding: 14px 16px;
            border-radius: 16px;
            background: #fceded;
            border: 1px solid #f6c5c5;
            color: #8d3737;
            font-size: 14px;
        }

        .foot {
            margin-top: 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            color: #5b7f65;
            font-size: 15px;
        }

        .foot a {
            color: var(--accent2);
            font-weight: 700;
            text-decoration: none;
        }

        @media (max-width: 920px) {
            .shell { grid-template-columns: 1fr; }
            .hero { min-height: 400px; }
        }

        @media (max-width: 680px) {
            .shell { gap: 16px; }
            .card, .hero { padding: 28px; }
            .hero-title { font-size: 32px; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <section class="hero">
            <div>
                <div class="brand">
                    <div class="mark">EL</div>
                    <div class="brand-text">
                        <h1>NAV Employee Leave</h1>
                        <span>Employee portal for leave requests, balance tracking, and team approvals.</span>
                    </div>
                </div>
                <h1 class="hero-title">Employee Portal for leave, approvals, and team support.</h1>
                <p class="hero-copy">Sign in to manage your leave, check your balance, and keep your time off workflow moving in one secure employee portal.</p>
            </div>
            <div class="hero-features">
                <div class="hero-card"><strong>Fast access</strong><span>Submit leave requests and review approvals from one dashboard.</span></div>
                <div class="hero-card"><strong>Clear visibility</strong><span>See remaining balances and approved time off right away.</span></div>
            </div>
        </section>

        <section class="card">
            <div class="tabs">
                <a class="active" href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Register</a>
            </div>
            <h3>Employee Portal</h3>
            <p>Use your employee ID or email to sign in and access your leave dashboard.</p>

            @if ($errors->any())
                <div class="err">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <label for="email">Employee ID or Email</label>
                <input id="email" type="text" name="email" value="{{ old('email') }}" required autofocus>

                <label for="password">Password</label>
                <div class="field-row">
                    <input id="password" type="password" name="password" required>
                    <button type="button" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                        <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg id="eyeOffIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>

                <div class="actions">
                    <label>
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                    <button type="submit" class="btn">Log in</button>
                </div>
            </form>

            <div class="foot">
                <span>No account?</span>
                <a href="{{ route('register') }}">Register Here</a>
            </div>
        </section>
    </div>
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        }
    </script>
</body>
</html>