<!DOCTYPE html>
<html>
<head>
    <title>Register - ELMS</title>
</head>
<body>
    <h2>Create an Account</h2>
    
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div>
            <label>Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus>
        </div>

        <div>
            <label>Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div>
            <label>Password</label>
            <input type="password" name="password" required autocomplete="new-password">
        </div>

        <div>
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <button type="submit">Register</button>
    </form>
</body>
</html>