<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - NAV Employee Leave Management System</title>
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
        .actions{display:flex;justify-content:flex-end;align-items:center;gap:12px;margin-top:18px}
        .btn{min-height:48px;padding:12px 18px;border:0;border-radius:14px;background:var(--accent);color:#0f1712;font-weight:800;font-size:15px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center}
        .hint{margin-top:18px;padding:14px 16px;border-radius:14px;background:rgba(90,184,139,.09);border:1px solid rgba(90,184,139,.18);color:#cbe8d7;font-size:15px;line-height:1.5}
        .err{margin-top:10px;padding:12px 14px;border-radius:12px;background:rgba(239,123,123,.12);border:1px solid rgba(239,123,123,.18);color:#ffd3d3;font-size:15px}
        .foot{margin-top:16px;display:flex;justify-content:space-between;gap:12px;color:var(--muted);font-size:15px}
        .grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        @media (max-width: 920px){.shell{grid-template-columns:1fr}.hero{min-height:unset}.hero h2{font-size:34px}.feature,.grid{grid-template-columns:1fr}}
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
                <h2>Create your account.</h2>
                <p>New accounts stay pending until HR verifies your employee details and activates access.</p>
            </div>
            <div class="feature">
                <div><strong>Pending review</strong><span>Registration is locked until HR confirms your identity.</span></div>
                <div><strong>Structured flow</strong><span>Profile data follows the same system naming and layout.</span></div>
                <div><strong>Unified UI</strong><span>Design stays consistent with the HR prototype colors.</span></div>
                <div><strong>Leave ready</strong><span>Once activated, balances are seeded automatically.</span></div>
            </div>
        </section>

        <section class="card">
            <div class="tabs">
                <a href="{{ route('login') }}">Log in</a>
                <a class="active" href="{{ route('register') }}">Register</a>
            </div>
            <h3>Register</h3>
            <p>Submit your details and wait for HR verification.</p>

            @if ($errors->any())
                <div class="err">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="grid">
                    <div>
                        <label>Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus>
                    </div>
                    <div>
                        <label>Employee ID</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id') }}" placeholder="EMP-0052">
                    </div>
                    <div>
                        <label>Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div>
                        <label>Password</label>
                        <input type="password" name="password" required autocomplete="new-password">
                    </div>
                    <div style="grid-column:1/-1">
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" required>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn">Register</button>
                </div>
            </form>

            <div class="hint">Your account will appear in HR verification as pending until activated.</div>
            <div class="foot">
                <span>Already have an account?</span>
                <a href="{{ route('login') }}" style="color:var(--accent2);font-weight:700;text-decoration:none">Log in here</a>
            </div>
        </section>
    </div>
</body>
</html>
