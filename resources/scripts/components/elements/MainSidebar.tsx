import { cn } from '@/lib/utils';

const MainSidebar = ({
    className,
    children,
    ...props
}: React.HTMLAttributes<HTMLElement>) => (
    <nav
        className={cn(
            'flex w-64 flex-col shrink-0 overflow-x-hidden bg-sidebar text-sidebar-foreground select-none border-r border-sidebar-border',
            className,
        )}
        {...props}
    >
        {children}
    </nav>
);

export default MainSidebar;
