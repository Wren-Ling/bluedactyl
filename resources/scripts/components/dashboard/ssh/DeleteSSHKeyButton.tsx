import { Trash2 } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

import { deleteSSHKey, useSSHKeys } from '@/api/account/ssh-keys';

import { useFlashKey } from '@/plugins/useFlash';

const DeleteSSHKeyButton = ({ name, fingerprint }: { name: string; fingerprint: string }) => {
    const [open, setOpen] = useState(false);
    const { clearAndAddHttpError } = useFlashKey('ssh-keys');
    const { mutate } = useSSHKeys();

    const onClick = () => {
        clearAndAddHttpError();

        Promise.all([
            mutate((data) => data?.filter((value) => value.fingerprint !== fingerprint), false),
            deleteSSHKey(fingerprint),
        ]).catch((error) => {
            mutate(undefined, true).catch(console.error);
            clearAndAddHttpError(error);
        });
        setOpen(false);
    };

    return (
        <>
            <Button variant='destructive' size='icon-sm' onClick={() => setOpen(true)}>
                <Trash2 className='size-4' />
            </Button>
            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete SSH Key</DialogTitle>
                        <DialogDescription>
                            Removing the <code className='rounded bg-muted px-1 font-mono text-sm'>{name}</code> SSH key
                            will invalidate its usage across the Panel.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant='outline' onClick={() => setOpen(false)}>
                            Cancel
                        </Button>
                        <Button variant='destructive' onClick={onClick}>
                            Delete Key
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
};

export default DeleteSSHKeyButton;
