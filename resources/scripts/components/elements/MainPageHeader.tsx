import { cn } from '@/lib/utils';

interface MainPageHeaderProps {
    children?: React.ReactNode;
    direction?: 'row' | 'column';
    titleChildren?: React.ReactNode;
    title?: string;
    headChildren?: React.ReactNode;
}

export const MainPageHeader: React.FC<MainPageHeaderProps> = ({
    children,
    headChildren,
    titleChildren,
    title,
    direction = 'row',
}) => (
    <div className='flex flex-col gap-6 mb-10 select-none'>
        <div
            className={cn(
                'flex justify-between gap-4',
                direction === 'row'
                    ? 'flex-col sm:flex-row sm:items-center'
                    : 'flex-col sm:flex-row sm:items-start',
            )}
        >
            <div className='flex items-center gap-3 min-w-0 flex-wrap flex-1'>
                <h1 className='text-2xl sm:text-3xl font-semibold tracking-tight'>{title}</h1>
                {headChildren}
            </div>
            {titleChildren && <div className='flex-shrink-0 w-full sm:w-auto'>{titleChildren}</div>}
        </div>
        {direction === 'column' && children && <div>{children}</div>}
    </div>
);
