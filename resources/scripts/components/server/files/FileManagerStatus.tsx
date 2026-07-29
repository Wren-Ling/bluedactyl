import { useTranslation } from 'react-i18next';
import { X } from 'lucide-react';
import { useEffect, useState } from 'react';

import { Button } from '@/components/ui/button';
import Code from '@/components/elements/Code';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

import { ServerContext } from '@/state/server';

// TODO: Make it more pretty
const CircleProgress = ({ progress, className }: { progress: number; className?: string }) => {
    const radius = 12;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (progress / 100) * circumference;

    return (
        <svg className={className} viewBox='0 0 32 32'>
            <circle
                stroke='currentColor'
                strokeWidth='4'
                fill='transparent'
                r={radius}
                cx='16'
                cy='16'
                className='opacity-25'
            />
            <circle
                className='transition-all duration-300'
                stroke='currentColor'
                strokeWidth='4'
                strokeLinecap='round'
                fill='transparent'
                r={radius}
                cx='16'
                cy='16'
                strokeDasharray={circumference}
                strokeDashoffset={offset}
                transform='rotate(-90 16 16)'
            />
        </svg>
    );
};

const FileUploadList = ({ onClose }: { onClose: () => void }) => {
    const { t } = useTranslation();

    const cancelFileUpload = ServerContext.useStoreActions((actions) => actions.files.cancelFileUpload);
    const clearFileUploads = ServerContext.useStoreActions((actions) => actions.files.clearFileUploads);
    const uploads = ServerContext.useStoreState((state) =>
        Object.entries(state.files.uploads).sort(([a], [b]) => a.localeCompare(b)),
    );

    return (
        <TooltipProvider>
            <div className={'space-y-2 mt-6'}>
                {uploads.map(([name, file]) => (
                    <div key={name} className={'flex items-center space-x-3 bg-card p-3 rounded-sm border border-border'}>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <div className={'shrink-0'}>
                                    <CircleProgress progress={(file.loaded / file.total) * 100} className={'w-6 h-6'} />
                                </div>
                            </TooltipTrigger>
                            <TooltipContent side='left' sideOffset={5}>
                                {`${Math.floor((file.loaded / file.total) * 100)}%`}
                            </TooltipContent>
                        </Tooltip>
                        <Code className={'flex-1 truncate'}>{name}</Code>
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button
                                    variant='secondary'
                                    size='sm'
                                    onClick={cancelFileUpload.bind(this, name)}
                                    className='hover:!text-red-400'
                                >
                                    <X size={16} />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent side='right' sideOffset={5}>
                                {t('files:cancel')}
                            </TooltipContent>
                        </Tooltip>
                    </div>
                ))}
            </div>
            <DialogFooter>
                <Button variant='destructive' onClick={() => clearFileUploads()}>
                    {t('files:cancel_uploads')}
                </Button>
                <Button variant='secondary' onClick={onClose}>
                    {t('files:close')}
                </Button>
            </DialogFooter>
        </TooltipProvider>
    );
};

const FileManagerStatus = () => {
    const { t } = useTranslation();

    const [open, setOpen] = useState(false);

    const count = ServerContext.useStoreState((state) => Object.keys(state.files.uploads).length);

    useEffect(() => {
        if (count === 0) {
            setOpen(false);
        }
    }, [count]);

    return (
        <>
            <TooltipProvider>
                {count > 0 && (
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Button
                                variant='secondary'
                                size='sm'
                                className='w-10 h-10 p-0'
                                onClick={() => {
                                    setOpen(true);
                                }}
                            >
                                <svg
                                    className='animate-spin h-5 w-5 text-foreground'
                                    xmlns='http://www.w3.org/2000/svg'
                                    fill='none'
                                    viewBox='0 0 24 24'
                                >
                                    <circle
                                        className='opacity-25'
                                        cx='12'
                                        cy='12'
                                        r='10'
                                        stroke='currentColor'
                                        strokeWidth='4'
                                    ></circle>
                                    <path
                                        className='opacity-75'
                                        fill='currentColor'
                                        d='M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z'
                                    ></path>
                                </svg>
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side='top' sideOffset={5}>
                            {t('files:files_uploading', { count })}
                        </TooltipContent>
                    </Tooltip>
                )}
                <Dialog open={open} onOpenChange={setOpen}>
                    <DialogContent>
                        <DialogHeader><DialogTitle>{t('files:file_uploads_title')}</DialogTitle></DialogHeader>
                        <p className='text-sm text-muted-foreground'>{t('files:file_uploads_description')}</p>
                        <FileUploadList onClose={() => setOpen(false)} />
                    </DialogContent>
                </Dialog>
            </TooltipProvider>
        </>
    );
};

export default FileManagerStatus;
