import { useTranslation } from 'react-i18next';
import { Form, Formik, FormikHelpers } from 'formik';
import { join } from 'pathe';
import { useEffect, useState } from 'react';
import { object, string } from 'yup';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import Code from '@/components/elements/Code';
import Field from '@/components/elements/Field';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';

import createDirectory from '@/api/server/files/createDirectory';

// import { FileObject } from '@/api/server/files/loadDirectory';
import { ServerContext } from '@/state/server';

import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import { useFlashKey } from '@/plugins/useFlash';

interface Values {
    directoryName: string;
}

// removed to prevent linting issues, you're welcome.
//
// const generateDirectoryData = (name: string): FileObject => ({
//     key: `dir_${name.split('/', 1)[0] ?? name}`,
//     name: name.replace(/^(\/*)/, '').split('/', 1)[0] ?? name,
//     mode: 'drwxr-xr-x',
//     modeBits: '0755',
//     size: 0,
//     isFile: false,
//     isSymlink: false,
//     mimetype: '',
//     createdAt: new Date(),
//     modifiedAt: new Date(),
//     isArchiveType: () => false,
//     isEditable: () => false,
// });

const NewDirectoryDialog = ({ open, onClose }: { open: boolean; onClose: (v: boolean) => void }) => {
    const { t } = useTranslation();

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const directory = ServerContext.useStoreState((state) => state.files.directory);

    const { mutate } = useFileManagerSwr();
    const { clearAndAddHttpError } = useFlashKey('files:directory-modal');

    const schema = object().shape({
        directoryName: string().required(t('files:valid_directory_name')),
    });

    useEffect(() => {
        return () => {
            clearAndAddHttpError();
        };
    }, []);

    const submit = ({ directoryName }: Values, { setSubmitting }: FormikHelpers<Values>) => {
        createDirectory(uuid, directory, directoryName)
            // .then(() => mutate((data) => [...data!, generateDirectoryData(directoryName)], false))
            .then(() => mutate())
            .then(() => onClose(false))
            .catch((error) => {
                setSubmitting(false);
                clearAndAddHttpError(error);
            });
    };

    return (
        <Formik onSubmit={submit} validationSchema={schema} initialValues={{ directoryName: '' }}>
            {({ submitForm, values }) => (
                <Dialog open={open} onOpenChange={onClose}>
                    <DialogContent>
                        <DialogHeader><DialogTitle>{t('files:new_folder')}</DialogTitle></DialogHeader>
                        <FlashMessageRender byKey='files:directory-modal' />
                        <Form className={`m-0`}>
                            <Field autoFocus id={'directoryName'} name={'directoryName'} label={t('files:name')} />
                            <p className={`mt-2 text-xs! break-all`}>
                                <span className={`text-foreground/80`}>{t('files:folder_created_as')}&nbsp;</span>
                                <Code>
                                    /root/
                                    <span className={`text-blue-200`}>
                                        {join(directory, values.directoryName).replace(/^(\.\.\/|\/)+/, '')}
                                    </span>
                                </Code>
                            </p>
                        </Form>
                        <DialogFooter>
                            <Button variant='outline' className={'w-full sm:w-auto'} onClick={() => onClose(false)}>
                                {t('files:cancel')}
                            </Button>
                            <Button className={'w-full sm:w-auto'} onClick={submitForm}>
                                {t('files:create')}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            )}
        </Formik>
    );
};

const NewDirectoryButton = () => {
    const { t } = useTranslation();

    const [open, setOpen] = useState(false);

    return (
        <>
            <NewDirectoryDialog open={open} onClose={setOpen} />
            <Button variant='secondary' onClick={() => setOpen(true)}>
                {t('files:new_folder')}
            </Button>
        </>
    );
};

export default NewDirectoryButton;
