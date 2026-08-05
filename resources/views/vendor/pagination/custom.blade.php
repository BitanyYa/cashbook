@php
    $paginator = $paginator ?? $notifications ?? $items ?? null;
@endphp

@if ($paginator && $paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" style="margin-top: 1.5rem; display: flex; justify-content: center; align-items: center; width: 100%;">
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; justify-content: center;">
            {{-- Previous Page --}}
            @if ($paginator->onFirstPage())
                <span style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; background-color: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: not-allowed; opacity: 0.6;">
                    &laquo; Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; background-color: #ffffff; color: #2563eb; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.875rem; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    &laquo; Previous
                </a>
            @endif

            {{-- Page Numbers (if available and less than 15 pages) --}}
            @if(method_exists($paginator, 'getUrlRange') && method_exists($paginator, 'lastPage') && $paginator->lastPage() > 1 && $paginator->lastPage() <= 15)
                @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 38px; padding: 0 0.5rem; background-color: #2563eb; color: #ffffff; border: 1px solid #2563eb; border-radius: 8px; font-size: 0.875rem; font-weight: 700;">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" style="display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 38px; padding: 0 0.5rem; background-color: #ffffff; color: #334155; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif

            {{-- Next Page --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; background-color: #ffffff; color: #2563eb; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.875rem; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                    Next &raquo;
                </a>
            @else
                <span style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; background-color: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: not-allowed; opacity: 0.6;">
                    Next &raquo;
                </span>
            @endif
        </div>
    </nav>
@endif
