import { AlertTriangle, ArrowDownToLine, Download, Trash2 } from 'lucide-react';
import { useStoreState } from 'easy-peasy';
import { Form, Formik, Field as FormikField, FormikHelpers, useFormikContext } from 'formik';
import { createContext, lazy, useCallback, useContext, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
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
    const { t } = useTranslation('backups');
    const { isSubmitting } = useFormikContext<BackupValues>();

    return (
        <Modal {...props} showSpinnerOverlay={isSubmitting} title={t('create_modal_title')}>
            <Form>
                <FlashMessageRender byKey={'backups:create'} />
                <Field
                    name={'name'}
                    label={t('backup_name_label')}
                    description={t('backup_name_description')}
                />
                <div className={`mt-6 flex flex-col`}>
                    <FormikFieldWrapper
                        className='flex flex-col gap-2'
                        name={'ignored'}
                        label={t('ignored_files_label')}
                        description={t('ignored_files_modal_description')}
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
                            label={t('locked_label')}
                            description={t('locked_description')}
                        />
                    </div>
                </Can>
                <div className={`mb-6 flex justify-end`}>
                    <Button type={'submit'} disabled={isSubmitting}>
                        {isSubmitting ? t('creating_backup') : t('start_backup')}
                    </Button>
                </div>
            </Form>
        </Modal>
    );
};

