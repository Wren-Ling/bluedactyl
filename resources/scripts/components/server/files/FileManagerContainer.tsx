import { hashToPath } from '@/helpers';
import { useVirtualizer } from '@tanstack/react-virtual';
import debounce from 'debounce';
import { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useLocation } from 'react-router-dom';

import Can from '@/components/elements/Can';
import ErrorBoundary from '@/components/elements/ErrorBoundary';
import { ServerError } from '@/components/elements/ScreenBlock';
import ServerContentBlock from '@/components/elements/ServerContentBlock';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import FileManagerBreadcrumbs from '@/components/server/files/FileManagerBreadcrumbs';
import FileManagerStatus from '@/components/server/files/FileManagerStatus';
import FileObjectRow from '@/components/server/files/FileObjectRow';
import MassActionsBar from '@/components/server/files/MassActionsBar';
import NewDirectoryButton from '@/components/server/files/NewDirectoryButton';
import PullFileButton from '@/components/server/files/PullFileButton';
import UploadButton from '@/components/server/files/UploadButton';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';

import { httpErrorToHuman } from '@/api/http';
import type { FileObject } from '@/api/server/files/loadDirectory';

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
    const { t } = useTranslation();

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
        return <ServerError title={t('files:something_went_wrong')} message={httpErrorToHuman(error)} />;
    }

    return (
        <ServerContentBlock className='p-0!' title={t('files:files_page_title')} showFlashKey={'files'}>
            <SpinnerOverlay visible={!files} />

            <div className='px-2 sm:px-14 pt-2 h-full sm:pt-14'>
                <ErrorBoundary>
                    <div className='mb-4'>
                        <div className='flex flex-col gap-4 xl:flex-row xl:justify-between xl:items-start'>
                            <div>
                                <h2 className='text-2xl font-semibold'>{t('files:files_page_title')}</h2>
                                <p className='text-sm text-muted-foreground leading-relaxed mt-1'>
                                    {t('files:files_page_description')}
                                </p>
                            </div>
                            <Can action={'file.create'}>
                                <div className='flex flex-wrap gap-2 items-center'>
                                    <FileManagerStatus />
                                    <NewDirectoryButton />
                                    <NewFileButton id={id} />
                                    <PullFileButton />
                                    <UploadButton />
                                </div>
                            </Can>
                        </div>
                    </div>

                    <div className='flex flex-wrap items-center justify-between gap-2 mb-4'>
                        <FileManagerBreadcrumbs
                            renderLeft={
                                <Checkbox
                                    className='ml-[1.22rem] mr-4'
                                    checked={selectedFilesLength === (files?.length === 0 ? -1 : files?.length)}
                                    onCheckedChange={() => onSelectAllClick()}
                                />
                            }
                        />
                        <Button variant='secondary' size='sm' onClick={() => void mutate().catch(() => undefined)}>
                            {t('files:refresh')}
                        </Button>
                    </div>
                </ErrorBoundary>
            </div>

            {!files ? null : (
                <>
                    {!files.length ? (
                        <p className='text-sm text-muted-foreground text-center'>{t('files:empty_folder')}</p>
                    ) : (
                        <>
                            <div className='relative p-1 border border-border rounded-md sm:ml-12 sm:mr-12 mx-2'>
                                <div className='absolute left-4 top-1/2 pl-2 -translate-y-1/2 pointer-events-none'>
                                    <svg
                                        xmlns='http://www.w3.org/2000/svg'
                                        fill='none'
                                        viewBox='0 0 24 24'
                                        strokeWidth={1.5}
                                        stroke='currentColor'
                                        className='w-5 h-5 opacity-40'
                                    >
                                        <path
                                            strokeLinecap='round'
                                            strokeLinejoin='round'
                                            d='m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z'
                                        />
                                    </svg>
                                </div>

                                <input
                                    ref={searchInputRef}
                                    className='pl-14 py-4 w-full rounded-lg bg-muted/30 text-sm font-bold outline-none'
                                    type='text'
                                    placeholder={t('files:search')}
                                    onChange={(event) => debouncedSearchTerm(event.target.value)}
                                />
                            </div>

                            <div ref={parentRef} className='max-h-screen min-h-screen overflow-auto'>
                                <div
                                    data-pyro-file-manager-files
                                    className='p-1 border border-border rounded-xl sm:ml-12 sm:mr-12 mx-2 bg-card'
                                    style={{ height: `${rowVirtualizer.getTotalSize()}px` }}
                                >
                                    <div
                                        className='w-full overflow-hidden rounded-lg gap-0.5 flex flex-col'
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
                                                        className='w-full absolute left-0 top-0'
                                                        style={{
                                                            height: `${item.size}px`,
                                                            transform: `translateY(${item.start}px)`,
                                                        }}
                                                    >
                                                        <FileObjectRow
                                                            file={filesArray[item.index]}
                                                            key={filesArray[item.index]?.name}
                                                        />
                                                    </div>
                                                );
                                            }
                                            return null;
                                        })}
                                    </div>
                                </div>
                            </div>

                            <MassActionsBar />
                        </>
                    )}
                </>
            )}
        </ServerContentBlock>
    );
};

export default FileManagerContainer;
