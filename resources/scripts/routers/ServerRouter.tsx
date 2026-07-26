'use client';

import { Ellipsis, LogOut, Server } from 'lucide-react';
import { useStoreState } from 'easy-peasy';
import { Fragment, Suspense, useEffect, useMemo, useState } from 'react';
import { NavLink, Route, Routes, useLocation, useParams } from 'react-router-dom';

import routes, { getServerNavRoutes } from '@/routers/routes';

import { AnimatedThemeToggler as ModeToggle } from '@/components/ui/animated-theme-toggler';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarInset,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarProvider,
    SidebarRail,
    SidebarTrigger,
} from '@/components/ui/sidebar';
import ErrorBoundary from '@/components/elements/ErrorBoundary';
import { ServerMobileMenu } from '@/components/elements/MobileFullScreenMenu';
import MobileTopBar from '@/components/elements/MobileTopBar';
import PermissionRoute from '@/components/elements/PermissionRoute';
import Logo from '@/components/elements/PyroLogo';
import { NotFound, ServerError } from '@/components/elements/ScreenBlock';
import CommandMenu from '@/components/elements/commandk/CmdK';
import ConflictStateRenderer from '@/components/server/ConflictStateRenderer';
import InstallListener from '@/components/server/InstallListener';
import ServerSidebarNavItem from '@/components/server/ServerSidebarNavItem';
import TransferListener from '@/components/server/TransferListener';
import WebsocketHandler from '@/components/server/WebsocketHandler';

import { httpErrorToHuman } from '@/api/http';
import http from '@/api/http';
import { getSubdomainInfo } from '@/api/server/network/subdomain';

import { ServerContext } from '@/state/server';

