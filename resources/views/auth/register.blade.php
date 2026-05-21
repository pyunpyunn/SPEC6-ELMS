<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - NAV Employee Leave Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* copy of login styles to keep auth pages consistent */
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
        .hero-title { margin:0;font-size:clamp(40px,4vw,52px);line-height:1.02;letter-spacing:-0.05em;max-width:11ch }
        .hero-copy { margin:22px 0 0;color:rgba(255,255,255,.8);font-size:16px;line-height:1.8;max-width:46ch }

        .card { background:white;padding:38px;display:flex;flex-direction:column;justify-content:space-between }
        .tabs { display:flex;gap:12px;margin-bottom:24px }
        .tabs a { flex:1;text-align:center;padding:12px 16px;border-radius:999px;text-decoration:none;font-weight:700;font-size:14px;border:1px solid #e4ece2;color:#4f6a5a;background:#f6faf5 }
        .tabs a.active { background:var(--accent); color:#f7fbf7; border-color:transparent }

        label { display:block;margin:16px 0 10px;font-size:14px;font-weight:700;color:#375a42 }
        input { width:100%;min-height:48px;padding:14px 16px;border-radius:16px;border:1px solid #d6e4d6;background:#f8fcf6;color:#0f2d1f;font-size:15px }
        .btn { min-height:50px;padding:0 18px;border:0;border-radius:16px;background:var(--accent);color:#f7fbf7;font-weight:800;font-size:15px;cursor:pointer }
        .err { margin-top:14px;padding:14px 16px;border-radius:16px;background:#fceded;border:1px solid #f6c5c5;color:#8d3737 }

        @media (max-width: 920px) { .shell { grid-template-columns: 1fr } .hero { min-height:400px } }
        @media (max-width: 680px) { .shell { gap:16px } .card, .hero { padding:28px } .hero-title { font-size:32px } }
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
                <p class="hero-copy">Create an account to submit leave requests and track your balances.</p>
            </div>
            <div class="hero-features">
                <div class="hero-card"><strong>Fast access</strong><span>Request leave, track balances, and get approvals quickly.</span></div>
                <div class="hero-card"><strong>Secure</strong><span>Accounts are protected and managed by HR.</span></div>
            </div>
        </section>

        <section class="card">
            <div class="tabs">
                <a href="{{ route('login') }}">Log in</a>
                <a class="active" href="{{ route('register') }}">Register</a>
            </div>

            <h3>Create an Account</h3>
            <p>Fill in your details to create an employee account.</p>

            @if ($errors->any())
                <div class="err">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <label for="registerName">Full Name</label>
                <input id="registerName" type="text" name="name" value="{{ old('name') }}" required autofocus>

                <label for="registerEmail">Email Address</label>
                <input id="registerEmail" type="email" name="email" value="{{ old('email') }}" required>

                <label for="registerPassword">Password</label>
                <input id="registerPassword" type="password" name="password" required autocomplete="new-password">

                <label for="registerPasswordConfirmation">Confirm Password</label>
                <input id="registerPasswordConfirmation" type="password" name="password_confirmation" required>

                <div style="margin-top:18px;display:flex;justify-content:flex-end">
                    <button type="submit" class="btn">Register</button>
                </div>
            </form>

        </section>
    </div>
</body>
</html>