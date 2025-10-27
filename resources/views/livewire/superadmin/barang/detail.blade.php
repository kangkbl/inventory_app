<div x-data="{
        openDetailModal: @entangle('showDetailModal').live,
        closeModal() {
            this.openDetailModal = false;
            this.$nextTick(() => $wire.closeDetailModal());
        }
    }" x-cloak x-on:keydown.escape.window="closeModal()">
    <div x-show="openDetailModal" x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 transition-opacity duration-200 ease-out" aria-labelledby="detail-dialog-title"
        role="dialog" aria-modal="true">
        <div x-show="openDetailModal" x-transition.opacity.duration.200ms
            class="absolute inset-0 bg-gray-900/50 transition-opacity duration-200 ease-out" x-on:click="closeModal()"
            aria-hidden="true"></div>

        <div class="relative flex min-h-full items-end justify-center p-4 text-center transition-opacity duration-200 ease-out sm:items-center sm:p-0">
            <div x-show="openDetailModal" x-transition.scale.95.opacity.duration.300ms
                class="relative w-full transform overflow-hidden rounded-lg bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:max-w-xl">
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="white" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 id="detail-dialog-title" class="text-base font-semibold text-white">Detail Barang</h3>
                        </div>
                        <button type="button" class="text-gray-400 transition hover:text-white" x-on:click="closeModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Nama Barang</dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ data_get($detailBarang, 'nama_barang', '-') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Merk</dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ data_get($detailBarang, 'merk', '-') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Kode Barang BMN</dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ data_get($detailBarang, 'kode_barang_bmn', '-') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Kategori</dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ data_get($detailBarang, 'kategori', '-') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Lokasi</dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ data_get($detailBarang, 'lokasi', '-') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Kondisi</dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ data_get($detailBarang, 'kondisi', '-') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Jumlah</dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ data_get($detailBarang, 'jumlah', '-') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Tahun Pengadaan</dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ data_get($detailBarang, 'tahun_pengadaan', '-') }}
                                </dd>
                            </div>
                        </dl>
                        <div>
                            <dl>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Keterangan</dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ data_get($detailBarang, 'keterangan', '-') }}
                                </dd>
                            </dl>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <dl>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Dibuat</dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ data_get($detailBarang, 'created_at', '-') }}
                                </dd>
                            </dl>
                            <dl>
                                <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Diperbarui</dt>
                                <dd class="mt-1 text-sm text-white">
                                    {{ data_get($detailBarang, 'updated_at', '-') }}
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="button"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-600 px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-white/10"
                            x-on:click="closeModal()">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>