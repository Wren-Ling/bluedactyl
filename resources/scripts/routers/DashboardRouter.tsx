import { useStoreState } from 'easy-peasy';
import {
    BadgeCheck,
    ChevronsUpDown,
    Cuboid,
    KeyRound,
    LogOut,
    Server,
    Settings2,
    User,
    UserKey,
} from 'lucide-react';
import { Fragment, useRef } from 'react';
import { NavLink, Route, Routes, useLocation } from 'react-router-dom';

import { AnimatedThemeToggler as ModeToggle } from '@/components/ui/animated-theme-toggler';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarHeader,
    SidebarInset,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarProvider,
    SidebarRail,
    SidebarTrigger,
    useSidebar,
} from '@/components/ui/sidebar';

import routes from '@/routers/routes';

import DashboardContainer from '@/components/dashboard/DashboardContainer';
import { NotFound } from '@/components/elements/ScreenBlock';

import http from '@/api/http';

function AppSidebarHeader() {
    return (
        <SidebarHeader className='border-b border-border/50'>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size='lg'
                        className='data-[slot=sidebar-menu-button]:p-1.5!'
                        render={<a href='#' />}
                    >
                        <div className='flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground'>
                            <Cuboid className='size-5' />
                        </div>
                        <div className='grid flex-1 text-left text-sm leading-tight'>
                            <span className='truncate font-semibold'>Pyrodactyl</span>
                            <span className='truncate text-xs text-muted-foreground'>Management</span>
                        </div>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
    );
}

function AppSidebarItems() {
    const navItems = [
        { title: 'Servers', url: '/', icon: <Server /> },
        { title: 'API Keys', url: '/account/api', icon: <UserKey /> },
        { title: 'SSH Keys', url: '/account/ssh', icon: <KeyRound /> },
        { title: 'Settings', url: '/account', icon: <Settings2 /> },
    ];

    return (
        <SidebarGroup>
            <SidebarContent className='flex flex-col gap-2'>
                {navItems.map((item) => (
                    <NavLink to={item.url} end key={item.title}>
                        <SidebarMenuItem>
                            <SidebarMenuButton tooltip={item.title}>
                                {item.icon}
                                <span>{item.title}</span>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </NavLink>
                ))}
            </SidebarContent>
        </SidebarGroup>
    );
}

function AppSidebarUser() {
    const { isMobile } = useSidebar();
    const rootAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const name = useStoreState((state) => state.user.data!.username);

    const onTriggerLogout = () => {
        http.post('/auth/logout').finally(() => {
            // @ts-expect-error this is valid
            window.location = '/';
        });
    };

    const onSelectAdminPanel = () => {
        window.open('/admin');
    };

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger
                        render={
                            <SidebarMenuButton
                                size='lg'
                                className='data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground'
                            >
                                <div className='flex aspect-square size-8 items-center justify-center rounded-full bg-muted text-muted-foreground'>
                                    <User className='size-4' />
                                </div>
                                <div className='grid flex-1 text-left text-sm leading-tight'>
                                    <span className='truncate font-medium'>{name}</span>
                                    <span className='truncate text-xs'>{rootAdmin ? 'Admin' : 'User'}</span>
                                </div>
                                <ChevronsUpDown className='ml-auto size-4' />
                            </SidebarMenuButton>
                        }
                    />
                    <DropdownMenuContent
                        className='w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg'
                        side={isMobile ? 'bottom' : 'right'}
                        align='end'
                        sideOffset={4}
                    >
                        <DropdownMenuLabel className='p-0 font-normal'>
                            <div className='flex items-center gap-2 px-1 py-1.5 text-left text-sm'>
                                <div className='flex aspect-square size-8 items-center justify-center rounded-full bg-muted text-muted-foreground'>
                                    <User className='size-4' />
                                </div>
                                <div className='grid flex-1 text-left text-sm leading-tight'>
                                    <span className='truncate font-medium'>{name}</span>
                                    <span className='truncate text-xs'>{rootAdmin ? 'Admin' : 'User'}</span>
                                </div>
                            </div>
                        </DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuGroup>
                            {rootAdmin && (
                                <DropdownMenuItem onClick={onSelectAdminPanel}>
                                    <BadgeCheck className='mr-2 size-4' />
                                    Admin Panel
                                </DropdownMenuItem>
                            )}
                        </DropdownMenuGroup>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem onClick={onTriggerLogout}>
                            <LogOut className='mr-2 size-4' />
                            Log out
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}

function AppSidebar() {
    return (
        <Sidebar variant='sidebar' collapsible='offcanvas'>
            <AppSidebarHeader />
            <SidebarContent>
                <AppSidebarItems />
            </SidebarContent>
            <SidebarFooter>
                <AppSidebarUser />
            </SidebarFooter>
            <SidebarRail />
        </Sidebar>
    );
}

function DashboardRouter() {
    const location = useLocation();

    const getBreadcrumbs = () => {
        const paths = location.pathname.split('/').filter(Boolean);
        if (paths.length === 0) return [{ title: 'Dashboard', active: true }];

        const breadcrumbs = [{ title: 'Dashboard', url: '/', active: false }];
        paths.forEach((path, index) => {
            const url = `/${paths.slice(0, index + 1).join('/')}`;
            const title = path.charAt(0).toUpperCase() + path.slice(1);
            breadcrumbs.push({
                title,
                url,
                active: index === paths.length - 1,
            });
        });
        return breadcrumbs;
    };

    return (
        <Fragment>
            <SidebarProvider>
                <AppSidebar />
                <SidebarInset>
                    <header className='sticky top-0 z-10 flex h-16 shrink-0 items-center gap-2 border-b bg-background px-4'>
                        <SidebarTrigger className='-ml-1' />
                        <nav aria-label='Breadcrumb'>
                            <ol className='flex flex-wrap items-center gap-1.5 break-words text-sm text-muted-foreground sm:gap-2.5'>
                                {getBreadcrumbs().map((crumb, i, arr) => (
                                    <li key={i} className='inline-flex items-center gap-1.5 sm:gap-2.5'>
                                        {crumb.active ? (
                                            <span className='font-normal text-foreground'>{crumb.title}</span>
                                        ) : (
                                            <>
                                                <NavLink
                                                    to={crumb.url || '#'}
                                                    className='transition-colors hover:text-foreground'
                                                >
                                                    {crumb.title}
                                                </NavLink>
                                                <span role='presentation' aria-hidden='true' className='[&>svg]:size-3.5'>
                                                    /
                                                </span>
                                            </>
                                        )}
                                    </li>
                                ))}
                            </ol>
                        </nav>
                        <div className='ml-auto'>
                            <ModeToggle />
                        </div>
                    </header>
                    <main className='flex-1 min-w-0 overflow-hidden bg-background text-foreground'>
                        <Routes>
                            <Route path='' element={<DashboardContainer />} />
                            {routes.account.map(({ route, component: Component }) => (
                                <Route
                                    key={route}
                                    path={`/account/${route}`.replace('//', '/')}
                                    element={<Component />}
                                />
                            ))}
                            <Route path='*' element={<NotFound />} />
                        </Routes>
                    </main>
                </SidebarInset>
            </SidebarProvider>
        </Fragment>
    );
}

export default DashboardRouter;
