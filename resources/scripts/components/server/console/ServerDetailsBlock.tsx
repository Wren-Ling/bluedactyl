import { useEffect, useMemo, useState } from 'react';

import StatBlock from '@/components/server/console/StatBlock';
import { SocketEvent, SocketRequest } from '@/components/server/events';
import { cn } from '@/lib/utils';

import { bytesToString, ip, mbToBytes } from '@/lib/formatters';

import { SubdomainInfo, getSubdomainInfo } from '@/api/server/network/subdomain';

import { ServerContext } from '@/state/server';

import useWebsocketEvent from '@/plugins/useWebsocketEvent';

type Stats = Record<'memory' | 'cpu' | 'disk' | 'uptime' | 'rx' | 'tx', number>;

// @ts-expect-error - Unused parameter in component definition
// eslint-disable-next-line @typescript-eslint/no-unused-vars
const Limit = ({ limit, children }: { limit: string | null; children: React.ReactNode }) => <>{children}</>;

const ServerDetailsBlock = ({ className }: { className?: string }) => {
    const [stats, setStats] = useState<Stats>({ memory: 0, cpu: 0, disk: 0, uptime: 0, tx: 0, rx: 0 });
    const [subdomainInfo, setSubdomainInfo] = useState<SubdomainInfo | null>(null);

    const status = ServerContext.useStoreState((state) => state.status.value);
    const connected = ServerContext.useStoreState((state) => state.socket.connected);
    const instance = ServerContext.useStoreState((state) => state.socket.instance);
    const limits = ServerContext.useStoreState((state) => state.server.data!.limits);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const serverAllocations = ServerContext.useStoreState((state) => state.server.data!.allocations);

    const textLimits = useMemo(
        () => ({
            cpu: limits?.cpu ? `${limits.cpu}%` : null,
            memory: limits?.memory ? bytesToString(mbToBytes(limits.memory)) : null,
            disk: limits?.disk ? bytesToString(mbToBytes(limits.disk)) : null,
        }),
        [limits],
    );

    const allocation = ServerContext.useStoreState((state) => {
        const match = state.server.data!.allocations.find((allocation) => allocation.isDefault);

        return !match ? 'n/a' : `${match.alias || ip(match.ip)}:${match.port}`;
    });

    const displayAddress = useMemo(() => {
        if (
            subdomainInfo?.current_subdomain?.attributes?.is_active &&
            subdomainInfo.current_subdomain.attributes.full_domain
        ) {
            return subdomainInfo.current_subdomain.attributes.full_domain;
        }
        return allocation;
    }, [subdomainInfo, allocation, serverAllocations]);

    useEffect(() => {
        const loadSubdomainInfo = async () => {
            try {
                const data = await getSubdomainInfo(uuid);
                setSubdomainInfo(data);
            } catch (error) {
                setSubdomainInfo(null);
            }
        };

        loadSubdomainInfo();
    }, [uuid]);

    useEffect(() => {
        if (!connected || !instance) {
            return;
        }

        instance.send(SocketRequest.SEND_STATS);
    }, [instance, connected]);

    useWebsocketEvent(SocketEvent.STATS, (data) => {
        let stats: any = {};
        try {
            stats = JSON.parse(data);
        } catch (e) {
            return;
        }

        setStats({
            memory: stats.memory_bytes,
            cpu: stats.cpu_absolute,
            disk: stats.disk_bytes,
            tx: stats.network.tx_bytes,
            rx: stats.network.rx_bytes,
            uptime: stats.uptime || 0,
        });
    });

    return (
        <div className={cn('grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4', className)}>
            <StatBlock title={'IP Address'} copyOnClick={displayAddress}>
                {displayAddress}
            </StatBlock>
            <StatBlock title={'CPU'}>
                {status === 'offline' ? (
                    <span className='text-muted-foreground'>Offline</span>
                ) : (
                    <Limit limit={textLimits.cpu}>{stats.cpu.toFixed(2)}%</Limit>
                )}
            </StatBlock>
            <StatBlock title={'RAM'}>
                {status === 'offline' ? (
                    <span className='text-muted-foreground'>Offline</span>
                ) : (
                    <Limit limit={textLimits.memory}>{bytesToString(stats.memory)}</Limit>
                )}
            </StatBlock>
            <StatBlock title={'Storage'}>
                <Limit limit={textLimits.disk}>{bytesToString(stats.disk)}</Limit>
            </StatBlock>
        </div>
    );
};

export default ServerDetailsBlock;
