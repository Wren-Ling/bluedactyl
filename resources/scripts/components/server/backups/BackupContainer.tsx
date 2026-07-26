import { AlertTriangle, ArrowDownToLine, Download, Trash2 } from 'lucide-react';
import { useStoreState } from 'easy-peasy';
import { Form, Formik, Field as FormikField, FormikHelpers, useFormikContext } from 'formik';
import { createContext, lazy, useCallback, useContext, useEffect, useState } from 'react';
import { toast } from 'sonner';
import { boolean, object, string } from 'yup';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import Can from '@/components/elements/Can';
import { Checkbox } from '@/components/ui/checkbox';
import Field from '@/components/elements/Field';
import FormikFieldWrapper from '@/components/elements/FormikFieldWrapper';
import FormikSwitchV2 from '@/components/elements/FormikSwitchV2';
import { Textarea } from '@/components/ui/textarea';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import Modal, { RequiredModalProps } from '@/components/elements/Modal';
import Pagination from '@/components/elements/Pagination';
import Spinner from '@/components/elements/Spinner';
import { PageListContainer } from '@/components/elements/pages/PageList';
import { SocketEvent } from '@/components/server/events';

import { httpErrorToHuman } from '@/api/http';
import deleteAllServerBackups from '@/api/server/backups/deleteAllServerBackups';
import { getGlobalDaemonType } from '@/api/server/getServer';
import { Context as ServerBackupContext } from '@/api/swr/getServerBackups';
import getServerBackups from '@/api/swr/getServerBackups';

import { ApplicationStore } from '@/state';
import { ServerContext } from '@/state/server';

import useFlash from '@/plugins/useFlash';
import useWebsocketEvent from '@/plugins/useWebsocketEvent';

import { useUnifiedBackups } from './useUnifiedBackups';

const BackupItemElytra = lazy(() => import('./elytra/BackupItem'));
const BackupItemWings = lazy(() => import('./wings/BackupItem'));

export const LiveProgressContext = createContext<Record<string, { status: string; progress: number; message: string; canRetry: boolean; lastUpdated: string; completed: boolean; isDeletion: boolean; backupName?: string }>>({});

const formatStorage = (mb: number | undefined | null): string => {
    if (mb === null || mb === undefined) return '0MB';
    if (mb >= 1024) return `${(mb / 1024).toFixed(1)}GB`;
    return `${mb.toFixed(1)}MB`;
};

interface BackupValues {
    name: string;
    ignored: string;
    isLocked: boolean;
}

const ModalContent = ({ ...props }: RequiredModalProps) => {
    const { isSubmitting } = useFormikContext<BackupValues>();

    return (
        <Modal {...props} showSpinnerOverlay={isSubmitting} title='Create server backup'>
            <Form>
                <FlashMessageRender byKey={'backups:create'} />
                <Field
                    name={'name'}
                    label={'Backup name'}
                    description={'If provided, the name that should be used to reference this backup.'}
                />
                <div className={`mt-6 flex flex-col`}>
                    <FormikFieldWrapper
                        className='flex flex-col gap-2'
                        name={'ignored'}
                        label={'Ignored Files & Directories'}
                        description={`
                            Enter the files or folders to ignore while generating this backup. Leave blank to use
                            the contents of the .pyroignore file in the root of the server directory if present.
                            Wildcard matching of files and folders is supported in addition to negating a rule by
                            prefixing the path with an exclamation point.
                        `}
                    >
                        <FormikField
                            as={Textarea}
                            name={'ignored'}
                            rows={6}
                        />
                    </FormikFieldWrapper>
                </div>
                <Can action={'backup.delete'}>
                    <div className={`my-6`}>
                        <FormikSwitchV2
                            name={'isLocked'}
                            label={'Locked'}
                            description={'Prevents this backup from being deleted until explicitly unlocked.'}
                        />
                    </div>
                </Can>
                <div className={`mb-6 flex justify-end`}>
                    <Button type={'submit'} disabled={isSubmitting}>
                        {isSubmitting ? 'Creating backup...' : 'Start backup'}
                    </Button>
                </div>
            </Form>
        </Modal>
    );
};