const ServerRouter = () => {
    const params = useParams<'id'>();
    const location = useLocation();

    const rootAdmin = useStoreState((state) => state.user.data!.rootAdmin);
    const [error, setError] = useState('');
    const [subdomainSupported, setSubdomainSupported] = useState(false);

    const id = ServerContext.useStoreState((state) => state.server.data?.id);
    const uuid = ServerContext.useStoreState((state) => state.server.data?.uuid);
    const inConflictState = ServerContext.useStoreState((state) => state.server.inConflictState);
    const serverId = ServerContext.useStoreState((state) => state.server.data?.internalId);
    const serverName = ServerContext.useStoreState((state) => state.server.data?.name);
    const getServer = ServerContext.useStoreActions((actions) => actions.server.getServer);
    const clearServerState = ServerContext.useStoreActions((actions) => actions.clearServerState);

    const [isMobileMenuVisible, setMobileMenuVisible] = useState(false);

    const navRoutes = useMemo(() => getServerNavRoutes(), []);

    const toggleMobileMenu = () => setMobileMenuVisible(!isMobileMenuVisible);
    const closeMobileMenu = () => setMobileMenuVisible(false);

    const onTriggerLogout = () => {
        http.post('/auth/logout').finally(() => {
            // @ts-expect-error this is valid
            window.location = '/';
        });
    };

    const onSelectManageServer = () => {
        window.open(`/admin/servers/view/${serverId}`);
    };

    useEffect(() => {
        return () => {
            clearServerState();
        };
    }, []);

    useEffect(() => {
        setError('');

        if (params.id === undefined) {
            return;
        }

        getServer(params.id).catch((err) => {
            console.error(err);
            setError(httpErrorToHuman(err));
        });

        return () => {
            clearServerState();
        };
    }, [params.id]);

    useEffect(() => {
        const checkSubdomainSupport = async () => {
            try {
                if (uuid) {
                    const data = await getSubdomainInfo(uuid);
                    setSubdomainSupported(data.supported);
                }
            } catch {
                setSubdomainSupported(false);
            }
        };

        if (uuid) {
            checkSubdomainSupport();
        }
    }, [uuid]);

    return (
        <Fragment key={'server-router'}>
            {!uuid || !id ? (
                error ? (
                    <ServerError title='Something went wrong' message={error} />
                ) : null
            ) : (
                <SidebarProvider>
                    <MobileTopBar
                        onMenuToggle={toggleMobileMenu}
                        onTriggerLogout={onTriggerLogout}
                        onSelectAdminPanel={onSelectManageServer}
                        rootAdmin={rootAdmin}
                    />

                    <ServerMobileMenu
                        isVisible={isMobileMenuVisible}
                        onClose={closeMobileMenu}
                        serverId={id}
                    />

                    <Sidebar variant='sidebar' collapsible='offcanvas'>
                        <SidebarHeader className='border-b border-border/50 p-2'>
                            <SidebarMenu>
                                <SidebarMenuItem>
                                    <div className='flex h-10 items-center justify-between gap-2 rounded-md px-1'>
                                        <NavLink to={'/'} className='flex shrink-0 items-center gap-2'>
                                            <Logo uniqueId='server-desktop-sidebar' className='size-5 text-sidebar-foreground' />
                                            <span className='text-sm font-semibold text-sidebar-foreground'>Pyrodactyl</span>
                                        </NavLink>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <button className='flex size-8 cursor-pointer items-center justify-center rounded-md text-sidebar-foreground/60 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground'>
                                                    <Ellipsis size={18} />
                                                </button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent sideOffset={8}>
                                                {rootAdmin && (
                                                    <DropdownMenuItem onSelect={onSelectManageServer}>
                                                        Manage Server
                                                    </DropdownMenuItem>
                                                )}
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem onSelect={onTriggerLogout}>
                                                    <LogOut className='mr-2 size-4' />
                                                    Log Out
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </SidebarHeader>

                        <SidebarContent>
                            <div className='flex flex-col gap-0.5 p-2'>
                                {navRoutes.map((route) => (
                                    <ServerSidebarNavItem
                                        key={route.path || 'home'}
                                        route={route}
                                        serverId={id}
                                        onClick={() => {}}
                                    />
                                ))}
                            </div>
                        </SidebarContent>

                        <SidebarFooter className='border-t border-border/50 p-2'>
                            <SidebarMenu>
                                <SidebarMenuItem>
                                    <SidebarMenuButton className='cursor-default hover:bg-transparent'>
                                        <Server className='size-4 shrink-0 text-sidebar-foreground/60' />
                                        <span className='truncate text-xs text-sidebar-foreground/60'>{serverName}</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </SidebarFooter>

                        <SidebarRail />
                    </Sidebar>

                    <SidebarInset>
                        <header className='sticky top-0 z-10 hidden h-16 shrink-0 items-center gap-2 border-b bg-background px-4 lg:flex'>
                            <SidebarTrigger className='-ml-1' />
                            <nav aria-label='Breadcrumb'>
                                <ol className='flex flex-wrap items-center gap-1.5 break-words text-sm text-muted-foreground sm:gap-2.5'>
                                    <li className='inline-flex items-center gap-1.5 sm:gap-2.5'>
                                        <span className='font-normal text-foreground'>{serverName}</span>
                                    </li>
                                </ol>
                            </nav>
                            <div className='ml-auto flex items-center gap-2'>
                                <ModeToggle />
                            </div>
                        </header>
                        <main className='relative flex-1 min-w-0 overflow-hidden bg-background text-foreground'>
                            <CommandMenu />
                            <InstallListener />
                            <TransferListener />
                            <WebsocketHandler />
                            {inConflictState &&
                            (!rootAdmin || (rootAdmin && !location.pathname.endsWith(`/server/${id}`))) ? (
                                <ConflictStateRenderer />
                            ) : (
                                <ErrorBoundary>
                                    <Routes location={location}>
                                        {routes.server.map(({ route, permission, component: Component }) => (
                                            <Route
                                                key={route}
                                                path={route}
                                                element={
                                                    <PermissionRoute permission={permission ?? undefined}>
                                                        <Suspense fallback={null}>
                                                            <Component />
                                                        </Suspense>
                                                    </PermissionRoute>
                                                }
                                            />
                                        ))}

                                        <Route path='*' element={<NotFound />} />
                                    </Routes>
                                </ErrorBoundary>
                            )}
                        </main>
                    </SidebarInset>
                </SidebarProvider>
            )}
        </Fragment>
    );
};

export default ServerRouter;
