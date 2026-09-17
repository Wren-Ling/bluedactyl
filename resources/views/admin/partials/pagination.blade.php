@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        if ($lastPage <= 4) {
            $pages = range(1, $lastPage);
        } elseif ($currentPage <= 2) {
            $pages = [1, 2, 3, 'ellipsis', $lastPage];
        } elseif ($currentPage >= $lastPage - 1) {
            $pages = [1, 'ellipsis', $lastPage - 2, $lastPage - 1, $lastPage];
        } else {
            $pages = [1, 'ellipsis-start', $currentPage, 'ellipsis-end', $lastPage];
        }
    @endphp

    <nav role="navigation" aria-label="分页导航" class="mx-auto flex w-full justify-center">
        <ul class="flex flex-row items-center gap-1">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="btn opacity-50" data-variant="ghost" data-size="icon" aria-disabled="true" aria-label="上一页">
                        <x-icon name="chevron-left" class="size-4" />
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="btn" data-variant="ghost" data-size="icon" rel="prev" aria-label="上一页">
                        <x-icon name="chevron-left" class="size-4" />
                    </a>
                @endif
            </li>

            @foreach ($pages as $page)
                @if (is_string($page))
                    <li>
                        <span class="flex size-9 items-center justify-center" aria-hidden="true">
                            <x-icon name="ellipsis" class="size-4" />
                        </span>
                    </li>
                @elseif ($page === $currentPage)
                    <li>
                        <a href="{{ $paginator->url($page) }}" class="btn" data-variant="outline" data-size="icon" aria-current="page" aria-label="第 {{ $page }} 页">{{ $page }}</a>
                    </li>
                @else
                    <li>
                        <a href="{{ $paginator->url($page) }}" class="btn" data-variant="ghost" data-size="icon" aria-label="第 {{ $page }} 页">{{ $page }}</a>
                    </li>
                @endif
            @endforeach

            <li>
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="btn" data-variant="ghost" data-size="icon" rel="next" aria-label="下一页">
                        <x-icon name="chevron-right" class="size-4" />
                    </a>
                @else
                    <span class="btn opacity-50" data-variant="ghost" data-size="icon" aria-disabled="true" aria-label="下一页">
                        <x-icon name="chevron-right" class="size-4" />
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif
