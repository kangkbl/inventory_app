<div x-data="{
        openCreateModal: @entangle('showCreateModal').live,
        closeModal() {
            this.openCreateModal = false;
            this.$nextTick(() => $wire.cancelCreate());
        }
    }" x-cloak x-on:keydown.escape.window="closeModal()">
    <div
        x-show="openCreateModal"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 transition-opacity duration-200 ease-out"
        aria-labelledby="dialog-title"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="openCreateModal"
            x-transition.opacity.duration.200ms
            class="absolute inset-0 bg-gray-900/50 transition-opacity duration-200 ease-out"
            x-on:click="closeModal()"
            aria-hidden="true"
        ></div>

        <div
            class="relative flex min-h-full items-end justify-center p-4 text-center transition-opacity duration-200 ease-out sm:items-center sm:p-0"
        >
            <div
                x-show="openCreateModal"
                x-transition.scale.95.opacity.duration.300ms
                class="relative w-full transform overflow-hidden rounded-lg bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:max-w-lg"
            >
                <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mb-3 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="white" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                        <h3 id="dialog-title" class="text-base font-semibold text-white">{{ $adduser }}</h3>
                    </div>

                    {{-- Ringkas error paling pertama (opsional) --}}
                    @if ($errors->any())
                        <div wire:key="create-form-error-banner"
                            class="mb-3 rounded-md border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300">                            
                            {{ $errors->first() }}
                        </div>
                    @endif

                    {{-- FORM --}}
                    <form class="mt-4 space-y-4" wire:submit.prevent="store" novalidate>
                        {{-- Nama --}}
                        <div>
                            <label for="nama" class="block text-sm text-gray-300">Nama</label>
                            <input id="nama" type="text" autocomplete="name"
                                wire:model.live.debounce.400ms="nama"
                                class="mt-1 w-full rounded-lg border {{ $errors->has('nama') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                placeholder="Masukkan nama">
                            @error('nama')
                                <p wire:key="error-nama" class="mt-1 text-xs text-red-400">
                                    {{ $message }}
                                </p>                            
                                @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm text-gray-300">Email</label>
                                <input id="email" type="email" autocomplete="email"
                                    wire:model.live.debounce.400ms="email"
                                    class="mt-1 w-full rounded-lg border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white placeholder-gray-400"
                                    placeholder="nama@contoh.id">
                                @error('email')
                                    <p wire:key="error-email" class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Role --}}
                            <div>
                                <label for="role" class="block text-sm text-gray-300">Role</label>
                                <select id="role" wire:model.live="role"
                                    class="mt-1 w-full rounded-lg border {{ $errors->has('role') ? 'border-red-500' : 'border-gray-700' }} bg-gray-900 px-3 py-2 text-sm text-white">
                                    <option value="">Pilih role</option>
                                    <option value="super_admin">Super Admin</option>
                                    <option value="admin">Admin</option>
                                </select>
                                @error('role')
                                    <p wire:key="error-role" 
                                    class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" wire:click="cancelCreate"
                                class="rounded-md bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20">
                                Cancel
                            </button>

                            <button type="submit" class="rounded-md bg-brand-500 px-3 py-2 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-50"
                                wire:loading.attr="disabled" wire:target="store" @disabled(!$this->canSave)>
                                {{-- tombol nonaktif bila belum valid --}}
                                <span wire:loading.remove wire:target="store">Save</span>
                                <span wire:loading wire:target="store">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>