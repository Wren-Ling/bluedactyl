import { CircleHelp, CloudUpload, PencilLine, Power, Terminal, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';
import Can from '@/components/elements/Can';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import ItemContainer from '@/components/elements/ItemContainer';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import TaskDetailsModal from '@/components/server/schedules/TaskDetailsModal';

import { httpErrorToHuman } from '@/api/http';
import deleteScheduleTask from '@/api/server/schedules/deleteScheduleTask';
import { Schedule, Task } from '@/api/server/schedules/getServerSchedules';

import { ServerContext } from '@/state/server';

import useFlash from '@/plugins/useFlash';

interface Props {
    schedule: Schedule;
    task: Task;
}

const getActionDetails = (action: string, t: (key: string) => string): [string, any, boolean?] => {
    switch (action) {
        case 'command':
            return [t('action_send_command'), Terminal, true];
        case 'power':
            return [t('action_send_power'), Power];
        case 'backup':
            return [t('action_create_backup'), CloudUpload];
        default:
            return [t('action_unknown'), CircleHelp];
    }
};

const ScheduleTaskRow = ({ schedule, task }: Props) => {
    const { t } = useTranslation('schedules');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { clearFlashes, addError } = useFlash();
    const [visible, setVisible] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [isEditing, setIsEditing] = useState(false);
    const appendSchedule = ServerContext.useStoreActions((actions) => actions.schedules.appendSchedule);

    const onConfirmDeletion = () => {
        setIsLoading(true);
        clearFlashes('schedules');
        deleteScheduleTask(uuid, schedule.id, task.id)
            .then(() =>
                appendSchedule({
                    ...schedule,
                    tasks: schedule.tasks.filter((t) => t.id !== task.id),
                }),
            )
            .catch((error) => {
                console.error(error);
                setIsLoading(false);
                addError({ message: httpErrorToHuman(error), key: 'schedules' });
            });
    };

    const [title, icon, copyOnClick] = getActionDetails(task.action, t);

    return (
        <ItemContainer
            title={title}
            description={
                task.payload && task.payload.length > 100 ? `${task.payload.substring(0, 100)}...` : task.payload
            }
            icon={icon}
            divClasses={`mb-2 gap-6`}
            copyDescription={copyOnClick}
            descriptionClasses={`whitespace-nowrap overflow-hidden text-ellipsis`}
        >
            <SpinnerOverlay visible={isLoading} fixed size={'large'} />
            <TaskDetailsModal
                schedule={schedule}
                task={task}
                visible={isEditing}
                onModalDismissed={() => setIsEditing(false)}
            />
            <Dialog open={visible} onOpenChange={(o) => { if (!o) setVisible(false); }}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('confirm_task_deletion')}</DialogTitle>
                    </DialogHeader>
                    {t('confirm_task_deletion_description')}
                    <DialogFooter>
                        <Button variant='outline' onClick={() => setVisible(false)}>{t('cancel')}</Button>
                        <Button variant='destructive' onClick={onConfirmDeletion}>{t('delete_task')}</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            {/* <div className={`flex-none sm:flex-1 w-full sm:w-auto overflow-x-auto`}>
                <p className={`md:ml-6 text-foreground uppercase text-sm`}>{title}</p>
                {task.payload && (
                    <div className={`md:ml-6 mt-2`}>
                        {task.action === 'backup' && (
                            <p className={`text-xs uppercase text-muted-foreground mb-1`}>Ignoring files & folders:</p>
                        )}
                        <div
                            className={`font-mono bg-muted rounded-sm py-1 px-2 text-sm w-auto inline-block whitespace-pre-wrap break-all`}
                        >
                            {task.payload && task.payload.length > 100
                                ? `${task.payload.substring(0, 100)}...`
                                : task.payload}
                        </div>
                    </div>
                )}
            </div> */}
            <div className={`flex flex-none items-end sm:items-center flex-col sm:flex-row gap-2`}>
                <div className='mr-0 sm:mr-6'>
                    {task.continueOnFailure && (
                        <div className={`px-2 py-1 bg-yellow-500 text-yellow-800 text-sm rounded-full`}>
                            {t('continues_on_failure')}
                        </div>
                    )}
                    {task.sequenceId > 1 && task.timeOffset > 0 && (
                        <div className={`px-2 py-1 bg-zinc-500 text-sm rounded-full`}>{task.timeOffset}s later</div>
                    )}
                </div>
                <Can action={'schedule.update'}>
                    <Button
                        variant='secondary'
                        size='sm'
                        className='flex flex-row items-center gap-2 ml-auto sm:ml-0'
                        onClick={() => setIsEditing(true)}
                        aria-label={t('edit_scheduled_task_aria')}
                    >
                        <PencilLine size={22} />
                        {t('edit')}
                    </Button>
                </Can>
                <Can action={'schedule.update'}>
                    <Button
                        variant='destructive'
                        size='sm'
                        onClick={() => setVisible(true)}
                        className='flex items-center gap-2'
                        aria-label={t('delete_scheduled_task_aria')}
                    >
                        <Trash2 size={22} className='w-4 h-4' />
                        <span className='hidden sm:inline'>{t('delete')}</span>
                    </Button>
                </Can>
            </div>
        </ItemContainer>
    );
};

export default ScheduleTaskRow;
