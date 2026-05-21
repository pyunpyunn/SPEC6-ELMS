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

        .brand { display:flex; gap:14px; align-items:center; margin-bottom:26px }
        .mark { width:52px;height:52px;border-radius:18px;background:linear-gradient(135deg,#3fa274,#1f593d);display:grid;place-items:center;color:white;font-weight:900;font-size:18px }
        .brand-text h1 { margin:0;font-size:18px;letter-spacing:-0.03em }
        .brand-text span { display:block;margin-top:4px;color:rgba(255,255,255,.72);font-size:14px;line-height:1.5 }
        .hero-title { margin:0;font-size:clamp(40px,4vw,52px);line-height:1.02;letter-spacing:-0.05em;max-width:11ch }
        .hero-copy { margin:22px 0 0;color:rgba(255,255,255,.8);font-size:16px;line-height:1.8;max-width:46ch }
        .hero-features { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:28px }
        .hero-card { padding:16px;border-radius:18px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12) }
        .hero-card strong { display:block;font-size:15px;margin-bottom:6px }
        .hero-card span { color:rgba(255,255,255,.72);font-size:14px;line-height:1.5 }

        .card { background:white;padding:38px;display:flex;flex-direction:column;justify-content:center }
        .tabs { display:flex;gap:12px;margin-bottom:24px }
        .tabs a { flex:1;text-align:center;padding:12px 16px;border-radius:999px;text-decoration:none;font-weight:700;font-size:14px;border:1px solid #e4ece2;color:#4f6a5a;background:#f6faf5 }
        .tabs a.active { background:var(--accent); color:#f7fbf7; border-color:transparent }
        .card h3 { margin:0 0 8px;font-size:30px;line-height:1.15;letter-spacing:-0.04em;color:#102d1d }
        .card p { margin:0 0 22px;color:#607468;font-size:15px;line-height:1.6 }

        label { display:block;margin:16px 0 10px;font-size:14px;font-weight:700;color:#375a42 }
        input { width:100%;min-height:48px;padding:14px 16px;border-radius:16px;border:1px solid #d6e4d6;background:#f8fcf6;color:#0f2d1f;font-size:15px;outline:none }
        input:focus { border-color:var(--accent);box-shadow:0 0 0 4px rgba(45,122,69,.12) }
        .password-field { position:relative;display:flex;align-items:center }
        .password-field input { padding-right:48px }
        .password-toggle { position:absolute;right:12px;width:28px;height:28px;border:0;background:transparent;color:#607468;display:grid;place-items:center;cursor:pointer }
        .password-toggle svg { width:18px;height:18px }
        .actions { display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:18px }
        .remember { display:flex;align-items:center;gap:8px;margin:0;color:#607468;font-weight:600 }
        .remember input { width:16px;min-height:auto;padding:0 }
        .btn { min-height:50px;padding:0 18px;border:0;border-radius:16px;background:var(--accent);color:#f7fbf7;font-weight:800;font-size:15px;cursor:pointer }
        .err { margin-bottom:14px;padding:14px 16px;border-radius:16px;background:#fceded;border:1px solid #f6c5c5;color:#8d3737 }
        .hint { margin-top:22px;padding:14px 16px;border-radius:16px;background:#f1f8f1;border:1px solid #dbeadb;color:#42614b;font-size:14px;line-height:1.5 }
        .foot { margin-top:18px;display:flex;justify-content:space-between;gap:12px;color:#607468;font-size:14px }
        .foot a { color:var(--accent);font-weight:800;text-decoration:none }

        @media (max-width: 920px) {
            .shell { grid-template-columns: 1fr }
            .hero { min-height:400px }
        }

        @media (max-width: 680px) {
            body { padding:16px }
            .shell { gap:16px }
            .card, .hero { padding:28px }
            .hero-title { font-size:32px }
            .hero-features { grid-template-columns:1fr }
            .actions, .foot { align-items:stretch;flex-direction:column }
            .btn { width:100% }
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
                <h1 class="hero-title">One workspace for leave management.</h1>
                <p class="hero-copy">Sign in to submit leave requests, monitor balances, and stay updated on approvals.</p>
            </div>
            <div class="hero-features">
                <div class="hero-card"><strong>Fast access</strong><span>Request leave, track balances, and view updates quickly.</span></div>
                <div class="hero-card"><strong>Secure review</strong><span>HR keeps employee records and requests verified.</span></div>
            </div>
        </section>

        <section class="card">
            <div>
                <div class="tabs">
                    <a class="active" href="{{ route('login') }}">Log in</a>
                    <a href="{{ route('register') }}">Register</a>
                </div>

                <h3>Welcome back</h3>
                <p>Sign in to continue to your leave dashboard.</p>

                @if ($errors->any())
                    <div class="err">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', 'hr@company.com') }}" required autofocus autocomplete="username">

                    <label for="password">Password</label>
                    <div class="password-field">
                        <input id="password" type="password" name="password" value="password" required autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility()" aria-label="Show password">
                            <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>

                    <div class="actions">
                        <label class="remember">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <button type="submit" class="btn">Log in</button>
                    </div>
                </form>

                <div class="hint">Default HR account: <strong>hr@company.com</strong> / <strong>password</strong>.</div>
                <div class="foot">
                    <span>Need an account?</span>
                    <a href="{{ route('register') }}">Register here</a>
                </div>
            </div>
        </section>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggle = document.querySelector('.password-toggle');

            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        }
    </script>
</body>
</html>
