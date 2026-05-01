<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ELMS Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- This loads your Tailwind --}}
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 p-4 text-white shadow-lg">
    <div class="container mx-auto flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <span class="font-bold text-xl tracking-tight">ELMS System</span>
            <a href="{{ route('home') }}" class="hover:text-blue-200">Dashboard</a>
        </div>

        <div class="flex items-center space-x-6">
            <span class="text-sm bg-blue-700 px-3 py-1 rounded-full">
                {{ auth()->user()->name }} ({{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }})
            </span>

            <!-- Logout Form -->
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm font-semibold transition">
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>

    <div class="container mx-auto mt-6">
        @yield('content') {{-- This is where the magic happens! --}}
    </div>
</body>
</html>