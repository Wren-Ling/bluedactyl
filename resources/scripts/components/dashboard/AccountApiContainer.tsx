import { Eye, EyeOff, Key, Plus, Trash2 } from 'lucide-react';
import { format } from 'date-fns';
import { Actions, useStoreActions } from 'easy-peasy';
import { Field, Form, Formik, FormikHelpers } from 'formik';
import { useEffect, useState } from 'react';
import { object, string } from 'yup';

import FlashMessageRender from '@/components/FlashMessageRender';
import ApiKeyModal from '@/components/dashboard/ApiKeyModal';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import FormikFieldWrapper from '@/components/elements/FormikFieldWrapper';
import { Input } from '@/components/ui/input';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';

import createApiKey from '@/api/account/createApiKey';
import deleteApiKey from '@/api/account/deleteApiKey';
import getApiKeys, { ApiKey } from '@/api/account/getApiKeys';
import { httpErrorToHuman } from '@/api/http';

import { ApplicationStore } from '@/state';

import { useFlashKey } from '@/plugins/useFlash';

interface CreateValues {
    description: string;
    allowedIps: string;
}

const AccountApiContainer = () => {
    const [deleteIdentifier, setDeleteIdentifier] = useState('');
    const [keys, setKeys] = useState<ApiKey[]>([]);
    const [loading, setLoading] = useState(true);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [apiKey, setApiKey] = useState('');
    const [showKeys, setShowKeys] = useState<Record<string, boolean>>({});

    const { clearAndAddHttpError } = useFlashKey('api-keys');
    const { addError, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    useEffect(() => {
        getApiKeys()
            .then((keys) => setKeys(keys))
            .then(() => setLoading(false))
            .catch((error) => clearAndAddHttpError(error));
    }, []);

    const doDeletion = (identifier: string) => {
        setLoading(true);
        clearAndAddHttpError();
        deleteApiKey(identifier)
            .then(() => setKeys((s) => [...(s || []).filter((key) => key.identifier !== identifier)]))
            .catch((error) => clearAndAddHttpError(error))
            .then(() => {
                setLoading(false);
                setDeleteIdentifier('');
            });
    };

    const submitCreate = (values: CreateValues, { setSubmitting, resetForm }: FormikHelpers<CreateValues>) => {
        clearFlashes('account:api-keys');
        createApiKey(values.description, values.allowedIps)
            .then(({ secretToken, ...key }) => {
                resetForm();
                setSubmitting(false);
                setApiKey(`${key.identifier}${secretToken}`);
                setKeys((s) => [...s!, key]);
                setShowCreateModal(false);
            })
            .catch((error) => {
                console.error(error);
                addError({ key: 'account:api-keys', message: httpErrorToHuman(error) });
                setSubmitting(false);
            });
    };

    const toggleKeyVisibility = (identifier: string) => {
        setShowKeys((prev) => ({
            ...prev,
            [identifier]: !prev[identifier],
        }));
    };

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <FlashMessageRender byKey='account:api-keys' />
            <ApiKeyModal visible={apiKey.length > 0} onModalDismissed={() => setApiKey('')} apiKey={apiKey} />

            {/* Create API Key Dialog */}
            <Dialog open={showCreateModal} onOpenChange={setShowCreateModal}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Create API Key</DialogTitle>
                    </DialogHeader>
                    <Formik
                        onSubmit={submitCreate}
                        initialValues={{ description: '', allowedIps: '' }}
                        validationSchema={object().shape({
                            allowedIps: string(),
                            description: string().required().min(4),
                        })}
                    >
                        {({ isSubmitting }) => (
                            <Form id='create-api-form' className='space-y-4'>
                                <SpinnerOverlay visible={isSubmitting} />

                                <FormikFieldWrapper
                                    label='Description'
                                    name='description'
                                    description='A description of this API key.'
                                >
                                    <Field name='description' as={Input} className='w-full' />
                                </FormikFieldWrapper>

                                <FormikFieldWrapper
                                    label='Allowed IPs'
                                    name='allowedIps'
                                    description='Leave blank to allow any IP address to use this API key, otherwise provide each IP address on a new line. Note: You can also use CIDR ranges here.'
                                >
                                    <Field name='allowedIps' as={Input} className='w-full' />
                                </FormikFieldWrapper>

                                <DialogFooter>
                                    <Button type='submit' disabled={isSubmitting}>
                                        Create Key
                                    </Button>
                                </DialogFooter>
                            </Form>
                        )}
                    </Formik>
                </DialogContent>
            </Dialog>

            <div className='flex w-full flex-1 flex-col px-2 sm:px-0'>
                <MainPageHeader
                    title='API Keys'
                    titleChildren={
                        <Button onClick={() => setShowCreateModal(true)} className='flex items-center gap-2'>
                            <Plus className='size-5' />
                            Create API Key
                        </Button>
                    }
                />

                <SpinnerOverlay visible={loading} />

                {/* Delete API Key Dialog */}
                <Dialog open={!!deleteIdentifier} onOpenChange={(o) => !o && setDeleteIdentifier('')}>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Delete API Key</DialogTitle>
                            <DialogDescription>
                                All requests using the <code className='rounded bg-muted px-1 font-mono text-sm'>{deleteIdentifier}</code> key will be invalidated.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <Button variant='outline' onClick={() => setDeleteIdentifier('')}>
                                Cancel
                            </Button>
                            <Button variant='destructive' onClick={() => doDeletion(deleteIdentifier)}>
                                Delete Key
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                {keys.length === 0 ? (
                    <div className='py-12 text-center'>
                        <div className='mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-muted'>
                            <Key className='size-5 text-muted-foreground' />
                        </div>
                        <h3 className='mb-2 text-lg font-medium text-foreground'>No API Keys</h3>
                        <p className='mx-auto max-w-sm text-sm text-muted-foreground'>
                            {loading
                                ? 'Loading your API keys...'
                                : "You haven't created any API keys yet. Create one to get started with the API."}
                        </p>
                    </div>
                ) : (
                    <div className='space-y-3'>
                        {keys.map((key) => (
                            <div
                                key={key.identifier}
                                className='rounded-lg border bg-card p-4 text-card-foreground shadow-sm transition-colors hover:border-border'
                            >
                                <div className='flex items-center justify-between'>
                                    <div className='min-w-0 flex-1'>
                                        <div className='mb-2 flex items-center gap-3'>
                                            <h4 className='truncate text-sm font-medium text-foreground'>
                                                {key.description}
                                            </h4>
                                        </div>
                                        <div className='flex items-center gap-4 text-xs text-muted-foreground'>
                                            <span>
                                                Last used:{' '}
                                                {key.lastUsedAt
                                                    ? format(key.lastUsedAt, 'MMM d, yyyy HH:mm')
                                                    : 'Never'}
                                            </span>
                                            <div className='flex items-center gap-2'>
                                                <span>Key:</span>
                                                <code className='rounded border bg-muted px-2 py-1 font-mono text-muted-foreground'>
                                                    {showKeys[key.identifier]
                                                        ? key.identifier
                                                        : '••••••••••••••••'}
                                                </code>
                                                <Button
                                                    variant='ghost'
                                                    size='icon-sm'
                                                    onClick={() => toggleKeyVisibility(key.identifier)}
                                                >
                                                    {showKeys[key.identifier] ? (
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
                                        onClick={() => setDeleteIdentifier(key.identifier)}
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

export default AccountApiContainer;
