import { Trans, useTranslation } from 'react-i18next';
import { encodePathSegments } from '@/helpers';
import { File, FolderOpen } from 'lucide-react';
import { differenceInHours, format, formatDistanceToNow } from 'date-fns';
import { join } from 'pathe';
import { ReactNode, memo, useCallback, useState } from 'react';
import isEqual from 'react-fast-compare';
import { NavLink } from 'react-router-dom';

import { Button } from '@/components/ui/button';
import { ContextMenu, ContextMenuTrigger } from '@/components/ui/context-menu';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import SelectFileCheckbox from '@/components/server/files/SelectFileCheckbox';

import { bytesToString } from '@/lib/formatters';

import deleteFiles from '@/api/server/files/deleteFiles';
import { FileObject } from '@/api/server/files/loadDirectory';

// import FileDropdownMenu from '@/components/server/files/FileDropdownMenu';
import { ServerContext } from '@/state/server';

import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import useFlash from '@/plugins/useFlash';
import { usePermissions } from '@/plugins/usePermissions';

import FileDropdownMenu from './FileDropdownMenu';

function Clickable({ file, children }: { file: FileObject; children: ReactNode }) {
    const [canRead] = usePermissions(['file.read']);
    const [canReadContents] = usePermissions(['file.read-content']);
    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const directory = ServerContext.useStoreState((state) => state.files.directory);

    return (file.isFile && (!file.isEditable() || !canReadContents)) || (!file.isFile && !canRead) ? (
        <div className='flex flex-1 items-center truncate overflow-hidden px-4 py-2 text-foreground/80 no-underline not-[:is(a)]:cursor-default'>{children}</div>
    ) : (
        <NavLink
            className='flex flex-1 items-center truncate overflow-hidden px-4 py-2 text-foreground/80 no-underline not-[:is(a)]:cursor-default'
            to={`/server/${id}/files${file.isFile ? '/edit' : '#'}${encodePathSegments(join(directory, file.name))}`}
        >
            {children}
        </NavLink>
    );
}

const MemoizedClickable = memo(Clickable, isEqual);

const FileObjectRow = ({ file }: { file: FileObject }) => {
    const { t } = useTranslation();

    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { mutate } = useFileManagerSwr();
    const { clearAndAddHttpError, clearFlashes } = useFlash();
    const directory = ServerContext.useStoreState((state) => state.files.directory);

    const doDeletion = () => {
        clearFlashes('files');
        mutate((files) => files!.filter((f) => f.key !== file.key), false);
        deleteFiles(uuid, directory, [file.name]).catch((error) => {
            mutate();
            clearAndAddHttpError({ key: 'files', error });
        });
        setShowDeleteConfirm(false);
    };

    const handleDeleteClick = useCallback(() => setShowDeleteConfirm(true), []);

    return (
        <>
            <ContextMenu>
                <ContextMenuTrigger asChild>
                    <div className='flex items-center border border-border bg-muted/30 p-1 text-sm no-underline transition first:rounded-t-sm last:rounded-b-sm hover:bg-muted/50 hover:duration-0 data-[state=open]:bg-muted/50 has-[button[data-state=checked]]:bg-destructive/20' key={file.name}>
                        <SelectFileCheckbox name={file.name} />
                        <MemoizedClickable file={file}>
                            <div className={`flex-none text-muted-foreground mr-4 text-lg pl-3 mb-0.5`}>
                                {file.isFile ? (
                                    <div>
                                        <File size={22} />
                                    </div>
                                ) : (
                                    <div>
                                        <FolderOpen size={22} />
                                    </div>
                                )}
                            </div>
                            <div className='flex-1 truncate font-bold text-sm'>{file.name}</div>
                            {file.isFile && (
                                <div className='w-1/6 text-right mr-4 hidden sm:block text-xs'>{bytesToString(file.size)}</div>
                            )}
                            <div className='w-1/5 text-right mr-4 hidden md:block text-xs' title={file.modifiedAt.toString()}>
                                {Math.abs(differenceInHours(file.modifiedAt, new Date())) > 48
                                    ? format(file.modifiedAt, 'MMM do, yyyy h:mma')
                                    : formatDistanceToNow(file.modifiedAt, { addSuffix: true })}
                            </div>
                        </MemoizedClickable>
                    </div>
                </ContextMenuTrigger>
                <FileDropdownMenu file={file} onDelete={handleDeleteClick} />
            </ContextMenu>
            <Dialog open={showDeleteConfirm} onOpenChange={setShowDeleteConfirm}>
                <DialogContent>
                    <DialogHeader><DialogTitle>{file.isFile ? t('files:confirm_delete_file') : t('files:confirm_delete_directory')}</DialogTitle></DialogHeader>
                    <Trans i18nKey='files:confirm_delete_message' values={{ name: file.name }}>
                        You will not be able to recover the contents of{' '}
                        <span className='font-semibold text-foreground'>{{ name: file.name }}</span> once deleted.
                    </Trans>
                    <DialogFooter>
                        <Button variant='outline' onClick={() => setShowDeleteConfirm(false)}>{t('files:cancel')}</Button>
                        <Button variant='destructive' onClick={doDeletion}>{t('files:confirm_delete')}</Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
};

export default memo(FileObjectRow, (prevProps, nextProps) => {
    /* eslint-disable @typescript-eslint/no-unused-vars */
    const { isArchiveType, isEditable, ...prevFile } = prevProps.file;
    const { isArchiveType: nextIsArchiveType, isEditable: nextIsEditable, ...nextFile } = nextProps.file;
    /* eslint-enable @typescript-eslint/no-unused-vars */

    return isEqual(prevFile, nextFile);
});
