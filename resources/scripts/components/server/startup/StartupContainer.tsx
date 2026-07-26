import { useEffect, useState } from 'react';
import isEqual from 'react-fast-compare';

import { Button } from '@/components/ui/button';
import CopyOnClick from '@/components/elements/CopyOnClick';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import FlashMessageRender from '@/components/FlashMessageRender';
import InputSpinner from '@/components/elements/InputSpinner';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import Pagination from '@/components/elements/Pagination';
import { ServerError } from '@/components/elements/ScreenBlock';
import Spinner from '@/components/elements/Spinner';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import VariableBox from '@/components/server/startup/VariableBox';

import { httpErrorToHuman } from '@/api/http';
import processStartupCommand from '@/api/server/processStartupCommand';
import resetStartupCommand from '@/api/server/resetStartupCommand';
import revertDockerImage from '@/api/server/revertDockerImage';
import setSelectedDockerImage from '@/api/server/setSelectedDockerImage';
import updateStartupCommand from '@/api/server/updateStartupCommand';
import getServerStartup from '@/api/swr/getServerStartup';

import { ServerContext } from '@/state/server';

import { useDeepCompareEffect } from '@/plugins/useDeepCompareEffect';
import useFlash from '@/plugins/useFlash';
import { usePermissions } from '@/plugins/usePermissions';

