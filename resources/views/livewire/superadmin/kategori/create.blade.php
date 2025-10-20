<div x-data="{
        openCreateModal: @entangle('showCreateModal').live,
        closeModal() {
            this.openCreateModal = false;
            this.$nextTick(() => $wire.cancelCreate());
        }
    }" x-cloak x-on:keydown.escape.window="closeModal()">
    <div x-show="openCreateModal" x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 transition-opacity duration-200 ease-out" aria-labelledby="dialog-title" role="dialog"
        aria-modal="true">
        <div x-show="openCreateModal" x-transition.opacity.duration.200ms
            class="absolute inset-0 bg-gray-900/50 transition-opacity duration-200 ease-out" x-on:click="closeModal()"
            aria-hidden="true"></div>

        <div class="relative flex min-h-full items-end justify-center p-4 text-center transition-opacity duration-200 ease-out sm:items-center sm:p-0">
            <div x-show="openCreateModal" x-transition.scale.95.opacity.duration.300ms
                class="relative w-full transform overflow-hidden rounded-lg bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:max-w-xl">
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-3 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="white" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                        </svg>
                        <h3 id="dialog-title" class="text-base font-semibold text-white">{{ $addCategory }}</h3>
                    </div>

                    @if ($errors->any())
                        <div wire:key="create-category-error-banner"
                            class="mb-3 rounded-md border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form class="mt-4 space-y-4" wire:submit.prevent="store" novalidate>
                        <div>
                            <label for="namaKategori" class="block text-sm text-gray-300">Nama Kategori</label>
                            <input id="namaKategori" type="text" autocomplete="off"
                                wire:model.live.debounce.400ms="namaKategori"
                                class="mt-1 w-full rounded-lg border {{ $errors->has('namaKategori') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                placeholder="Masukkan nama kategori">
                            @error('namaKategori')
                                <p wire:key="error-namaKategori" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="deskripsi" class="block text-sm text-gray-300">Deskripsi (Opsional)</label>
                            <textarea id="deskripsi" rows="4" wire:model.live.debounce.400ms="deskripsi"
                                class="mt-1 w-full rounded-lg border {{ $errors->has('deskripsi') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                placeholder="Tambahkan deskripsi kategori"></textarea>
                            @error('deskripsi')
                                <p wire:key="error-deskripsi" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" x-on:click="closeModal()"
                                class="rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20">
                                Batal
                            </button>

                            <button type="submit"
                                class="rounded-md bg-brand-500 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-50"
                                wire:loading.attr="disabled" wire:target="store" @disabled(!$this->canSave)>
                                <span wire:loading.remove wire:target="store">Simpan</span>
                                <span wire:loading wire:target="store">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>