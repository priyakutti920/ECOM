<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    @php $loginFavicon = \App\Models\StoreSetting::getFaviconUrl(); @endphp
    <link rel="shortcut icon" href="{{ $loginFavicon }}">
    <link rel="icon" href="{{ $loginFavicon }}">
    <link rel="apple-touch-icon" href="{{ $loginFavicon }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-indigo-900 flex items-center justify-center px-4">

    <div class="w-full max-w-sm">

        {{-- Card --}}
        <div class="bg-white rounded-xl shadow-lg p-8">

            <h1 class="text-2xl font-bold text-gray-900 text-center mb-8">Admin Login</h1>

            {{-- Error --}}
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600">{{ $errors->first() }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login', [], false) }}" novalidate>
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email Address
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="admin@example.com"
                        class="w-full px-4 py-2.5 rounded-lg border @error('email') border-red-400 bg-red-50 @else border-gray-300 bg-white @enderror text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    >
                </div>

                {{-- Password with toggle eye --}}
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full px-4 py-2.5 pr-11 rounded-lg border border-gray-300 bg-white text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                        >
                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 focus:outline-none"
                            tabindex="-1"
                            aria-label="Toggle password visibility"
                        >
                            {{-- Eye open icon (shown when password is hidden) --}}
                            <svg id="eyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{-- Eye slash icon (shown when password is visible) --}}
                            <svg id="eyeSlash" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Remember me --}}
                <div class="flex items-center mb-6">
                    <input
                        type="checkbox"
                        id="remember"
                        name="remember"
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                    >
                    <label for="remember" class="ml-2 text-sm text-gray-600">Remember me</label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-2.5 px-4 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Sign In
                </button>
            </form>
        </div>
    </div>

    <script>
        const toggle = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeSlash = document.getElementById('eyeSlash');

        toggle.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeOpen.classList.toggle('hidden', isPassword);
            eyeSlash.classList.toggle('hidden', !isPassword);
        });
    </script>

</body>
</html>