const StartupContainer = () => {
    const [loading, setLoading] = useState(false);
    const [commandLoading, setCommandLoading] = useState(false);
    const [editingCommand, setEditingCommand] = useState(false);
    const [commandValue, setCommandValue] = useState('');
    const [liveProcessedCommand, setLiveProcessedCommand] = useState('');
    const [revertModalVisible, setRevertModalVisible] = useState(false);
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const [canEditCommand] = usePermissions(['startup.command']);
    const [canEditDockerImage] = usePermissions(['startup.docker-image']);

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const server = ServerContext.useStoreState((state) => state.server.data!, isEqual);
    const variables = ServerContext.useStoreState(
        ({ server }) => ({
            variables: server.data!.variables,
            invocation: server.data!.invocation,
            dockerImage: server.data!.dockerImage,
        }),
        isEqual,
    );

    const { data, error, isValidating, mutate } = getServerStartup(uuid, {
        ...variables,
        dockerImages: { [variables.dockerImage]: variables.dockerImage },
        rawStartupCommand: '',
    });

    const ITEMS_PER_PAGE = 6;
    const [currentPage, setCurrentPage] = useState(1);

    const paginatedVariables = data
        ? data.variables.slice((currentPage - 1) * ITEMS_PER_PAGE, currentPage * ITEMS_PER_PAGE)
        : [];

    const setServerFromState = ServerContext.useStoreActions((actions) => actions.server.setServerFromState);
    const isCustomImage =
        data &&
        !Object.values(data.dockerImages)
            .map((v) => v.toLowerCase())
            .includes(variables.dockerImage.toLowerCase());

    useEffect(() => {
        mutate();
    }, [mutate]);

    useDeepCompareEffect(() => {
        if (!data) return;
        setServerFromState((s) => ({ ...s, invocation: data.invocation, variables: data.variables }));
    }, [data]);

    const updateSelectedDockerImage = (image: string) => {
        setLoading(true);
        clearFlashes('startup:image');
        setSelectedDockerImage(uuid, image)
            .then(() => setServerFromState((s) => ({ ...s, dockerImage: image })))
            .catch((error) => { console.error(error); clearAndAddHttpError({ key: 'startup:image', error }); })
            .then(() => setLoading(false));
    };

    const revertToEggDefault = () => {
        setLoading(true);
        clearFlashes('startup:image');
        revertDockerImage(uuid)
            .then(() => {
                const defaultImage = data ? Object.values(data.dockerImages)[0] || '' : '';
                setServerFromState((s) => ({ ...s, dockerImage: defaultImage }));
                setRevertModalVisible(false);
            })
            .catch((error) => { console.error(error); clearAndAddHttpError({ key: 'startup:image', error }); })
            .then(() => setLoading(false));
    };

    const updateCommand = () => {
        setCommandLoading(true);
        clearFlashes('startup:command');
        updateStartupCommand(uuid, commandValue)
            .then((invocation) => {
                mutate((data) => ({ ...data!, invocation, rawStartupCommand: commandValue }), false);
                setEditingCommand(false);
            })
            .catch((error) => { console.error(error); clearAndAddHttpError({ key: 'startup:command', error }); })
            .then(() => setCommandLoading(false));
    };

    const loadDefaultCommand = async () => {
        try {
            const defaultCommand = await resetStartupCommand(uuid);
            setCommandValue(defaultCommand);
            const processed = await processCommandLive(defaultCommand);
            setLiveProcessedCommand(processed);
        } catch (error) { console.error('Failed to load default command:', error); }
    };

    const processCommandLive = async (rawCommand: string): Promise<string> => {
        try { return await processStartupCommand(uuid, rawCommand); }
        catch (error) { console.error('Failed to process command:', error); return rawCommand; }
    };

    const startEditingCommand = async () => {
        const initialCommand = data?.rawStartupCommand || '';
        setCommandValue(initialCommand);
        const processed = await processCommandLive(initialCommand);
        setLiveProcessedCommand(processed);
        setEditingCommand(true);
    };

    const cancelEditingCommand = () => {
        setEditingCommand(false);
        setCommandValue('');
        setLiveProcessedCommand('');
    };

    const handleCommandChange = async (value: string) => {
        setCommandValue(value);
        const processed = await processCommandLive(value);
        setLiveProcessedCommand(processed);
    };

    if (!data) {
        if (!error || (error && isValidating)) {
            return (
                <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
                    <FlashMessageRender byKey={'startup:image'} />
                    <div className='flex min-h-[60vh] items-center justify-center'>
                        <div className='flex flex-col items-center gap-4'>
                            <Spinner centered size={Spinner.Size.LARGE} />
                            <p className='text-sm text-muted-foreground'>Loading startup configuration...</p>
                        </div>
                    </div>
                </div>
            );
        }
        return <ServerError title={'Oops!'} message={httpErrorToHuman(error)} />;
    }

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <FlashMessageRender byKey={'startup:image'} />

            <Dialog open={revertModalVisible} onOpenChange={setRevertModalVisible}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Revert Docker Image</DialogTitle>
                        <DialogDescription>
                            This will revert your server&apos;s Docker image back to the egg&apos;s default specification.
                        </DialogDescription>
                    </DialogHeader>
                    <div className='rounded-xl border border-amber-500/20 bg-linear-to-b from-amber-500/10 to-amber-600/5 p-3'>
                        <p className='text-sm text-amber-200'>
                            <span className='font-medium'>Warning:</span> You will not be able to set a custom image
                            back without contacting support.
                        </p>
                    </div>
                    <p className='text-sm text-muted-foreground'>Are you sure you want to continue?</p>
                    <DialogFooter>
                        <Button variant='outline' onClick={() => setRevertModalVisible(false)}>Cancel</Button>
                        <Button variant='default' onClick={revertToEggDefault} disabled={loading}>
                            Yes, revert to default
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <div className='space-y-6'>
                <MainPageHeader direction='column' title='Startup Settings'>
                    <p className='text-sm leading-relaxed text-muted-foreground'>
                        Configure how your server starts up. These settings control the startup command and environment
                        variables.
                        <span className='font-medium text-amber-400'> Exercise caution when modifying these settings.</span>
                    </p>
                </MainPageHeader>

                <div className='space-y-6'>
                    <Card className='p-6'>
                        <CardHeader>
                            <CardTitle className='text-xl font-extrabold tracking-tight'>Startup Command</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className='mb-6 space-y-4'>
                                <p className='text-sm leading-relaxed text-muted-foreground'>
                                    Configure the command that starts your server. You can edit the raw command or view the
                                    processed version with variables resolved.
                                </p>
                            </div>
                            {editingCommand ? (
                                <div className='space-y-4'>
                                    <div className='grid grid-cols-1 gap-4 md:gap-6 xl:grid-cols-2'>
                                        <div>
                                            <label className='mb-3 block text-sm font-medium text-foreground'>Raw Command</label>
                                            <textarea
                                                className='h-32 w-full resize-none rounded-xl border-2 border-blue-500/30 bg-linear-to-b from-muted/30 to-muted/10 px-3 py-3 font-mono text-sm text-foreground placeholder:text-muted-foreground/50 focus:border-blue-500/60 focus:outline-none focus:ring-2 focus:ring-blue-500/50 sm:h-36 sm:px-4 sm:py-4 sm:text-base md:h-40'
                                                value={commandValue}
                                                onChange={(e) => handleCommandChange(e.target.value)}
                                                placeholder='Enter startup command with variables like {{SERVER_MEMORY}} or {{SERVER_PORT}}...'
                                            />
                                        </div>
                                        <div>
                                            <label className='mb-3 block text-sm font-medium text-foreground'>Live Preview</label>
                                            <CopyOnClick text={liveProcessedCommand}>
                                                <div className='group cursor-pointer'>
                                                    <div className='h-32 w-full overflow-auto rounded-xl border-2 border-green-500/20 bg-linear-to-b from-muted/10 to-muted/5 px-3 py-3 font-mono text-sm text-green-200 transition-all group-hover:border-green-500/40 sm:h-36 sm:px-4 sm:py-4 sm:text-base md:h-40'>
                                                        {liveProcessedCommand || 'Enter a command to see the live preview...'}
                                                    </div>
                                                </div>
                                            </CopyOnClick>
                                        </div>
                                    </div>
                                    <div className='flex flex-col gap-3 border-t border-border/30 pt-4 sm:flex-row sm:gap-4'>
                                        <InputSpinner visible={commandLoading}>
                                            <Button variant='default' size='default' onClick={updateCommand} disabled={commandLoading || !commandValue.trim()}>
                                                {commandLoading ? 'Saving...' : 'Save Command'}
                                            </Button>
                                        </InputSpinner>
                                        <Button variant='outline' size='default' onClick={loadDefaultCommand} disabled={commandLoading}>
                                            Load Default
                                        </Button>
                                        <Button variant='outline' size='default' onClick={cancelEditingCommand} disabled={commandLoading}>
                                            Cancel
                                        </Button>
                                    </div>
                                </div>
                            ) : (
                                <div className='space-y-5'>
                                    {data.rawStartupCommand && (
                                        <div className='space-y-3'>
                                            <div className='flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between'>
                                                <label className='text-sm font-medium text-foreground'>Raw Command</label>
                                                {canEditCommand && (
                                                    <Button variant='outline' size='sm' onClick={startEditingCommand}>
                                                        Edit Command
                                                    </Button>
                                                )}
                                            </div>
                                            <CopyOnClick text={data.rawStartupCommand}>
                                                <div className='group cursor-pointer'>
                                                    <div className='flex min-h-[3.5rem] flex-row items-center overflow-auto rounded-xl border border-border/20 bg-linear-to-b from-muted/20 to-muted/10 px-3 py-3 font-mono text-sm text-foreground transition-all group-hover:border-border/40 sm:min-h-[4rem] sm:px-4 sm:py-4 sm:text-base'>
                                                        {data.rawStartupCommand}
                                                    </div>
                                                </div>
                                            </CopyOnClick>
                                        </div>
                                    )}
                                    <div className='space-y-3'>
                                        <div className='flex flex-col items-center gap-2 sm:flex-row'>
                                            <label className='text-sm font-medium text-foreground'>Processed Command</label>
                                            <span className='w-fit rounded text-xs text-muted-foreground'>Read-only</span>
                                        </div>
                                        <CopyOnClick text={data.invocation}>
                                            <div className='group cursor-pointer'>
                                                <div className='flex min-h-[3.5rem] flex-row items-center overflow-auto rounded-xl border border-border/10 bg-linear-to-b from-muted/5 to-muted/2 px-3 py-3 font-mono text-sm text-muted-foreground transition-all group-hover:border-border/20 sm:min-h-[4rem] sm:px-4 sm:py-4 sm:text-base'>
                                                    {data.invocation}
                                                </div>
                                            </div>
                                        </CopyOnClick>
                                    </div>
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    <Card className='p-6'>
                        <CardHeader>
                            <CardTitle className='text-xl font-extrabold tracking-tight'>Docker Image</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className='mb-6 space-y-4'>
                                <p className='text-sm leading-relaxed text-muted-foreground'>
                                    The container image used to run your server. Different images provide different software
                                    versions and configurations.
                                </p>
                            </div>
                            {Object.keys(data.dockerImages).length > 1 && !isCustomImage ? (
                                <div className='space-y-4'>
                                    <InputSpinner visible={loading}>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger asChild>
                                                <button className='flex w-full cursor-pointer items-center justify-between gap-3 rounded-md border border-border/20 bg-linear-to-b from-muted/30 to-muted/20 px-3 py-3 font-mono text-sm font-medium text-foreground transition-all hover:border-border/40 hover:from-muted/40 hover:to-muted/30 sm:px-4 sm:py-3 sm:text-base'>
                                                    <span className='truncate text-left'>
                                                        {Object.keys(data.dockerImages).find(
                                                            (key) => data.dockerImages[key] === variables.dockerImage,
                                                        ) || variables.dockerImage}
                                                    </span>
                                                    <svg width='16' height='16' viewBox='0 0 13 13' fill='none' className='shrink-0 opacity-60'>
                                                        <path d='M3.39257 5.3429C3.48398 5.25161 3.60788 5.20033 3.73707 5.20033C3.86626 5.20033 3.99016 5.25161 4.08157 5.3429L6.49957 7.7609L8.91757 5.3429C8.9622 5.29501 9.01602 5.25659 9.07582 5.22995C9.13562 5.2033 9.20017 5.18897 9.26563 5.18782C9.33109 5.18667 9.39611 5.19871 9.45681 5.22322C9.51751 5.24774 9.57265 5.28424 9.61895 5.33053C9.66524 5.37682 9.70173 5.43196 9.72625 5.49267C9.75077 5.55337 9.76281 5.61839 9.76166 5.68384C9.7605 5.7493 9.74617 5.81385 9.71953 5.87365C9.69288 5.93345 9.65447 5.98727 9.60657 6.0319L6.84407 8.7944C6.75266 8.8857 6.62876 8.93698 6.49957 8.93698C6.37038 8.93698 6.24648 8.8857 6.15507 8.7944L3.39257 6.0319C3.30128 5.9405 3.25 5.81659 3.25 5.6874C3.25 5.55822 3.30128 5.43431 3.39257 5.3429Z' fill='currentColor' fillOpacity='0.6'/>
                                                    </svg>
                                                </button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent className='z-99999 flex flex-col gap-1' sideOffset={8}>
                                                <DropdownMenuRadioGroup
                                                    value={variables.dockerImage}
                                                    onValueChange={(value) => updateSelectedDockerImage(value)}
                                                >
                                                    {Object.keys(data.dockerImages).map((key) => (
                                                        <DropdownMenuRadioItem
                                                            value={data.dockerImages[key] as string}
                                                            key={data.dockerImages[key]}
                                                        >
                                                            {key}
                                                        </DropdownMenuRadioItem>
                                                    ))}
                                                </DropdownMenuRadioGroup>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </InputSpinner>
                                </div>
                            ) : (
                                <div className='space-y-4'>
                                    <div className='overflow-auto rounded-xl border border-border/10 bg-linear-to-b from-muted/20 to-muted/10 px-3 py-3 sm:px-4 sm:py-4'>
                                        <span className='break-all font-mono text-sm text-foreground sm:text-base'>
                                            {Object.keys(data.dockerImages).find(
                                                (key) => data.dockerImages[key] === variables.dockerImage,
                                            ) || variables.dockerImage}
                                        </span>
                                    </div>
                                    {isCustomImage && (
                                        <div className='rounded-xl border border-amber-500/20 bg-linear-to-b from-amber-500/10 to-amber-600/5 p-3 sm:p-4'>
                                            <div className='flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between'>
                                                <div className='flex-1'>
                                                    <p className='text-sm text-amber-200'>
                                                        <span className='font-medium'>Notice:</span> This server&apos;s
                                                        Docker image has been manually set by an administrator and cannot be
                                                        changed through this interface.
                                                    </p>
                                                    {canEditDockerImage && (
                                                        <p className='mt-2 text-xs text-amber-300/80'>
                                                            You can revert to the egg&apos;s default image, but you
                                                            won&apos;t be able to set it back without contacting support.
                                                        </p>
                                                    )}
                                                </div>
                                                {canEditDockerImage && (
                                                    <div className='shrink-0'>
                                                        <InputSpinner visible={loading}>
                                                            <Button
                                                                variant='outline'
                                                                size='sm'
                                                                onClick={() => setRevertModalVisible(true)}
                                                                disabled={loading}
                                                            >
                                                                Revert to Default
                                                            </Button>
                                                        </InputSpinner>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    )}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {data && data.variables.length > 0 && (
                    <div className='space-y-6'>
                        <div className='space-y-3'>
                            <h3 className='text-2xl font-extrabold text-foreground'>Environment Variables</h3>
                            <p className='text-sm leading-relaxed text-muted-foreground'>
                                Configure environment variables that will be available to your server. These variables
                                can be used to customize server behavior and settings.
                            </p>
                        </div>

                        <div className='rounded-xl border border-border/10 bg-linear-to-b from-muted/10 to-muted/5 p-4'>
                            <div className='space-y-3'>
                                <h4 className='text-sm font-medium text-foreground'>Global Server Variables</h4>
                                <div className='grid grid-cols-1 gap-3 text-xs sm:grid-cols-2 lg:grid-cols-3'>
                                    {[
                                        { label: 'SERVER_MEMORY', value: server?.limits?.memory || 'null' },
                                        { label: 'SERVER_IP', value: server?.allocations?.find((a) => a.isDefault)?.ip || 'null' },
                                        { label: 'SERVER_PORT', value: server?.allocations?.find((a) => a.isDefault)?.port || 'null' },
                                        { label: 'SERVER_UUID', value: uuid },
                                        { label: 'SERVER_NAME', value: server?.name || 'null' },
                                        { label: 'SERVER_CPU', value: server?.limits?.cpu || 'null' },
                                    ].map(({ label, value }) => (
                                        <div key={label} className='flex items-center justify-between gap-2 rounded border border-border/10 bg-muted/20 px-3 py-2'>
                                            <span className='font-mono text-muted-foreground'>{label}</span>
                                            <CopyOnClick text={value}>
                                                <span className='font-mono text-foreground'>{value}</span>
                                            </CopyOnClick>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        <div className='flex min-h-[40svh] flex-col justify-between'>
                            <div className='grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-2 xl:grid-cols-3'>
                                {paginatedVariables.map((variable) => (
                                    <VariableBox key={variable.envVariable} variable={variable} />
                                ))}
                            </div>
                            {data.variables.length > ITEMS_PER_PAGE && (
                                <div className='mt-6 border-t border-border/10 pt-4'>
                                    <Pagination
                                        data={{
                                            items: paginatedVariables,
                                            pagination: {
                                                currentPage,
                                                totalPages: Math.ceil(data.variables.length / ITEMS_PER_PAGE),
                                                total: data.variables.length,
                                                count: data.variables.length,
                                                perPage: ITEMS_PER_PAGE,
                                            },
                                        }}
                                        onPageSelect={setCurrentPage}
                                    >
                                        {() => <></>}
                                    </Pagination>
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

export default StartupContainer;
