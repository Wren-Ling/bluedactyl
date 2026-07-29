import { ExternalLink, Server } from 'lucide-react';
import { useStoreState } from 'easy-peasy';
import { useTranslation } from 'react-i18next';
import isEqual from 'react-fast-compare';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import Can from '@/components/elements/Can';
import CopyOnClick from '@/components/elements/CopyOnClick';
import Label from '@/components/elements/Label';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import ReinstallServerBox from '@/components/server/settings/ReinstallServerBox';

import { ip } from '@/lib/formatters';

import { ServerContext } from '@/state/server';

import RenameServerBox from './RenameServerBox';

const SettingsContainer = () => {
    const { t } = useTranslation('settings');
    const username = useStoreState((state) => state.user.data!.username);
    const id = ServerContext.useStoreState((state) => state.server.data!.id);
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const node = ServerContext.useStoreState((state) => state.server.data!.node);
    const sftp = ServerContext.useStoreState((state) => state.server.data!.sftpDetails, isEqual);

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <FlashMessageRender byKey={'settings'} />
            <MainPageHeader direction='column' title={t('title')}>
                <p className='text-sm leading-relaxed text-muted-foreground'>
                    {t('description')}
                </p>
            </MainPageHeader>
            <Can action={'settings.rename'}>
                <div className={`mb-6 md:mb-10`}>
                    <RenameServerBox />
                </div>
            </Can>

            <div className='flex h-full w-full flex-col gap-8'>
                <Can action={'settings.reinstall'}>
                    <ReinstallServerBox />
                </Can>
                <Card>
                    <CardHeader>
                        <CardTitle className='text-xl font-extrabold tracking-tight'>{t('debug_info_title')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className={`flex items-center justify-between text-sm`}>
                            <p className='text-muted-foreground'>{t('node')}</p>
                            <code className={`rounded-sm bg-muted px-2 py-1 font-mono text-foreground`}>{node}</code>
                        </div>
                        <CopyOnClick text={uuid}>
                            <div className={`mt-2 flex items-center justify-between text-sm`}>
                                <p className='text-muted-foreground'>{t('server_id')}</p>
                                <code className={`rounded-sm bg-muted px-2 py-1 font-mono text-foreground`}>{uuid}</code>
                            </div>
                        </CopyOnClick>
                    </CardContent>
                </Card>
                <Can action={'file.sftp'}>
                    <Card className={'mb-6 md:mb-10'}>
                        <CardHeader>
                            <CardTitle className='text-xl font-extrabold tracking-tight'>{t('sftp_details_title')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className={`flex items-center justify-between text-sm`}>
                                <Label className='text-muted-foreground'>{t('server_address')}</Label>
                                <CopyOnClick text={`sftp://${ip(sftp.ip)}:${sftp.port}`}>
                                    <code className={`rounded-sm bg-muted px-2 py-1 font-mono text-foreground`}>
                                        {`sftp://${ip(sftp.ip)}:${sftp.port}`}
                                    </code>
                                </CopyOnClick>
                            </div>
                            <div className={`mt-2 flex items-center justify-between text-sm`}>
                                <Label className='text-muted-foreground'>{t('username')}</Label>
                                <CopyOnClick text={`${username}.${id}`}>
                                    <code className={`rounded-sm bg-muted px-2 py-1 font-mono text-foreground`}>
                                        {`${username}.${id}`}
                                    </code>
                                </CopyOnClick>
                            </div>
                            <div className={`mt-6 flex items-center`}>
                                <div className={`flex-1`}>
                                    <div className={`border-l-4 border-primary p-3`}>
                                        <p className={`text-xs text-muted-foreground`}>
                                            {t('sftp_password_note')}
                                        </p>
                                    </div>
                                </div>
                                <div className={`ml-4`}>
                                    <a href={`sftp://${username}.${id}@${ip(sftp.ip)}:${sftp.port}`}>
                                        <Button variant='outline'>
                                            <ExternalLink className='mr-2 size-4' />
                                            {t('launch_sftp')}
                                        </Button>
                                    </a>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </Can>
            </div>
        </div>
    );
};

export default SettingsContainer;
