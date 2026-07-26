import { useEffect, useState } from 'react';
import { toast } from 'sonner';

import Can from '@/components/elements/Can';
import { Dialog } from '@/components/elements/dialog';
import { PowerAction } from '@/components/server/console/ServerConsoleContainer';
import { Button } from '@/components/ui/button';

import { ServerContext } from '@/state/server';

interface PowerButtonProps {
    className?: string;
}

const PowerButtons = ({ className }: PowerButtonProps) => {
    const [open, setOpen] = useState(false);
    const status = ServerContext.useStoreState((state) => state.status.value);
    const instance = ServerContext.useStoreState((state) => state.socket.instance);

    const killable = status === 'stopping';
    const onButtonClick = (
        action: PowerAction | 'kill-confirmed',
        e: React.MouseEvent<HTMLButtonElement, MouseEvent>,
    ): void => {
        e.preventDefault();
        if (action === 'kill') {
            return setOpen(true);
        }

        if (instance) {
            if (action === 'start') {
                toast.success('Your server is starting!');
            } else if (action === 'restart') {
                toast.success('Your server is restarting.');
            } else {
                toast.success('Your server is being stopped.');
            }
            setOpen(false);
            instance.send('set state', action === 'kill-confirmed' ? 'kill' : action);
        }
    };

    useEffect(() => {
        if (status === 'offline') {
            setOpen(false);
        }
    }, [status]);

    if (!status) {
        return null;
    }

    return (
        <div className={className}>
            <Dialog.Confirm
                open={open}
                hideCloseIcon
                onClose={() => setOpen(false)}
                title={'Forcibly Stop Process'}
                confirm={'Continue'}
                onConfirmed={onButtonClick.bind(this, 'kill-confirmed')}
            >
                Forcibly stopping a server can lead to data corruption.
            </Dialog.Confirm>
            <Can action={'control.start'}>
                <Button
                    variant={status === 'offline' ? 'default' : 'secondary'}
                    className='rounded-l-full rounded-r-md px-8'
                    disabled={status !== 'offline'}
                    onClick={onButtonClick.bind(this, 'start')}
                >
                    Start
                </Button>
            </Can>
            <Can action={'control.restart'}>
                <Button
                    variant='secondary'
                    className='rounded-none px-8'
                    disabled={!status}
                    onClick={onButtonClick.bind(this, 'restart')}
                >
                    Restart
                </Button>
            </Can>
            <Can action={'control.stop'}>
                <Button
                    variant={status === 'offline' ? 'secondary' : 'destructive'}
                    className='rounded-r-full rounded-l-md px-8'
                    disabled={status === 'offline'}
                    onClick={onButtonClick.bind(this, killable ? 'kill' : 'stop')}
                >
                    {killable ? 'Kill' : 'Stop'}
                </Button>
            </Can>
        </div>
    );
};

export default PowerButtons;
