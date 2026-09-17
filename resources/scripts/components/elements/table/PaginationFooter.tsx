import { ChevronLeft, ChevronRight } from 'lucide-react';
import { cn } from '@/lib/utils';
import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';

import { PaginationDataSet } from '@/api/http';

interface Props {
    className?: string;
    pagination: PaginationDataSet;
    onPageSelect: (page: number) => void;
}

const PaginationFooter = ({ pagination, className, onPageSelect }: Props) => {
    const { t } = useTranslation();
    const start = (pagination.currentPage - 1) * pagination.perPage;
    const end = (pagination.currentPage - 1) * pagination.perPage + pagination.count;

    const { currentPage: current, totalPages: total } = pagination;

    const pages = { previous: [] as number[], next: [] as number[] };
    for (let i = 1; i <= 2; i++) {
        if (current - i >= 1) {
            pages.previous.push(current - i);
        }
        if (current + i <= total) {
            pages.next.push(current + i);
        }
    }

    if (pagination.total === 0) {
        return null;
    }

    return (
        <div className={cn('flex items-center justify-between my-2', className)}>
            <p className={'text-sm text-muted-foreground'}>
                {t('common:showing_results', { start: Math.max(start, Math.min(pagination.total, 1)), end, total: pagination.total })}
            </p>
            {pagination.totalPages > 1 && (
                <div className={'flex space-x-1'}>
                    <Button
                        variant='outline'
                        size='icon'
                        onClick={() => onPageSelect(current - 1)}
                        disabled={current <= 1}
                    >
                        <ChevronLeft className='size-4' />
                    </Button>
                    {pages.previous.reverse().map((value) => (
                        <Button
                            key={`previous-${value}`}
                            variant='outline'
                            size='sm'
                            onClick={() => onPageSelect(value)}
                            className='w-8'
                        >
                            {value}
                        </Button>
                    ))}
                    <Button
                        variant='default'
                        size='sm'
                        className='w-8'
                        disabled
                    >
                        {current}
                    </Button>
                    {pages.next.map((value) => (
                        <Button
                            key={`next-${value}`}
                            variant='outline'
                            size='sm'
                            onClick={() => onPageSelect(value)}
                            className='w-8'
                        >
                            {value}
                        </Button>
                    ))}
                    <Button
                        variant='outline'
                        size='icon'
                        onClick={() => onPageSelect(current + 1)}
                        disabled={current >= total}
                    >
                        <ChevronRight className='size-4' />
                    </Button>
                </div>
            )}
        </div>
    );
};

export default PaginationFooter;
