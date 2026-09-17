import ModalContext from '@/context/ModalContext';
import { TZDate } from '@date-fns/tz';
import { Link, TriangleAlert } from 'lucide-react';
import { toString } from 'cronstrue';
import { format } from 'date-fns';
import { useStoreState } from 'easy-peasy';
import { Form, Formik, FormikHelpers } from 'formik';
import { useContext, useEffect, useMemo } from 'react';
import { useTranslation } from 'react-i18next';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import Field from '@/components/elements/Field';
import FormikSwitchV2 from '@/components/elements/FormikSwitchV2';
import ItemContainer from '@/components/elements/ItemContainer';

import asModal from '@/hoc/asModal';

import { httpErrorToHuman } from '@/api/http';
import createOrUpdateSchedule from '@/api/server/schedules/createOrUpdateSchedule';
import { Schedule } from '@/api/server/schedules/getServerSchedules';

import { ServerContext } from '@/state/server';

import useFlash from '@/plugins/useFlash';

interface Props {
    schedule?: Schedule;
}

interface Values {
    name: string;
    dayOfWeek: string;
    month: string;
    dayOfMonth: string;
    hour: string;
    minute: string;
    enabled: boolean;
    onlyWhenOnline: boolean;
}

const getTimezoneInfo = (serverTimezone: string) => {
    const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const now = new Date();

    const userOffsetString = format(now, 'xxx');
    let serverOffsetString: string;
    let offsetDifferenceMinutes = 0;

    let isServerTimezoneValid = true;
    try {
        const serverDate = new TZDate(now, serverTimezone);
        const userDate = new TZDate(now, userTimezone);
        serverOffsetString = format(serverDate, 'xxx');

        // offset difference in minutes
        const serverOffsetValue = serverDate.getTimezoneOffset();
        const userOffsetValue = userDate.getTimezoneOffset();

        // + values mean behind UTC
        // - values mean ahead of UTC
        offsetDifferenceMinutes = userOffsetValue - serverOffsetValue;
    } catch {
        serverOffsetString = 'Unknown';
        isServerTimezoneValid = false;
    }

    let differenceDescription = '';
    if (!isServerTimezoneValid) {
        differenceDescription = 'at an unknown difference to';
    } else if (offsetDifferenceMinutes === 0) {
        differenceDescription = 'same time';
    } else {
        const offsetDifferenceHours = offsetDifferenceMinutes / 60;
        const absDifferenceHours = Math.abs(offsetDifferenceHours);
        const isAhead = offsetDifferenceMinutes > 0;

        if (absDifferenceHours === Math.floor(absDifferenceHours)) {
            // whole hours
            differenceDescription = `${absDifferenceHours} hour${absDifferenceHours !== 1 ? 's' : ''} ${isAhead ? 'ahead of' : 'behind'}`;
        } else {
            // hours & minutes
            const hours = Math.floor(absDifferenceHours);
            const minutes = Math.abs(offsetDifferenceMinutes % 60);

            if (hours > 0) {
                differenceDescription = `${hours}h ${minutes}m ${isAhead ? 'ahead of' : 'behind'}`;
            } else {
                differenceDescription = `${minutes} minute${minutes !== 1 ? 's' : ''} ${isAhead ? 'ahead of' : 'behind'}`;
            }
        }
    }

    return {
        user: { timezone: userTimezone, offset: userOffsetString },
        server: { timezone: serverTimezone, offset: serverOffsetString },
        difference: differenceDescription,
        isDifferent: userTimezone !== serverTimezone,
    };
};

const formatTimezoneDisplay = (timezone: string, offset: string) => {
    return `${timezone} (${offset})`;
};

const getCronDescription = (
    minute: string,
    hour: string,
    dayOfMonth: string,
    month: string,
    dayOfWeek: string,
    t: (key: string) => string,
): string => {
    try {
        // Build cron expression: minute hour dayOfMonth month dayOfWeek
        const cronExpression = `${minute} ${hour} ${dayOfMonth} ${month} ${dayOfWeek}`;
        const description = toString(cronExpression, {
            throwExceptionOnParseError: false,
            verbose: true,
        });

        // Check if cronstrue returned an error message
        if (
            description ===
            'An error occurred when generating the expression description. Check the cron expression syntax.'
        ) {
            return t('invalid_cron');
        }

        return description;
    } catch {
        return t('invalid_cron');
    }
};

