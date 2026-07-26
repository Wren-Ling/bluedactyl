import { Trash2 } from 'lucide-react';
import { Actions, useStoreActions } from 'easy-peasy';
import { useState } from 'react';

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
                        <DialogTitle>Remove {subuser.username}?</DialogTitle>
                    </DialogHeader>
                    All access to the server will be removed immediately.
                    <DialogFooter>
                        <Button variant='outline' onClick={() => setShowConfirmation(false)}>Cancel</Button>
                        <Button variant='destructive' onClick={doDeletion} disabled={loading}>
                            {loading ? 'Removing...' : `Remove ${subuser.username}`}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            <Button
                variant='destructive'
                size='sm'
                className='flex items-center gap-2'
                onClick={() => setShowConfirmation(true)}
                aria-label='Delete subuser'
            >
                <Trash2 size={22} className='size-4' />
                Delete
            </Button>
        </>
    );
};

export default RemoveSubuserButton;
