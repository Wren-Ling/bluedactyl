import { useEffect } from 'react';

import FlashMessageRender from '@/components/FlashMessageRender';
import MainPage from '@/components/elements/MainPage';
import { cn } from '@/lib/utils';

export interface PageContentBlockProps {
    title?: string;
    className?: string;
    showFlashKey?: string;
    children?: React.ReactNode;
}

const PageContentBlock: React.FC<PageContentBlockProps> = ({ title, showFlashKey, className, children }) => {
    useEffect(() => {
        if (title) {
            document.title = title + ' | Pyrodactyl';
        }
    }, [title]);

    return (
        <MainPage className={cn('mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10', className)}>
            {showFlashKey && <FlashMessageRender byKey={showFlashKey} />}
            {children}
        </MainPage>
    );
};

export default PageContentBlock;
