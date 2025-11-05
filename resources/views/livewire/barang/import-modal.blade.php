@php
    $importModalKey = $importModalKey ?? 'barang-import';
    $inputId = $importModalKey . '-file';
    $formId = $importModalKey . '-form';
    $summary = $importSummary ?? null;
    $errorsList = $summary['errors'] ?? [];
    $displayErrors = array_slice($errorsList, 0, 5);
    $remainingErrors = max(count($errorsList) - count($displayErrors), 0);
@endphp

<div x-data="{
        openImportModal: @entangle('showImportModal').live,
        closeModal() {
            this.openImportModal = false;
            this.$nextTick(() => {
                this.clearFileInput();
                $wire.cancelImport();
            });
        },
        clearFileInput() {
            if (this.$refs.importFile) {
                this.$refs.importFile.value = '';
            }
        }
    }"
    x-init="$watch('openImportModal', value => { if (!value) { this.clearFileInput(); } })"
    x-effect="if (! $wire.importFile) { this.clearFileInput(); }"
    x-cloak
    x-on:keydown.escape.window="closeModal()"
>
    <div
        x-show="openImportModal"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[10000] transition-opacity duration-200 ease-out"
        aria-labelledby="{{ $importModalKey }}-title"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="openImportModal"
            x-transition.opacity.duration.200ms
            class="absolute inset-0 bg-gray-900/50 transition-opacity duration-200 ease-out"
            x-on:click="closeModal()"
            aria-hidden="true"
        ></div>

        <div class="relative flex min-h-full items-end justify-center p-4 text-center transition-opacity duration-200 ease-out sm:items-center sm:p-0">
            <div
                x-show="openImportModal"
                x-transition.scale.95.opacity.duration.300ms
                class="relative w-full transform overflow-hidden rounded-lg bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:max-w-2xl"
            >
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-4 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke="white" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <h3 id="{{ $importModalKey }}-title" class="text-base font-semibold text-white">Import Data Barang</h3>
                    </div>

                    @if ($errors->has('importFile'))
                        <div wire:key="{{ $importModalKey }}-file-error" class="mb-3 rounded-md border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300">
                            {{ $errors->first('importFile') }}
                        </div>
                    @endif

                    <form id="{{ $formId }}" class="space-y-5" wire:submit.prevent="import" novalidate>
                        <div>
                            <label for="{{ $inputId }}" class="block text-sm text-gray-300">Pilih Berkas CSV</label>
                            <input id="{{ $inputId }}" type="file" wire:model="importFile" accept=".csv,text/csv"
                                x-ref="importFile"
                                class="mt-1 w-full rounded-lg border {{ $errors->has('importFile') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400">
                            <p class="mt-2 text-xs text-gray-400">Berkas maksimal 5MB dengan format CSV.</p>
                        </div>

                        <div class="rounded-lg border border-gray-700 bg-gray-900/60 p-3 text-xs text-gray-300">
                            <p class="font-semibold text-gray-200">Susunan kolom wajib:</p>
                            <p class="mt-1 font-mono text-[11px] text-gray-400">
                                nama_barang, merk, kode_barang_bmn, kategori, lokasi, kondisi, jumlah, tahun_pengadaan, keterangan
                            </p>
                            <p class="mt-2 text-gray-400">Header harus tepat seperti di atas (tidak peka huruf besar kecil). Baris kosong akan diabaikan.</p>
                        </div>

                        @if ($summary)
                            <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 p-4 text-sm text-emerald-100">
                                <h4 class="text-sm font-semibold text-white">Ringkasan Impor</h4>
                                <dl class="mt-3 grid grid-cols-1 gap-2 text-xs sm:grid-cols-3">
                                    <div>
                                        <dt class="font-medium text-gray-300">Diproses</dt>
                                        <dd class="mt-1 text-lg font-semibold text-white">{{ $summary['processed'] ?? 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-300">Berhasil</dt>
                                        <dd class="mt-1 text-lg font-semibold text-white">{{ $summary['created'] ?? 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="font-medium text-gray-300">Dilewati</dt>
                                        <dd class="mt-1 text-lg font-semibold text-white">{{ $summary['skipped'] ?? 0 }}</dd>
                                    </div>
                                </dl>

                                @if (!empty($displayErrors))
                                    <div class="mt-3 rounded-md border border-amber-500/40 bg-amber-500/10 p-3 text-xs text-amber-100">
                                        <p class="font-semibold">Baris yang dilewati:</p>
                                        <ul class="mt-2 space-y-1">
                                            @foreach ($displayErrors as $message)
                                                <li class="list-disc pl-4">{{ $message }}</li>
                                            @endforeach
                                        </ul>
                                        @if ($remainingErrors > 0)
                                            <p class="mt-2 text-amber-200">Terdapat {{ $remainingErrors }} baris lain yang dilewati.</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </form>
                </div>

                <div class="bg-gray-900 px-4 py-4 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="submit" form="{{ $formId }}" wire:target="import,importFile" wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 focus:ring-offset-gray-900 sm:ml-3 sm:w-auto">
                        <span wire:loading.remove wire:target="import,importFile">Mulai Import</span>
                        <span wire:loading wire:target="import,importFile" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                    <button type="button" x-on:click="closeModal()"
                        class="mt-3 inline-flex w-full items-center justify-center rounded-lg border border-gray-600 px-4 py-2 text-sm font-semibold text-gray-200 transition hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-900 sm:mt-0 sm:w-auto">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>