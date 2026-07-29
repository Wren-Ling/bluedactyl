import { memo, useEffect, useState } from 'react';
import isEqual from 'react-fast-compare';
import { useTranslation } from 'react-i18next';

import ErrorBoundary from '@/components/elements/ErrorBoundary';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import Spinner from '@/components/elements/Spinner';
import Console from '@/components/server/console/Console';
import PowerButtons from '@/components/server/console/PowerButtons';
import ServerDetailsBlock from '@/components/server/console/ServerDetailsBlock';
import StatGraphs from '@/components/server/console/StatGraphs';
import { SocketEvent, SocketRequest } from '@/components/server/events';
import { CrashAnalysisCard } from '@/components/server/features/MclogsFeature';
import { Alert } from '@/components/elements/alert';

import { ServerContext } from '@/state/server';

import useWebsocketEvent from '@/plugins/useWebsocketEvent';

import Features from '@feature/Features';

import UptimeDuration from '../UptimeDuration';

export type PowerAction = 'start' | 'stop' | 'restart' | 'kill';
type UptimeStat = Record<'uptime', number>;

const ServerConsoleContainer = () => {
    const { t } = useTranslation();
    const name = ServerContext.useStoreState((state) => state.server.data!.name);
    const description = ServerContext.useStoreState((state) => state.server.data!.description);
    const isInstalling = ServerContext.useStoreState((state) => state.server.isInstalling);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data!.isTransferring);
    const connected = ServerContext.useStoreState((state) => state.socket.connected);
    const instance = ServerContext.useStoreState((state) => state.socket.instance);
    const eggFeatures = ServerContext.useStoreState((state) => state.server.data!.eggFeatures, isEqual);
    const isNodeUnderMaintenance = ServerContext.useStoreState((state) => state.server.data!.isNodeUnderMaintenance);
    const [uptime, setUptime] = useState<UptimeStat>({ uptime: 0 });

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
        setUptime({
            uptime: stats.uptime || 0,
        });
    });

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            {(isNodeUnderMaintenance || isInstalling || isTransferring) && (
                <Alert type={'warning'}>
                    {isNodeUnderMaintenance
                        ? t('console:node_maintenance')
                        : isInstalling
                            ? t('console:server_installing')
                            : t('console:server_transferring')}
                </Alert>
            )}

            <MainPageHeader
                title={name}
                headChildren={
                    <p className='rounded-lg border bg-card px-3 py-1 text-sm text-muted-foreground shadow-sm'>
                        {t('console:uptime_label')}: {UptimeDuration(uptime)}
                    </p>
                }
                titleChildren={
                    <PowerButtons className='flex items-center justify-center gap-1' />
                }
            />

            {description && (
                <div className='rounded-xl border bg-card p-3 text-card-foreground shadow-sm sm:p-4'>
                    <p className='text-sm leading-relaxed text-muted-foreground'>{description}</p>
                </div>
            )}

            <div className='flex flex-col gap-3 sm:gap-4'>
                <div className='rounded-xl border bg-card p-3 text-card-foreground shadow-sm sm:p-4'>
                    <ServerDetailsBlock />
                </div>

                {eggFeatures.map((v) => v.toLowerCase()).includes('mclogs') && <CrashAnalysisCard />}

                <div className='rounded-xl border bg-card p-3 text-card-foreground shadow-sm sm:p-4'>
                    <Console />
                </div>

                <div className='rounded-xl border bg-card p-3 text-card-foreground shadow-sm sm:p-4'>
                    <div className={'grid grid-cols-1 gap-3 md:grid-cols-3 sm:gap-4'}>
                        <Spinner.Suspense>
                            <StatGraphs />
                        </Spinner.Suspense>
                    </div>
                </div>

                <ErrorBoundary>
                    <Features enabled={eggFeatures} />
                </ErrorBoundary>
            </div>
        </div>
    );
};

export default memo(ServerConsoleContainer, isEqual);
