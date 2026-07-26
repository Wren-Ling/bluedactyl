import { TriangleAlert } from 'lucide-react';

import { cn } from '@/lib/utils';

interface AlertProps {
    type: 'warning' | 'danger';
    className?: string;
    children: React.ReactNode;
}

const Alert = ({ type, className, children }: AlertProps) => {
    return (
        <div
            className={cn(
                'flex items-center rounded-md border-l-8 px-4 py-3 text-sm text-foreground shadow-sm',
                type === 'danger' ? 'border-destructive bg-destructive/20' : 'border-yellow-500 bg-yellow-500/20',
                className,
            )}
        >
            <TriangleAlert
                className={cn('mr-2 size-5 shrink-0', type === 'danger' ? 'text-destructive' : 'text-yellow-500')}
            />
            {children}
        </div>
    );
};

export default Alert;
