import { cn } from '@/lib/utils';

const MainWrapper = ({
    className,
    ...props
}: React.HTMLAttributes<HTMLDivElement>) => (
    <div className={cn('h-full w-full bg-background', className)} {...props} />
);

export default MainWrapper;
