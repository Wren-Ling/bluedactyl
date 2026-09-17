import { Form, Formik } from 'formik';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';
import { object, string } from 'yup';

import FlashMessageRender from '@/components/FlashMessageRender';
import Field from '@/components/elements/Field';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

import pullFile from '@/api/server/files/pullFile';

import { ServerContext } from '@/state/server';

import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import { useFlashKey } from '@/plugins/useFlash';

const PullFileButton = () => {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const { mutate } = useFileManagerSwr();
    const { clearFlashes, clearAndAddHttpError } = useFlashKey('files:pull-modal');

    const schema = object({
        url: string()
            .trim()
            .required(t('files:valid_download_url'))
            .test('http-url', t('files:valid_download_url'), (value) => {
                try {
                    const url = new URL(value ?? '');
                    return url.protocol === 'http:' || url.protocol === 'https:';
                } catch {
                    return false;
                }
            }),
    });

    return (
        <>
            <Button
                variant='secondary'
                onClick={() => {
                    clearFlashes();
                    setOpen(true);
                }}
            >
                {t('files:remote_download')}
            </Button>
            {open && (
                <Formik
                    initialValues={{ url: '' }}
                    validationSchema={schema}
                    onSubmit={async ({ url }) => {
                        clearFlashes();
                        try {
                            await pullFile(uuid, directory, url.trim());
                        } catch (error) {
                            clearAndAddHttpError(error as Error);
                            return;
                        }

                        setOpen(false);
                        toast.success(t('files:remote_download_submitted'));
                        // Refresh failures must not turn an accepted download into a retryable error.
                        void mutate().catch(() => undefined);
                    }}
                >
                    {({ isSubmitting }) => (
                        <Dialog
                            open={open}
                            onOpenChange={(value) => {
                                if (!isSubmitting) {
                                    clearFlashes();
                                    setOpen(value);
                                }
                            }}
                        >
                            <DialogContent showCloseButton={!isSubmitting}>
                                <DialogHeader>
                                    <DialogTitle>{t('files:remote_download')}</DialogTitle>
                                    <DialogDescription>{t('files:remote_download_description')}</DialogDescription>
                                </DialogHeader>
                                <FlashMessageRender byKey='files:pull-modal' />
                                <Form className='space-y-4'>
                                    <Field
                                        autoFocus
                                        id='pull-url'
                                        name='url'
                                        type='url'
                                        label={t('files:download_url')}
                                        placeholder='https://example.com/file.zip'
                                        disabled={isSubmitting}
                                    />
                                    <p className='text-sm text-muted-foreground break-all'>
                                        {t('files:remote_download_directory', { directory })}
                                    </p>
                                    <DialogFooter>
                                        <Button
                                            type='button'
                                            variant='outline'
                                            disabled={isSubmitting}
                                            onClick={() => {
                                                clearFlashes();
                                                setOpen(false);
                                            }}
                                        >
                                            {t('files:cancel')}
                                        </Button>
                                        <Button type='submit' disabled={isSubmitting}>
                                            {t(isSubmitting ? 'files:remote_download_submitting' : 'files:download')}
                                        </Button>
                                    </DialogFooter>
                                </Form>
                            </DialogContent>
                        </Dialog>
                    )}
                </Formik>
            )}
        </>
    );
};

export default PullFileButton;
