import { useStoreState } from 'easy-peasy';
import {
    BadgeCheck,
    ChevronsUpDown,
    Cuboid,
    Home,
    KeyRound,
    Languages,
    LogOut,
    Server,
    Settings2,
    User,
    UserKey,
} from 'lucide-react';
import { Fragment, useRef } from 'react';
import { useTranslation } from 'react-i18next';
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
    const { t } = useTranslation();
    return (
        <SidebarHeader className='border-b border-border/50'>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size='lg'
                        className='data-[slot=sidebar-menu-button]:p-1.5!'
                        render={<a href='/' />}
                    >
                        <div className='flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground'>
                            <Cuboid className='size-5' />
                        </div>
                        <div className='grid flex-1 text-left text-sm leading-tight'>
                            <span className='truncate font-semibold'>{t('dashboard:pyrodactyl')}</span>
                            <span className='truncate text-xs text-muted-foreground'>{t('dashboard:management')}</span>
                        </div>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
    );
}

function AppSidebarItems() {
    const { t } = useTranslation();
    const navItems = [
        { title: t('dashboard:sidebar_servers'), url: '/', icon: <Server /> },
        { title: t('dashboard:sidebar_api_keys'), url: '/account/api', icon: <UserKey /> },
        { title: t('dashboard:sidebar_ssh_keys'), url: '/account/ssh', icon: <KeyRound /> },
        { title: t('account:sidebar_language'), url: '/account/language', icon: <Languages /> },
        { title: t('dashboard:sidebar_settings'), url: '/account', icon: <Settings2 /> },
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
    const { t } = useTranslation();
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
                    <DropdownMenuTrigger asChild>
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
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        className='w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg'
                        side={isMobile ? 'bottom' : 'right'}
                        align='end'
                        sideOffset={4}
                    >
                        <DropdownMenuGroup>
                            <DropdownMenuLabel className='p-0 font-normal'>
                                <div className='flex items-center gap-2 px-1 py-1.5 text-left text-sm'>
                                    <div className='flex aspect-square size-8 items-center justify-center rounded-full bg-muted text-muted-foreground'>
                                        <User className='size-4' />
                                    </div>
                                    <div className='grid flex-1 text-left text-sm leading-tight'>
                                        <span className='truncate font-medium'>{name}</span>
                                        <span className='truncate text-xs'>{rootAdmin ? t('dashboard:role_admin') : t('dashboard:role_user')}</span>
                                    </div>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            {rootAdmin && (
                                <DropdownMenuItem onClick={onSelectAdminPanel}>
                                    <BadgeCheck className='mr-2 size-4' />
                                    {t('dashboard:admin_panel')}
                                </DropdownMenuItem>
                            )}
                        </DropdownMenuGroup>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem onClick={onTriggerLogout}>
                            <LogOut className='mr-2 size-4' />
                            {t('dashboard:log_out')}
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
    const { t } = useTranslation();
    const location = useLocation();

    const getBreadcrumbs = () => {
        const paths = location.pathname.split('/').filter(Boolean);
        if (paths.length === 0) return [{ title: t('dashboard:title'), active: true }];

        const breadcrumbLabels: Record<string, string> = {
            account: t('common:account'),
            api: t('common:api_credentials'),
            ssh: t('common:ssh_keys'),
            activity: t('common:activity'),
            language: t('account:sidebar_language'),
        };
        const breadcrumbs = [{ title: t('dashboard:title'), url: '/', active: false }];
        paths.forEach((path, index) => {
            const url = `/${paths.slice(0, index + 1).join('/')}`;
            const title = breadcrumbLabels[path] || path.charAt(0).toUpperCase() + path.slice(1);
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
                        <div className='ml-auto flex items-center gap-2'>
                            <a
                                href='/'
                                className='flex items-center justify-center rounded-md transition-colors hover:bg-accent hover:text-accent-foreground size-9'
                            >
                                <Home className='h-[1.2rem] w-[1.2rem]' />
                                <span className='sr-only'>{t('dashboard:home_sr')}</span>
                            </a>
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
