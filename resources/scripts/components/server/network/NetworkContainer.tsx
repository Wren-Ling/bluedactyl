import { GitBranch, Plus } from 'lucide-react';
import { For } from 'million/react';
import { useEffect, useState } from 'react';
import isEqual from 'react-fast-compare';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import Can from '@/components/elements/Can';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import { PageListContainer } from '@/components/elements/pages/PageList';
import AllocationRow from '@/components/server/network/AllocationRow';
import SubdomainManagement from '@/components/server/network/SubdomainManagement';

import createServerAllocation from '@/api/server/network/createServerAllocation';
import getServerAllocations from '@/api/swr/getServerAllocations';

import { ServerContext } from '@/state/server';

import { useDeepCompareEffect } from '@/plugins/useDeepCompareEffect';
import { useFlashKey } from '@/plugins/useFlash';

const NetworkContainer = () => {
    const [_, setLoading] = useState(false);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const allocationLimit = ServerContext.useStoreState((state) => state.server.data!.featureLimits.allocations);
    const allocations = ServerContext.useStoreState((state) => state.server.data!.allocations, isEqual);
    const setServerFromState = ServerContext.useStoreActions((actions) => actions.server.setServerFromState);

    const { clearFlashes, clearAndAddHttpError } = useFlashKey('server:network');
    const { data, error, mutate } = getServerAllocations();

    useEffect(() => {
        mutate(allocations);
    }, []);

    useEffect(() => {
        clearAndAddHttpError(error);
    }, [error]);

    useDeepCompareEffect(() => {
        if (!data) return;
        setServerFromState((state) => ({ ...state, allocations: data }));
    }, [data]);

    const onCreateAllocation = () => {
        clearFlashes();
        setLoading(true);
        createServerAllocation(uuid)
            .then((allocation) => {
                setServerFromState((s) => ({ ...s, allocations: s.allocations.concat(allocation) }));
                return mutate(data?.concat(allocation), false);
            })
            .catch((error) => clearAndAddHttpError(error))
            .then(() => setLoading(false));
    };

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <FlashMessageRender byKey={'server:network'} />

            <MainPageHeader direction='column' title={'Networking'}>
                <p className='text-sm leading-relaxed text-muted-foreground'>
                    Configure network settings for your server. Manage subdomains, IP addresses and ports that your
                    server can bind to for incoming connections.
                </p>
            </MainPageHeader>

            <div className='space-y-12'>
                <SubdomainManagement />

                <div className='rounded-xl border bg-card p-6 text-card-foreground shadow-sm'>
                    <div className='mb-6 flex items-center justify-between'>
                        <h3 className='text-xl font-extrabold tracking-tight'>Port Allocations</h3>
                        {data && (
                            <Can action={'allocation.create'}>
                                <div className='flex items-center gap-4'>
                                    {allocationLimit === null && (
                                        <span className='rounded-lg border border-border bg-muted/30 px-3 py-1 text-sm text-muted-foreground'>
                                            {data.length} allocations (unlimited)
                                        </span>
                                    )}
                                    {allocationLimit > 0 && (
                                        <span className='rounded-lg border border-border bg-muted/30 px-3 py-1 text-sm text-muted-foreground'>
                                            {data.length} of {allocationLimit}
                                        </span>
                                    )}
                                    {allocationLimit === 0 && (
                                        <span className='rounded-lg border border-border bg-muted/30 px-3 py-1 text-sm text-destructive'>
                                            Allocations disabled
                                        </span>
                                    )}
                                    {(allocationLimit === null ||
                                        (allocationLimit > 0 && allocationLimit > data.length)) && (
                                        <Button variant='default' size='sm' onClick={onCreateAllocation}>
                                            <Plus className='mr-1 size-4' />
                                            New Allocation
                                        </Button>
                                    )}
                                </div>
                            </Can>
                        )}
                    </div>

                    {!data ? (
                        <div className='flex items-center justify-center py-12'>
                            <div className='flex flex-col items-center gap-3'>
                                <div className='size-6 animate-spin rounded-full border-b-2 border-primary' />
                                <p className='text-sm text-muted-foreground'>Loading allocations...</p>
                            </div>
                        </div>
                    ) : data.length > 0 ? (
                        <PageListContainer data-pyro-network-container-allocations>
                            <For each={data} memo>
                                {(allocation) => (
                                    <AllocationRow
                                        key={`${allocation.ip}:${allocation.port}`}
                                        allocation={allocation}
                                    />
                                )}
                            </For>
                        </PageListContainer>
                    ) : (
                        <div className='flex flex-col items-center justify-center py-12'>
                            <div className='text-center'>
                                <div className='mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-muted'>
                                    <GitBranch className='size-6 text-muted-foreground' />
                                </div>
                                <h4 className='mb-2 text-lg font-medium text-foreground'>
                                    {allocationLimit === 0 ? 'Allocations unavailable' : 'No allocations found'}
                                </h4>
                                <p className='max-w-sm text-center text-sm text-muted-foreground'>
                                    {allocationLimit === 0
                                        ? 'Network allocations cannot be created for this server.'
                                        : 'Create your first allocation to get started.'}
                                </p>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

export default NetworkContainer;
