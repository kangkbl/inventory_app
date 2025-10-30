<div x-data="{
        openEditModal: @entangle('showEditModal').live,
        closeModal() {
            this.openEditModal = false;
            this.$nextTick(() => $wire.cancelEdit());
        }
    }" x-cloak x-on:keydown.escape.window="closeModal()">
    <div
        x-show="openEditModal"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 transition-opacity duration-200 ease-out"
        aria-labelledby="dialog-title"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="openEditModal"
            x-transition.opacity.duration.200ms
            class="absolute inset-0 bg-gray-900/50 transition-opacity duration-200 ease-out"
            x-on:click="closeModal()"
            aria-hidden="true"
        ></div>

        <div
            class="relative flex min-h-full items-end justify-center p-4 text-center transition-opacity duration-200 ease-out sm:items-center sm:p-0"
        >
            <div
                x-show="openEditModal"
                x-transition.scale.95.opacity.duration.300ms
                class="relative w-full transform overflow-hidden rounded-lg bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:max-w-2xl"
            >
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-3 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="white" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 15.17a4.5 4.5 0 0 1-1.897 1.13L6 17l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l6.932-6.931Zm0 0L19.5 7.125M18 13v4.75A2.25 2.25 0 0 1 15.75 20H5.25A2.25 2.25 0 0 1 3 17.75V7.25A2.25 2.25 0 0 1 5.25 5H9" />
                        </svg>
                        <h3 id="dialog-title" class="text-base font-semibold text-white">Edit Barang</h3>
                    </div>

                    @if ($errors->any())
                        <div wire:key="edit-item-error-banner"
                            class="mb-3 rounded-md border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form class="mt-4 space-y-4" wire:submit.prevent="update" novalidate>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="edit-namaBarang" class="block text-sm text-gray-300">Nama Barang</label>
                                <input id="edit-namaBarang" type="text" autocomplete="off"
                                    wire:model.live.debounce.400ms="namaBarang"
                                    class="mt-1 w-full rounded-lg border {{ $errors->has('namaBarang') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                    placeholder="Masukkan nama barang">
                                @error('namaBarang')
                                    <p wire:key="edit-error-namaBarang" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="edit-merk" class="block text-sm text-gray-300">Merk</label>
                                <input id="edit-merk" type="text" autocomplete="off"
                                    wire:model.live.debounce.400ms="merk"
                                    class="mt-1 w-full rounded-lg border {{ $errors->has('merk') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                    placeholder="Masukkan merk">
                                @error('merk')
                                    <p wire:key="edit-error-merk" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="edit-kodeBarangBmn" class="block text-sm text-gray-300">Kode Barang BMN</label>
                                <input id="edit-kodeBarangBmn" type="text" autocomplete="off"
                                    wire:model.live.debounce.400ms="kodeBarangBmn"
                                    class="mt-1 w-full rounded-lg border {{ $errors->has('kodeBarangBmn') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                    placeholder="BMN-XXXX">
                                @error('kodeBarangBmn')
                                    <p wire:key="edit-error-kodeBarangBmn" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="edit-kategori" class="block text-sm text-gray-300">Kategori</label>
                                <input id="edit-kategori" type="text" autocomplete="off"
                                    wire:model.live.debounce.400ms="kategori"
                                    class="mt-1 w-full rounded-lg border {{ $errors->has('kategori') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                    placeholder="Masukkan kategori">
                                @error('kategori')
                                    <p wire:key="edit-error-kategori" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label for="edit-lokasi" class="block text-sm text-gray-300">Lokasi</label>
                                <input id="edit-lokasi" type="text" autocomplete="off"
                                    wire:model.live.debounce.400ms="lokasi"
                                    class="mt-1 w-full rounded-lg border {{ $errors->has('lokasi') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                    placeholder="Masukkan lokasi">
                                @error('lokasi')
                                    <p wire:key="edit-error-lokasi" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="edit-kondisi" class="block text-sm text-gray-300">Kondisi</label>
                                <select id="edit-kondisi" wire:model.live="kondisi"
                                    class="mt-1 w-full rounded-lg border {{ $errors->has('kondisi') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white">
                                    <option value="">Pilih kondisi</option>
                                    @foreach ($kondisiOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                                @error('kondisi')
                                    <p wire:key="edit-error-kondisi" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="edit-jumlah" class="block text-sm text-gray-300">Jumlah</label>
                                <input id="edit-jumlah" type="number" min="1" inputmode="numeric"
                                    wire:model.live.debounce.400ms="jumlah"
                                    class="mt-1 w-full rounded-lg border {{ $errors->has('jumlah') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                    placeholder="0">
                                @error('jumlah')
                                    <p wire:key="edit-error-jumlah" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="edit-tahunPengadaan" class="block text-sm text-gray-300">Tahun Pengadaan</label>
                                <input id="edit-tahunPengadaan" type="number" inputmode="numeric"
                                    wire:model.live.debounce.400ms="tahunPengadaan"
                                    class="mt-1 w-full rounded-lg border {{ $errors->has('tahunPengadaan') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                    placeholder="2024">
                                @error('tahunPengadaan')
                                    <p wire:key="edit-error-tahunPengadaan" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="superadmin-edit-photo" class="block text-sm text-gray-300">Foto Barang (Opsional)</label>
                            <input id="superadmin-edit-photo" type="file" wire:model="photo" accept="image/*"
                                class="mt-1 w-full rounded-lg border {{ $errors->has('photo') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400">
                            @error('photo')
                                <p wire:key="superadmin-edit-error-photo" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-400">Apabila tidak diunggah, sistem akan mencoba mencari gambar otomatis berdasarkan nama barang dan merk.</p>

                            @if ($photoPreviewUrl)
                                <div class="mt-3 overflow-hidden rounded-lg border border-gray-700 bg-gray-900">
                                    <img src="{{ $photoPreviewUrl }}" alt="Preview Foto Barang" class="h-48 w-full object-cover">
                                </div>
                            @endif
                        </div>

                        <div>
                            <label for="edit-keterangan" class="block text-sm text-gray-300">Keterangan (Opsional)</label>
                            <textarea id="edit-keterangan" rows="3" wire:model.live.debounce.400ms="keterangan"
                                class="mt-1 w-full rounded-lg border {{ $errors->has('keterangan') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                placeholder="Tambahkan keterangan tambahan"></textarea>
                            @error('keterangan')
                                <p wire:key="edit-error-keterangan" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" x-on:click="closeModal()"
                                class="rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20">
                                Cancel
                            </button>

                            <button type="submit"
                                class="rounded-md bg-brand-500 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-50"
                                wire:loading.attr="disabled" wire:target="update" @disabled(!$this->canSave)>
                                <span wire:loading.remove wire:target="update">Update</span>
                                <span wire:loading wire:target="update">Updating...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>