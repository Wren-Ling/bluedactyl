import { Tabs as TabsBase } from '@base-ui/react/tabs';
import * as React from 'react';

import { cn } from '@/lib/utils';

const Tabs = TabsBase.Root;

const TabsList = React.forwardRef<
    React.ComponentRef<typeof TabsBase.List>,
    React.ComponentPropsWithoutRef<typeof TabsBase.List>
>(({ className, ...props }, ref) => (
    <TabsBase.List
        ref={ref}
        className={cn(
            'inline-flex h-9 items-center justify-center rounded-lg bg-white/5 p-1 text-muted-foreground',
            className,
        )}
        {...props}
    />
));
TabsList.displayName = 'TabsList';

const TabsTrigger = React.forwardRef<
    React.ComponentRef<typeof TabsBase.Tab>,
    React.ComponentPropsWithoutRef<typeof TabsBase.Tab>
>(({ className, ...props }, ref) => (
    <TabsBase.Tab
        ref={ref}
        className={cn(
            'inline-flex cursor-pointer items-center justify-center whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[active]:bg-white/10 data-[active]:text-foreground data-[active]:shadow-sm',
            className,
        )}
        {...props}
    />
));
TabsTrigger.displayName = 'TabsTrigger';

const TabsContent = React.forwardRef<
    React.ComponentRef<typeof TabsBase.Panel>,
    React.ComponentPropsWithoutRef<typeof TabsBase.Panel>
>(({ className, ...props }, ref) => (
    <TabsBase.Panel
        ref={ref}
        className={cn(
            'mt-2 ring-offset-background focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
            className,
        )}
        {...props}
    />
));
TabsContent.displayName = 'TabsContent';

export { Tabs, TabsContent, TabsList, TabsTrigger };
