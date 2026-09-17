import { ActivityLog } from '@definitions/user';
import { Terminal } from 'lucide-react';
// FIXME: add icons back
// FIXME: replace with radix tooltip
// import Tooltip from '@/components/elements/tooltip/Tooltip';
import { formatDistanceToNowStrict } from 'date-fns';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';

import ActivityLogMetaButton from '@/components/elements/activity/ActivityLogMetaButton';

import { formatObjectToIdentString } from '@/lib/objects';

import useLocationHash from '@/plugins/useLocationHash';

interface Props {
    activity: ActivityLog;
    children?: React.ReactNode;
}

const ActivityLogEntry = ({ activity, children }: Props) => {
    const { t } = useTranslation();
    const { pathTo } = useLocationHash();
    const actor = activity.relationships.actor;

    return (
        <div className='flex items-center py-2 px-3 border-b border-border/30 last:border-0 group hover:bg-muted/30 transition-colors duration-150'>
            <div className='flex-shrink-0 w-8 h-8 rounded-full bg-muted overflow-hidden mr-3'>
                {actor?.image ? (
                    <img src={actor.image} alt={actor.username || t('common:system')} className='w-full h-full object-cover' />
                ) : (
                    <div className='w-full h-full flex items-center justify-center text-muted-foreground text-xs font-semibold'>
                        {(actor?.username || 'S').charAt(0).toUpperCase()}
                    </div>
                )}
            </div>
            <div className='flex-1 min-w-0'>
                <div className='flex items-center gap-2 text-sm'>
                    <span className='font-medium text-foreground truncate'>{actor?.username || t('common:system')}</span>
                    <span className='text-muted-foreground/50'>•</span>
                    <Link
                        to={`#${pathTo({ event: activity.event })}`}
                        className='font-mono text-xs bg-muted/50 text-muted-foreground px-2 py-1 rounded hover:bg-muted/80 hover:text-foreground transition-colors duration-150 truncate'
                    >
                        {activity.event}
                    </Link>
                    <div className='flex items-center gap-1 ml-auto'>
                        {activity.isApi && (
                            <span className='text-xs bg-blue-500/10 text-blue-600 dark:text-blue-400 px-1.5 py-0.5 rounded flex items-center gap-1'>
                                <Terminal size={22} />
                                {t('common:api')}
                            </span>
                        )}
                        {children}
                    </div>
                </div>
                <div className='flex items-center gap-3 mt-1 text-xs text-muted-foreground'>
                    {activity.ip && (
                        <span className='font-mono bg-muted/30 px-1.5 py-0.5 rounded'>{activity.ip}</span>
                    )}
                    <span>{formatDistanceToNowStrict(activity.timestamp, { addSuffix: true })}</span>
                    {!activity.hasAdditionalMetadata &&
                        activity.properties &&
                        Object.keys(activity.properties).length > 0 && (
                            <span className='text-muted-foreground/70 truncate max-w-xs'>
                                {formatObjectToIdentString(activity.properties)}
                            </span>
                        )}
                </div>
            </div>
            {activity.hasAdditionalMetadata && (
                <div className='flex-shrink-0 ml-2'>
                    <ActivityLogMetaButton meta={activity.properties} />
                </div>
            )}
        </div>
    );
};

export default ActivityLogEntry;
