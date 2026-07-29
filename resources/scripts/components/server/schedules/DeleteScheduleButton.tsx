import { Actions, useStoreActions } from 'easy-peasy';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import { Dialog } from '@/components/elements/dialog';

import { httpErrorToHuman } from '@/api/http';
import deleteSchedule from '@/api/server/schedules/deleteSchedule';

import { ApplicationStore } from '@/state';
import { ServerContext } from '@/state/server';

interface Props {
    scheduleId: number;
    onDeleted: () => void;
}

const DeleteScheduleButton = ({ scheduleId, onDeleted }: Props) => {
    const { t } = useTranslation('schedules');
    const [visible, setVisible] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { addError, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const onDelete = () => {
        setIsLoading(true);
        clearFlashes('schedules');
        deleteSchedule(uuid, scheduleId)
            .then(() => {
                setIsLoading(false);
                onDeleted();
            })
            .catch((error) => {
                console.error(error);

                addError({ key: 'schedules', message: httpErrorToHuman(error) });
                setIsLoading(false);
                setVisible(false);
            });
    };

    return (
        <>
            <Dialog.Confirm
                open={visible}
                onClose={() => setVisible(false)}
                title={t('delete_schedule_title')}
                confirm={t('delete')}
                onConfirmed={onDelete}
                loading={isLoading}
            >
                {t('delete_schedule_description')}
            </Dialog.Confirm>
            <Button variant='destructive' className={'flex-1 sm:flex-none'} onClick={() => setVisible(true)}>
                {t('delete')}
            </Button>
        </>
    );
};

export default DeleteScheduleButton;
