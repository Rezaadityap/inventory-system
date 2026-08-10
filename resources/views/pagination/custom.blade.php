@if ($paginator->hasPages())
    @if ($paginator->onFirstPage())
        <span class="pagination-item pagination-item-disabled">&laquo;</span>
    @else
        <button type="button" wire:click="previousPage" wire:loading.attr="disabled" class="pagination-item">&laquo;</button>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="pagination-item pagination-item-disabled">{{ $element }}</span>
        @endif

        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="pagination-item pagination-item-active">{{ $page }}</span>
                @else
                    <button type="button" wire:click="gotoPage({{ $page }})" wire:loading.attr="disabled" class="pagination-item">{{ $page }}</button>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <button type="button" wire:click="nextPage" wire:loading.attr="disabled" class="pagination-item">&raquo;</button>
    @else
        <span class="pagination-item pagination-item-disabled">&raquo;</span>
    @endif
@endif
