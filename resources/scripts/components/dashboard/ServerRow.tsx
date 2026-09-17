import { Fragment, useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

import { Card, CardContent, CardDescription, CardTitle } from '@/components/ui/card';

import { bytesToString, ip } from '@/lib/formatters';

import { Server } from '@/api/server/getServer';
import getServerResourceUsage, { ServerPowerState, ServerStats } from '@/api/server/getServerResourceUsage';

const isAlarmState = (current: number, limit: number): boolean => limit > 0 && current / (limit * 1024 * 1024) >= 0.9;

type Timer = ReturnType<typeof setInterval>;

const statusColors: Record<string, string> = {
    running: 'bg-green-500 shadow-[0_0_8px_2px] shadow-green-500/50',
    offline: 'bg-red-500 shadow-[0_0_8px_2px] shadow-red-500/50',
    installing: 'bg-blue-500 shadow-[0_0_8px_2px] shadow-blue-500/50',
};

const getStatusColor = (status?: ServerPowerState) =>
    statusColors[status || 'offline'] || 'bg-yellow-500 shadow-[0_0_8px_2px] shadow-yellow-500/50';

const ServerRow = ({ server, className }: { server: Server; className?: string }) => {
    const { t } = useTranslation();
    const interval = useRef<Timer>(null) as React.MutableRefObject<Timer>;
    const [isSuspended, setIsSuspended] = useState(server.status === 'suspended');
    const [isInstalling, setIsInstalling] = useState(server.status === 'installing');
    const [stats, setStats] = useState<ServerStats | null>(null);

    const getStats = () =>
        getServerResourceUsage(server.uuid)
            .then((data) => setStats(data))
            .catch((error) => console.error(error));

    useEffect(() => {
        setIsSuspended(stats?.isSuspended || server.status === 'suspended');
    }, [stats?.isSuspended, server.status]);

    useEffect(() => {
        setIsInstalling(stats?.isInstalling || server.status === 'installing');
    }, [stats?.isInstalling, server.status]);

    useEffect(() => {
        if (isSuspended) return;
        getStats().then(() => {
            interval.current = setInterval(() => getStats(), 30000);
        });
        return () => {
            if (interval.current) clearInterval(interval.current);
        };
    }, [isSuspended]);

    const alarms = { cpu: false, memory: false, disk: false };
    if (stats) {
        alarms.cpu = server.limits.cpu === 0 ? false : stats.cpuUsagePercent >= server.limits.cpu * 0.9;
        alarms.memory = isAlarmState(stats.memoryUsageInBytes, server.limits.memory);
        alarms.disk = server.limits.disk === 0 ? false : isAlarmState(stats.diskUsageInBytes, server.limits.disk);
    }

    return (
        <Link to={`/server/${server.id}`} className={`${className || ''} block`}>
            <Card
                size='sm'
                className='transition-all hover:bg-accent hover:ring-accent'
            >
                <CardContent className='flex items-center justify-between gap-4 py-3'>
                    <div className='flex items-center gap-3 min-w-0'>
                        <div className={`h-2.5 w-2.5 shrink-0 rounded-full ${getStatusColor(stats?.status)}`} />
                        <div className='min-w-0'>
                            <div className='flex items-center gap-2'>
                                <CardTitle className='truncate'>{server.name}</CardTitle>
                                {isSuspended && (
                                    <span className='rounded bg-destructive/10 px-1 py-0.5 text-[11px] font-medium text-destructive'>
                                        {t('dashboard:suspended')}
                                    </span>
                                )}
                            </div>
                            <CardDescription className='truncate'>
                                {server.allocations
                                    .filter((alloc) => alloc.isDefault)
                                    .map((allocation) => (
                                        <Fragment key={allocation.ip + allocation.port.toString()}>
                                            {allocation.alias || ip(allocation.ip)}:{allocation.port}
                                        </Fragment>
                                    ))}
                            </CardDescription>
                        </div>
                    </div>
                    <div className='flex shrink-0 items-center gap-4'>
                        {!stats || isSuspended || isInstalling ? (
                            <span className='text-xs text-muted-foreground'>
                                {isSuspended
                                    ? t('dashboard:suspended')
                                    : server.isTransferring
                                      ? t('dashboard:transferring')
                                      : server.status === 'installing'
                                        ? t('dashboard:installing')
                                        : server.status === 'restoring_backup'
                                          ? t('dashboard:restoring_backup')
                                          : t('dashboard:unavailable')}
                            </span>
                        ) : (
                            <>
                                {(['cpu', 'memory', 'disk'] as const).map((key) => (
                                    <div key={key} className='hidden sm:block text-right'>
                                        <p className='text-[11px] uppercase text-muted-foreground'>{t(`dashboard:stats_${key}`)}</p>
                                        <p className={`text-xs font-medium ${alarms[key] ? 'text-destructive' : ''}`}>
                                            {key === 'cpu'
                                                ? `${stats.cpuUsagePercent.toFixed(1)}%`
                                                : bytesToString(
                                                      key === 'memory' ? stats.memoryUsageInBytes : stats.diskUsageInBytes,
                                                      0,
                                                  )}
                                        </p>
                                    </div>
                                ))}
                            </>
                        )}
                    </div>
                </CardContent>
            </Card>
        </Link>
    );
};

export default ServerRow;
