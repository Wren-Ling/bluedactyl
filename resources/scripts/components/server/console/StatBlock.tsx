import { cn } from '@/lib/utils';

import CopyOnClick from '@/components/elements/CopyOnClick';

interface StatBlockProps {
    title: string;
    copyOnClick?: string;
    children: React.ReactNode;
    className?: string;
}

const StatBlock = ({ title, copyOnClick, className, children }: StatBlockProps) => {
    return (
        <CopyOnClick text={copyOnClick}>
            <div
                className={cn(
                    'group rounded-xl border bg-card p-3 text-card-foreground shadow-sm transition-all duration-150 hover:border-foreground/20 sm:p-4',
                    className,
                )}
            >
                <div className='flex w-full cursor-default flex-col justify-center overflow-hidden'>
                    <p className='mb-2 text-xs leading-tight font-medium tracking-wide text-muted-foreground uppercase'>
                        {title}
                    </p>
                    <div className='w-full truncate text-lg leading-tight font-bold tracking-tight transition-colors duration-150 sm:text-xl'>
                        {children}
                    </div>
                </div>
            </div>
        </CopyOnClick>
    );
};

export default StatBlock;
