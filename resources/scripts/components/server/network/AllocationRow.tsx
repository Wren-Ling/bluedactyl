import { Check, Copy, Crown, Trash2, Wifi, X } from 'lucide-react';
import debounce from 'debounce';
import { memo, useCallback, useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import isEqual from 'react-fast-compare';

import { Button } from '@/components/ui/button';
import Can from '@/components/elements/Can';
import Code from '@/components/elements/Code';
import CopyOnClick from '@/components/elements/CopyOnClick';
import { Textarea } from '@/components/ui/textarea';
import InputSpinner from '@/components/elements/InputSpinner';
import Spinner from '@/components/elements/Spinner';
import { Dialog } from '@/components/elements/dialog';
import { PageListItem } from '@/components/elements/pages/PageList';

import { ip } from '@/lib/formatters';

import { Allocation } from '@/api/server/getServer';
import deleteServerAllocation from '@/api/server/network/deleteServerAllocation';
import setPrimaryServerAllocation from '@/api/server/network/setPrimaryServerAllocation';
import setServerAllocationNotes from '@/api/server/network/setServerAllocationNotes';
import getServerAllocations from '@/api/swr/getServerAllocations';

import { ServerContext } from '@/state/server';

import { useFlashKey } from '@/plugins/useFlash';

interface Props {
    allocation: Allocation;
}

const AllocationRow = ({ allocation }: Props) => {
    const { t } = useTranslation('network');
    const [loading, setLoading] = useState(false);
    const [deleteLoading, setDeleteLoading] = useState(false);
    const [isEditingNotes, setIsEditingNotes] = useState(false);
    const [notesValue, setNotesValue] = useState(allocation.notes || '');
    const [showDeleteDialog, setShowDeleteDialog] = useState(false);
    const textareaRef = useRef<HTMLTextAreaElement>(null);
    const { clearFlashes, clearAndAddHttpError } = useFlashKey('server:network');
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { mutate } = getServerAllocations();

    const onNotesChanged = useCallback(
        (id: number, notes: string) => {
            mutate((data) => data?.map((a) => (a.id === id ? { ...a, notes } : a)), false);
        },
        [mutate],
    );

    const saveNotes = useCallback(() => {
        setLoading(true);
        clearFlashes();

        setServerAllocationNotes(uuid, allocation.id, notesValue)
            .then(() => {
                onNotesChanged(allocation.id, notesValue);
                setIsEditingNotes(false);
            })
            .catch((error) => clearAndAddHttpError(error))
            .then(() => setLoading(false));
    }, [uuid, allocation.id, notesValue, onNotesChanged, clearFlashes, clearAndAddHttpError]);

    const cancelEdit = useCallback(() => {
        setNotesValue(allocation.notes || '');
        setIsEditingNotes(false);
    }, [allocation.notes]);

    const startEdit = useCallback(() => {
        setIsEditingNotes(true);
        setTimeout(() => textareaRef.current?.focus(), 0);
    }, []);

    useEffect(() => {
        setNotesValue(allocation.notes || '');
    }, [allocation.notes]);

    // Format the full allocation string for copying
    const allocationString = allocation.alias
        ? `${allocation.alias}:${allocation.port}`
        : `${ip(allocation.ip)}:${allocation.port}`;

    const setPrimaryAllocation = () => {
        clearFlashes();
        mutate((data) => data?.map((a) => ({ ...a, isDefault: a.id === allocation.id })), false);

        setPrimaryServerAllocation(uuid, allocation.id).catch((error) => {
            clearAndAddHttpError(error);
            mutate();
        });
    };

    const deleteAllocation = () => {
        setShowDeleteDialog(false);
        clearFlashes();
        setDeleteLoading(true);

        deleteServerAllocation(uuid, allocation.id)
            .then(() => {
                mutate((data) => data?.filter((a) => a.id !== allocation.id), false);
            })
            .catch((error) => clearAndAddHttpError(error))
            .then(() => setDeleteLoading(false));
    };

    return (
        <PageListItem>
            <div className='flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full'>
                <div className='flex-1 min-w-0'>
                    <div className='flex items-center gap-3 mb-3'>
                        <div className='flex-shrink-0 w-8 h-8 rounded-lg bg-muted/30 flex items-center justify-center'>
                            <Wifi size={22} className='text-muted-foreground' />
                        </div>
                        <div className='min-w-0 flex-1'>
                            <div className='flex items-center flex-wrap gap-2'>
                                <CopyOnClick text={allocationString}>
                                    <div className='flex items-center gap-2 cursor-pointer hover:text-foreground transition-colors group'>
                                        <h3 className='text-base font-medium text-foreground font-mono truncate'>
                                            {allocation.alias ? allocation.alias : ip(allocation.ip)}:{allocation.port}
                                        </h3>
                                        <Copy
                                            size={22}
                                            className='text-foreground0 transition-colors group-hover:text-muted-foreground'
                                        />
                                    </div>
                                </CopyOnClick>
                                {allocation.isDefault && (
                                    <span className='flex items-center gap-1 text-xs text-foreground font-medium bg-secondary px-2 py-1 rounded'>
                                        <Crown size={22} />
                                        {t('primary')}
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Notes Section - Inline Editable */}
                    <div className='mt-3'>
                        <p className='text-xs text-foreground0 uppercase tracking-wide mb-2'>{t('notes')}</p>

                        {isEditingNotes ? (
                            <div className='space-y-2'>
                                <InputSpinner visible={loading}>
                                    <Textarea
                                        ref={textareaRef}
                                        className='w-full bg-muted/30 border border-border rounded-lg p-3 text-sm text-foreground placeholder-muted-foreground resize-none focus:ring-1 focus:ring-ring focus:border-ring transition-all'
                                        placeholder={t('add_notes_placeholder')}
                                        value={notesValue}
                                        onChange={(e) => setNotesValue(e.currentTarget.value)}
                                        rows={3}
                                    />
                                </InputSpinner>
                                <div className='flex items-center gap-2'>
                                    <Button size='sm' onClick={saveNotes} disabled={loading}>
                                        {loading ? (
                                            <Spinner size='small' />
                                        ) : (
                                            <Check className='mr-1 size-3' />
                                        )}
                                        {t('save')}
                                    </Button>
                                    <Button variant='secondary' size='sm' onClick={cancelEdit} disabled={loading}>
                                        <X className='mr-1' size={22} />
                                        {t('cancel')}
                                    </Button>
                                </div>
                            </div>
                        ) : (
                            <Can action={'allocation.update'}>
                                <div
                                    className={`min-h-[2.5rem] p-3 rounded-lg border border-border bg-background cursor-pointer hover:border-border transition-colors ${allocation.notes ? 'text-sm text-foreground' : 'text-sm text-muted-foreground italic'}`}
                                    onClick={startEdit}
                                >
                                    {allocation.notes || t('click_to_add_notes')}
                                </div>
                            </Can>
                        )}
                    </div>
                </div>

                <div className='flex items-center justify-center gap-2 sm:flex-col sm:gap-3'>
                    <Can action={'allocation.update'}>
                        <Button
                            variant='secondary'
                            size='sm'
                            onClick={setPrimaryAllocation}
                            disabled={allocation.isDefault}
                            title={
                                allocation.isDefault
                                    ? t('already_primary_title')
                                    : t('make_primary_title')
                            }
                        >
                            <Crown size={22} className='mr-1' />
                            <span className='hidden sm:inline'>{t('make_primary')}</span>
                            <span className='sm:hidden'>{t('primary')}</span>
                        </Button>
                    </Can>
                    <Can action={'allocation.delete'}>
                        <Button
                            variant='destructive'
                            size='sm'
                            onClick={() => setShowDeleteDialog(true)}
                            disabled={allocation.isDefault || deleteLoading}
                            title={
                                allocation.isDefault ? t('cannot_delete_primary') : t('delete_allocation_title')
                            }
                        >
                            {deleteLoading ? (
                                <Spinner size='small' />
                            ) : (
                                <Trash2 size={22} className='mr-1' />
                            )}
                            <span className='hidden sm:inline'>{t('delete')}</span>
                        </Button>
                    </Can>
                </div>
            </div>
            <Dialog.Confirm
                open={showDeleteDialog}
                onClose={() => setShowDeleteDialog(false)}
                title={t('delete_allocation_dialog_title')}
                confirm={t('delete')}
                onConfirmed={deleteAllocation}
            >
                {t('delete_allocation_confirm')}
            </Dialog.Confirm>
        </PageListItem>
    );
};

export default memo(AllocationRow, isEqual);
