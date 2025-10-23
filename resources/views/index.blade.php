@extends('layouts.app')

@section('title', 'Dashboard')
@section('menuDashboard', 'bg-gray-100 text-gray-900 dark:bg-white/10')

@section('content')
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
            @php
                $totalItems = class_exists(\App\Models\Barang::class) ? \App\Models\Barang::count() : 0;
                $field = \Illuminate\Support\Facades\Schema::hasColumn('barangs', 'kondisi') ? 'kondisi' : 'status_barang';

                $counts = [
                    'baik' => class_exists(\App\Models\Barang::class) ? \App\Models\Barang::whereRaw("LOWER($field) = 'baik'")->count() : 0,
                    'rusak' => class_exists(\App\Models\Barang::class) ? \App\Models\Barang::whereRaw("LOWER($field) = 'rusak'")->count() : 0,
                    'perbaikan' => class_exists(\App\Models\Barang::class) ? \App\Models\Barang::whereRaw("LOWER($field) = 'perbaikan'")->count() : 0,
                ];

                $percentage = static fn(int $value): int => $totalItems > 0 ? (int) round(($value / $totalItems) * 100) : 0;

                $data = [
                    'total' => $totalItems,
                    'baik' => ['count' => $counts['baik'], 'percentage' => $percentage($counts['baik'])],
                    'perbaikan' => ['count' => $counts['perbaikan'], 'percentage' => $percentage($counts['perbaikan'])],
                    'rusak' => ['count' => $counts['rusak'], 'percentage' => $percentage($counts['rusak'])],
                ];
            @endphp

            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Barang</h2>
                <p class="mt-3 text-3xl font-semibold text-gray-900 dark:text-white">{{ $data['total'] }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Semua kategori barang yang tercatat.</p>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Kondisi Baik</h2>
                <p class="mt-3 text-3xl font-semibold text-emerald-600 dark:text-emerald-400">{{ $data['baik']['count'] }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $data['baik']['percentage'] }}% dari total inventaris.</p>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Dalam Perbaikan</h2>
                <p class="mt-3 text-3xl font-semibold text-amber-600 dark:text-amber-400">{{ $data['perbaikan']['count'] }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $data['perbaikan']['percentage'] }}% perlu ditindaklanjuti.</p>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Rusak</h2>
                <p class="mt-3 text-3xl font-semibold text-rose-600 dark:text-rose-400">{{ $data['rusak']['count'] }}</p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $data['rusak']['percentage'] }}% perlu penghapusan stok.</p>
            </article>
        </section>

        <section class="mt-10 grid gap-6 lg:grid-cols-2">
            <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Aktivitas Terbaru</h2>
                    <a href="{{ route('superadmin.user.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">
                        Lihat semua
                    </a>
                </div>
                <ul class="mt-5 space-y-4">
                    <li class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            3 barang baru ditambahkan oleh admin minggu ini.
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            2 barang diperbarui statusnya menjadi perbaikan.
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            Laporan stok bulanan sudah tersedia untuk diunduh.
                        </div>
                    </li>
                </ul>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ringkasan Pengguna</h2>
                    <a href="{{ route('superadmin.user.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">
                        Kelola pengguna
                    </a>
                </div>
                <dl class="mt-6 grid grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-300">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Super Admin</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">2</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Admin</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">5</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Petugas Gudang</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">12</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Pengguna Aktif Bulan Ini</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">86%</dd>
                    </div>
                </dl>
            </article>
        </section>
    </main>
@endsection