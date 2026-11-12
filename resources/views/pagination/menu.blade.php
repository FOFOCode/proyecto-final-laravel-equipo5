@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Paginación">
        @if ($paginator->onFirstPage())
            <span class="btn btn-secondary" aria-disabled="true">← Anterior</span>
        @else
            <a class="btn btn-secondary" href="{{ $paginator->previousPageUrl() }}" rel="prev">← Anterior</a>
        @endif
        <span aria-current="page">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a class="btn btn-secondary" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente →</a>
        @else
            <span class="btn btn-secondary" aria-disabled="true">Siguiente →</span>
        @endif
    </nav>
@endif
