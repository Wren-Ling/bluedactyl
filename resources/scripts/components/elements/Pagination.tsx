import { ChevronLeft, ChevronRight } from 'lucide-react';

import { Button } from '@/components/ui/button';

import { PaginatedResult } from '@/api/http';

interface RenderFuncProps<T> {
    items: T[];
    isLastPage: boolean;
    isFirstPage: boolean;
}

interface Props<T> {
    data: PaginatedResult<T>;
    showGoToLast?: boolean;
    showGoToFirst?: boolean;
    onPageSelect: (page: number) => void;
    children: (props: RenderFuncProps<T>) => React.ReactNode;
}

function Pagination<T>({ data: { items, pagination }, onPageSelect, children }: Props<T>) {
    const isFirstPage = pagination.currentPage === 1;
    const isLastPage = pagination.currentPage >= pagination.totalPages;

    const pages = [];

    // Start two spaces before the current page. If that puts us before the starting page default
    // to the first page as the starting point.
    const start = Math.max(pagination.currentPage - 2, 1);
    const end = Math.min(pagination.totalPages, pagination.currentPage + 5);

    for (let i = start; i <= end; i++) {
        // @ts-expect-error - Type issue with array push
        pages.push(i);
    }

    return (
        <>
            {children({ items, isFirstPage, isLastPage })}
            {pages.length > 1 && (
                <div className={`flex justify-center mt-4`}>
                    <div className='flex justify-center gap-1 p-1 w-fit bg-muted/30 border border-border rounded-md'>
                        <Button
                            variant='ghost'
                            size='sm'
                            disabled={pagination.currentPage <= 1}
                            onClick={() =>
                                pagination.currentPage > 1 &&
                                pagination.totalPages > 1 &&
                                onPageSelect(pagination.currentPage - 1)
                            }
                        >
                            <ChevronLeft className='size-4' />
                        </Button>
                        {pages.map((i) => (
                            <Button
                                variant={pagination.currentPage === i ? 'default' : 'ghost'}
                                size='sm'
                                key={`block_page_${i}`}
                                onClick={() => onPageSelect(i)}
                            >
                                {i}
                            </Button>
                        ))}
                        <Button
                            variant='ghost'
                            size='sm'
                            disabled={pagination.currentPage >= pagination.totalPages}
                            onClick={() =>
                                pagination.currentPage < pagination.totalPages &&
                                onPageSelect(pagination.currentPage + 1)
                            }
                        >
                            <ChevronRight className='size-4' />
                        </Button>
                    </div>
                </div>
            )}
        </>
    );
}

export default Pagination;
