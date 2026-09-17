import { useTranslation } from 'react-i18next';

const ScheduleCheatsheetCards = () => {
    const { t } = useTranslation('schedules');
    return (
        <>
            <div className={`md:w-1/2 h-full bg-muted`}>
                <div className={`flex flex-col`}>
                    <h2 className={`py-4 px-6 font-bold`}>{t('examples')}</h2>
                    <div className={`flex py-4 px-6 bg-zinc-500`}>
                        <div className={`w-1/2`}>*/5 * * * *</div>
                        <div className={`w-1/2`}>{t('every_5_minutes')}</div>
                    </div>
                    <div className={`flex py-4 px-6`}>
                        <div className={`w-1/2`}>0 */1 * * *</div>
                        <div className={`w-1/2`}>{t('every_hour')}</div>
                    </div>
                    <div className={`flex py-4 px-6 bg-zinc-500`}>
                        <div className={`w-1/2`}>0 8-12 * * *</div>
                        <div className={`w-1/2`}>{t('hour_range')}</div>
                    </div>
                    <div className={`flex py-4 px-6`}>
                        <div className={`w-1/2`}>0 0 * * *</div>
                        <div className={`w-1/2`}>{t('once_a_day')}</div>
                    </div>
                    <div className={`flex py-4 px-6 bg-zinc-500`}>
                        <div className={`w-1/2`}>0 0 * * MON</div>
                        <div className={`w-1/2`}>{t('every_monday')}</div>
                    </div>
                </div>
            </div>
            <div className={`md:w-1/2 h-full bg-muted`}>
                <h2 className={`py-4 px-6 font-bold`}>{t('special_characters')}</h2>
                <div className={`flex flex-col`}>
                    <div className={`flex py-4 px-6 bg-zinc-500`}>
                        <div className={`w-1/2`}>*</div>
                        <div className={`w-1/2`}>{t('any_value')}</div>
                    </div>
                    <div className={`flex py-4 px-6`}>
                        <div className={`w-1/2`}>,</div>
                        <div className={`w-1/2`}>{t('value_list_separator')}</div>
                    </div>
                    <div className={`flex py-4 px-6 bg-zinc-500`}>
                        <div className={`w-1/2`}>-</div>
                        <div className={`w-1/2`}>{t('range_values')}</div>
                    </div>
                    <div className={`flex py-4 px-6`}>
                        <div className={`w-1/2`}>/</div>
                        <div className={`w-1/2`}>{t('step_values')}</div>
                    </div>
                </div>
            </div>
        </>
    );
};

export default ScheduleCheatsheetCards;
