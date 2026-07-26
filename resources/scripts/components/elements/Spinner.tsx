import { Suspense } from 'react';

import { cn } from '@/lib/utils';

import ErrorBoundary from '@/components/elements/ErrorBoundary';
import PyroLogo from '@/components/elements/PyroLogo';
import { Spinner as ShadcnSpinner, type SpinnerSize } from '@/components/ui/spinner';

export type SpinnerSize = 'small' | 'base' | 'large';

interface Props {
    size?: SpinnerSize;
    visible?: boolean;
    centered?: boolean;
    children?: React.ReactNode;
}

interface Spinner extends React.FC<Props> {
    Size: Record<'SMALL' | 'BASE' | 'LARGE', SpinnerSize>;
    Suspense: React.FC<{ children: React.ReactNode }>;
}

const sizeMap: Record<SpinnerSize, string> = {
    small: 'size-4',
    base: 'size-8',
    large: 'size-16',
};

const Spinner: Spinner = ({ centered, visible = true, size = 'base' }) => {
    if (!visible) return null;

    const spinner = <ShadcnSpinner className={cn(sizeMap[size])} />;

    if (centered) {
        return (
            <div className='flex justify-center items-center w-full sm:absolute sm:inset-0 sm:z-50'>
                {spinner}
            </div>
        );
    }

    return spinner;
};

Spinner.displayName = 'Spinner';

Spinner.Size = {
    SMALL: 'small',
    BASE: 'base',
    LARGE: 'large',
};

Spinner.Suspense = ({ children }) => (
    <Suspense
        fallback={
            <div className='flex min-h-screen items-center justify-center bg-background'>
                <div className='flex flex-col items-center gap-4'>
                    <div className='flex items-center gap-2'>
                        <PyroLogo className='size-8 text-foreground' />
                        <span className='text-lg font-semibold tracking-tight text-foreground'>Pyrodactyl</span>
                    </div>
                    <ShadcnSpinner className='size-5 text-muted-foreground' />
                    <p className='text-sm text-muted-foreground animate-pulse'>Loading&hellip;</p>
                </div>
            </div>
        }
    >
        <ErrorBoundary>{children}</ErrorBoundary>
    </Suspense>
);
Spinner.Suspense.displayName = 'Spinner.Suspense';

export default Spinner;
