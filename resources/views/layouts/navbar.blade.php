<header class="sticky top-0 z-[9999] w-full border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
    <div class="flex items-center justify-between px-3 py-3 lg:px-6 lg:py-4">
        <!-- Kiri: Burger + Logo mobile -->
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Toggle Sidebar -->
            <button id="btn-sidebar-toggle"
                class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 lg:h-11 lg:w-11 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-800"
                aria-label="Toggle sidebar">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M4 6.75h16v1.5H4v-1.5ZM4 11.25h16v1.5H4v-1.5ZM4 15.75h16v1.5H4v-1.5Z" />
                </svg>
            </button>

            <!-- Logo mobile -->
            <a href="{{ url('/') }}" class="lg:hidden">
                <img class="h-6 dark:hidden" src="/images/logo/logo.svg" alt="Logo" />
                <img class="hidden h-6 dark:block" src="/images/logo/logo-dark.svg" alt="Logo" />
            </a>
        </div>

        <!-- Kanan: Actions -->
        <div class="flex items-center gap-2 lg:gap-3">
            <!-- Toggle actions (mobile) -->
            <button id="btn-menu-toggle"
                class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-700 hover:bg-gray-100 lg:hidden dark:text-gray-400 dark:hover:bg-gray-800"
                aria-label="Toggle topbar menu" aria-expanded="false">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M6 10.5A1.5 1.5 0 1 1 6 13.5 1.5 1.5 0 0 1 6 10.5Zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm6 0a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Z" />
                </svg>
            </button>

            <!-- Actions container -->
            <div id="topbar-actions" class="hidden w-full items-center justify-end gap-2 lg:flex">
                <!-- Local Time -->
                <div id="clock-pill"
                    class="flex h-11 items-center rounded-full border border-gray-200 bg-white px-4 text-sm font-medium text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                    aria-live="polite" title="Local time">
                    <svg class="mr-2 h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path
                            d="M12 2.75a9.25 9.25 0 1 0 0 18.5 9.25 9.25 0 0 0 0-18.5Zm.75 4.75a.75.75 0 0 0-1.5 0v5.19l-3.03 3.03a.75.75 0 1 0 1.06 1.06l3.22-3.22c.15-.15.25-.36.25-.59V7.5Z" />
                    </svg>
                    <time id="local-time" class="font-mono">--:--:--</time>
                    <span id="tz-abbr" class="ml-2 hidden sm:inline text-xs opacity-70"></span>
                </div>


                <!-- Notification -->
                @php
                    $user = auth()->user();
                    $displayName = $user?->name ?? 'Pengguna';
                    $displayEmail = $user?->email ?? 'pengguna@example.com';
                    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($displayName) . '&background=4f46e5&color=fff';
                @endphp
                <div class="relative">
                    <button id="btn-notif"
                        class="relative flex h-11 w-11 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
                        aria-haspopup="menu" aria-expanded="false" aria-controls="panel-notif">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M10.75 2.292a.75.75 0 1 0-1.5 0v.544A6.375 6.375 0 0 0 3.625 9.167v5.292H3.333a.75.75 0 0 0 0 1.5H16.667a.75.75 0 0 0 0-1.5H16.375V9.167A6.375 6.375 0 0 0 10.75 2.836v-.544ZM8 17.709a.75.75 0 0 0 .75.75h2.5a.75.75 0 0 0 0-1.5h-2.5a.75.75 0 0 0-.75.75Z" />
                        </svg>
                    </button>
                    <div id="panel-notif"
                        class="invisible absolute right-0 mt-4 w-[350px] max-w-[90vw] translate-y-2 rounded-2xl border border-gray-200 bg-white p-3 opacity-0 shadow-xl transition-all duration-150 dark:border-gray-800 dark:bg-gray-900"
                        role="menu" aria-labelledby="btn-notif">
                        <div
                            class="mb-3 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                            <h5 class="text-lg font-semibold text-gray-800 dark:text-white/90">Notification</h5>
                            <button
                                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                data-close="#panel-notif" aria-label="Close">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="m6.34 7.75 4.25 4.25-4.25 4.25 1.41 1.41 4.25-4.25 4.25 4.25 1.41-1.41-4.25-4.25 4.25-4.25-1.41-1.41-4.25 4.25L7.75 6.34 6.34 7.75Z" />
                                </svg>
                            </button>
                        </div>
                        <ul class="max-h-[360px] space-y-2 overflow-y-auto">
                            <li>
                                <a class="flex gap-3 rounded-lg p-3 hover:bg-gray-100 dark:hover:bg-white/5"
                                    href="#">
                                    <span class="relative block h-10 w-10 shrink-0">
                                        <img src="/images/user/user-02.jpg" alt="User"
                                            class="h-10 w-10 rounded-full object-cover" />
                                        <span
                                            class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border border-white bg-green-500 dark:border-gray-900"></span>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block text-sm text-gray-700 dark:text-gray-300"><strong
                                                class="text-gray-900 dark:text-white">Terry Franci</strong> updated
                                            <strong class="text-gray-900 dark:text-white">Nganter App</strong></span>
                                        <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">5 min
                                            ago</span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                        <a href="#"
                            class="mt-3 flex justify-center rounded-lg border border-gray-300 bg-white p-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/5">View
                            All</a>
                    </div>
                </div>

                <!-- User -->
                <div class="relative">
                    <button id="btn-user"
                        class="flex items-center rounded-lg px-2 py-1 text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                        aria-haspopup="menu" aria-expanded="false" aria-controls="panel-user">
                        <span class="mr-2 h-9 w-9 overflow-hidden rounded-full">
                            <img src="{{ $avatarUrl }}" alt="{{ $displayName }}" class="h-9 w-9 object-cover" />
                        </span>
                        <span class="hidden text-sm font-medium sm:block">{{ $displayName }}</span>
                        <svg class="ml-1 h-5 w-5 stroke-gray-500 dark:stroke-gray-400" viewBox="0 0 18 20"
                            fill="none">
                            <path d="M4.3125 8.65625L9 13.3437L13.6875 8.65625" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                    <div id="panel-user"
                        class="invisible absolute right-0 mt-3 w-64 translate-y-2 rounded-2xl border border-gray-200 bg-white p-3 opacity-0 shadow-xl transition-all duration-150 dark:border-gray-800 dark:bg-gray-900"
                        role="menu" aria-labelledby="btn-user">
                        <div>
                            <span class="block text-sm font-medium text-gray-800 dark:text-gray-300">{{ $displayName }}</span>
                            <span
                                class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">{{ $displayEmail }}</span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="mt-3">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                                <svg class="h-6 w-6 fill-gray-500" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M15.1 19.247a.75.75 0 0 1-.75-.75V14.245h-1.5v4.252c0 1.243 1.007 2.25 2.25 2.25H18.5a2.25 2.25 0 0 0 2.25-2.25V5.496A2.25 2.25 0 0 0 18.5 3.246h-3.4a2.25 2.25 0 0 0-2.25 2.25v4.249h1.5V5.496a.75.75 0 0 1 .75-.75H18.5a.75.75 0 0 1 .75.75V18.497a.75.75 0 0 1-.75.75H15.1ZM3.251 11.998c0 .216.091.41.238.548l4.607 4.61a.75.75 0 1 0 1.062-1.059L6.81 12.748H16a.75.75 0 0 0 0-1.5H6.815l2.343-2.343a.75.75 0 1 0-1.061-1.061l-4.572 4.572a.75.75 0 0 0-.274.582Z" />
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /Actions -->
        </div>
    </div>
</header>