const BackupContainer = () => {
    const { t } = useTranslation('backups');
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
            toast.error(t('password_required_delete_all'));
            return;
        }
        if (hasTwoFactor && !deleteAllTotpCode) {
            toast.error(t('totp_required'));
            return;
        }
        setIsDeleting(true);
        try {
            await deleteAllServerBackups(uuid, deleteAllPassword, hasTwoFactor, deleteAllTotpCode);
            toast.success(t('all_backups_deleting'));
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
            addFlash({ key: 'backups:bulk_delete', type: 'error', message: t('password_required_delete_bulk') });
            return;
        }
        if (hasTwoFactor && !bulkDeleteTotpCode) {
            addFlash({ key: 'backups:bulk_delete', type: 'error', message: t('totp_required') });
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
            addFlash({ key: 'backups', type: 'success', message: t('bulk_delete_success', { count: selectedBackups.size }) });
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
                <MainPageHeader direction='column' title={t('title')}>
                    <p className='text-sm leading-relaxed text-muted-foreground'>
                        {t('description')}
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
                title={t('title')}
                titleChildren={
                    <Can action={'backup.create'}>
                        <div className='flex flex-col items-center justify-end gap-4 sm:flex-row'>
                            <div className='flex flex-col gap-1 text-center sm:text-right'>
                                {backupLimit === null && <p className='text-sm text-muted-foreground'>{t('count', { count: backupCount })}</p>}
                                {backupLimit > 0 && (
                                    <p className='text-sm text-muted-foreground'>
                                        {t('count_of', { count: backupCount, max: backupLimit })}
                                    </p>
                                )}
                                {backupLimit === 0 && <p className='text-sm text-destructive'>{t('disabled')}</p>}

                                {storage && (
                                    <div className='flex flex-col gap-0.5'>
                                        {backupStorageLimit === null ? (
                                            <>
                                                <p className='cursor-help text-sm text-muted-foreground' title={`${storage.used_mb?.toFixed(2) || 0}MB total`}>
                                                    <span className='font-medium'>{formatStorage(storage.used_mb)}</span> {t('storage_used')}
                                                </p>
                                                {(storage.repository_usage_mb > 0 && storage.legacy_usage_mb > 0) && (
                                                    <p className='text-xs text-muted-foreground/60'>
                                                        {storage.repository_usage_mb > 0 && `${formatStorage(storage.repository_usage_mb)} ${t('deduplicated')}`}
                                                        {storage.repository_usage_mb > 0 && storage.legacy_usage_mb > 0 && ' + '}
                                                        {storage.legacy_usage_mb > 0 && `${formatStorage(storage.legacy_usage_mb)} ${t('legacy')}`}
                                                    </p>
                                                )}
                                            </>
                                        ) : (
                                            <>
                                                <p className='cursor-help text-sm text-muted-foreground' title={`${storage.used_mb?.toFixed(2) || 0}MB used of ${backupStorageLimit}MB`}>
                                                    <span className='font-medium'>{formatStorage(storage.used_mb)}</span> {t('of_storage_used', { max: formatStorage(backupStorageLimit) })}
                                                </p>
                                                {(storage.repository_usage_mb > 0 && storage.legacy_usage_mb > 0) && (
                                                    <p className='text-xs text-muted-foreground/60'>
                                                        {storage.repository_usage_mb > 0 && `${formatStorage(storage.repository_usage_mb)} ${t('deduplicated')}`}
                                                        {storage.repository_usage_mb > 0 && storage.legacy_usage_mb > 0 && ' + '}
                                                        {storage.legacy_usage_mb > 0 && `${formatStorage(storage.legacy_usage_mb)} ${t('legacy')}`}
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
                                        {t('delete_all_backups')}
                                    </Button>
                                )}
                                {(backupLimit === null || backupLimit > backupCount) &&
                                    (!backupStorageLimit || !storage?.is_over_limit) && (
                                        <Button variant='default' onClick={() => setCreateModalVisible(true)} disabled={hasActiveOperation}>
                                            {t('new_backup')}
                                        </Button>
                                    )}
                            </div>
                        </div>
                    </Can>
                }
            >
                <p className='text-sm leading-relaxed text-muted-foreground'>
                    {t('description_extended')}
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
                    title={t('delete_all_title')}
                >
                    <div className='space-y-4'>
                        <p className='text-sm text-muted-foreground'>
                            {t('delete_all_warning', { count: backupCount })}
                        </p>

                        <div className='rounded-lg border border-destructive/20 bg-destructive/10 p-4'>
                            <div className='flex items-start gap-3'>
                                <AlertTriangle className='mt-0.5 size-5 shrink-0 text-destructive' />
                                <div className='text-sm'>
                                    <p className='font-medium text-destructive'>{t('cannot_undo')}</p>
                                    <ul className='mt-2 list-inside list-disc space-y-1 text-destructive/80'>
                                        <li>{t('delete_all_item_1')}</li>
                                        <li>{t('delete_all_item_2')}</li>
                                        <li>{t('delete_all_item_3')}</li>
                                        <li>{t('delete_all_item_4')}</li>
                                        <li>{t('delete_all_item_5')}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div className='space-y-3'>
                            <div>
                                <label htmlFor='password' className='mb-1 block text-sm font-medium text-muted-foreground'>{t('password_label')}</label>
                                <input id='password' type='password' className='w-full rounded-lg border border-border bg-transparent px-4 py-2 text-sm outline-none focus:border-ring focus:ring-3 focus:ring-ring/50' placeholder={t('password_placeholder')} value={deleteAllPassword} onChange={(e) => setDeleteAllPassword(e.target.value)} disabled={isDeleting} />
                            </div>
                            {hasTwoFactor && (
                                <div>
                                    <label htmlFor='totp_code' className='mb-1 block text-sm font-medium text-muted-foreground'>{t('totp_label')}</label>
                                    <input id='totp_code' type='text' className='w-full rounded-lg border border-border bg-transparent px-4 py-2 text-sm outline-none focus:border-ring focus:ring-3 focus:ring-ring/50' placeholder={t('totp_placeholder')} maxLength={6} value={deleteAllTotpCode} onChange={(e) => setDeleteAllTotpCode(e.target.value.replace(/[^0-9]/g, ''))} disabled={isDeleting} />
                                </div>
                            )}
                        </div>

                        <div className='flex justify-end gap-3 pb-6 pt-2'>
                            <Button variant='outline' onClick={() => { setDeleteAllModalVisible(false); setDeleteAllPassword(''); setDeleteAllTotpCode(''); }} disabled={isDeleting}>{t('cancel')}</Button>
                            <Button variant='destructive' onClick={handleDeleteAll} disabled={isDeleting}>
                                {isDeleting ? t('deleting') : t('delete_all_backups')}
                            </Button>
                        </div>
                    </div>
                </Modal>
            )}

            {bulkDeleteModalVisible && (
                <Modal
                    visible={bulkDeleteModalVisible}
                    onDismissed={() => { setBulkDeleteModalVisible(false); setBulkDeletePassword(''); setBulkDeleteTotpCode(''); }}
                    title={t('delete_selected_title')}
                >
                    <FlashMessageRender byKey={'backups:bulk_delete'} />
                    <div className='space-y-4'>
                        <p className='text-sm text-muted-foreground'>
                            {t('delete_selected_warning', { count: selectedBackups.size })}
                        </p>
                        <div className='rounded-lg border border-destructive/20 bg-destructive/10 p-4'>
                            <div className='flex items-start gap-3'>
                                <AlertTriangle className='mt-0.5 size-5 shrink-0 text-destructive' />
                                <div className='text-sm'>
                                    <p className='font-medium text-destructive'>{t('warning')}</p>
                                    <p className='mt-1 text-destructive/80'>{t('delete_selected_description')}</p>
                                </div>
                            </div>
                        </div>
                        <div className='space-y-3'>
                            <div>
                                <label htmlFor='bulk-password' className='mb-1 block text-sm font-medium text-muted-foreground'>{t('password_label')}</label>
                                <input id='bulk-password' type='password' className='w-full rounded-lg border border-border bg-transparent px-4 py-2 text-sm outline-none focus:border-ring focus:ring-3 focus:ring-ring/50' placeholder={t('password_placeholder')} value={bulkDeletePassword} onChange={(e) => setBulkDeletePassword(e.target.value)} disabled={isBulkDeleting} />
                            </div>
                            {hasTwoFactor && (
                                <div>
                                    <label htmlFor='bulk-totp' className='mb-1 block text-sm font-medium text-muted-foreground'>{t('totp_label')}</label>
                                    <input id='bulk-totp' type='text' className='w-full rounded-lg border border-border bg-transparent px-4 py-2 text-sm outline-none focus:border-ring focus:ring-3 focus:ring-ring/50' placeholder={t('totp_placeholder')} maxLength={6} value={bulkDeleteTotpCode} onChange={(e) => setBulkDeleteTotpCode(e.target.value.replace(/[^0-9]/g, ''))} disabled={isBulkDeleting} />
                                </div>
                            )}
                        </div>
                        <div className='flex justify-end gap-3 pb-6 pt-2'>
                            <Button variant='outline' onClick={() => { setBulkDeleteModalVisible(false); setBulkDeletePassword(''); setBulkDeleteTotpCode(''); }} disabled={isBulkDeleting}>{t('cancel')}</Button>
                            <Button variant='destructive' onClick={handleBulkDelete} disabled={isBulkDeleting}>
                                {isBulkDeleting ? t('deleting') : t('delete_selected_button', { count: selectedBackups.size })}
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
                            {backupLimit === 0 ? t('unavailable') : t('no_backups')}
                        </h3>
                        <p className='max-w-sm text-sm text-muted-foreground'>
                            {backupLimit === 0
                                ? t('cannot_create')
                                : t('create_one_to_start')}
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
                                        <><span className='font-medium'>{selectedBackups.size}</span> {t('selected')}</>
                                    ) : t('select_backups')}
                                </span>
                            </div>
                            <div className={`flex items-center gap-3 transition-opacity ${selectedBackups.size > 0 ? 'opacity-100' : 'pointer-events-none opacity-0'}`}>
                                <Button variant='outline' onClick={clearSelection}>{t('clear')}</Button>
                                <Can action='backup.delete'>
                                    <Button variant='destructive' onClick={() => setBulkDeleteModalVisible(true)}>
                                        {t('delete_selected', { count: selectedBackups.size })}
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