const BackupContainer = () => {
    const { page, setPage } = useContext(ServerBackupContext);
    const { clearFlashes, clearAndAddHttpError, addFlash } = useFlash();
    const liveProgress = useContext(LiveProgressContext);
    const [createModalVisible, setCreateModalVisible] = useState(false);
    const [deleteAllModalVisible, setDeleteAllModalVisible] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);
    const [deleteAllPassword, setDeleteAllPassword] = useState('');
    const [deleteAllTotpCode, setDeleteAllTotpCode] = useState('');

    const [selectedBackups, setSelectedBackups] = useState<Set<string>>(new Set());
    const [bulkDeleteModalVisible, setBulkDeleteModalVisible] = useState(false);
    const [isBulkDeleting, setIsBulkDeleting] = useState(false);
    const [bulkDeletePassword, setBulkDeletePassword] = useState('');
    const [bulkDeleteTotpCode, setBulkDeleteTotpCode] = useState('');
    const daemonType = getGlobalDaemonType();

    const hasTwoFactor = useStoreState((state: ApplicationStore) => state.user.data?.useTotp || false);

    const { backups, backupCount, storage, pagination, error, isValidating, createBackup, retryBackup, refresh } =
        useUnifiedBackups();

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const backupLimit = ServerContext.useStoreState((state) => state.server.data!.featureLimits.backups);
    const backupStorageLimit = ServerContext.useStoreState((state) => state.server.data!.featureLimits.backupStorageMb);

    const hasActiveOperation = Object.values(liveProgress).some((op) => !op.completed);

    useEffect(() => {
        clearFlashes('backups:create');
    }, [createModalVisible]);

    const submitBackup = async (values: BackupValues, { setSubmitting }: FormikHelpers<BackupValues>) => {
        clearFlashes('backups:create');
        try {
            await createBackup(values.name, values.ignored, values.isLocked);
            clearFlashes('backups');
            clearFlashes('backups:create');
            setSubmitting(false);
            setCreateModalVisible(false);
        } catch (error) {
            clearAndAddHttpError({ key: 'backups:create', error });
            setSubmitting(false);
        }
    };

    const handleDeleteAll = async () => {
        if (!deleteAllPassword) {
            toast.error('Password is required to delete all backups.');
            return;
        }
        if (hasTwoFactor && !deleteAllTotpCode) {
            toast.error('Two-factor authentication code is required.');
            return;
        }
        setIsDeleting(true);
        try {
            await deleteAllServerBackups(uuid, deleteAllPassword, hasTwoFactor, deleteAllTotpCode);
            toast.success('All backups and repositories are being deleted. This may take a few minutes.');
            setDeleteAllModalVisible(false);
            setDeleteAllPassword('');
            setDeleteAllTotpCode('');
        } catch (error) {
            toast.error(httpErrorToHuman(error));
        } finally {
            setIsDeleting(false);
        }
    };

    const toggleBackupSelection = (backupUuid: string) => {
        setSelectedBackups((prev) => {
            const newSet = new Set(prev);
            if (newSet.has(backupUuid)) {
                newSet.delete(backupUuid);
            } else {
                newSet.add(backupUuid);
            }
            return newSet;
        });
    };

    const toggleSelectAll = () => {
        if (selectedBackups.size === selectableBackups.length) {
            setSelectedBackups(new Set());
        } else {
            setSelectedBackups(new Set(selectableBackups.map((b) => b.uuid)));
        }
    };

    const clearSelection = () => {
        setSelectedBackups(new Set());
    };

    const selectableBackups = backups.filter((b) => b.status === 'completed' && b.isSuccessful && !b.isLiveOnly);

    const handleBulkDelete = async () => {
        if (!bulkDeletePassword) {
            addFlash({ key: 'backups:bulk_delete', type: 'error', message: 'Password is required to delete backups.' });
            return;
        }
        if (hasTwoFactor && !bulkDeleteTotpCode) {
            addFlash({ key: 'backups:bulk_delete', type: 'error', message: 'Two-factor authentication code is required.' });
            return;
        }
        setIsBulkDeleting(true);
        clearFlashes('backups:bulk_delete');
        try {
            const http = (await import('@/api/http')).default;
            await http.post(`/api/client/servers/${uuid}/backups/bulk-delete`, {
                backup_uuids: Array.from(selectedBackups),
                password: bulkDeletePassword,
                ...(hasTwoFactor ? { totp_code: bulkDeleteTotpCode } : {}),
            });
            addFlash({ key: 'backups', type: 'success', message: `${selectedBackups.size} backup${selectedBackups.size > 1 ? 's are' : ' is'} being deleted.` });
            setBulkDeleteModalVisible(false);
            setBulkDeletePassword('');
            setBulkDeleteTotpCode('');
            clearSelection();
            await refresh();
        } catch (error) {
            clearAndAddHttpError({ key: 'backups:bulk_delete', error });
        } finally {
            setIsBulkDeleting(false);
        }
    };

    useEffect(() => {
        if (!error) {
            clearFlashes('backups');
            return;
        }
        clearAndAddHttpError({ error, key: 'backups' });
    }, [error]);

    const pageLayout = (content: React.ReactNode) => (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <FlashMessageRender byKey={'backups'} />
            {content}
        </div>
    );

    if (!backups || (error && isValidating)) {
        return pageLayout(
            <>
                <MainPageHeader direction='column' title={'Backups'}>
                    <p className='text-sm leading-relaxed text-muted-foreground'>
                        Create and manage server backups to protect your data. Schedule automated backups, download
                        existing ones, and restore when needed.
                    </p>
                </MainPageHeader>
                <div className='flex items-center justify-center py-12'>
                    <div className='size-8 animate-spin rounded-full border-b-2 border-primary' />
                </div>
            </>
        );
    }

    return pageLayout(
        <>
            <MainPageHeader
                direction='column'
                title={'Backups'}
                titleChildren={
                    <Can action={'backup.create'}>
                        <div className='flex flex-col items-center justify-end gap-4 sm:flex-row'>
                            <div className='flex flex-col gap-1 text-center sm:text-right'>
                                {backupLimit === null && <p className='text-sm text-muted-foreground'>{backupCount} backups</p>}
                                {backupLimit > 0 && (
                                    <p className='text-sm text-muted-foreground'>
                                        {backupCount} of {backupLimit} backups
                                    </p>
                                )}
                                {backupLimit === 0 && <p className='text-sm text-destructive'>Backups disabled</p>}

                                {storage && (
                                    <div className='flex flex-col gap-0.5'>
                                        {backupStorageLimit === null ? (
                                            <>
                                                <p className='cursor-help text-sm text-muted-foreground' title={`${storage.used_mb?.toFixed(2) || 0}MB total`}>
                                                    <span className='font-medium'>{formatStorage(storage.used_mb)}</span> storage used
                                                </p>
                                                {(storage.repository_usage_mb > 0 && storage.legacy_usage_mb > 0) && (
                                                    <p className='text-xs text-muted-foreground/60'>
                                                        {storage.repository_usage_mb > 0 && `${formatStorage(storage.repository_usage_mb)} deduplicated`}
                                                        {storage.repository_usage_mb > 0 && storage.legacy_usage_mb > 0 && ' + '}
                                                        {storage.legacy_usage_mb > 0 && `${formatStorage(storage.legacy_usage_mb)} legacy`}
                                                    </p>
                                                )}
                                            </>
                                        ) : (
                                            <>
                                                <p className='cursor-help text-sm text-muted-foreground' title={`${storage.used_mb?.toFixed(2) || 0}MB used of ${backupStorageLimit}MB`}>
                                                    <span className='font-medium'>{formatStorage(storage.used_mb)}</span> of {formatStorage(backupStorageLimit)} used
                                                </p>
                                                {(storage.repository_usage_mb > 0 && storage.legacy_usage_mb > 0) && (
                                                    <p className='text-xs text-muted-foreground/60'>
                                                        {storage.repository_usage_mb > 0 && `${formatStorage(storage.repository_usage_mb)} deduplicated`}
                                                        {storage.repository_usage_mb > 0 && storage.legacy_usage_mb > 0 && ' + '}
                                                        {storage.legacy_usage_mb > 0 && `${formatStorage(storage.legacy_usage_mb)} legacy`}
                                                    </p>
                                                )}
                                            </>
                                        )}
                                    </div>
                                )}
                            </div>
                            <div className='flex gap-2'>
                                {backupCount > 0 && (
                                    <Button variant='destructive' onClick={() => setDeleteAllModalVisible(true)} disabled={hasActiveOperation}>
                                        <Trash2 className='mr-2 size-4' />
                                        Delete All Backups
                                    </Button>
                                )}
                                {(backupLimit === null || backupLimit > backupCount) &&
                                    (!backupStorageLimit || !storage?.is_over_limit) && (
                                        <Button variant='default' onClick={() => setCreateModalVisible(true)} disabled={hasActiveOperation}>
                                            New Backup
                                        </Button>
                                    )}
                            </div>
                        </div>
                    </Can>
                }
            >
                <p className='text-sm leading-relaxed text-muted-foreground'>
                    Create and manage server backups to protect your data. Schedule automated backups, download existing
                    ones, and restore when needed. Backups are deduplicated, meaning unchanged files are only stored
                    once across all backups
                </p>
            </MainPageHeader>

            {createModalVisible && (
                <Formik
                    onSubmit={submitBackup}
                    initialValues={{ name: '', ignored: '', isLocked: false }}
                    validationSchema={object().shape({
                        name: string().max(191),
                        ignored: string(),
                        isLocked: boolean(),
                    })}
                >
                    <ModalContent visible={createModalVisible} onDismissed={() => setCreateModalVisible(false)} />
                </Formik>
            )}

            {deleteAllModalVisible && (
                <Modal
                    visible={deleteAllModalVisible}
                    onDismissed={() => { setDeleteAllModalVisible(false); setDeleteAllPassword(''); setDeleteAllTotpCode(''); }}
                    title='Delete All Backups'
                >
                    <div className='space-y-4'>
                        <p className='text-sm text-muted-foreground'>
                            You are about to permanently delete{' '}
                            <span className='font-medium text-destructive'>
                                {backupCount} {backupCount === 1 ? 'backup' : 'backups'}
                            </span>{' '}
                            and completely destroy the backup repository for this server.
                        </p>

                        <div className='rounded-lg border border-destructive/20 bg-destructive/10 p-4'>
                            <div className='flex items-start gap-3'>
                                <AlertTriangle className='mt-0.5 size-5 shrink-0 text-destructive' />
                                <div className='text-sm'>
                                    <p className='font-medium text-destructive'>This action cannot be undone</p>
                                    <ul className='mt-2 list-inside list-disc space-y-1 text-destructive/80'>
                                        <li>All backup data will be permanently deleted</li>
                                        <li>Locked backups will also be deleted</li>
                                        <li>The entire backup repository will be destroyed</li>
                                        <li>This operation may take several minutes to complete</li>
                                        <li>You will not be able to restore any of these backups</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div className='space-y-3'>
                            <div>
                                <label htmlFor='password' className='mb-1 block text-sm font-medium text-muted-foreground'>Password</label>
                                <input id='password' type='password' className='w-full rounded-lg border border-border bg-transparent px-4 py-2 text-sm outline-none focus:border-ring focus:ring-3 focus:ring-ring/50' placeholder='Enter your password' value={deleteAllPassword} onChange={(e) => setDeleteAllPassword(e.target.value)} disabled={isDeleting} />
                            </div>
                            {hasTwoFactor && (
                                <div>
                                    <label htmlFor='totp_code' className='mb-1 block text-sm font-medium text-muted-foreground'>Two-Factor Authentication Code</label>
                                    <input id='totp_code' type='text' className='w-full rounded-lg border border-border bg-transparent px-4 py-2 text-sm outline-none focus:border-ring focus:ring-3 focus:ring-ring/50' placeholder='6-digit code' maxLength={6} value={deleteAllTotpCode} onChange={(e) => setDeleteAllTotpCode(e.target.value.replace(/[^0-9]/g, ''))} disabled={isDeleting} />
                                </div>
                            )}
                        </div>

                        <div className='flex justify-end gap-3 pb-6 pt-2'>
                            <Button variant='outline' onClick={() => { setDeleteAllModalVisible(false); setDeleteAllPassword(''); setDeleteAllTotpCode(''); }} disabled={isDeleting}>Cancel</Button>
                            <Button variant='destructive' onClick={handleDeleteAll} disabled={isDeleting}>
                                {isDeleting ? 'Deleting...' : 'Delete All Backups'}
                            </Button>
                        </div>
                    </div>
                </Modal>
            )}

            {bulkDeleteModalVisible && (
                <Modal
                    visible={bulkDeleteModalVisible}
                    onDismissed={() => { setBulkDeleteModalVisible(false); setBulkDeletePassword(''); setBulkDeleteTotpCode(''); }}
                    title='Delete Selected Backups'
                >
                    <FlashMessageRender byKey={'backups:bulk_delete'} />
                    <div className='space-y-4'>
                        <p className='text-sm text-muted-foreground'>
                            You are about to permanently delete{' '}
                            <span className='font-medium text-destructive'>{selectedBackups.size} backup{selectedBackups.size > 1 ? 's' : ''}</span>.
                            This action cannot be undone.
                        </p>
                        <div className='rounded-lg border border-destructive/20 bg-destructive/10 p-4'>
                            <div className='flex items-start gap-3'>
                                <AlertTriangle className='mt-0.5 size-5 shrink-0 text-destructive' />
                                <div className='text-sm'>
                                    <p className='font-medium text-destructive'>Warning</p>
                                    <p className='mt-1 text-destructive/80'>The selected backup files and their snapshots will be permanently deleted.</p>
                                </div>
                            </div>
                        </div>
                        <div className='space-y-3'>
                            <div>
                                <label htmlFor='bulk-password' className='mb-1 block text-sm font-medium text-muted-foreground'>Password</label>
                                <input id='bulk-password' type='password' className='w-full rounded-lg border border-border bg-transparent px-4 py-2 text-sm outline-none focus:border-ring focus:ring-3 focus:ring-ring/50' placeholder='Enter your password' value={bulkDeletePassword} onChange={(e) => setBulkDeletePassword(e.target.value)} disabled={isBulkDeleting} />
                            </div>
                            {hasTwoFactor && (
                                <div>
                                    <label htmlFor='bulk-totp' className='mb-1 block text-sm font-medium text-muted-foreground'>Two-Factor Authentication Code</label>
                                    <input id='bulk-totp' type='text' className='w-full rounded-lg border border-border bg-transparent px-4 py-2 text-sm outline-none focus:border-ring focus:ring-3 focus:ring-ring/50' placeholder='6-digit code' maxLength={6} value={bulkDeleteTotpCode} onChange={(e) => setBulkDeleteTotpCode(e.target.value.replace(/[^0-9]/g, ''))} disabled={isBulkDeleting} />
                                </div>
                            )}
                        </div>
                        <div className='flex justify-end gap-3 pb-6 pt-2'>
                            <Button variant='outline' onClick={() => { setBulkDeleteModalVisible(false); setBulkDeletePassword(''); setBulkDeleteTotpCode(''); }} disabled={isBulkDeleting}>Cancel</Button>
                            <Button variant='destructive' onClick={handleBulkDelete} disabled={isBulkDeleting}>
                                {isBulkDeleting ? 'Deleting...' : `Delete ${selectedBackups.size} Backup${selectedBackups.size > 1 ? 's' : ''}`}
                            </Button>
                        </div>
                    </div>
                </Modal>
            )}

            {backups.length === 0 ? (
                <div className='flex min-h-[60vh] flex-col items-center justify-center px-4 py-12'>
                    <div className='text-center'>
                        <div className='mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-muted'>
                            <ArrowDownToLine className='size-6 text-muted-foreground' />
                        </div>
                        <h3 className='mb-2 text-lg font-medium text-foreground'>
                            {backupLimit === 0 ? 'Backups unavailable' : 'No backups found'}
                        </h3>
                        <p className='max-w-sm text-sm text-muted-foreground'>
                            {backupLimit === 0
                                ? 'Backups cannot be created for this server.'
                                : 'Your server does not have any backups. Create one to get started.'}
                        </p>
                    </div>
                </div>
            ) : (
                <>
                    {selectableBackups.length > 0 && (
                        <div className='mb-8 flex items-center justify-between rounded-xl border border-border bg-muted/30 px-4 py-3.5'>
                            <div className='flex items-center gap-4'>
                                <Checkbox
                                    checked={selectedBackups.size === selectableBackups.length && selectableBackups.length > 0}
                                    onCheckedChange={toggleSelectAll}
                                />
                                <span className='text-sm text-muted-foreground'>
                                    {selectedBackups.size > 0 ? (
                                        <><span className='font-medium'>{selectedBackups.size}</span> selected</>
                                    ) : 'Select backups'}
                                </span>
                            </div>
                            <div className={`flex items-center gap-3 transition-opacity ${selectedBackups.size > 0 ? 'opacity-100' : 'pointer-events-none opacity-0'}`}>
                                <Button variant='outline' onClick={clearSelection}>Clear</Button>
                                <Can action='backup.delete'>
                                    <Button variant='destructive' onClick={() => setBulkDeleteModalVisible(true)}>
                                        Delete Selected ({selectedBackups.size})
                                    </Button>
                                </Can>
                            </div>
                        </div>
                    )}

                    <PageListContainer>
                        {backups.map((backup) =>
                            daemonType === 'elytra' ? (
                                <BackupItemElytra
                                    key={backup.uuid}
                                    backup={backup}
                                    isSelected={selectedBackups.has(backup.uuid)}
                                    onToggleSelect={() => toggleBackupSelection(backup.uuid)}
                                    isSelectable={selectableBackups.some((b) => b.uuid === backup.uuid)}
                                    retryBackup={retryBackup}
                                />
                            ) : (
                                <BackupItemWings key={backup.uuid} backup={backup} />
                            ),
                        )}
                    </PageListContainer>

                    {pagination && pagination.currentPage && pagination.totalPages && pagination.totalPages > 1 && (
                        <Pagination data={{ items: backups, pagination }} onPageSelect={setPage}>
                            {() => null}
                        </Pagination>
                    )}
                </>
            )}
        </>
    );
};

