@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md dark:bg-gray-900 dark:text-gray-400 dark:border-gray-700">
                    {!! __('pagination.previous') !!}
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                    dusk="previousPage{{ ucfirst($paginator->getPageName()) }}.button"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring focus:ring-brand-500/50 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 dark:hover:text-white">
                    {!! __('pagination.previous') !!}
                </button>
            @endif

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                    dusk="nextPage{{ ucfirst($paginator->getPageName()) }}.button"
                    class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring focus:ring-brand-500/50 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 dark:hover:text-white">
                    {!! __('pagination.next') !!}
                </button>
            @else
                <span class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5 rounded-md dark:bg-gray-900 dark:text-gray-400 dark:border-gray-700">
                    {!! __('pagination.next') !!}
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700 leading-5 dark:text-gray-300">
                    {!! __('Showing') !!}
                    <span class="font-medium">{{ $paginator->firstItem() }}</span>
                    {!! __('to') !!}
                    <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    {!! __('of') !!}
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                @php
                    $visible = 7;
                    $current = $paginator->currentPage();
                    $last = $paginator->lastPage();
                    $visible = min($visible, $last);
                    $start = max(1, $current - intdiv($visible - 1, 2));
                    $end = $start + $visible - 1;
                    if ($end > $last) {
                        $end = $last;
                        $start = max(1, $end - $visible + 1);
                    }
                @endphp

                <span class="relative z-0 inline-flex rounded-md shadow-sm isolate">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}"
                            class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 cursor-default rounded-l-md leading-5 dark:bg-gray-900 dark:text-gray-500 dark:border-gray-700">
                            <span class="sr-only">{{ __('pagination.previous') }}</span>
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </span>
                    @else
                        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                            dusk="previousPage{{ ucfirst($paginator->getPageName()) }}.button"
                            rel="prev" aria-label="{{ __('pagination.previous') }}"
                            class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md leading-5 hover:text-gray-400 focus:z-20 focus:outline-none focus:ring focus:ring-brand-500/50 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 dark:hover:text-white">
                            <span class="sr-only">{{ __('pagination.previous') }}</span>
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                    @endif

                    {{-- Pagination Elements --}}
                    @for ($page = $start; $page <= $end; $page++)
                        @if ($page == $current)
                            <span aria-current="page"
                                class="relative -ml-px inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-brand-500 border border-brand-500 leading-5 focus:z-20 focus:outline-none focus:ring focus:ring-brand-500/50 dark:bg-brand-500 dark:border-brand-500">
                                {{ $page }}
                            </span>
                        @else
                            <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                class="relative -ml-px inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 leading-5 hover:text-gray-800 focus:z-20 focus:outline-none focus:ring focus:ring-brand-500/50 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 dark:hover:text-white"
                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </button>
                        @endif
                    @endfor

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"
                            dusk="nextPage{{ ucfirst($paginator->getPageName()) }}.button"
                            rel="next" aria-label="{{ __('pagination.next') }}"
                            class="relative -ml-px inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md leading-5 hover:text-gray-400 focus:z-20 focus:outline-none focus:ring focus:ring-brand-500/50 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150 dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700 dark:hover:text-white">
                            <span class="sr-only">{{ __('pagination.next') }}</span>
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('pagination.next') }}"
                            class="relative -ml-px inline-flex items-center px-2 py-2 text-sm font-medium text-gray-400 bg-white border border-gray-300 cursor-default rounded-r-md leading-5 dark:bg-gray-900 dark:text-gray-500 dark:border-gray-700">
                            <span class="sr-only">{{ __('pagination.next') }}</span>
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif