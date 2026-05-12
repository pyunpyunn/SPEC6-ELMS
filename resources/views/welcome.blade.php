<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NAV Employee Leave Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#0f1712;--panel:#18241d;--line:#30453a;--text:#edf5ef;--muted:#a9b8ae;--accent:#5ab88b;--accent2:#86d2af}
        *{box-sizing:border-box} html,body{margin:0;min-height:100%}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:
            radial-gradient(circle at top left, rgba(90,184,139,.18), transparent 28%),
            radial-gradient(circle at bottom right, rgba(134,210,175,.14), transparent 30%),
            linear-gradient(180deg, #0d1410 0%, #111a14 100%);color:var(--text);min-height:100vh;display:grid;place-items:center;padding:24px}
        .shell{width:min(960px,100%);background:rgba(24,36,29,.95);border:1px solid rgba(255,255,255,.08);border-radius:28px;box-shadow:0 28px 90px rgba(0,0,0,.35);padding:34px}
        .top{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .brand{display:flex;align-items:center;gap:12px}
        .mark{width:50px;height:50px;border-radius:16px;background:linear-gradient(135deg,var(--accent),#316f4e);display:grid;place-items:center;font-weight:900}
        h1{margin:0;font-size:20px;letter-spacing:-.03em}
        .hero{display:grid;grid-template-columns:1.1fr .9fr;gap:22px;align-items:center;margin-top:24px}
        .hero h2{margin:0;font-size:48px;line-height:1.02;max-width:10ch;letter-spacing:-.05em}
        .hero p{margin:14px 0 0;color:var(--muted);font-size:17px;line-height:1.7;max-width:55ch}
        .panel{background:rgba(255,255,255,.03);border:1px solid var(--line);border-radius:24px;padding:24px}
        .panel h3{margin:0 0 8px;font-size:22px}
        .panel p{margin:0 0 18px;color:var(--muted);font-size:15px;line-height:1.6}
        .cta{display:flex;gap:12px;flex-wrap:wrap;margin-top:24px}
        a.btn{min-height:48px;padding:12px 18px;border-radius:14px;font-weight:800;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;font-size:15px}
        .btn.primary{background:var(--accent);color:#0f1712}
        .btn.secondary{border:1px solid var(--line);color:var(--text)}
        .cards{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:18px}
        .cards div{padding:14px 15px;border-radius:16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06)}
        .cards strong{display:block;font-size:15px;margin-bottom:4px}
        .cards span{font-size:15px;color:var(--muted)}
        @media (max-width: 860px){.hero{grid-template-columns:1fr}.hero h2{font-size:38px}.cards{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <main class="shell">
        <div class="top">
            <div class="brand">
                <div class="mark">EL</div>
                <div>
                    <h1>NAV Employee Leave Management System</h1>
                    <div style="font-size:15px;color:var(--muted)">HR leave management and verification</div>
                </div>
            </div>
            <div class="cta">
                <a class="btn secondary" href="{{ route('login') }}">Log in</a>
                <a class="btn primary" href="{{ route('register') }}">Register</a>
            </div>
        </div>

        <section class="hero">
            <div>
                <h2>HR workflows, simplified.</h2>
                <p>Manage approvals, employee records, leave balances, notifications, department cards, and yearly reports in one consistent interface.</p>
            </div>
            <div class="panel">
                <h3>Start here</h3>
                <p>Use the buttons to access the secured system. The register flow stays pending until HR activates the account.</p>
                <div class="cards">
                    <div><strong>Login</strong><span>Return to the dashboard and review current work.</span></div>
                    <div><strong>Register</strong><span>Submit your employee details for HR verification.</span></div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
