<div>
    <div class="p-4 pb-20 md:p-6 md:pb-6">
        <div class="flex items-center gap-3 text-2xl font-bold text-gray-800 dark:text-white lg:pb-6">
            <svg class="h-6 w-6 text-gray-800 dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 7.5a4.5 4.5 0 119 0V9h4.5A3.5 3.5 0 0120 12.5v5A3.5 3.5 0 0116.5 21h-9A4.5 4.5 0 013 16.5V7.5z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 11h4m-2 4h6" />
            </svg>
            <h1>Log Aktivitas Barang</h1>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white lg:p-6 dark:border-gray-800 dark:bg-white/5">
            <div class="flex flex-col gap-5 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Riwayat perubahan data barang
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Pantau siapa yang melakukan perubahan terakhir dan detail perubahannya.
                    </p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.65 5.65a7.5 7.5 0 0010.6 10.6z" />
                            </svg>
                        </span>
                        <input type="search" wire:model.live="search" placeholder="Cari barang, pengguna, atau aksi..."
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-10 pr-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/40" />
                    </div>
                    <div>
                        <select wire:model.live="filterAction"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @foreach ($actions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select wire:model.live="perPage"
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:border-brand-300 focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="10">10 / halaman</option>
                            <option value="25">25 / halaman</option>
                            <option value="50">50 / halaman</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm dark:divide-gray-800">
                        <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="py-3">Waktu</th>
                                <th class="py-3">Barang</th>
                                <th class="py-3">Aksi</th>
                                <th class="py-3">Pengguna</th>
                                <th class="py-3 text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($logs as $log)
                                @php
                                    $actionLabel = $this->formatHistoryAction($log->action);
                                    $changeCount = is_array($log->changes) ? count($log->changes) : 0;
                                @endphp
                                <tr wire:key="log-row-{{ $log->id }}" class="text-gray-700 dark:text-gray-300">
                                    <td class="py-3 align-top text-sm">
                                        {{ optional($log->created_at)->translatedFormat('d F Y H:i') ?? '-' }}
                                    </td>
                                    <td class="py-3 align-top">
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ optional($log->barang)->nama_barang ?? 'Barang tidak tersedia' }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            Kode: {{ optional($log->barang)->kode_barang_bmn ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3 align-top">
                                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                            {{ $actionLabel }}
                                        </span>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ $changeCount }} perubahan
                                        </div>
                                    </td>
                                    <td class="py-3 align-top text-sm">
                                        {{ optional($log->updatedBy)->name ?? 'Tidak diketahui' }}
                                    </td>
                                    <td class="py-3 align-top text-center">
                                        <button type="button"
                                            wire:click="openDetailModal({{ $log->id }})"
                                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-white/10">
                                            Lihat detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Belum ada aktivitas log yang cocok.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>

    <div x-data="{
            open: @entangle('showDetailModal').live,
            close() {
                this.open = false;
                this.$nextTick(() => $wire.closeDetailModal());
            }
        }" x-cloak>
        <div x-show="open" x-transition.opacity.duration.200ms class="fixed inset-0 z-[10000] flex items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open" x-transition.opacity.duration.200ms class="absolute inset-0 bg-gray-900/50" @click="close()"></div>
            <div x-show="open" x-transition.scale.95.opacity.duration.300ms class="relative w-full transform overflow-hidden rounded-lg bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:max-w-xl">
                <div class="flex max-h-[85vh] flex-col">
                    <div class="flex items-center justify-between border-b border-white/5 px-5 py-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-base font-semibold text-white">Detail Log</h3>
                        </div>
                        <button type="button" class="text-gray-400 transition hover:text-white" @click="close()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex-1 space-y-4 overflow-y-auto px-5 py-4 text-sm text-gray-200">
                        <dl class="grid grid-cols-1 gap-y-3 sm:grid-cols-2 sm:gap-6">
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-400">Waktu</dt>
                                <dd class="mt-1 text-white">{{ data_get($detailLog, 'timestamp', '-') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-400">Aksi</dt>
                                <dd class="mt-1 text-white">{{ data_get($detailLog, 'action', '-') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-400">Barang</dt>
                                <dd class="mt-1 text-white">{{ data_get($detailLog, 'barang', '-') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-gray-400">Pengguna</dt>
                                <dd class="mt-1 text-white">{{ data_get($detailLog, 'user', '-') }}</dd>
                            </div>
                        </dl>

                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Perubahan</h4>
                            <div class="mt-2 space-y-3">
                                @php
                                    $detailChanges = data_get($detailLog, 'changes', []);
                                @endphp
                                @if (!empty($detailChanges))
                                    @foreach ($detailChanges as $change)
                                        <div class="rounded-lg border border-white/10 bg-white/5 p-3">
                                            <div class="text-xs font-semibold text-white">{{ $change['label'] ?? '-' }}</div>
                                            <div class="mt-1 text-xs text-gray-300">
                                                <span class="text-gray-500">Dari:</span>
                                                <span class="text-white">{{ $change['old'] ?? '-' }}</span>
                                            </div>
                                            <div class="text-xs text-gray-300">
                                                <span class="text-gray-500">Ke:</span>
                                                <span class="text-white">{{ $change['new'] ?? '-' }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <p class="text-xs text-gray-400">Tidak ada detail perubahan.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-white/5 px-5 py-4">
                        <button type="button" @click="close()"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-200 transition hover:bg-white/10">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>