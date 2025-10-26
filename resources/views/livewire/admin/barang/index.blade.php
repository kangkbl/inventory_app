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
                        Products List
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Track your store's progress to boost your sales.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button
                        class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
                        Export
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                            fill="none">
                            <path
                                d="M16.667 13.3333V15.4166C16.667 16.1069 16.1074 16.6666 15.417 16.6666H4.58295C3.89259 16.6666 3.33295 16.1069 3.33295 15.4166V13.3333M10.0013 13.3333L10.0013 3.33325M6.14547 9.47942L9.99951 13.331L13.8538 9.47942"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            </path>
                        </svg>
                    </button>
                    <button type="button"
                        wire:click="openCreateModal"                        
                        class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                        <!-- icon -->
                    {{ $addbarang }}
                    </button>
                    
                </div>
            </div>
            @if ($categories->isNotEmpty())
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="selectCategory(null)"
                            class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition focus:outline-hidden focus:ring-2 focus:ring-brand-400 {{ $selectedCategory === null ? 'bg-brand-500 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-300 dark:hover:bg-white/20' }}">
                            Semua
                            <span class="text-xs font-semibold opacity-70">{{ $categories->sum('total') }}</span>
                        </button>
                        @foreach ($categories as $index => $category)
                            <button type="button"
                                wire:key="category-pill-{{ $index }}"
                                wire:click="selectCategory({{ \Illuminate\Support\Js::from($category->kategori) }})"
                                class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium transition focus:outline-hidden focus:ring-2 focus:ring-brand-400 {{ $selectedCategory === $category->kategori ? 'bg-brand-500 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-300 dark:hover:bg-white/20' }}">
                                <span>{{ $category->kategori }}</span>
                                <span class="text-xs font-semibold opacity-70">{{ $category->total }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <div class="flex gap-3 sm:justify-between">
                    <div class="relative flex-1 sm:flex-auto">
                        <span class="absolute top-1/2 left-4 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                            <svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M3.04199 9.37363C3.04199 5.87693 5.87735 3.04199 9.37533 3.04199C12.8733 3.04199 15.7087 5.87693 15.7087 9.37363C15.7087 12.8703 12.8733 15.7053 9.37533 15.7053C5.87735 15.7053 3.04199 12.8703 3.04199 9.37363ZM9.37533 1.54199C5.04926 1.54199 1.54199 5.04817 1.54199 9.37363C1.54199 13.6991 5.04926 17.2053 9.37533 17.2053C11.2676 17.2053 13.0032 16.5344 14.3572 15.4176L17.1773 18.238C17.4702 18.5309 17.945 18.5309 18.2379 18.238C18.5308 17.9451 18.5309 17.4703 18.238 17.1773L15.4182 14.3573C16.5367 13.0033 17.2087 11.2669 17.2087 9.37363C17.2087 5.04817 13.7014 1.54199 9.37533 1.54199Z"
                                    fill=""></path>
                            </svg>
                        </span>
                        <input wire:model.live="search" type="text" placeholder="Search..."
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pr-4 pl-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden sm:w-[300px] sm:min-w-[300px] dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                    </div>
                    <div class="relative" x-data="{ showFilter: false }">
                        <button
                            class="shadow-theme-xs flex h-11 w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 sm:w-auto sm:min-w-[100px] dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"
                            @click="showFilter = !showFilter" type="button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                                fill="none">
                                <path
                                    d="M14.6537 5.90414C14.6537 4.48433 13.5027 3.33331 12.0829 3.33331C10.6631 3.33331 9.51206 4.48433 9.51204 5.90415M14.6537 5.90414C14.6537 7.32398 13.5027 8.47498 12.0829 8.47498C10.663 8.47498 9.51204 7.32398 9.51204 5.90415M14.6537 5.90414L17.7087 5.90411M9.51204 5.90415L2.29199 5.90411M5.34694 14.0958C5.34694 12.676 6.49794 11.525 7.91777 11.525C9.33761 11.525 10.4886 12.676 10.4886 14.0958M5.34694 14.0958C5.34694 15.5156 6.49794 16.6666 7.91778 16.6666C9.33761 16.6666 10.4886 15.5156 10.4886 14.0958M5.34694 14.0958L2.29199 14.0958M10.4886 14.0958L17.7087 14.0958"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round"></path>
                            </svg>
                            Filter
                        </button>
                        <div x-show="showFilter" @click.away="showFilter = false"
                            class="absolute right-0 z-10 mt-2 w-56 rounded-lg border border-gray-200 bg-white p-4 shadow-lg dark:border-gray-700 dark:bg-gray-800"
                            style="display: none;">
                            <div class="mb-5">
                                <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Category
                                </label>
                                <input type="text"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    placeholder="Search category...">
                            </div>
                            <div class="mb-5">
                                <label class="mb-2 block text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Company
                                </label>
                                <input type="text"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
                                    placeholder="Search company...">
                            </div>
                            <button
                                class="bg-brand-500 hover:bg-brand-600 h-10 w-full rounded-lg px-3 py-2 text-sm font-medium text-white">
                                Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class=" max-h-[600px] overflow-x-auto">
                <table class="w-full min-w-[700px] table-auto">
                    <thead class="font-black divide-x divide-y divide-gray-200 dark:divide-gray-800">
                        <tr class="border-b border-gray-200 dark:divide-gray-800 dark:border-gray-800 ">

                            <th class="cursor-pointer py-4 text-left text-xs  text-gray-500 dark:text-gray-400"
                                @click="sortBy('name')">
                                <div class="flex items-center gap-3">
                                    <p class="text-theme-xs text-gray-500 dark:text-gray-400">
                                        No
                                    </p>
                                    <span class="flex flex-col gap-0.5">
                                        <svg :class="sort.key === 'name' & amp; & amp;
                                        sort.asc ? 'text-gray-500 dark:text-gray-400' :
                                            'text-gray-300 dark:text-gray-400/50'"
                                            width="8" height="5" viewBox="0 0 8 5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg" class="text-gray-500 dark:text-gray-400">
                                            <path
                                                d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"
                                                fill="currentColor"></path>
                                        </svg>

                                        <svg :class="sort.key === 'name' & amp; & amp;
                                        !sort.asc ? 'text-gray-500 dark:text-gray-400' :
                                            'text-gray-300 dark:text-gray-400/50'"
                                            width="8" height="5" viewBox="0 0 8 5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="text-gray-300 dark:text-gray-400/50">
                                            <path
                                                d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th class="cursor-pointer py-4 text-left text-xs  text-gray-500 dark:text-gray-400"
                                @click="sortBy('category')">
                                <div class="flex items-center gap-3">
                                    <p class="text-theme-xs  text-gray-500 dark:text-gray-400">
                                        Nama Peralatan
                                    </p>
                                    <span class="flex flex-col gap-0.5">
                                        <svg :class="sort.key === 'category' & amp; & amp;
                                        sort.asc ? 'text-gray-500 dark:text-gray-400' :
                                            'text-gray-300 dark:text-gray-400/50'"
                                            width="8" height="5" viewBox="0 0 8 5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="text-gray-300 dark:text-gray-400/50">
                                            <path
                                                d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"
                                                fill="currentColor"></path>
                                        </svg>

                                        <svg :class="sort.key === 'category' & amp; & amp;
                                        !sort.asc ? 'text-gray-500 dark:text-gray-400' :
                                            'text-gray-300 dark:text-gray-400/50'"
                                            width="8" height="5" viewBox="0 0 8 5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="text-gray-300 dark:text-gray-400/50">
                                            <path
                                                d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th class="cursor-pointer py-4 text-left text-xs  text-gray-500 dark:text-gray-400"
                                @click="sortBy('brand')">
                                <div class="flex items-center gap-3">
                                    <p class="text-theme-xs  text-gray-500 dark:text-gray-400">
                                        Merk
                                    </p>
                                    <span class="flex flex-col gap-0.5">
                                        <svg :class="sort.key === 'brand' & amp; & amp;
                                        sort.asc ? 'text-gray-500 dark:text-gray-400' :
                                            'text-gray-300 dark:text-gray-400/50'"
                                            width="8" height="5" viewBox="0 0 8 5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="text-gray-300 dark:text-gray-400/50">
                                            <path
                                                d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"
                                                fill="currentColor"></path>
                                        </svg>

                                        <svg :class="sort.key === 'brand' & amp; & amp;
                                        !sort.asc ? 'text-gray-500 dark:text-gray-400' :
                                            'text-gray-300 dark:text-gray-400/50'"
                                            width="8" height="5" viewBox="0 0 8 5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="text-gray-300 dark:text-gray-400/50">
                                            <path
                                                d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th class="cursor-pointer py-4 text-left text-xs  text-gray-500 dark:text-gray-400"
                                @click="sortBy('price')">
                                <div class="flex items-center gap-3">
                                    <p class="text-theme-xs  text-gray-500 dark:text-gray-400">
                                        Tahun Pengadaan
                                    </p>
                                    <span class="flex flex-col gap-0.5">
                                        <svg :class="sort.key === 'price' & amp; & amp;
                                        sort.asc ? 'text-gray-500 dark:text-gray-400' : 'text-gray-300'"
                                            width="8" height="5" viewBox="0 0 8 5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg" class="text-gray-300">
                                            <path
                                                d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"
                                                fill="currentColor"></path>
                                        </svg>

                                        <svg :class="sort.key === 'price' & amp; & amp;
                                        !sort.asc ? 'text-gray-500 dark:text-gray-400' : 'text-gray-300'"
                                            width="8" height="5" viewBox="0 0 8 5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg" class="text-gray-300">
                                            <path
                                                d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th class="cursor-pointer py-4 text-left text-xs  text-gray-500 dark:text-gray-400"
                                @click="sortBy('stock')">
                                <div class="flex items-center gap-3">
                                    <p class="text-theme-xs  text-gray-500 dark:text-gray-400">
                                        Jumlah
                                    </p>
                                    <span class="flex flex-col gap-0.5">
                                        <svg :class="sort.key === 'price' & amp; & amp;
                                        sort.asc ? 'text-gray-500 dark:text-gray-400' : 'text-gray-300'"
                                            width="8" height="5" viewBox="0 0 8 5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg" class="text-gray-300">
                                            <path
                                                d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"
                                                fill="currentColor"></path>
                                        </svg>

                                        <svg :class="sort.key === 'price' & amp; & amp;
                                        !sort.asc ? 'text-gray-500 dark:text-gray-400' : 'text-gray-300'"
                                            width="8" height="5" viewBox="0 0 8 5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg" class="text-gray-300">
                                            <path
                                                d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th class="cursor-pointer py-4 text-left text-xs  text-gray-500 dark:text-gray-400"
                                @click="sortBy('condition')">
                                <div class="flex items-center gap-3">
                                    <p class="text-theme-xs  text-gray-500 dark:text-gray-400">
                                        Kondisi
                                    </p>
                                    <span class="flex flex-col gap-0.5">
                                        <svg :class="sort.key === 'price' & amp; & amp;
                                        sort.asc ? 'text-gray-500 dark:text-gray-400' : 'text-gray-300'"
                                            width="8" height="5" viewBox="0 0 8 5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg" class="text-gray-300">
                                            <path
                                                d="M4.40962 0.585167C4.21057 0.300808 3.78943 0.300807 3.59038 0.585166L1.05071 4.21327C0.81874 4.54466 1.05582 5 1.46033 5H6.53967C6.94418 5 7.18126 4.54466 6.94929 4.21327L4.40962 0.585167Z"
                                                fill="currentColor"></path>
                                        </svg>

                                        <svg :class="sort.key === 'price' & amp; & amp;
                                        !sort.asc ? 'text-gray-500 dark:text-gray-400' : 'text-gray-300'"
                                            width="8" height="5" viewBox="0 0 8 5" fill="none"
                                            xmlns="http://www.w3.org/2000/svg" class="text-gray-300">
                                            <path
                                                d="M4.40962 4.41483C4.21057 4.69919 3.78943 4.69919 3.59038 4.41483L1.05071 0.786732C0.81874 0.455343 1.05582 0 1.46033 0H6.53967C6.94418 0 7.18126 0.455342 6.94929 0.786731L4.40962 4.41483Z"
                                                fill="currentColor"></path>
                                        </svg>
                                    </span>
                                </div>
                            </th>
                            <th class="px-5 py-4 text-gray-500 dark:text-gray-400">
                                <svg class="items-center w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true"
                                    xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M1 5h1.424a3.228 3.228 0 0 0 6.152 0H19a1 1 0 1 0 0-2H8.576a3.228 3.228 0 0 0-6.152 0H1a1 1 0 1 0 0 2Zm18 4h-1.424a3.228 3.228 0 0 0-6.152 0H1a1 1 0 1 0 0 2h10.424a3.228 3.228 0 0 0 6.152 0H19a1 1 0 0 0 0-2Zm0 6H8.576a3.228 3.228 0 0 0-6.152 0H1a1 1 0 0 0 0 2h1.424a3.228 3.228 0 0 0 6.152 0H19a1 1 0 0 0 0-2Z" />
                                </svg>
                            </th>

                        </tr>
                    </thead>
                    <tbody class="divide-x divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($barang as $item)
                            <tr class="border-b border-gray-200 dark:divide-gray-800 dark:border-gray-800">
                                <td class="py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ $loop->iteration }}</td>
                                <td class="py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ $item->nama_barang }}</td>
                                <td class="py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ $item->merk }}</td>
                                <td class="py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ $item->tahun_pengadaan }}</td>
                                <td class="py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ $item->jumlah }}</td>
                                <td class="py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ $item->kondisi }}</td>

                                <td class="items-center">
                                    <button
                                        class="bg-yellow-500 hover:bg-yellow-600 inline-flex h-8 w-8 items-center justify-center rounded-lg text-white transition"
                                        type="button"
                                        wire:click="openEditModal({{ $item->id }})">
                                        <i class="fas"><svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </i>
                                    </button>
                                    <button
                                        class="bg-red-500 hover:bg-red-600 inline-flex h-8 w-8 items-center justify-center rounded-lg text-white transition"
                                        type="button"
                                        wire:click="confirmDelete({{ $item->id }})">
                                        <i class="trash">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
</svg>



</i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada barang yang cocok dengan filter saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-5">

                    {{ $barang->links() }}
                </div>
            </div>

        </div>
        @include('livewire.admin.barang.create')
        @include('livewire.admin.barang.edit')
    </div>

</div>
