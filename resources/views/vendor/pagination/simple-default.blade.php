@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" style="margin-top: 1.5rem; display: flex; justify-content: center;">
        <ul class="pagination" style="display: flex; gap: 0.75rem; list-style: none; margin: 0; padding: 0;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true">
                    <span style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; border: 1px solid #e2e8f0; background: #f8fafc; color: #94a3b8; opacity: 0.6; cursor: not-allowed;">
                        &laquo; Previous
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; border: 1px solid #cbd5e1; background: #ffffff; color: #2563eb; text-decoration: none;">
                        &laquo; Previous
                    </a>
                </li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; border: 1px solid #cbd5e1; background: #ffffff; color: #2563eb; text-decoration: none;">
                        Next &raquo;
                    </a>
                </li>
            @else
                <li class="disabled" aria-disabled="true">
                    <span style="display: inline-flex; align-items: center; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; border: 1px solid #e2e8f0; background: #f8fafc; color: #94a3b8; opacity: 0.6; cursor: not-allowed;">
                        Next &raquo;
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
