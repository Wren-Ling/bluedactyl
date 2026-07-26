import { ShieldAlert } from 'lucide-react';
import { cn } from '@/lib/utils';
import { useContext, useEffect } from 'react';

import { DialogContext, DialogIconProps } from './';

export default ({ type, position, className }: DialogIconProps) => {
    const { setIcon, setIconPosition } = useContext(DialogContext);

    useEffect(() => {
        const typeStyles: Record<string, string> = {
            danger: 'bg-destructive text-destructive-foreground',
            warning: 'bg-yellow-600 text-yellow-50',
            success: 'bg-green-600 text-green-50',
            info: 'bg-muted text-muted-foreground',
        };

        setIcon(
            <div className={cn('mr-4 flex h-10 w-10 items-center justify-center rounded-full', typeStyles[type], className)}>
                <ShieldAlert className='size-5' />
            </div>,
        );
    }, [type, className]);

    useEffect(() => {
        setIconPosition(position);
    }, [position]);

    return null;
};
