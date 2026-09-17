import ModalContext from '@/context/ModalContext';
import { Form, Formik, Field as FormikField, FormikHelpers, useField } from 'formik';
import { useContext, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { boolean, number, object, string } from 'yup';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import Field from '@/components/elements/Field';
import FormikFieldWrapper from '@/components/elements/FormikFieldWrapper';
import FormikSwitchV2 from '@/components/elements/FormikSwitchV2';
import { Textarea } from '@/components/ui/textarea';
import Select from '@/components/elements/Select';

import asModal from '@/hoc/asModal';

import { httpErrorToHuman } from '@/api/http';
import createOrUpdateScheduleTask from '@/api/server/schedules/createOrUpdateScheduleTask';
import { Schedule, Task } from '@/api/server/schedules/getServerSchedules';

import { ServerContext } from '@/state/server';

import useFlash from '@/plugins/useFlash';

// TODO: Port modern dropdowns to Formik and integrate them
// import { DropdownMenu, DropdownMenuContent, DropdownMenuRadioGroup, DropdownMenuRadioItem } from '@/components/ui/dropdown-menu';
// import { DropdownMenuTrigger } from '@radix-ui/react-dropdown-menu';

interface Props {
    schedule: Schedule;
    // If a task is provided we can assume we're editing it. If not provided,
    // we are creating a new one.
    task?: Task;
}

interface Values {
    action: string;
    payload: string;
    timeOffset: string;
    continueOnFailure: boolean;
}

const schema = object().shape({
    action: string().required().oneOf(['command', 'power', 'backup']),
    payload: string().when('action', {
        is: (v) => v !== 'backup',
        then: () => string().required('A task payload must be provided.'),
        otherwise: () => string(),
    }),
    continueOnFailure: boolean(),
    timeOffset: number()
        .typeError('The time offset must be a valid number between 0 and 900.')
        .required('A time offset value must be provided.')
        .min(0, 'The time offset must be at least 0 seconds.')
        .max(900, 'The time offset must be less than 900 seconds.'),
});

const ActionListener = () => {
    const [{ value }, { initialValue: initialAction }] = useField<string>('action');
    const [, { initialValue: initialPayload }, { setValue, setTouched }] = useField<string>('payload');

    useEffect(() => {
        if (value !== initialAction) {
            setValue(value === 'power' ? 'start' : '');
            setTouched(false);
        } else {
            setValue(initialPayload || '');
            setTouched(false);
        }
    }, [value]);

    return null;
};

const TaskDetailsModal = ({ schedule, task }: Props) => {
    const { t } = useTranslation('schedules');
    const { dismiss, setPropOverrides } = useContext(ModalContext);
    const { clearFlashes, addError } = useFlash();

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const appendSchedule = ServerContext.useStoreActions((actions) => actions.schedules.appendSchedule);
    const backupLimit = ServerContext.useStoreState((state) => state.server.data!.featureLimits.backups);

    useEffect(() => {
        return () => {
            clearFlashes('schedule:task');
        };
    }, []);

    useEffect(() => {
        setPropOverrides({ title: task ? t('edit_task') : t('create_task') });
    }, []);

    const submit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes('schedule:task');
        if (backupLimit === 0 && values.action === 'backup') {
            setSubmitting(false);
            addError({
                message: t('backup_limit_error'),
                key: 'schedule:task',
            });
        } else {
            createOrUpdateScheduleTask(uuid, schedule.id, task?.id, values)
                .then((task) => {
                    let tasks = schedule.tasks.map((t) => (t.id === task.id ? task : t));
                    if (!schedule.tasks.find((t) => t.id === task.id)) {
                        tasks = [...tasks, task];
                    }

                    appendSchedule({ ...schedule, tasks });
                    dismiss();
                })
                .catch((error) => {
                    console.error(error);
                    setSubmitting(false);
                    addError({ message: httpErrorToHuman(error), key: 'schedule:task' });
                });
        }
    };

    return (
        <div className='min-w-full'>
            <Formik
                onSubmit={submit}
                validationSchema={schema}
                initialValues={{
                    action: task?.action || 'command',
                    payload: task?.payload || '',
                    timeOffset: task?.timeOffset.toString() || '0',
                    continueOnFailure: task?.continueOnFailure || false,
                }}
            >
                {({ isSubmitting, values }) => (
                    <Form>
                        <FlashMessageRender byKey={'schedule:task'} />
                        <div className={`flex flex-col gap-3`}>
                            <div>
                                <label className='inline-block text-muted-foreground text-sm pb-2'>{t('action_label')}</label>
                                <ActionListener />
                                <FormikFieldWrapper name={'action'}>
                                    <FormikField
                                        className='px-4 py-2 bg-muted/30 rounded-lg min-w-full'
                                        as={Select}
                                        name={'action'}
                                    >
                                        <option className='bg-black' value={'command'}>
                                            {t('action_send_command')}
                                        </option>
                                        <option className='bg-black' value={'power'}>
                                            {t('action_power')}
                                        </option>
                                        <option className='bg-black' value={'backup'}>
                                            {t('action_create_backup')}
                                        </option>
                                    </FormikField>
                                </FormikFieldWrapper>
                            </div>
                            <div>
                                <Field
                                    name={'timeOffset'}
                                    label={t('time_offset_label')}
                                    description={t('time_offset_description')}
                                />
                            </div>
                        </div>
                        <div className={`my-6`}>
                            {values.action === 'command' ? (
                                <div>
                                    <label className='inline-block text-muted-foreground text-sm pb-2'>{t('payload_label')}</label>
                                    <FormikFieldWrapper name={'payload'}>
                                        <FormikField
                                            className='w-full rounded-xl p-2 bg-muted/30'
                                            as={Textarea}
                                            name={'payload'}
                                            rows={6}
                                        />
                                    </FormikFieldWrapper>
                                </div>
                            ) : values.action === 'power' ? (
                                <div>
                                    <label className='inline-block text-muted-foreground text-sm pb-2'>{t('payload_label')}</label>
                                    <FormikFieldWrapper name={'payload'}>
                                        <FormikField
                                            className='px-4 py-2 bg-muted/30 rounded-lg min-w-full'
                                            as={Select}
                                            name={'payload'}
                                        >
                                            <option className='bg-black' value={'start'}>
                                                {t('power_start')}
                                            </option>
                                            <option className='bg-black' value={'restart'}>
                                                {t('power_restart')}
                                            </option>
                                            <option className='bg-black' value={'stop'}>
                                                {t('power_stop')}
                                            </option>
                                            <option className='bg-black' value={'kill'}>
                                                {t('power_kill')}
                                            </option>
                                        </FormikField>
                                    </FormikFieldWrapper>
                                </div>
                            ) : (
                                <div>
                                    <label className='inline-block text-muted-foreground text-sm pb-2'>{t('ignored_files_label')}</label>
                                    <FormikFieldWrapper
                                        name={'payload'}
                                        description={t('ignored_files_description')}
                                    >
                                        <FormikField
                                            className='w-full rounded-xl bg-muted/30'
                                            as={Textarea}
                                            name={'payload'}
                                            rows={6}
                                        />
                                    </FormikFieldWrapper>
                                </div>
                            )}
                        </div>
                        <FormikSwitchV2
                            name={'continueOnFailure'}
                            description={t('continue_on_failure_description')}
                            label={t('continue_on_failure_label')}
                        />
                        <div className={`flex justify-end my-6`}>
                            <Button type={'submit'} disabled={isSubmitting}>
                                {task ? t('save_changes') : t('create_task')}
                            </Button>
                        </div>
                    </Form>
                )}
            </Formik>
        </div>
    );
};

export default asModal<Props>()(TaskDetailsModal);
