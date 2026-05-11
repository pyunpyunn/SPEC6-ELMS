<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - NAV Employee Leave Management System</title>
    <style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f3f6f4;font-family:Arial,sans-serif;color:#172b22}.card{width:min(420px,92vw);background:#fff;border:1px solid #dde5e0;border-radius:12px;padding:28px;box-shadow:0 8px 28px rgba(26,75,55,.1)}h1{font-size:22px;margin:0 0 4px;color:#2a6349}p{margin:0 0 22px;color:#78957f;font-size:13px}label{display:block;font-weight:700;font-size:12px;margin:12px 0 6px}input{width:100%;box-sizing:border-box;border:1px solid #dde5e0;border-radius:8px;padding:10px}button{width:100%;margin-top:18px;border:0;border-radius:8px;background:#2a6349;color:#fff;padding:11px;font-weight:800;cursor:pointer}.hint{font-size:12px;color:#42614f;background:#e8f4ee;padding:10px;border-radius:8px;margin-top:14px}.err{color:#b83030;font-size:12px}
    </style>
</head>
<body>
    <form class="card" method="POST" action="{{ route('login') }}">
        @csrf
        <h1>NAV Employee Leave Management System</h1>
        <p>Sign in to continue.</p>
        @if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
        <label>Email</label>
        <input type="email" name="email" value="hr@company.com" required autofocus>
        <label>Password</label>
        <input type="password" name="password" value="password" required>
        <button type="submit">Login</button>
        <div class="hint">Default HR account: hr@company.com / password</div>
    </form>
</body>
</html>
