import { Actions, useStoreActions } from 'easy-peasy';
import { useEffect, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';

import { httpErrorToHuman } from '@/api/http';
import reinstallServer from '@/api/server/reinstallServer';

import { ApplicationStore } from '@/state';
import { ServerContext } from '@/state/server';

const ReinstallServerBox = () => {
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const [modalVisible, setModalVisible] = useState(false);
    const [loading, setLoading] = useState(false);
    const { addFlash, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const reinstall = () => {
        setLoading(true);
        clearFlashes('settings');
        reinstallServer(uuid)
            .then(() => {
                addFlash({
                    key: 'settings',
                    type: 'success',
                    message: 'Your server has begun the reinstallation process.',
                });
            })
            .catch((error) => {
                console.error(error);

                addFlash({ key: 'settings', type: 'error', message: httpErrorToHuman(error) });
            })
            .then(() => {
                setLoading(false);
                setModalVisible(false);
            });
    };

    useEffect(() => {
        clearFlashes();
    }, []);

    return (
        <Card>
            <CardHeader>
                <CardTitle className='text-xl font-extrabold tracking-tight'>Reinstall Server</CardTitle>
            </CardHeader>
            <CardContent>
                <Dialog open={modalVisible} onOpenChange={setModalVisible}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Confirm server reinstallation</DialogTitle>
                        </DialogHeader>
                        <div className='text-sm text-muted-foreground'>
                            Your server will be stopped and some files may be deleted or modified during this process, are
                            you sure you wish to continue?
                        </div>
                        <DialogFooter>
                            <Button variant='outline' onClick={() => setModalVisible(false)} disabled={loading}>
                                Cancel
                            </Button>
                            <Button variant='destructive' onClick={reinstall} isLoading={loading}>
                                Yes, reinstall server
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
                <p className={`text-sm`}>
                    Reinstalling your server will stop it, and then re-run the installation script that initially set it
                    up.&nbsp;
                    <strong className={`font-medium`}>
                        Some files may be deleted or modified during this process, please back up your data before
                        continuing.
                    </strong>
                </p>
                <div className={`mt-6 text-right`}>
                    <Button variant='destructive' onClick={() => setModalVisible(true)}>
                        Reinstall Server
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
};

export default ReinstallServerBox;
