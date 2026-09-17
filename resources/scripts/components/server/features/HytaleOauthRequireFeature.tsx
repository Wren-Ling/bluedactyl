import { useEffect, useState } from 'react';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { SocketEvent } from '@/components/server/events';

import { ServerContext } from '@/state/server';

import useFlash from '@/plugins/useFlash';

const HytaleOauthRequireFeature = () => {
    const [visible, setVisible] = useState(false);
    const [userCode, setUserCode] = useState('');
    const [verificationUri, setVerificationUri] = useState('');

    const status = ServerContext.useStoreState((state) => state.status.value);
    const { clearFlashes } = useFlash();
    const { connected, instance } = ServerContext.useStoreState((state) => state.socket);

    useEffect(() => {
        if (!connected || !instance || status === 'running') return;

        const listener = (line: string) => {
            const urlMatch = line.match(
                /https:\/\/oauth\.accounts\.hytale\.com\/oauth2\/device\/verify\?user_code=([a-zA-Z0-9\s]+)/i,
            );
            if (urlMatch) {
                const code = urlMatch[1]?.trim() || '';
                setUserCode(code);
                setVerificationUri(urlMatch[0] || '');
                setVisible(true);
                return;
            }
        };

        instance.addListener(SocketEvent.CONSOLE_OUTPUT, listener);
        return () => {
            instance.removeListener(SocketEvent.CONSOLE_OUTPUT, listener);
        };
    }, [connected, instance, status]);

    useEffect(() => {
        clearFlashes('feature:hytaleOauth');
    }, []);

    const handleAuthenticate = () => {
        if (verificationUri) {
            window.open(verificationUri, '_blank', 'noopener,noreferrer');
            setVisible(false);
        }
    };

    return (
        <Dialog open={visible} onOpenChange={(o) => { if (!o) { setVisible(false); setUserCode(''); setVerificationUri(''); } }}>
            <DialogContent className='sm:max-w-md'>
                <DialogHeader>
                    <DialogTitle>Hytale Authentication</DialogTitle>
                </DialogHeader>
                <FlashMessageRender key='feature:hytaleOauth' />
                <div>
                    <div className='text-center text-foreground/80 mb-6'>
                        <p className='mb-4 text-md'>
                            Server requires authentication to start. Click below to verify this device.
                        </p>
                    </div>

                    <Button
                        onClick={handleAuthenticate}
                        className='w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded mb-6 flex items-center justify-center gap-2'
                    >
                        Authenticate Server
                    </Button>
                    <div className='relative my-6'>
                        <div className='absolute inset-0 flex items-center'>
                            <div className='w-full h-px bg-white/20'></div>
                        </div>

                        <div className='relative flex justify-center text-muted-foreground uppercase text-sm tracking-wider'>
                            <span className='bg-zinc-900 px-5'>OR ENTER CODE MANUALLY</span>
                        </div>
                    </div>

                    <div className='bg-zinc-900 border border-border rounded p-4 text-center'>
                        <div className='text-muted-foreground text-sm mb-2'>DEVICE CODE</div>
                        {userCode ? (
                            <div
                                className='text-3xl font-mono text-white tracking-wider mb-2 cursor-pointer hover:text-foreground/80 transition-colors'
                                onClick={() => navigator.clipboard.writeText(userCode)}
                            >
                                {userCode}
                            </div>
                        ) : (
                            <div className='text-3xl font-mono text-white tracking-wider mb-2'>•••• ••••</div>
                        )}
                    </div>

                    <p className='text-foreground0 text-xs text-center mt-4'>Only required once per server</p>
                </div>
            </DialogContent>
        </Dialog>
    );
};

export default HytaleOauthRequireFeature;
