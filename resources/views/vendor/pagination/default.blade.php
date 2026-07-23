@if ($paginator->lastPage() > 1)
    <nav role="navigation" aria-label="pagination" class="mx-auto flex w-full justify-center">
        <ul class="flex flex-row items-center gap-1">
            @if ($paginator->onFirstPage())
                <li><span class="btn" data-variant="ghost" data-size="sm" aria-disabled="true"><x-icon name="chevron-left" class="size-4" /> <span>Previous</span></span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" class="btn" data-variant="ghost" data-size="sm" rel="prev"><x-icon name="chevron-left" class="size-4" /> <span>Previous</span></a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><div class="size-9 flex items-center justify-center"><x-icon name="ellipsis" class="size-4" /></div></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><a href="{{ $url }}" class="btn" data-variant="outline" data-size="icon" aria-current="page">{{ $page }}</a></li>
                        @else
                            <li><a href="{{ $url }}" class="btn" data-variant="ghost" data-size="icon">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" class="btn" data-variant="ghost" data-size="sm" rel="next"><span>Next</span> <x-icon name="chevron-right" class="size-4" /></a></li>
            @else
                <li><span class="btn" data-variant="ghost" data-size="sm" aria-disabled="true"><span>Next</span> <x-icon name="chevron-right" class="size-4" /></span></li>
            @endif
        </ul>
    </nav>
@endif
