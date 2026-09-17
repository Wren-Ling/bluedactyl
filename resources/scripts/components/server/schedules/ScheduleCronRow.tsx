import { cn } from '@/lib/utils';
import { useTranslation } from 'react-i18next';

import { Schedule } from '@/api/server/schedules/getServerSchedules';

interface Props {
    cron: Schedule['cron'];
    className?: string;
}

const ScheduleCronRow = ({ cron, className }: Props) => {
    const { t } = useTranslation('schedules');

    return (
        <div className={cn('flex flex-wrap gap-4 justify-center m-auto', className)}>
            <div className={'text-center'}>
                <p className={'font-medium'}>{cron.minute}</p>
                <p className={'text-xs text-muted-foreground uppercase'}>{t('minute')}</p>
            </div>
            <div className={'text-center'}>
                <p className={'font-medium'}>{cron.hour}</p>
                <p className={'text-xs text-muted-foreground uppercase'}>{t('hour')}</p>
            </div>
            <div className={'text-center'}>
                <p className={'font-medium'}>{cron.dayOfMonth}</p>
                <p className={'text-xs text-muted-foreground uppercase'}>{t('day_month')}</p>
            </div>
            <div className={'text-center'}>
                <p className={'font-medium'}>{cron.month}</p>
                <p className={'text-xs text-muted-foreground uppercase'}>{t('month')}</p>
            </div>
            <div className={'text-center'}>
                <p className={'font-medium'}>{cron.dayOfWeek}</p>
                <p className={'text-xs text-muted-foreground uppercase'}>{t('day_week')}</p>
            </div>
        </div>
    );
};

export default ScheduleCronRow;
