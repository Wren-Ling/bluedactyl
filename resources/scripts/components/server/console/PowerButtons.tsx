import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { useTranslation } from 'react-i18next';

import Can from '@/components/elements/Can';
import { Dialog } from '@/components/elements/dialog';
import { PowerAction } from '@/components/server/console/ServerConsoleContainer';
import { Button } from '@/components/ui/button';

import { ServerContext } from '@/state/server';

interface PowerButtonProps {
    className?: string;
}

const PowerButtons = ({ className }: PowerButtonProps) => {
    const { t } = useTranslation();
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
                toast.success(t('console:server_starting'));
            } else if (action === 'restart') {
                toast.success(t('console:server_restarting'));
            } else {
                toast.success(t('console:server_stopping'));
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
                title={t('console:forcibly_stop_process')}
                confirm={t('console:continue')}
                onConfirmed={onButtonClick.bind(this, 'kill-confirmed')}
            >
                {t('console:forcibly_stop_warning')}
            </Dialog.Confirm>
            <Can action={'control.start'}>
                <Button
                    variant={status === 'offline' ? 'default' : 'secondary'}
                    className='rounded-l-full rounded-r-md px-8'
                    disabled={status !== 'offline'}
                    onClick={onButtonClick.bind(this, 'start')}
                >
                    {t('console:power_start')}
                </Button>
            </Can>
            <Can action={'control.restart'}>
                <Button
                    variant='secondary'
                    className='rounded-none px-8'
                    disabled={!status}
                    onClick={onButtonClick.bind(this, 'restart')}
                >
                    {t('console:power_restart')}
                </Button>
            </Can>
            <Can action={'control.stop'}>
                <Button
                    variant={status === 'offline' ? 'secondary' : 'destructive'}
                    className='rounded-r-full rounded-l-md px-8'
                    disabled={status === 'offline'}
                    onClick={onButtonClick.bind(this, killable ? 'kill' : 'stop')}
                >
                    {killable ? t('console:power_kill') : t('console:power_stop')}
                </Button>
            </Can>
        </div>
    );
};

export default PowerButtons;