const BackupContainerWrapper = () => {
    const [page, setPage] = useState<number>(1);
    const { mutate } = getServerBackups();
    const [liveProgress, setLiveProgress] = useState<Record<string, { status: string; progress: number; message: string; canRetry: boolean; lastUpdated: string; completed: boolean; isDeletion: boolean; backupName?: string }>>({});

    const handleBackupStatus = useCallback((rawData: any) => {
        let data;
        try {
            data = typeof rawData === 'string' ? JSON.parse(rawData) : rawData;
        } catch { return; }
        const backup_uuid = data?.backup_uuid;
        if (!backup_uuid) return;
        const { status, progress, message, timestamp, operation, error: errorMsg, name } = data;
        const can_retry = status === 'failed' && operation === 'create';
        const last_updated_at = timestamp ? new Date(timestamp * 1000).toISOString() : new Date().toISOString();
        const isDeletionOperation = operation === 'delete' || data.deleted === true;

        setLiveProgress((prevProgress) => {
            const currentState = prevProgress[backup_uuid];
            const newProgress = progress || 0;
            const isCompleted = status === 'completed' && newProgress === 100;
            const displayMessage = errorMsg ? `${message || 'Operation failed'}: ${errorMsg}` : message || '';
            if (currentState?.completed && !isCompleted) return prevProgress;
            if (currentState && !isCompleted && currentState.lastUpdated >= last_updated_at && currentState.progress >= newProgress) return prevProgress;
            return { ...prevProgress, [backup_uuid]: { status, progress: newProgress, message: displayMessage, canRetry: can_retry || false, lastUpdated: last_updated_at, completed: isCompleted, isDeletion: isDeletionOperation, backupName: name || currentState?.backupName } };
        });

        if (status === 'completed' && progress === 100) {
            if (isDeletionOperation) {
                mutate((currentData) => { if (!currentData) return currentData; return { ...currentData, items: currentData.items.filter((b) => b.uuid !== backup_uuid), backupCount: Math.max(0, (currentData.backupCount || 0) - 1) }; }, { revalidate: false });
                setLiveProgress((prev) => { const updated = { ...prev }; delete updated[backup_uuid]; return updated; });
            } else {
                mutate();
                const checkForBackup = async (attempts = 0) => {
                    if (attempts > 10) { setLiveProgress((prev) => { const updated = { ...prev }; delete updated[backup_uuid]; return updated; }); return; }
                    const currentBackups = await mutate();
                    if (currentBackups?.items?.some((b) => b.uuid === backup_uuid)) {
                        setLiveProgress((prev) => { const updated = { ...prev }; delete updated[backup_uuid]; return updated; });
                    } else { setTimeout(() => checkForBackup(attempts + 1), 1000); }
                };
                setTimeout(() => checkForBackup(), 1000);
            }
        }
    }, [mutate]);

    useWebsocketEvent(SocketEvent.BACKUP_STATUS, handleBackupStatus);

    return (
        <LiveProgressContext.Provider value={liveProgress}>
            <ServerBackupContext.Provider value={{ page, setPage }}>
                <BackupContainer />
            </ServerBackupContext.Provider>
        </LiveProgressContext.Provider>
    );
};

export default BackupContainerWrapper;