const EditScheduleModal = ({ schedule }: Props) => {
    const { t } = useTranslation('schedules');
    const { addError, clearFlashes } = useFlash();
    const { dismiss, setPropOverrides } = useContext(ModalContext);

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const appendSchedule = ServerContext.useStoreActions((actions) => actions.schedules.appendSchedule);
    const serverTimezone = useStoreState((state) => state.settings.data?.timezone || 'Unknown');

    const timezoneInfo = useMemo(() => {
        return getTimezoneInfo(serverTimezone);
    }, [serverTimezone]);

    useEffect(() => {
        setPropOverrides({ title: schedule ? t('edit_schedule') : t('create_schedule') });
    }, []);

    useEffect(() => {
        return () => {
            clearFlashes('schedule:edit');
        };
    }, []);

    const submit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes('schedule:edit');
        createOrUpdateSchedule(uuid, {
            id: schedule?.id,
            name: values.name,
            cron: {
                minute: values.minute,
                hour: values.hour,
                dayOfWeek: values.dayOfWeek,
                month: values.month,
                dayOfMonth: values.dayOfMonth,
            },
            onlyWhenOnline: values.onlyWhenOnline,
            isActive: values.enabled,
        })
            .then((schedule) => {
                setSubmitting(false);
                appendSchedule(schedule);
                dismiss();
            })
            .catch((error) => {
                console.error(error);

                setSubmitting(false);
                addError({ key: 'schedule:edit', message: httpErrorToHuman(error) });
            });
    };

    return (
        <Formik
            onSubmit={submit}
            initialValues={
                {
                    name: schedule?.name || '',
                    minute: schedule?.cron.minute || '*/5',
                    hour: schedule?.cron.hour || '*',
                    dayOfMonth: schedule?.cron.dayOfMonth || '*',
                    month: schedule?.cron.month || '*',
                    dayOfWeek: schedule?.cron.dayOfWeek || '*',
                    enabled: schedule?.isActive ?? true,
                    onlyWhenOnline: schedule?.onlyWhenOnline ?? true,
                } as Values
            }
        >
            {({ isSubmitting, values }) => {
                const cronDescription = getCronDescription(
                    values.minute,
                    values.hour,
                    values.dayOfMonth,
                    values.month,
                    values.dayOfWeek,
                    t,
                );

                return (
                    <Form>
                        <FlashMessageRender byKey={'schedule:edit'} />
                        <Field
                            name={'name'}
                            label={t('schedule_name')}
                            description={t('schedule_name_description')}
                        />
                        <div className={`grid grid-cols-2 sm:grid-cols-5 gap-4 mt-6`}>
                            <Field name={'minute'} label={t('minute')} />
                            <Field name={'hour'} label={t('hour')} />
                            <Field name={'dayOfWeek'} label={t('day_of_week')} />
                            <Field name={'dayOfMonth'} label={t('day_of_month')} />
                            <Field name={'month'} label={t('month')} />
                        </div>

                        <div className={`mt-3 p-3 rounded-lg bg-muted border border-border`}>
                            <p className={`text-sm text-foreground font-medium`}>{cronDescription}</p>
                        </div>

                        <p className={`text-muted-foreground text-xs mt-2`}>
                            {t('cron_syntax_description')}
                        </p>

                        {timezoneInfo.isDifferent && (
                            <div className={'bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 my-2'}>
                                <div className={'flex items-start gap-3'}>
                                    <TriangleAlert
                                        size={22}
                                        className={'text-blue-400 mt-0.5 flex-shrink-0 h-5 w-5'}
                                    />
                                    <div className={'text-sm'}>
                                        <p className={'text-blue-100 font-medium mb-1'}>{t('timezone_info_title')}</p>
                                        <p className={'text-blue-200/80 text-xs mb-2'}>
                                            {t('timezone_info_description')}
                                            {timezoneInfo.difference !== 'same time' && (
                                                <span className={'text-blue-100 font-medium'}>
                                                    {' '}
                                                    {t('timezone_difference', { difference: timezoneInfo.difference })}
                                                </span>
                                            )}
                                        </p>
                                        <div className={'mt-2 text-xs space-y-1'}>
                                            <div className={'text-blue-200/60'}>
                                                {t('your_timezone')}:
                                                <span className={'font-mono'}>
                                                    {' '}
                                                    {formatTimezoneDisplay(
                                                        timezoneInfo.user.timezone,
                                                        timezoneInfo.user.offset,
                                                    )}
                                                </span>
                                            </div>
                                            <div className={'text-blue-200/60'}>
                                                {t('server_timezone')}:
                                                <span className={'font-mono'}>
                                                    {' '}
                                                    {formatTimezoneDisplay(
                                                        timezoneInfo.server.timezone,
                                                        timezoneInfo.server.offset,
                                                    )}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        )}

                        <div className='gap-3 my-6 flex flex-col'>
                            <a href='https://crontab.guru/' target='_blank' rel='noreferrer'>
                                <ItemContainer
                                    description={t('crontab_guru_description')}
                                    title={t('crontab_guru')}
                                    // defaultChecked={showCheatsheet}
                                    // onChange={() => setShowCheetsheet((s) => !s)}
                                    labelClasses='cursor-pointer'
                                >
                                    <Link size={22} className={`px-5 h-5 w-5`} />
                                </ItemContainer>
                            </a>
                            {/* This table would be pretty awkward to make look nice
                            Maybe there could be an element for a dropdown later? */}
                            {/* {showCheatsheet && (
                            <div className={`block md:flex w-full`}>
                                <ScheduleCheatsheetCards />
                            </div>
                        )} */}
                            <FormikSwitchV2
                                name={'onlyWhenOnline'}
                                description={t('only_when_online_description')}
                                label={t('only_when_online_label')}
                            />
                            <FormikSwitchV2
                                name={'enabled'}
                                description={t('enabled_description')}
                                label={t('enabled_label')}
                            />
                        </div>
                        <div className={`mb-6 text-right`}>
                            <Button
                                className={'w-full sm:w-auto'}
                                type={'submit'}
                                disabled={isSubmitting}
                            >
                                {schedule ? t('save_changes') : t('create_schedule')}
                            </Button>
                        </div>
                    </Form>
                );
            }}
        </Formik>
    );
};

export default asModal<Props>()(EditScheduleModal);
