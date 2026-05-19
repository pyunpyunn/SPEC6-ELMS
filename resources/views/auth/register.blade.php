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
            <label for="registerName">Full Name</label>
            <input id="registerName" type="text" name="name" value="{{ old('name') }}" required autofocus>
        </div>

        <div>
            <label for="registerEmail">Email Address</label>
            <input id="registerEmail" type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div>
            <label for="registerPassword">Password</label>
            <input id="registerPassword" type="password" name="password" required autocomplete="new-password">
        </div>

        <div>
            <label for="registerPasswordConfirmation">Confirm Password</label>
            <input id="registerPasswordConfirmation" type="password" name="password_confirmation" required>
        </div>

        <button type="submit">Register</button>
    </form>
</body>
</html>