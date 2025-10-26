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

        <section class="mt-10 grid gap-6 lg:grid-cols-3">
            <article class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Aktivitas Terbaru</h2>
                    @if ($recentBarangs->isNotEmpty())
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $recentBarangs->count() }} entri terakhir</span>
                    @endif
                </div>
                <ul class="mt-5 space-y-4">
                    @forelse ($recentBarangs->take(5) as $activity)
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
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Lokasi Terpadat</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lima lokasi dengan jumlah unit terbanyak.</p>
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
            </article>
        </section>
        

             <section class="mt-10">
            <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Perlu Perhatian</h2>
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
        </section>
    </main>
@endsection