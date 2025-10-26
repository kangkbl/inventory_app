@extends('layouts.app')

@section('title', 'Dashboard')
@section('menuDashboard', 'bg-gray-100 text-gray-900 dark:bg-white/10')

@section('content')
@extends('layouts.app')

@section('title', 'Dashboard')
@section('menuDashboard', 'bg-gray-100 text-gray-900 dark:bg-white/10')

@section('content')
    @php
        $hasBarangModel = class_exists(\App\Models\Barang::class);
        $barangTableExists = $hasBarangModel && \Illuminate\Support\Facades\Schema::hasTable('barangs');
        $barangRecords = $barangTableExists ? \App\Models\Barang::all() : collect();

        $totalRecords = $barangRecords->count();

        $conditionField = null;
        if ($barangTableExists) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('barangs', 'kondisi')) {
                $conditionField = 'kondisi';
            } elseif (\Illuminate\Support\Facades\Schema::hasColumn('barangs', 'status_barang')) {
                $conditionField = 'status_barang';
            }
        }

        $normalizeCondition = static function ($item) use ($conditionField) {
            if (! $conditionField) {
                return null;
            }

            $value = $item->{$conditionField} ?? null;

            return $value ? strtolower((string) $value) : null;
        };

        $counts = [
            'baik' => $conditionField ? $barangRecords->filter(fn($item) => $normalizeCondition($item) === 'baik')->count() : 0,
            'perbaikan' => $conditionField ? $barangRecords->filter(fn($item) => $normalizeCondition($item) === 'perbaikan')->count() : 0,
            'rusak' => $conditionField ? $barangRecords->filter(fn($item) => $normalizeCondition($item) === 'rusak')->count() : 0,
        ];

        $percentage = static fn (int $value): int => $totalRecords > 0 ? (int) round(($value / $totalRecords) * 100) : 0;

        $totalQuantity = $barangRecords->sum(function ($item) {
            return is_numeric($item->jumlah) ? (int) $item->jumlah : 0;
        });

        $topCategories = $barangRecords
            ->groupBy(fn($item) => $item->kategori ?: 'Tanpa kategori')
            ->map(fn($items, $key) => [
                'label' => $key,
                'total' => $items->sum(fn($item) => is_numeric($item->jumlah) ? (int) $item->jumlah : 0),
            ])
            ->sortByDesc('total')
            ->values()
            ->take(5);

        $topLocations = $barangRecords
            ->groupBy(fn($item) => $item->lokasi ?: 'Tidak diketahui')
            ->map(fn($items, $key) => [
                'label' => $key,
                'total' => $items->sum(fn($item) => is_numeric($item->jumlah) ? (int) $item->jumlah : 0),
            ])
            ->sortByDesc('total')
            ->values()
            ->take(5);

        $recentBarangs = $barangRecords
            ->sortByDesc(function ($item) {
                $reference = $item->updated_at ?? $item->created_at;

                return $reference instanceof \DateTimeInterface ? $reference->getTimestamp() : 0;
            })
            ->take(8);

        $lowStockThreshold = 5;
        $lowStockItems = $barangRecords
            ->filter(fn($item) => is_numeric($item->jumlah) && $item->jumlah <= $lowStockThreshold)
            ->sortBy('jumlah')
            ->take(5);

        $itemsAddedThisMonth = $barangRecords
            ->filter(fn($item) => ($item->created_at?->isSameMonth(now())) ?? false)
            ->count();

        $avgQuantity = $barangRecords->count() > 0
            ? round($barangRecords->avg(fn($item) => is_numeric($item->jumlah) ? (int) $item->jumlah : 0), 1)
            : 0;

        $uniqueCategories = $barangRecords->pluck('kategori')->filter()->unique()->count();
        $uniqueLocations = $barangRecords->pluck('lokasi')->filter()->unique()->count();

        $conditionBadge = static function ($value): array {
            $normalized = $value ? strtolower((string) $value) : null;

            return match ($normalized) {
                'baik' => [
                    'label' => 'Baik',
                    'classes' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                ],
                'perbaikan' => [
                    'label' => 'Perbaikan',
                    'classes' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                ],
                'rusak' => [
                    'label' => 'Rusak',
                    'classes' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300',
                ],
                default => [
                    'label' => $value ?: 'Tidak diketahui',
                    'classes' => 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-300',
                ],
            };
        };

        $conditionColor = static function ($value): string {
            $normalized = $value ? strtolower((string) $value) : null;

            return match ($normalized) {
                'baik' => 'bg-emerald-500',
                'perbaikan' => 'bg-amber-500',
                'rusak' => 'bg-rose-500',
                default => 'bg-indigo-500',
            };
        };
    @endphp

    <main class="flex-1 bg-gray-50 px-4 pb-10 pt-6 dark:bg-gray-950 sm:px-6 lg:px-10">
        <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Ringkasan Inventaris</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Pantau statistik utama dan aktivitas terbaru gudang Anda dari satu tempat.
                </p>
            </div>
            <a href="{{ route('superadmin.barang.index') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-500">
                Lihat Semua Barang
            </a>
        </header>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Barang</h2>
                <p class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">{{ $totalRecords }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Semua entri barang yang tercatat.</p>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Kondisi Baik</h2>
                <p class="mt-3 text-3xl font-semibold text-emerald-600 dark:text-emerald-400">{{ $counts['baik'] }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $percentage($counts['baik']) }}% dari total entri.</p>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Dalam Perbaikan</h2>
                <p class="mt-3 text-3xl font-semibold text-amber-600 dark:text-amber-400">{{ $counts['perbaikan'] }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $percentage($counts['perbaikan']) }}% perlu tindakan.</p>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Rusak</h2>
                <p class="mt-3 text-3xl font-semibold text-rose-600 dark:text-rose-400">{{ $counts['rusak'] }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $percentage($counts['rusak']) }}% memerlukan penghapusan stok.</p>
            </article>
        </section>

        <section class="mt-10 grid gap-6 lg:grid-cols-2">
            <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Aktivitas Terbaru</h2>
                    @if ($recentBarangs->isNotEmpty())
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $recentBarangs->count() }} entri terakhir</span>
                    @endif
                </div>
                <ul class="mt-5 space-y-4">
                    @forelse ($recentBarangs->take(3) as $activity)
                        @php
                            $badge = $conditionBadge($conditionField ? $activity->{$conditionField} : null);
                            $bulletClass = $conditionColor($conditionField ? $activity->{$conditionField} : null);
                        @endphp
                        <li class="flex items-start gap-3">
                            <span class="mt-1 h-2.5 w-2.5 rounded-full {{ $bulletClass }}"></span>
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $activity->nama_barang ?? 'Barang tanpa nama' }}</p>
                                <p class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $activity->kategori ?? 'Kategori tidak tersedia' }}</span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="hidden sm:inline">•</span>
                                        <span class="rounded-full px-2 py-0.5 text-[11px] font-medium {{ $badge['classes'] }}">{{ $badge['label'] }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="hidden sm:inline">•</span>
                                        <span>{{ optional($activity->updated_at ?? $activity->created_at)->diffForHumans() ?? 'Baru saja' }}</span>
                                    </span>
                                </p>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500 dark:text-gray-400">Belum ada aktivitas terbaru yang dapat ditampilkan.</li>
                    @endforelse
                </ul>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Statistik Inventaris</h2>
                </div>
                <dl class="mt-6 grid grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Total Unit</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($totalQuantity, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Kategori Unik</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $uniqueCategories }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Lokasi Aktif</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $uniqueLocations }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Barang Baru Bulan Ini</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $itemsAddedThisMonth }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-gray-500 dark:text-gray-400">Rata-rata Jumlah per Barang</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($avgQuantity, 1, ',', '.') }}</dd>
                    </div>
                </dl>
            </article>
        </section>
        <section id="analytics" class="mt-10">
            <header class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Analitik Persediaan</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Gambaran kategori dan lokasi dengan pergerakan stok tertinggi.</p>
                </div>
            </header>

            <div class="grid gap-6 xl:grid-cols-3">
                <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-2">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Top Kategori Berdasarkan Jumlah Unit</h3>
                    <ul class="mt-4 space-y-4">
                        @forelse ($topCategories as $category)
                            @php
                                $percent = $totalQuantity > 0 ? (int) round(($category['total'] / $totalQuantity) * 100) : 0;
                            @endphp
                            <li>
                                <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-300">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $category['label'] }}</span>
                                    <span>{{ number_format($category['total'], 0, ',', '.') }} unit</span>
                                </div>
                                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
                                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ $percent }}%"></div>
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500 dark:text-gray-400">Belum ada data kategori untuk dianalisis.</li>
                        @endforelse
                    </ul>
                </article>

                <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Lokasi Terpadat</h3>
                    <ul class="mt-4 space-y-4">
                        @forelse ($topLocations as $location)
                            <li class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-300">
                                <span>{{ $location['label'] }}</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ number_format($location['total'], 0, ',', '.') }} unit</span>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500 dark:text-gray-400">Belum ada data lokasi yang tercatat.</li>
                        @endforelse
                    </ul>
                    <div class="mt-6 rounded-xl bg-indigo-50 px-4 py-3 text-xs text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                        Data dihitung otomatis dari seluruh entri barang yang tersedia.
                    </div>
                </article>
            </div>
        </section>

        <section id="stocks" class="mt-10">
            <header class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Status Stok</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Periksa pergerakan stok terbaru dan barang yang perlu diperhatikan.</p>
                </div>
                <a href="{{ route('superadmin.barang.index') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 px-3 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50 dark:border-indigo-500/40 dark:text-indigo-300 dark:hover:bg-indigo-500/10">
                    Kelola Barang
                </a>
            </header>

            <div class="grid gap-6 lg:grid-cols-3">
                <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Aktivitas Stok Terbaru</h3>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm dark:divide-gray-800">
                            <thead>
                                <tr class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    <th class="px-4 py-3 font-semibold">Barang</th>
                                    <th class="px-4 py-3 font-semibold">Kondisi</th>
                                    <th class="px-4 py-3 font-semibold text-center">Jumlah</th>
                                    <th class="px-4 py-3 font-semibold text-right">Terakhir diperbarui</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse ($recentBarangs as $barang)
                                    @php
                                        $badge = $conditionBadge($conditionField ? $barang->{$conditionField} : null);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 align-top">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $barang->nama_barang ?? 'Barang tanpa nama' }}</div>
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $barang->kategori ?? 'Kategori tidak tersedia' }}</div>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium {{ $badge['classes'] }}">{{ $badge['label'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-center align-top text-gray-900 dark:text-gray-200">
                                            {{ is_numeric($barang->jumlah) ? number_format((int) $barang->jumlah, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right align-top text-xs text-gray-500 dark:text-gray-400">
                                            {{ optional($barang->updated_at ?? $barang->created_at)->diffForHumans() ?? 'Belum ada data' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Belum ada data barang yang dapat ditampilkan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Perlu Perhatian</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Barang dengan stok rendah (≤ {{ $lowStockThreshold }} unit).</p>
                    <ul class="mt-4 space-y-4">
                        @forelse ($lowStockItems as $item)
                            @php
                                $badge = $conditionBadge($conditionField ? $item->{$conditionField} : null);
                            @endphp
                            <li class="rounded-xl border border-gray-100 p-4 dark:border-gray-800">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $item->nama_barang ?? 'Barang tanpa nama' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->kategori ?? 'Kategori tidak tersedia' }} • {{ $item->lokasi ?? 'Lokasi tidak diketahui' }}</p>
                                    </div>
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-medium {{ $badge['classes'] }}">{{ $badge['label'] }}</span>
                                </div>
                                <div class="mt-3 flex items-center justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Jumlah</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ is_numeric($item->jumlah) ? number_format((int) $item->jumlah, 0, ',', '.') : '-' }} unit</span>
                                </div>
                            </li>
                        @empty
                            <li class="rounded-xl bg-gray-50 px-4 py-6 text-sm text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                Semua stok berada di atas ambang batas aman.
                            </li>
                        @endforelse
                    </ul>
                </article>
            </div>
        </section>
    </main>
@endsection