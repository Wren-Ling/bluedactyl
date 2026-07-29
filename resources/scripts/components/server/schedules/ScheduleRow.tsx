import { Calendar } from 'lucide-react';
import { format } from 'date-fns';
import { useTranslation } from 'react-i18next';

import ScheduleCronRow from '@/components/server/schedules/ScheduleCronRow';

import { Schedule } from '@/api/server/schedules/getServerSchedules';

const ScheduleRow = ({ schedule }: { schedule: Schedule }) => {
    const { t } = useTranslation('schedules');

    return (
    <>
        <div className={`flex-auto`}>
            <div className='flex flex-row flex-none align-middle items-center gap-6'>
                <Calendar size={25} className='flex-none' />
                <div>
                    <div className='flex flex-row items-center gap-2 text-lg'>
                        <p>{schedule.name}</p>
                    </div>
                    <p className={`text-xs text-muted-foreground`}>
                        {t('last_run_at')}: {schedule.lastRunAt ? format(schedule.lastRunAt, "MMM do 'at' h:mma") : t('na')}
                    </p>
                </div>
            </div>
        </div>
        <ScheduleCronRow cron={schedule.cron} />
        <div className='flex-none w-20 sm:ml-2 flex items-center align-middle justify-center'>
            <p className='rounded-full px-2 py-px text-xs uppercase bg-neutral-600 text-white'>
                {schedule.isProcessing ? t('processing') : schedule.isActive ? t('active') : t('inactive')}
            </p>
        </div>
    </>
    );
};

export default ScheduleRow;
