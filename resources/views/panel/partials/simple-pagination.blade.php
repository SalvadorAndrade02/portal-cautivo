@if ($paginator->hasPages())
<div class="pagination">
    @if ($paginator->onFirstPage())
    <span>Anterior</span>
    @else
    <a href="{{ $paginator->previousPageUrl() }}">
        Anterior
    </a>
    @endif

    <strong>
        Página {{ $paginator->currentPage() }}
        de {{ $paginator->lastPage() }}
    </strong>

    @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}">
        Siguiente
    </a>
    @else
    <span>Siguiente</span>
    @endif
</div>
@endif