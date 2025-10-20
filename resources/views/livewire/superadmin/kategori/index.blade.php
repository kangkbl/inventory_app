<div>
    
    <div class="p-4 pb-20 md:p-6 md:pb-6">
                <div class="flex items-center gap-3 text-2xl font-bold text-gray-800 dark:text-white lg:pb-6">
            <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                fill="currentColor" viewBox="0 0 18 20">
                <path d="{{ $iconPath }}">
            </svg>
            <h1>{{ $title }}</h1>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white lg:p-6 dark:border-gray-800 dark:bg-white/[0.03]">

            <div
                class="flex flex-col justify-between gap-5 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        Category List
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Kelola kategori barang untuk memudahkan pengelompokan inventaris.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button type="button"
                        wire:click="openCreateModal"
                        class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M5 10.0002H15.0006M10.0002 5V15.0006" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        {{ $addCategory }}
                    </button>
                </div>
            </div>
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative w-full sm:w-auto sm:min-w-[300px]">
                        <span class="absolute top-1/2 left-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z"
                                    fill=""></path>
                            </svg>
                        </span>
                        <input wire:model.live="search" type="text" placeholder="Cari kategori..."
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                    </div>
            </div>
            <div class="max-h-[600px] overflow-x-auto">
                <table class="w-full min-w-[600px] table-auto">
                    <thead class="divide-y divide-gray-200 dark:divide-gray-800">
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                No
                            </th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Nama Kategori
                            </th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Deskripsi
                            </th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Dibuat
                            </th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($kategoris as $index => $kategori)
                            <tr wire:key="category-row-{{ $kategori->id }}" class="bg-white dark:bg-white/5">
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $kategoris->firstItem() + $index }}
                                </td>
                                <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $kategori->nama }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ $kategori->deskripsi ?? '—' }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    {{ optional($kategori->created_at)->format('d M Y') ?? '—' }}
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <button type="button" wire:click="openEditModal({{ $kategori->id }})"
                                            class="inline-flex items-center gap-1 rounded-md border border-brand-500/40 bg-brand-500/10 px-3 py-2 text-xs font-medium text-brand-500 transition hover:bg-brand-500/20">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125L17.25 4.875" />
                                            </svg>
                                            Edit
                                        </button>
                                        <button type="button" wire:click="delete({{ $kategori->id }})"
                                            class="inline-flex items-center gap-1 rounded-md border border-red-500/40 bg-red-500/10 px-3 py-2 text-xs font-medium text-red-500 transition hover:bg-red-500/20"
                                            onclick="confirm('Hapus kategori ini?') || event.stopImmediatePropagation()">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21a48.108 48.108 0 00-3.478-.397m-12 .562a48.11 48.11 0 013.478-.397m7.5 0V4.042c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0C9.118 1.878 8.208 2.862 8.208 4.042v.916m7.5 0a48.667 48.667 0 00-7.5 0M5.772 5.79l.546 13.884A2.25 2.25 0 008.56 21.75h6.879a2.25 2.25 0 002.244-2.077l.546-13.884" />
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>

                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Belum ada kategori yang tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-5">
                    {{ $kategoris->links() }}

                </div>
            </div>
        </div>
        @include('livewire.superadmin.kategori.create')
        @include('livewire.superadmin.kategori.edit')
    </div>

</div>
