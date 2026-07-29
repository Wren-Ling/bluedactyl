import { Actions, useStoreActions } from 'easy-peasy';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';

import { httpErrorToHuman } from '@/api/http';
import reinstallServer from '@/api/server/reinstallServer';

import { ApplicationStore } from '@/state';
import { ServerContext } from '@/state/server';

const ReinstallServerBox = () => {
    const { t } = useTranslation('settings');
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
                    message: t('reinstall_started'),
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
                <CardTitle className='text-xl font-extrabold tracking-tight'>{t('reinstall_title')}</CardTitle>
            </CardHeader>
            <CardContent>
                <Dialog open={modalVisible} onOpenChange={setModalVisible}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>{t('reinstall_confirm_title')}</DialogTitle>
                        </DialogHeader>
                        <div className='text-sm text-muted-foreground'>
                            {t('reinstall_confirm_description')}
                        </div>
                        <DialogFooter>
                            <Button variant='outline' onClick={() => setModalVisible(false)} disabled={loading}>
                                {t('cancel')}
                            </Button>
                            <Button variant='destructive' onClick={reinstall} isLoading={loading}>
                                {t('reinstall_yes')}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
                <p className={`text-sm`}>
                    {t('reinstall_description')}&nbsp;
                    <strong className={`font-medium`}>
                        {t('reinstall_warning')}
                    </strong>
                </p>
                <div className={`mt-6 text-right`}>
                    <Button variant='destructive' onClick={() => setModalVisible(true)}>
                        {t('reinstall_button')}
                    </Button>
                </div>
            </CardContent>
        </Card>
    );
};

export default ReinstallServerBox;
