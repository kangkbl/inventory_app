<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | InventaryMUX</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-950">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3 text-gray-900 dark:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-10 w-10" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2.5" y="2.5" width="19" height="19" rx="5" />
                        <path d="M12 16V9M12 9L7.5 6M12 9L16.5 6" />
                        <rect x="5.8" y="16.6" width="2.8" height="2.8" rx="0.5" fill="currentColor" opacity=".85" />
                        <rect x="10.6" y="16.6" width="2.8" height="2.8" rx="0.5" fill="currentColor" opacity=".85" />
                        <rect x="15.4" y="16.6" width="2.8" height="2.8" rx="0.5" fill="currentColor" opacity=".85" />
                    </svg>
                    <div class="text-left">
                        <p class="text-sm font-medium uppercase tracking-wide text-indigo-500">InventaryMUX</p>
                        <p class="text-lg font-semibold">Selamat Datang Kembali</p>
                    </div>
                </a>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Masuk ke akun Anda</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gunakan akun yang sudah terdaftar.</p>

                <form method="POST" action="{{ route('login.attempt') }}" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <div class="mt-1">
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100" />
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                        <div class="mt-1">
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100" />
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-rose-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-950">
                            Ingat saya
                        </label>
                        <span class="text-xs text-gray-400">Hubungi Super Admin jika lupa password.</span>
                    </div>

                    <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900">
                        Masuk
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>