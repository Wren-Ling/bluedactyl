import { Play, Copy, FileDown, FileArchive, PencilLine, Shield, Trash2 } from 'lucide-react';
import { join } from 'pathe';
import { memo, useState } from 'react';
import isEqual from 'react-fast-compare';
import { toast } from 'sonner';

import Can from '@/components/elements/Can';
import { ContextMenuContent, ContextMenuItem } from '@/components/ui/context-menu';
import ChmodFileModal from '@/components/server/files/ChmodFileModal';
import RenameFileModal from '@/components/server/files/RenameFileModal';

import compressFiles from '@/api/server/files/compressFiles';
import copyFile from '@/api/server/files/copyFile';
import decompressFiles from '@/api/server/files/decompressFiles';
import getFileDownloadUrl from '@/api/server/files/getFileDownloadUrl';
import { FileObject } from '@/api/server/files/loadDirectory';

import { ServerContext } from '@/state/server';

import useFileManagerSwr from '@/plugins/useFileManagerSwr';
import useFlash from '@/plugins/useFlash';

type ModalType = 'rename' | 'move' | 'chmod';

const FileDropdownMenu = ({ file, onDelete }: { file: FileObject; onDelete?: () => void }) => {
    const [modal, setModal] = useState<ModalType | null>(null);

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const { mutate } = useFileManagerSwr();
    const { clearAndAddHttpError, clearFlashes } = useFlash();
    const directory = ServerContext.useStoreState((state) => state.files.directory);

    const doCopy = () => {
        clearFlashes('files');
        toast.info('Duplicating...');

        copyFile(uuid, join(directory, file.name))
            .then(() => mutate())
            .then(() => toast.success('File successfully duplicated.'))
            .catch((error) => clearAndAddHttpError({ key: 'files', error }));
    };

    const doDownload = () => {
        clearFlashes('files');

        getFileDownloadUrl(uuid, join(directory, file.name))
            .then((url) => {
                // @ts-expect-error this is valid
                window.location = url;
            })
            .catch((error) => clearAndAddHttpError({ key: 'files', error }));
    };

    const doArchive = () => {
        clearFlashes('files');
        toast.info('Archiving files...');

        compressFiles(uuid, directory, [file.name])
            .then(() => mutate())
            .then(() => toast.success('Files successfully archived.'))
            .catch((error) => clearAndAddHttpError({ key: 'files', error }));
    };

    const doUnarchive = () => {
        clearFlashes('files');
        toast.info('Unarchiving files...');

        decompressFiles(uuid, directory, file.name)
            .then(() => mutate())
            .then(() => toast.success('Files successfully unarchived.'))
            .catch((error) => clearAndAddHttpError({ key: 'files', error }));
    };

    return (
        <>
            {modal ? (
                modal === 'chmod' ? (
                    <ChmodFileModal
                        visible
                        appear
                        files={[{ file: file.name, mode: file.modeBits }]}
                        onDismissed={() => setModal(null)}
                    />
                ) : (
                    <RenameFileModal
                        visible
                        appear
                        files={[file.name]}
                        useMoveTerminology={modal === 'move'}
                        onDismissed={() => setModal(null)}
                    />
                )
            ) : null}
            <ContextMenuContent className='flex flex-col gap-1'>
                <Can action={'file.update'}>
                    <ContextMenuItem className='flex gap-2' onSelect={() => setModal('rename')}>
                        <PencilLine className='h-4! w-4!' />
                        <span>Rename</span>
                    </ContextMenuItem>
                    <ContextMenuItem className='flex gap-2' onSelect={() => setModal('move')}>
                        <Play className='h-4! w-4!' />
                        <span>Move</span>
                    </ContextMenuItem>
                    <ContextMenuItem className='flex gap-2' onSelect={() => setModal('chmod')}>
                        <Shield className='h-4! w-4!' />
                        <span>Permissions</span>
                    </ContextMenuItem>
                </Can>
                {file.isFile && (
                    <Can action={'file.create'}>
                        <ContextMenuItem className='flex gap-2' onClick={doCopy}>
                            <Copy className='h-4! w-4!' />
                            <span>Duplicate</span>
                        </ContextMenuItem>
                    </Can>
                )}
                {file.isArchiveType() ? (
                    <Can action={'file.create'}>
                        <ContextMenuItem className='flex gap-2' onSelect={doUnarchive} title={'Unarchive'}>
                            <FileArchive className='h-4! w-4!' />
                            <span>Unarchive</span>
                        </ContextMenuItem>
                    </Can>
                ) : (
                    <Can action={'file.archive'}>
                        <ContextMenuItem className='flex gap-2' onSelect={doArchive}>
                            <FileArchive className='h-4! w-4!' />
                            <span>Archive</span>
                        </ContextMenuItem>
                    </Can>
                )}
                {file.isFile && (
                    <ContextMenuItem className='flex gap-2' onSelect={doDownload}>
                        <FileDown className='h-4! w-4!' />
                        <span>Download</span>
                    </ContextMenuItem>
                )}
                <Can action={'file.delete'}>
                    <ContextMenuItem className='flex gap-2' onClick={onDelete}>
                        <Trash2 className='h-4! w-4!' />
                        <span>Delete</span>
                    </ContextMenuItem>
                </Can>
            </ContextMenuContent>
        </>
    );
};

export default memo(FileDropdownMenu, isEqual);
