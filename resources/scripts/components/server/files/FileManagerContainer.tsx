import { hashToPath } from '@/helpers';
import { useVirtualizer } from '@tanstack/react-virtual';
import debounce from 'debounce';
import { Search } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useLocation } from 'react-router-dom';

import Can from '@/components/elements/Can';
import { Checkbox } from '@/components/ui/checkbox';
import ErrorBoundary from '@/components/elements/ErrorBoundary';
import FlashMessageRender from '@/components/FlashMessageRender';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import { ServerError } from '@/components/elements/ScreenBlock';
import FileManagerBreadcrumbs from '@/components/server/files/FileManagerBreadcrumbs';
import FileManagerStatus from '@/components/server/files/FileManagerStatus';
import FileObjectRow from '@/components/server/files/FileObjectRow';
import MassActionsBar from '@/components/server/files/MassActionsBar';
import NewDirectoryButton from '@/components/server/files/NewDirectoryButton';
import UploadButton from '@/components/server/files/UploadButton';

import { httpErrorToHuman } from '@/api/http';
import { FileObject } from '@/api/server/files/loadDirectory';

import { useStoreActions } from '@/state/hooks';
import { ServerContext } from '@/state/server';

import useFileManagerSwr from '@/plugins/useFileManagerSwr';

import NewFileButton from './NewFileButton';

const sortFiles = (files: FileObject[]): FileObject[] => {
    const sortedFiles: FileObject[] = files
        .sort((a, b) => a.name.localeCompare(b.name))
        .sort((a, b) => (a.isFile === b.isFile ? 0 : a.isFile ? 1 : -1));
    return sortedFiles.filter((file, index) => index === 0 || file.name !== sortedFiles[index - 1]?.name);
};

const FileManagerContainer = () => {
    const parentRef = useRef<HTMLDivElement | null>(null);

    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const { hash, pathname } = useLocation();
    const { data: files, error, mutate } = useFileManagerSwr();

    const directory = ServerContext.useStoreState((state) => state.files.directory);
    const clearFlashes = useStoreActions((actions) => actions.flashes.clearFlashes);
    const setDirectory = ServerContext.useStoreActions((actions) => actions.files.setDirectory);

    const setSelectedFiles = ServerContext.useStoreActions((actions) => actions.files.setSelectedFiles);
    const selectedFilesLength = ServerContext.useStoreState((state) => state.files.selectedFiles.length);

    const searchInputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        clearFlashes('files');
        setSelectedFiles([]);
        setDirectory(hashToPath(hash));
    }, [hash]);

    useEffect(() => {
        mutate();
    }, [directory]);

    const onSelectAllClick = () => {
        console.log('files', files);
        setSelectedFiles(
            selectedFilesLength === (files?.length === 0 ? -1 : files?.length)
                ? []
                : files?.map((file) => file.name) || [],
        );
    };

    const [searchTerm, setSearchTerm] = useState('');
    const debouncedSearchTerm = debounce(setSearchTerm, 50);

    const filesArray = sortFiles(files ?? []).filter((file) =>
        file.name.toLowerCase().includes(searchTerm.toLowerCase()),
    );

    useEffect(() => {
        setSearchTerm('');

        if (searchInputRef.current) {
            searchInputRef.current.value = '';
        }
    }, [hash, pathname, directory]);

    const rowVirtualizer = useVirtualizer({
        count: filesArray.length,
        getScrollElement: () => parentRef.current,
        estimateSize: () => 54,
    });

    if (error) {
        return <ServerError title={'Something went wrong.'} message={httpErrorToHuman(error)} />;
    }

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <FlashMessageRender byKey={'files'} />
            <ErrorBoundary>
                <MainPageHeader
                    direction='column'
                    title={'Files'}
                    titleChildren={
                        <Can action={'file.create'}>
                            <div className='flex flex-row gap-1'>
                                <FileManagerStatus />
                                <NewDirectoryButton />
                                <NewFileButton id={id} />
                                <UploadButton />
                            </div>
                        </Can>
                    }
                >
                    <p className='text-sm leading-relaxed text-muted-foreground'>
                        Manage your server files and directories. Upload, download, edit, and organize your
                        server&apos;s file system with our integrated file manager.
                    </p>
                </MainPageHeader>
                <div className={'mb-4 flex flex-wrap-reverse md:flex-nowrap'}>
                    <FileManagerBreadcrumbs
                        renderLeft={
                            <Checkbox
                                className='ml-[1.22rem] mr-4'
                                checked={selectedFilesLength === (files?.length === 0 ? -1 : files?.length)}
                                onCheckedChange={() => onSelectAllClick()}
                            />
                        }
                    />
                </div>
            </ErrorBoundary>

            {!files ? null : (
                <>
                    {!files.length ? (
                        <p className={'text-center text-sm text-muted-foreground'}>This folder is empty.</p>
                    ) : (
                        <>
                            <div className='relative mx-2 rounded-md border border-border p-1 sm:ml-12 sm:mr-12'>
                                <div className='pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 pl-2'>
                                    <Search className='size-5 opacity-40' />
                                </div>

                                <input
                                    ref={searchInputRef}
                                    className='w-full rounded-lg bg-muted/40 px-14 py-4 text-sm font-bold outline-none'
                                    type='text'
                                    placeholder='Search...'
                                    onChange={(event) => debouncedSearchTerm(event.target.value)}
                                />
                            </div>
                            <div ref={parentRef} className='max-h-screen min-h-screen overflow-auto'>
                                <div
                                    data-pyro-file-manager-files
                                    className='mx-2 rounded-xl border border-border bg-card p-1 sm:ml-12 sm:mr-12'
                                    style={{ height: `${rowVirtualizer.getTotalSize()}px` }}
                                >
                                    <div
                                        className='flex w-full flex-col gap-0.5 overflow-hidden rounded-lg'
                                        style={{
                                            height: `${rowVirtualizer.getTotalSize()}px`,
                                            width: '100%',
                                            position: 'relative',
                                        }}
                                    >
                                        {rowVirtualizer.getVirtualItems().map((item) => {
                                            if (filesArray[item.index] !== undefined) {
                                                return (
                                                    <div
                                                        key={item.key}
                                                        className='absolute left-0 top-0 w-full'
                                                        style={{
                                                            height: `${item.size}px`,
                                                            transform: `translateY(${item.start}px)`,
                                                        }}
                                                    >
                                                        <FileObjectRow
                                                            // @ts-expect-error - Legacy type suppression
                                                            file={filesArray[item.index]}
                                                            key={filesArray[item.index]?.name}
                                                        />
                                                    </div>
                                                );
                                            }
                                            return <></>;
                                        })}
                                    </div>
                                </div>
                            </div>
                            <MassActionsBar />
                        </>
                    )}
                </>
            )}
        </div>
    );
};

export default FileManagerContainer;
