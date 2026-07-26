import { Search } from 'lucide-react';
import debounce from 'debounce';
import { useCallback, useEffect, useState } from 'react';
import { toast } from 'sonner';

import Can from '@/components/elements/Can';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

import { Input } from '@/components/ui/input';

import LoaderSelector from './LoaderSelector';
import { ModList } from './ModList';
import GameVersionSelector from './VersionSelector';
import { GlobalStateProvider, ModrinthService, appVersion, useGlobalStateContext } from './config';

const ModrinthContainerInner = () => {
    const {
        mods,
        loaders,
        gameVersions,
        selectedLoaders,
        selectedVersions,
        searchQuery,
        setMods,
        setLoaders,
        setGameVersions,
        setSelectedLoaders,
        setSelectedVersions,
        setSearchQuery,
        updateGameVersions,
        updateLoaders,
    } = useGlobalStateContext();

    const [searchTerm, setSearchTerm] = useState(searchQuery);
    const [isLoadingLoader, setLoaderLoading] = useState(true);
    const [isLoadingVersion, setVersionLoading] = useState(true);
    const [isInitialized, setIsInitialized] = useState(false);

    const debouncedSetSearchTerm = useCallback(
        debounce((value: string) => {
            setSearchQuery(value);
        }, 500),
        [setSearchQuery],
    );

    const handleInputChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const value = event.target.value;
        setSearchTerm(value);
        debouncedSetSearchTerm(value);
    };

    useEffect(() => {
        const initialize = async () => {
            if (isInitialized) return;

            const initialized = await ModrinthService.init(appVersion);
            if (!initialized) {
                toast.error('Failed to initialize Modrinth API');
                return;
            }

            try {
                const [loaderResponse, versionResponse] = await Promise.all([
                    ModrinthService.fetchLoaders(),
                    ModrinthService.fetchGameVersions(),
                ]);

                updateLoaders(loaderResponse.data);
                updateGameVersions(versionResponse.data);

                setLoaderLoading(false);
                setVersionLoading(false);
                setIsInitialized(true);
            } catch (error) {
                console.error('Initial fetch error:', error);
                toast.error(error instanceof Error ? error.message : 'Failed to fetch initial data');
                setLoaderLoading(false);
                setVersionLoading(false);
            }
        };

        initialize();
    }, [isInitialized, updateLoaders, updateGameVersions]);

    useEffect(() => {
        setSearchTerm(searchQuery);
    }, [searchQuery]);

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <Card className='mb-5'>
                <CardContent className='p-8' />
            </Card>
            <div className='flex flex-wrap gap-4'>
                <Card className='w-full md:w-1/6'>
                    <CardContent className='p-8'>
                        <Can action={'modrinth.loader'}>
                            <div className='mb-4 w-full text-nowrap select-none'>
                                <div>
                                    <CardTitle className='mb-4'>Loader</CardTitle>
                                    {isLoadingLoader ? <p className='text-sm text-muted-foreground'>Loading loaders...</p> : <LoaderSelector />}
                                </div>
                            </div>
                        </Can>
                        <Can action={'modrinth.version'}>
                            <div className='mb-4 w-full text-nowrap select-none'>
                                <div>
                                    <CardTitle className='mb-4 mt-6'>Version</CardTitle>
                                    {isLoadingVersion ? <p className='text-sm text-muted-foreground'>Loading versions...</p> : <GameVersionSelector />}
                                </div>
                            </div>
                        </Can>
                    </CardContent>
                </Card>

                <Card className='w-full md:w-4/5'>
                    <CardHeader>
                        <CardTitle>Downloader</CardTitle>
                    </CardHeader>
                    <CardContent className='p-8'>
                        <div className='relative mb-4 h-full w-full'>
                            <Search className='pointer-events-none absolute left-5 top-1/2 size-5 -translate-y-1/2 opacity-40' />
                            <Input
                                type='text'
                                placeholder='Search'
                                value={searchTerm}
                                onChange={handleInputChange}
                                className='py-4 pl-14 pr-4 text-sm font-bold'
                            />
                        </div>
                        <ModList />
                    </CardContent>
                </Card>
            </div>
        </div>
    );
};

const ModrinthContainer = () => {
    return (
        <GlobalStateProvider>
            <ModrinthContainerInner />
        </GlobalStateProvider>
    );
};

export default ModrinthContainer;
