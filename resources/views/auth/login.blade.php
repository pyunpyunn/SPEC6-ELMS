<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NAV Employee Leave Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#0f1712;--panel:#17241d;--panel2:#1f2d25;--line:#30453a;--text:#edf5ef;--muted:#a7b9ae;--accent:#5ab88b;--accent2:#86d2af;--danger:#ef7b7b}
        *{box-sizing:border-box} html,body{margin:0;min-height:100%} body{font-family:'Plus Jakarta Sans',sans-serif;color:var(--text);background:
            radial-gradient(circle at top left, rgba(90,184,139,.18), transparent 32%),
            radial-gradient(circle at top right, rgba(134,210,175,.16), transparent 28%),
            linear-gradient(180deg, #0d1410 0%, #111a14 100%);min-height:100vh;display:grid;place-items:center;padding:24px}
        .shell{width:min(1080px,100%);display:grid;grid-template-columns:1.1fr .9fr;gap:18px;align-items:stretch}
        .hero,.card{border:1px solid rgba(255,255,255,.08);background:linear-gradient(180deg, rgba(23,36,29,.96), rgba(17,25,20,.96));box-shadow:0 20px 60px rgba(0,0,0,.28);border-radius:24px}
        .hero{padding:34px;display:flex;flex-direction:column;justify-content:space-between;min-height:620px;position:relative;overflow:hidden}
        .hero::after{content:'';position:absolute;inset:auto -120px -120px auto;width:280px;height:280px;border-radius:50%;background:radial-gradient(circle, rgba(90,184,139,.18), transparent 70%);filter:blur(10px)}
        .brand{display:flex;align-items:center;gap:12px}
        .mark{width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,var(--accent),#32744f);display:grid;place-items:center;color:#fff;font-weight:900}
        .brand h1{margin:0;font-size:18px;letter-spacing:-.03em}
        .hero h2{margin:28px 0 10px;font-size:42px;line-height:1.02;letter-spacing:-.05em;max-width:10ch}
        .hero p{margin:0;color:var(--muted);font-size:16px;line-height:1.7;max-width:54ch}
        .feature{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:28px;position:relative;z-index:1}
        .feature div{padding:14px 15px;border-radius:16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06)}
        .feature strong{display:block;font-size:15px;margin-bottom:4px}
        .feature span{font-size:15px;color:var(--muted)}
        .card{padding:30px}
        .tabs{display:flex;gap:10px;margin-bottom:24px}
        .tabs a{flex:1;text-align:center;padding:12px 16px;border-radius:999px;text-decoration:none;font-weight:700;font-size:15px;border:1px solid var(--line);color:var(--muted);background:rgba(255,255,255,.02)}
        .tabs a.active{background:var(--accent);border-color:var(--accent);color:#0f1712}
        .card h3{margin:0 0 6px;font-size:28px;letter-spacing:-.04em}
        .card p{margin:0 0 22px;color:var(--muted);font-size:15px;line-height:1.6}
        label{display:block;font-size:15px;font-weight:700;margin:14px 0 8px}
        input{width:100%;min-height:48px;padding:12px 14px;border-radius:14px;border:1px solid var(--line);background:var(--panel2);color:var(--text);font-size:15px;outline:none}
        input:focus{border-color:var(--accent);box-shadow:0 0 0 4px rgba(90,184,139,.14)}
        .actions{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:18px}
        .btn{min-height:48px;padding:12px 18px;border:0;border-radius:14px;background:var(--accent);color:#0f1712;font-weight:800;font-size:15px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}
        .btn.secondary{background:transparent;color:var(--text);border:1px solid var(--line)}
        .hint{margin-top:18px;padding:14px 16px;border-radius:14px;background:rgba(90,184,139,.09);border:1px solid rgba(90,184,139,.18);color:#cbe8d7;font-size:15px;line-height:1.5}
        .err{margin-top:10px;padding:12px 14px;border-radius:12px;background:rgba(239,123,123,.12);border:1px solid rgba(239,123,123,.18);color:#ffd3d3;font-size:15px}
        .foot{margin-top:16px;display:flex;justify-content:space-between;gap:12px;color:var(--muted);font-size:15px}
        @media (max-width: 920px){.shell{grid-template-columns:1fr}.hero{min-height:unset}.hero h2{font-size:34px}.feature{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <div class="shell">
        <section class="hero">
            <div>
                <div class="brand">
                    <div class="mark">EL</div>
                    <div>
                        <h1>NAV Employee Leave Management System</h1>
                        <div style="font-size:15px;color:var(--muted)">HR operations, leave control, and verification</div>
                    </div>
                </div>
                <h2>One workspace for leave management.</h2>
                <p>Access dashboard summaries, approve requests, manage employee records, and track leave balances in a cleaner HR-focused interface.</p>
            </div>
            <div class="feature">
                <div><strong>HR review</strong><span>Leave requests and verification in one control surface.</span></div>
                <div><strong>Balance tracking</strong><span>Fast access to sick, vacation, and approved leave data.</span></div>
                <div><strong>Unified UI</strong><span>Light and dark modes stay consistent with the prototype.</span></div>
                <div><strong>Secure access</strong><span>Registration stays pending until HR activates the account.</span></div>
            </div>
        </section>

        <section class="card">
            <div class="tabs">
                <a class="active" href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Register</a>
            </div>
            <h3>Welcome back</h3>
            <p>Sign in with your employee ID or email address.</p>

            @if ($errors->any())
                <div class="err">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <label>Employee ID or Email</label>
                <input type="text" name="email" value="{{ old('email', 'HR-2000-001') }}" required autofocus>

                <label>Password</label>
                <div style="position: relative; display: flex; align-items: center;">
                    <input type="password" id="password" name="password" value="password" required style="padding-right: 40px;">
                    <button type="button" onclick="togglePasswordVisibility()" style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: var(--muted); display: flex; align-items: center;">
                        <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <svg id="eyeOffIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; display: none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                            <line x1="1" y1="1" x2="23" y2="23"></line>
                        </svg>
                    </button>
                </div>

                <div class="actions">
                    <label style="display:flex;align-items:center;gap:8px;margin:0;font-size:15px;font-weight:600;color:var(--muted)">
                        <input type="checkbox" name="remember" style="width:16px;min-height:auto"> Remember me
                    </label>
                    <button type="submit" class="btn">Log in</button>
                </div>
            </form>

            <div class="hint">Default HR account: <strong>HR-2000-001</strong> or <strong>hr@company.com</strong> / <strong>password</strong>.</div>
            <div class="foot">
                <span>Need an account?</span>
                <a href="{{ route('register') }}" style="color:var(--accent2);font-weight:700;text-decoration:none">Register here</a>
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