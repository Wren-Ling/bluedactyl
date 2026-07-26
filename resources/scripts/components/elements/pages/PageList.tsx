import { cn } from '@/lib/utils';

interface Props {
    children: React.ReactNode;
    className?: string;
}

const PageListContainer = ({ className, children }: Props) => (
    <div
        className={cn(className, 'p-2 border rounded-xl bg-card')}
    >
        <div className='flex h-full w-full flex-col gap-3 overflow-hidden rounded-lg'>{children}</div>
    </div>
);
PageListContainer.displayName = 'PageListContainer';

const PageListItem = ({ className, children }: Props) => (
    <div
        className={cn(
            className,
            'bg-linear-to-b from-white/[0.03] to-white/[0.02] border px-5 py-4 rounded-xl hover:border-foreground/20 transition-all flex items-center gap-3 flex-col sm:flex-row',
        )}
    >
        {children}
    </div>
);
PageListItem.displayName = 'PageListItem';

export { PageListContainer, PageListItem };
