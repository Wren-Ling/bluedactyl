import { cn } from '@/lib/utils';

const MainPage = ({
    className,
    ...props
}: React.HTMLAttributes<HTMLDivElement>) => (
    <div className={cn('flex flex-1 flex-col w-full', className)} {...props} />
);
MainPage.displayName = 'MainPage';

export default MainPage;
