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
                class="relative w-full transform overflow-hidden rounded-lg bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:max-w-lg"
            >
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-3 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="white" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 15.17a4.5 4.5 0 0 1-1.897 1.13L6 17l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l6.932-6.931Zm0 0L19.5 7.125M18 13v4.75A2.25 2.25 0 0 1 15.75 20H5.25A2.25 2.25 0 0 1 3 17.75V7.25A2.25 2.25 0 0 1 5.25 5H9" />
                        </svg>
                        <h3 id="dialog-title" class="text-base font-semibold text-white">Edit User</h3>
                    </div>

                    @if ($errors->any())
                        <div wire:key="edit-form-error-banner"
                            class="mb-3 rounded-md border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form class="mt-4 space-y-4" wire:submit.prevent="update" novalidate>
                        <div>
                            <label for="edit-nama" class="block text-sm text-gray-300">Nama</label>
                            <input id="edit-nama" type="text" autocomplete="name"
                                wire:model.live.debounce.400ms="nama"
                                class="mt-1 w-full rounded-lg border {{ $errors->has('nama') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                placeholder="Masukkan nama">
                            @error('nama')
                                <p wire:key="edit-error-nama" class="mt-1 text-xs text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="edit-email" class="block text-sm text-gray-300">Email</label>
                                <input id="edit-email" type="email" autocomplete="email"
                                    wire:model.live.debounce.400ms="email"
                                    class="mt-1 w-full rounded-lg border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                    placeholder="nama@contoh.id">
                                @error('email')
                                    <p wire:key="edit-error-email" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="edit-role" class="block text-sm text-gray-300">Role</label>
                                <select id="edit-role" wire:model.live="role"
                                    class="mt-1 w-full rounded-lg border {{ $errors->has('role') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white">
                                    <option value="">Pilih role</option>
                                    @foreach ($roleOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <p wire:key="edit-error-role" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" x-on:click="closeModal()"
                                class="rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20">
                                Cancel
                            </button>

                            <button type="submit" class="rounded-md bg-brand-500 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-50"
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