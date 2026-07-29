import { ChevronDown, House, LayoutGrid, Menu, SlidersVertical } from 'lucide-react';
import { useStoreState } from 'easy-peasy';
import { useEffect, useState } from 'react';
import { useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import useSWR from 'swr';

import ServerRow from '@/components/dashboard/ServerRow';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Spinner } from '@/components/ui/spinner';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Pagination from '@/components/elements/Pagination';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

import getServers from '@/api/getServers';
import { PaginatedResult } from '@/api/http';
import { Server } from '@/api/server/getServer';

import useFlash from '@/plugins/useFlash';
import { usePersistedState } from '@/plugins/usePersistedState';

import { MainPageHeader } from '../elements/MainPageHeader';

const DashboardContainer = () => {
    const { t } = useTranslation();
    const getTitle = () => {
        if (serverViewMode === 'admin-all') return t('dashboard:all_servers_admin');
        if (serverViewMode === 'all') return t('dashboard:all_servers');
        return t('dashboard:your_servers');
    };

    const { search } = useLocation();
    const defaultPage = Number(new URLSearchParams(search).get('page') || '1');

    const [page, setPage] = useState(!isNaN(defaultPage) && defaultPage > 0 ? defaultPage : 1);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const uuid = useStoreState((state) => state.user.data!.uuid);
    const rootAdmin = useStoreState((state) => state.user.data!.rootAdmin);

    const [serverViewMode, setServerViewMode] = usePersistedState<'owner' | 'admin-all' | 'all'>(
        `${uuid}:server_view_mode`,
        'owner',
    );

    const [dashboardDisplayOption, setDashboardDisplayOption] = usePersistedState(
        `${uuid}:dashboard_display_option`,
        'list',
    );
    const getApiType = (): string | undefined => {
        if (serverViewMode === 'owner') return 'owner';
        if (serverViewMode === 'admin-all') return 'admin-all';
        if (serverViewMode === 'all') return 'all';
        return undefined;
    };

    const { data: servers, error } = useSWR<PaginatedResult<Server>>(
        ['/api/client/servers', serverViewMode, page],
        () => getServers({ page, type: getApiType() }),
        { revalidateOnFocus: false },
    );

    useEffect(() => {
        if (!servers) return;
        if (servers.pagination.currentPage > 1 && !servers.items.length) {
            setPage(1);
        }
    }, [servers?.pagination.currentPage]);

    useEffect(() => {
        window.history.replaceState(null, document.title, `/${page <= 1 ? '' : `?page=${page}`}`);
    }, [page]);

    useEffect(() => {
        if (error) clearAndAddHttpError({ key: 'dashboard', error });
        if (!error) clearFlashes('dashboard');
    }, [error]);

    const renderLoading = () => (
        <div className='flex items-center justify-center py-16'>
            <Spinner className='size-6 text-muted-foreground' />
        </div>
    );

    const renderEmpty = () => (
        <div className='flex flex-col items-center justify-center py-16 px-4'>
            <div className='text-center'>
                <div className='mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-muted'>
                    <House className='size-6 text-muted-foreground' />
                </div>
                <h3 className='mb-1 text-base font-medium text-foreground'>
                    {serverViewMode === 'admin-all' ? t('dashboard:no_other_servers_found') : t('dashboard:no_servers_found')}
                </h3>
                <p className='max-w-sm text-sm text-muted-foreground'>
                    {serverViewMode === 'admin-all'
                        ? t('dashboard:no_other_servers_display')
                        : t('dashboard:no_servers_account')}
                </p>
            </div>
        </div>
    );

    return (
        <PageContentBlock title={t('dashboard:title')} showFlashKey='dashboard'>
            <Tabs
                defaultValue={'list'}
                onValueChange={(value) => {
                    setDashboardDisplayOption(value);
                }}
                className='w-full'
            >
                <div className='mb-6'>
                    <MainPageHeader
                        title={getTitle()}
                        titleChildren={
                            <div className='flex items-center gap-3'>
                                <DropdownMenu>
                                    <DropdownMenuTrigger className='inline-flex h-9 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-md border border-border bg-background px-3 py-1.5 text-sm font-medium text-muted-foreground transition-all hover:bg-muted hover:text-foreground focus-visible:outline-hidden'>
                                        <SlidersVertical className='size-3.5' />
                                        {getTitle()}
                                        <ChevronDown className='size-3.5' />
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent className='z-50' sideOffset={8}>
                                        <DropdownMenuItem
                                            onClick={() => setServerViewMode('owner')}
                                            className={serverViewMode === 'owner' ? 'bg-accent' : ''}
                                        >
                                            {t('dashboard:your_servers_only')}
                                        </DropdownMenuItem>

                                        {rootAdmin && (
                                            <DropdownMenuItem
                                                onClick={() => setServerViewMode('admin-all')}
                className={serverViewMode === 'admin-all' ? 'bg-accent' : ''}
                                            >
                                                {t('dashboard:all_servers_admin')}
                                            </DropdownMenuItem>
                                        )}
                                    </DropdownMenuContent>
                                </DropdownMenu>

                                <TabsList>
                                    <TabsTrigger aria-label={t('dashboard:list_layout_aria')} value='list'>
                                        <Menu className='size-4' />
                                    </TabsTrigger>
                                    <TabsTrigger aria-label={t('dashboard:grid_layout_aria')} value='grid'>
                                        <LayoutGrid className='size-4' />
                                    </TabsTrigger>
                                </TabsList>
                            </div>
                        }
                    />
                </div>
                <TabsContent value='list'>
                    {!servers ? (
                        renderLoading()
                    ) : (
                        <Pagination data={servers} onPageSelect={setPage}>
                            {({ items }) =>
                                items.length > 0 ? (
                                    <div className='flex flex-col gap-2'>
                                        {items.map((server) => (
                                            <ServerRow key={server.uuid} server={server} />
                                        ))}
                                    </div>
                                ) : (
                                    renderEmpty()
                                )
                            }
                        </Pagination>
                    )}
                </TabsContent>
                <TabsContent value='grid'>
                    {!servers ? (
                        renderLoading()
                    ) : (
                        <Pagination data={servers} onPageSelect={setPage}>
                            {({ items }) =>
                                items.length > 0 ? (
                                    <div className='grid grid-cols-1 gap-2 md:grid-cols-2'>
                                        {items.map((server) => (
                                            <ServerRow key={server.uuid} server={server} />
                                        ))}
                                    </div>
                                ) : (
                                    renderEmpty()
                                )
                            }
                        </Pagination>
                    )}
                </TabsContent>
            </Tabs>
        </PageContentBlock>
    );
};

export default DashboardContainer;
