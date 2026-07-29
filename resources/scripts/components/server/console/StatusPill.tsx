import { cn } from '@/lib/utils';
import { useTranslation } from 'react-i18next';

import { ServerContext } from '@/state/server';

export const StatusPill = () => {
    const { t } = useTranslation();
    const status = ServerContext.useStoreState((state) => state.status.value);

    return (
        <div
            className={cn(
                'relative transition rounded-full pl-3 pr-3 py-2 flex items-center gap-1',
                status === 'offline' ? 'bg-red-400/25' : status === 'running' ? 'bg-green-400/25' : 'bg-yellow-400/25',
            )}
        >
            <div
                className={cn(
                    'transition rounded-full h-4 w-4',
                    status === 'offline' ? 'bg-red-500' : status === 'running' ? 'bg-green-500' : 'bg-yellow-500',
                )}
            ></div>
            <div
                className={cn(
                    'transition rounded-full h-4 w-4 animate-ping absolute top-2.5 opacity-45',
                    status === 'offline' ? 'bg-red-500' : status === 'running' ? 'bg-green-500' : 'bg-yellow-500',
                )}
            ></div>
            <div className='text-sm font-bold'>
                {status === 'offline'
                    ? t('console:offline')
                    : status === 'running'
                      ? t('console:online')
                      : status === 'stopping'
                        ? t('console:stopping')
                        : status === 'starting'
                          ? t('console:starting')
                          : t('console:fetching')}
            </div>
        </div>
    );
};
