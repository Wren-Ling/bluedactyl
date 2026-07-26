import { Eye, EyeOff, Key, Plus, Trash2 } from 'lucide-react';
import { format } from 'date-fns';
import { Actions, useStoreActions } from 'easy-peasy';
import { Field, Form, Formik, FormikHelpers } from 'formik';
import { useEffect, useState } from 'react';
import { object, string } from 'yup';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import FormikFieldWrapper from '@/components/elements/FormikFieldWrapper';
import { Input } from '@/components/ui/input';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';

import { createSSHKey, deleteSSHKey, useSSHKeys } from '@/api/account/ssh-keys';
import { httpErrorToHuman } from '@/api/http';

import { ApplicationStore } from '@/state';

import { useFlashKey } from '@/plugins/useFlash';

interface CreateValues {
    name: string;
    publicKey: string;
}

const AccountSSHContainer = () => {
    const [deleteKey, setDeleteKey] = useState<{ name: string; fingerprint: string } | null>(null);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showKeys, setShowKeys] = useState<Record<string, boolean>>({});

    const { clearAndAddHttpError } = useFlashKey('account:ssh-keys');
    const { addError, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);
    const { data, isValidating, error, mutate } = useSSHKeys({
        revalidateOnMount: true,
        revalidateOnFocus: false,
    });

    useEffect(() => {
        clearAndAddHttpError(error);
    }, [error]);

    const doDeletion = () => {
        if (!deleteKey) return;

        clearAndAddHttpError();
        Promise.all([
            mutate((data) => data?.filter((value) => value.fingerprint !== deleteKey.fingerprint), false),
            deleteSSHKey(deleteKey.fingerprint),
        ])
            .catch((error) => {
                mutate(undefined, true).catch(console.error);
                clearAndAddHttpError(error);
            })
            .finally(() => {
                setDeleteKey(null);
            });
    };

    const submitCreate = (values: CreateValues, { setSubmitting, resetForm }: FormikHelpers<CreateValues>) => {
        clearFlashes('account:ssh-keys');
        createSSHKey(values.name, values.publicKey)
            .then((key) => {
                resetForm();
                setSubmitting(false);
                mutate((data) => (data || []).concat(key));
                setShowCreateModal(false);
            })
            .catch((error) => {
                console.error(error);
                addError({ key: 'account:ssh-keys', message: httpErrorToHuman(error) });
                setSubmitting(false);
            });
    };

    const toggleKeyVisibility = (fingerprint: string) => {
        setShowKeys((prev) => ({
            ...prev,
            [fingerprint]: !prev[fingerprint],
        }));
    };

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <FlashMessageRender byKey='account:ssh-keys' />

            {/* Create SSH Key Dialog */}
            <Dialog open={showCreateModal} onOpenChange={setShowCreateModal}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add SSH Key</DialogTitle>
                    </DialogHeader>
                    <Formik
                        onSubmit={submitCreate}
                        initialValues={{ name: '', publicKey: '' }}
                        validationSchema={object().shape({
                            name: string().required('SSH Key Name is required'),
                            publicKey: string().required('Public Key is required'),
                        })}
                    >
                        {({ isSubmitting }) => (
                            <Form id='create-ssh-form' className='space-y-4'>
                                <SpinnerOverlay visible={isSubmitting} />

                                <FormikFieldWrapper
                                    label='SSH Key Name'
                                    name='name'
                                    description='A name to identify this SSH key.'
                                >
                                    <Field name='name' as={Input} className='w-full' />
                                </FormikFieldWrapper>

                                <FormikFieldWrapper
                                    label='Public Key'
                                    name='publicKey'
                                    description='Enter your public SSH key.'
                                >
                                    <Field name='publicKey' as={Input} className='w-full' />
                                </FormikFieldWrapper>

                                <DialogFooter>
                                    <Button type='submit' disabled={isSubmitting}>
                                        Add Key
                                    </Button>
                                </DialogFooter>
                            </Form>
                        )}
                    </Formik>
                </DialogContent>
            </Dialog>

            <div className='flex w-full flex-1 flex-col px-2 sm:px-0'>
                <MainPageHeader
                    title='SSH Keys'
                    titleChildren={
                        <Button onClick={() => setShowCreateModal(true)} className='flex items-center gap-2'>
                            <Plus className='size-5' />
                            Add SSH Key
                        </Button>
                    }
                />

                {/* Delete SSH Key Dialog */}
                <Dialog open={!!deleteKey} onOpenChange={(o) => !o && setDeleteKey(null)}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Delete SSH Key</DialogTitle>
                            <DialogDescription>
                                Removing the <code className='rounded bg-muted px-1 font-mono text-sm'>{deleteKey?.name}</code> SSH key will invalidate its usage across the Panel.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <Button variant='outline' onClick={() => setDeleteKey(null)}>
                                Cancel
                            </Button>
                            <Button variant='destructive' onClick={doDeletion}>
                                Delete Key
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <SpinnerOverlay visible={!data && isValidating} />

                {!data || data.length === 0 ? (
                    <div className='py-12 text-center'>
                        <div className='mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-muted'>
                            <Key className='size-5 text-muted-foreground' />
                        </div>
                        <h3 className='mb-2 text-lg font-medium text-foreground'>No SSH Keys</h3>
                        <p className='mx-auto max-w-sm text-sm text-muted-foreground'>
                            {!data
                                ? 'Loading your SSH keys...'
                                : "You haven't added any SSH keys yet. Add one to securely access your servers."}
                        </p>
                    </div>
                ) : (
                    <div className='space-y-3'>
                        {data.map((key) => (
                            <div
                                key={key.fingerprint}
                                className='rounded-lg border bg-card p-4 text-card-foreground shadow-sm transition-colors hover:border-border'
                            >
                                <div className='flex items-center justify-between'>
                                    <div className='min-w-0 flex-1'>
                                        <div className='mb-2 flex items-center gap-3'>
                                            <h4 className='truncate text-sm font-medium text-foreground'>
                                                {key.name}
                                            </h4>
                                        </div>
                                        <div className='flex items-center gap-4 text-xs text-muted-foreground'>
                                            <span>Added: {format(key.createdAt, 'MMM d, yyyy HH:mm')}</span>
                                            <div className='flex items-center gap-2'>
                                                <span>Fingerprint:</span>
                                                <code className='rounded border bg-muted px-2 py-1 font-mono text-muted-foreground'>
                                                    {showKeys[key.fingerprint]
                                                        ? `SHA256:${key.fingerprint}`
                                                        : 'SHA256:••••••••••••••••'}
                                                </code>
                                                <Button
                                                    variant='ghost'
                                                    size='icon-sm'
                                                    onClick={() => toggleKeyVisibility(key.fingerprint)}
                                                >
                                                    {showKeys[key.fingerprint] ? (
                                                        <EyeOff className='size-4' />
                                                    ) : (
                                                        <Eye className='size-4' />
                                                    )}
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                    <Button
                                        variant='destructive'
                                        size='icon-sm'
                                        className='ml-4'
                                        onClick={() =>
                                            setDeleteKey({ name: key.name, fingerprint: key.fingerprint })
                                        }
                                    >
                                        <Trash2 className='size-4' />
                                    </Button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
};

export default AccountSSHContainer;
