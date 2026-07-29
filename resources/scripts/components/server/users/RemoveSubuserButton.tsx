import { Trash2 } from 'lucide-react';
import { Actions, useStoreActions } from 'easy-peasy';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

import { httpErrorToHuman } from '@/api/http';
import deleteSubuser from '@/api/server/users/deleteSubuser';

import { ApplicationStore } from '@/state';
import { ServerContext } from '@/state/server';
import { Subuser } from '@/state/server/subusers';

const RemoveSubuserButton = ({ subuser }: { subuser: Subuser }) => {
    const { t } = useTranslation('users');
    const [loading, setLoading] = useState(false);
    const [showConfirmation, setShowConfirmation] = useState(false);

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const removeSubuser = ServerContext.useStoreActions((actions) => actions.subusers.removeSubuser);
    const { addError, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const doDeletion = () => {
        setLoading(true);
        clearFlashes('users');
        deleteSubuser(uuid, subuser.uuid)
            .then(() => {
                setLoading(false);
                removeSubuser(subuser.uuid);
                setShowConfirmation(false);
            })
            .catch((error) => {
                console.error(error);
                addError({ key: 'users', message: httpErrorToHuman(error) });
                setShowConfirmation(false);
            });
    };

    return (
        <>
            <Dialog open={showConfirmation} onOpenChange={setShowConfirmation}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('remove_title', { username: subuser.username })}</DialogTitle>
                    </DialogHeader>
                    {t('remove_description')}
                    <DialogFooter>
                        <Button variant='outline' onClick={() => setShowConfirmation(false)}>{t('cancel')}</Button>
                        <Button variant='destructive' onClick={doDeletion} disabled={loading}>
                            {loading ? t('removing') : t('remove_button', { username: subuser.username })}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            <Button
                variant='destructive'
                size='sm'
                className='flex items-center gap-2'
                onClick={() => setShowConfirmation(true)}
                aria-label={t('delete_subuser_aria')}
            >
                <Trash2 size={22} className='size-4' />
                {t('delete')}
            </Button>
        </>
    );
};

export default RemoveSubuserButton;
