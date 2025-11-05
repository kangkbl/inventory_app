<aside id="sidebar"
    class="sidebar fixed inset-y-0 left-0 z-[9999] flex h-screen w-72 -translate-x-full flex-col overflow-y-auto border-r border-gray-200 bg-white px-4 py-6 transition-all duration-300 ease-in-out dark:border-gray-800 dark:bg-black lg:sticky lg:top-0 lg:translate-x-0 lg:w-72">

    <!-- Header logo -->
    <div class="px-2 flex items-center justify-between pb-6">
        <a href="{{ url('/') }}" class="flex items-center gap-3 text-white">
            <span class="items-center inline-flex">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    class="logo w-8 h-8 mr-4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                    aria-label="Asset Tree">
                    <rect x="2.5" y="2.5" width="19" height="19" rx="5" />
                    <path d="M12 16V9M12 9L7.5 6M12 9L16.5 6" />
                    <rect x="5.8" y="16.6" width="2.8" height="2.8" rx="0.5" fill="currentColor"
                        opacity=".85" />
                    <rect x="10.6" y="16.6" width="2.8" height="2.8" rx="0.5" fill="currentColor"
                        opacity=".85" />
                    <rect x="15.4" y="16.6" width="2.8" height="2.8" rx="0.5" fill="currentColor"
                        opacity=".85" />
                </svg>
                <h3 class="logo-title font-bold">InventaryMUX</h3>
                <img class="h-6 dark:hidden" src="/images/logo/logo.svg" alt="GUDANG" />
                <img class="hidden h-6 dark:block" src="/images/logo/logo-dark.svg" alt=" " />
            </span>

            <img class="logo-icon hidden h-6" src="/images/logo/logo-icon.svg" alt=" " />
        </a>
    </div>

    <!-- MENU -->
    <nav class="flex-1 space-y-6">
        @php
            $user = auth()->user();
        @endphp
        <div>
            <h3 class="menu-group-title px-2 text-xs font-semibold uppercase tracking-wide text-gray-400">MENU</h3>
            <ul class="mt-2 space-y-1">
                <li>
                    <a href="{{ route('dashboard') }}" wire:current.exact="bg-gray-100 text-gray-900 dark:bg-white/10"
                        class="minimize-side-bar flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5 @yield('menuDashboard')">
                        <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M5.5 3.25C4.26 3.25 3.25 4.26 3.25 5.5V9c0 1.24 1.01 2.25 2.25 2.25H9A2.25 2.25 0 0 0 11.25 9V5.5C11.25 4.26 10.24 3.25 9 3.25H5.5ZM5.5 12.75C4.26 12.75 3.25 13.76 3.25 15V18.5C3.25 19.74 4.26 20.75 5.5 20.75H9A2.25 2.25 0 0 0 11.25 18.5V15A2.25 2.25 0 0 0 9 12.75H5.5ZM12.75 5.5C12.75 4.26 13.76 3.25 15 3.25H18.5C19.74 3.25 20.75 4.26 20.75 5.5V9c0 1.24-1.01 2.25-2.25 2.25H15A2.25 2.25 0 0 1 12.75 9V5.5ZM15 12.75C13.76 12.75 12.75 13.76 12.75 15V18.5C12.75 19.74 13.76 20.75 15 20.75H18.5C19.74 20.75 20.75 19.74 20.75 18.5V15A2.25 2.25 0 0 0 18.5 12.75H15Z" />
                        </svg>
                        <span class="menu-item-text">Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        @if ($user?->role === 'Super Admin')
            <div>
                <h3 class="menu-group-title px-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Super Admin</h3>
                <ul class="mt-2 space-y-1">
                    <li>
                        <a wire:navigate wire:current="bg-gray-100 text-gray-900 dark:bg-white/10"
                            href="{{ route('superadmin.user.index') }}"
                            class="minimize-side-bar flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5">
                            <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M5.5 3.25C4.26 3.25 3.25 4.26 3.25 5.5V9c0 1.24 1.01 2.25 2.25 2.25H9A2.25 2.25 0 0 0 11.25 9V5.5C11.25 4.26 10.24 3.25 9 3.25H5.5ZM5.5 12.75C4.26 12.75 3.25 13.76 3.25 15V18.5C3.25 19.74 4.26 20.75 5.5 20.75H9A2.25 2.25 0 0 0 11.25 18.5V15A2.25 2.25 0 0 0 9 12.75H5.5ZM12.75 5.5C12.75 4.26 13.76 3.25 15 3.25H18.5C19.74 3.25 20.75 4.26 20.75 5.5V9c0 1.24-1.01 2.25-2.25 2.25H15A2.25 2.25 0 0 1 12.75 9V5.5ZM15 12.75C13.76 12.75 12.75 13.76 12.75 15V18.5C12.75 19.74 13.76 20.75 15 20.75H18.5C19.74 20.75 20.75 19.74 20.75 18.5V15A2.25 2.25 0 0 0 18.5 12.75H15Z" />
                            </svg>
                            <span class="menu-item-text">User</span>
                        </a>
                    </li>

                    <li>
                        <a wire:navigate wire:current="bg-gray-100 text-gray-900 dark:bg-white/10"
                            href="{{ route('superadmin.barang.index') }}"
                            class="minimize-side-bar flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5">
                            <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M3 13l4-4 4 4 6-6 4 4v5H3v-3Z" />
                            </svg>
                            <span class="menu-item-text">Barang</span>
                        </a>
                    </li>

                    <li>
                        <a wire:navigate wire:current="bg-gray-100 text-gray-900 dark:bg-white/10"
                            href="{{ route('superadmin.kategori.index') }}"
                            class="minimize-side-bar flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5">
                            <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5 9h3v10H5V9Zm5-4h3v14h-3V5Zm5 7h3v7h-3v-7Z" />
                            </svg>
                            <span class="menu-item-text">Kategori</span>
                        </a>
                    </li>
                    
                    <li>
                        <a wire:navigate wire:current="bg-gray-100 text-gray-900 dark:bg-white/10"
                            href="{{ route('superadmin.logs.index') }}"
                            class="minimize-side-bar flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5">
                            <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M5.25 4.5A2.25 2.25 0 0 1 7.5 2.25h9A2.25 2.25 0 0 1 18.75 4.5V6H20a.75.75 0 0 1 .75.75v13.5a.75.75 0 0 1-.75.75H8.25a.75.75 0 0 1-.75-.75V18H4.5A2.25 2.25 0 0 1 2.25 15.75v-9A2.25 2.25 0 0 1 4.5 4.5h.75Zm0 1.5a.75.75 0 0 0-.75.75v9c0 .414.336.75.75.75H7.5V6h-2.25Zm3 10.5v3h10.5V7.5H9.75v6a.75.75 0 0 1-.75.75H5.25Z" />
                                <path d="M12 12.75h4.5a.75.75 0 0 0 0-1.5H12a.75.75 0 0 0 0 1.5Zm0-3H16.5a.75.75 0 0 0 0-1.5H12a.75.75 0 0 0 0 1.5Z" />
                            </svg>
                            <span class="menu-item-text">Logs</span>
                        </a>
                    </li>
                </ul>
            </div>
        @elseif ($user?->role === 'Admin')
            <div>
                <h3 class="menu-group-title px-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Admin</h3>
                <ul class="mt-2 space-y-1">
                    <li>
                        <a href="{{ route('admin.barang.index') }}"
                            class="minimize-side-bar flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5 @yield('menuAdminBarang')">
                            <svg class="h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M5.5 3.25h13A2.25 2.25 0 0 1 20.75 5.5v13A2.25 2.25 0 0 1 18.5 20.75h-13A2.25 2.25 0 0 1 3.25 18.5v-13A2.25 2.25 0 0 1 5.5 3.25Zm13 4.083H5.5V8.75h13V7.333Zm0 2.75H15.416v3.833H18.5v-3.833Zm-5.584 0H10.083v3.833h2.833v-3.833ZM8.583 10.083H5.5v3.833h3.083v-3.833Z" />
                            </svg>
                            <span class="menu-item-text">Barang</span>
                        </a>
                    </li>
                </ul>
            </div>
        @endif
    </nav>

    <!-- Promo box -->
    @if ($user?->role === 'Super Admin')
        {{-- ===== Sidebar Extras (ganti promo box) ===== --}}
        @php
            $totalItems = class_exists(\App\Models\Barang::class) ? \App\Models\Barang::count() : 0;
            $field = \Illuminate\Support\Facades\Schema::hasColumn('barangs', 'kondisi') ? 'kondisi' : 'status_barang';

            $baik  = class_exists(\App\Models\Barang::class) ? \App\Models\Barang::whereRaw("LOWER($field) = 'baik'")->count()  : 0;
            $perbaikan = class_exists(\App\Models\Barang::class) ? \App\Models\Barang::whereRaw("LOWER($field) = 'perbaikan'")->count() : 0;
            $rusak = class_exists(\App\Models\Barang::class) ? \App\Models\Barang::whereRaw("LOWER($field) = 'rusak'")->count() : 0;

            $tercatat = $baik + $perbaikan + $rusak;
            $lainnya  = max($totalItems - $tercatat, 0);

            $pct = fn($n) => $totalItems ? round(($n / $totalItems) * 100) : 0;
        @endphp

        <div
            class="rounded-2xl bg-gray-50 p-4 dark:bg-white/5"
            data-snapshot-root
            data-snapshot-endpoint="{{ route('snapshot-kondisi') }}"
        >
            <div class="snapshot-sidebar mb-3 flex items-center justify-between">
                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Snapshot Kondisi</h4>
                <span class="promo-box rounded-md bg-indigo-600/10 px-2 py-0.5 text-[11px] font-medium text-indigo-600 dark:text-indigo-400">
                    <span data-snapshot-total>{{ $totalItems }}</span> items
                </span>
            </div>

            <div
                class="flex flex-col gap-2 text-center"
                data-snapshot-layout
            >
                {{-- BAIK --}}
                <a href="{{ route('superadmin.barang.index', ['kondisi' => 'baik']) }}"
                   class="flex flex-col items-center gap-1 rounded-lg border border-gray-200 p-3 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-white/10"
                   data-snapshot-item>
                    <div class="mb-1 flex items-center justify-center gap-1 text-emerald-600 dark:text-emerald-400">
                        <span class="text-xs font-medium">Baik</span>
                    </div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white" data-snapshot-count="baik">{{ $baik }}</div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400" data-snapshot-percent="baik">{{ $pct($baik) }}%</div>
                </a>

                {{-- CUKUP --}}
                <a href="{{ route('superadmin.barang.index', ['kondisi' => 'rusak']) }}"
                   class="flex flex-col items-center gap-1 rounded-lg border border-gray-200 p-3 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-white/10"
                   data-snapshot-item>
                    <div class="mb-1 flex items-center justify-center gap-1 text-amber-600 dark:text-amber-400">
                        <span class="text-xs font-medium">Perbaikan</span>
                    </div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white" data-snapshot-count="perbaikan">{{ $perbaikan }}</div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400" data-snapshot-percent="perbaikan">{{ $pct($perbaikan) }}%</div>
                </a>

                {{-- RUSAK --}}
                <a href="{{ route('superadmin.barang.index', ['kondisi' => 'perbaikan']) }}"
                   class="flex flex-col items-center gap-1 rounded-lg border border-gray-200 p-3 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-white/10"
                   data-snapshot-item>
                    <div class="mb-1 flex items-center justify-center gap-1 text-rose-600 dark:text-rose-400">
                        <span class="text-xs font-medium">Rusak</span>
                    </div>
                    <div class="text-lg font-bold text-gray-900 dark:text-white" data-snapshot-count="rusak">{{ $rusak }}</div>
                    <div class="text-[11px] text-gray-500 dark:text-gray-400" data-snapshot-percent="rusak">{{ $pct($rusak) }}%</div>
                </a>
            </div>

            {{-- Progress ringkas (opsional) --}}
            <div class="mt-3 h-2 w-full flex overflow-hidden rounded-full bg-gray-200/70 dark:bg-white/10">
    <div class="h-full bg-emerald-500" data-snapshot-bar="baik" style="width: {{ $pct($baik) }}%"></div>
    <div class="h-full bg-amber-500" data-snapshot-bar="perbaikan" style="width: {{ $pct($perbaikan) }}%"></div>
    <div class="h-full bg-rose-500" data-snapshot-bar="rusak" style="width: {{ $pct($rusak) }}%"></div>
</div>


            <div
                class="promo-box mt-2 text-[11px] text-gray-500 dark:text-gray-400 {{ $lainnya > 0 ? '' : 'hidden' }}"
                data-snapshot-lainnya-container
            >
                Lainnya: <span data-snapshot-count="lainnya">{{ $lainnya }}</span> item (label kondisi berbeda)
            </div>
        </div>
    @endif

</aside>