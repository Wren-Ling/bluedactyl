import { Database } from 'lucide-react';
import { Form, Formik, FormikHelpers } from 'formik';
import { For } from 'million/react';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { object, string } from 'yup';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import Can from '@/components/elements/Can';
import Field from '@/components/elements/Field';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import Modal from '@/components/elements/Modal';
import { PageListContainer } from '@/components/elements/pages/PageList';
import DatabaseRow from '@/components/server/databases/DatabaseRow';

import { httpErrorToHuman } from '@/api/http';
import createServerDatabase from '@/api/server/databases/createServerDatabase';
import getServerDatabases from '@/api/server/databases/getServerDatabases';

import { ServerContext } from '@/state/server';

import { useDeepMemoize } from '@/plugins/useDeepMemoize';
import useFlash from '@/plugins/useFlash';

interface DatabaseValues {
    databaseName: string;
    connectionsFrom: string;
}

const databaseSchema = object().shape({
    databaseName: string()
        .required('A database name must be provided.')
        .min(3, 'Database name must be at least 3 characters.')
        .max(48, 'Database name must not exceed 48 characters.')
        .matches(
            /^[\w\-.]{3,48}$/,
            'Database name should only contain alphanumeric characters, underscores, dashes, and/or periods.',
        ),
    connectionsFrom: string().matches(/^[\w\-/.%:]+$/, 'A valid host address must be provided.'),
});

const DatabasesContainer = () => {
    const { t } = useTranslation('databases');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const databaseLimit = ServerContext.useStoreState((state) => state.server.data!.featureLimits.databases);

    const { addError, clearFlashes } = useFlash();
    const [loading, setLoading] = useState(true);
    const [createModalVisible, setCreateModalVisible] = useState(false);

    const databases = useDeepMemoize(ServerContext.useStoreState((state) => state.databases.data));
    const setDatabases = ServerContext.useStoreActions((state) => state.databases.setDatabases);
    const appendDatabase = ServerContext.useStoreActions((actions) => actions.databases.appendDatabase);

    const submitDatabase = (values: DatabaseValues, { setSubmitting, resetForm }: FormikHelpers<DatabaseValues>) => {
        clearFlashes('database:create');
        createServerDatabase(uuid, {
            databaseName: values.databaseName,
            connectionsFrom: values.connectionsFrom || '%',
        })
            .then((database) => {
                resetForm();
                appendDatabase(database);
                setSubmitting(false);
                setCreateModalVisible(false);
            })
            .catch((error) => {
                addError({ key: 'database:create', message: httpErrorToHuman(error) });
                setSubmitting(false);
            });
    };

    useEffect(() => {
        setLoading(!databases.length);
        clearFlashes('databases');

        getServerDatabases(uuid)
            .then((databases) => setDatabases(databases))
            .catch((error) => {
                console.error(error);
                addError({ key: 'databases', message: httpErrorToHuman(error) });
            })
            .then(() => setLoading(false));
    }, []);

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <FlashMessageRender byKey={'databases'} />
            <MainPageHeader
                direction='column'
                title={t('title')}
                titleChildren={
                    <Can action={'database.create'}>
                        <div className='flex flex-col items-center justify-end gap-4 sm:flex-row'>
                            {databaseLimit === null && (
                                <p className='text-center text-sm text-muted-foreground sm:text-right'>
                                    {t('count_unlimited', { count: databases.length })}
                                </p>
                            )}
                            {databaseLimit > 0 && (
                                <p className='text-center text-sm text-muted-foreground sm:text-right'>
                                    {t('count_of', { count: databases.length, max: databaseLimit })}
                                </p>
                            )}
                            {databaseLimit === 0 && (
                                <p className='text-center text-sm text-destructive sm:text-right'>{t('disabled')}</p>
                            )}
                            {(databaseLimit === null || (databaseLimit > 0 && databaseLimit !== databases.length)) && (
                                <Button variant='default' onClick={() => setCreateModalVisible(true)}>
                                    {t('new_database')}
                                </Button>
                            )}
                        </div>
                    </Can>
                }
            >
                <p className='text-sm leading-relaxed text-muted-foreground'>
                    {t('description')}
                </p>
            </MainPageHeader>

            <Formik
                onSubmit={submitDatabase}
                initialValues={{ databaseName: '', connectionsFrom: '' }}
                validationSchema={databaseSchema}
            >
                {({ isSubmitting, resetForm }) => (
                    <Modal
                        visible={createModalVisible}
                        dismissable={!isSubmitting}
                        showSpinnerOverlay={isSubmitting}
                        onDismissed={() => {
                            resetForm();
                            setCreateModalVisible(false);
                        }}
                        title={t('create_modal_title')}
                    >
                        <div className='flex flex-col'>
                            <FlashMessageRender byKey={'database:create'} />
                            <Form>
                                <Field
                                    type={'string'}
                                    id={'database_name'}
                                    name={'databaseName'}
                                    label={t('database_name_label')}
                                    description={t('database_name_description')}
                                />
                                <div className={`mt-6`}>
                                    <Field
                                        type={'string'}
                                        id={'connections_from'}
                                        name={'connectionsFrom'}
                                        label={t('connections_from_label')}
                                        description={t('connections_from_description')}
                                    />
                                </div>
                                <div className={`my-6 flex justify-end gap-3`}>
                                    <Button variant='default' type={'submit'}>
                                        {t('create_database_button')}
                                    </Button>
                                </div>
                            </Form>
                        </div>
                    </Modal>
                )}
            </Formik>

            {!databases.length && loading ? (
                <div className='flex items-center justify-center py-12'>
                    <div className='size-8 animate-spin rounded-full border-b-2 border-primary' />
                </div>
            ) : databases.length > 0 ? (
                <PageListContainer data-pyro-databases>
                    <For each={databases} memo>
                        {(database, index) => <DatabaseRow key={database.id} database={database} />}
                    </For>
                </PageListContainer>
            ) : (
                <div className='flex min-h-[60vh] flex-col items-center justify-center px-4 py-12'>
                    <div className='text-center'>
                        <div className='mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-muted'>
                            <Database className='size-8 text-muted-foreground' />
                        </div>
                        <h3 className='mb-2 text-lg font-medium text-foreground'>
                            {databaseLimit === 0 ? t('unavailable') : t('no_databases')}
                        </h3>
                        <p className='max-w-sm text-sm text-muted-foreground'>
                            {databaseLimit === 0
                                ? t('cannot_create')
                                : t('create_one_to_start')}
                        </p>
                    </div>
                </div>
            )}
        </div>
    );
};

export default DatabasesContainer;
