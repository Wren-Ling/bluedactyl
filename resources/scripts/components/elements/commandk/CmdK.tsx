import {
    Box,
    CloudUpload,
    Database,
    FolderOpen,
    GitBranch,
    History,
    House,
    PencilLine,
    Power,
    Settings,
    Terminal,
    Users,
} from 'lucide-react';
import { Command } from 'cmdk';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { toast } from 'sonner';

import Can from '@/components/elements/Can';

import { ServerContext } from '@/state/server';

const CommandMenu = () => {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);
    const id = ServerContext.useStoreState((state) => state.server.data?.id);
    const navigate = useNavigate();
    // controls server power status
    const status = ServerContext.useStoreState((state) => state.status.value);
    const instance = ServerContext.useStoreState((state) => state.socket.instance);

    const cmdkPowerAction = (action: string) => {
        if (instance) {
            if (action === 'start') {
                toast.success(t('common:server_starting'));
            } else if (action === 'restart') {
                toast.success(t('common:server_restarting'));
            } else {
                toast.success(t('common:server_stopping'));
            }
            setOpen(false);
            instance.send('set state', action === 'kill-confirmed' ? 'kill' : action);
        }
    };

    const cmdkNavigate = (url: string) => {
        navigate('/server/' + id + url);
        setOpen(false);
    };

    useEffect(() => {
        const down = (e) => {
            if (e.key === 'k' && (e.metaKey || e.ctrlKey)) {
                e.preventDefault();
                setOpen((open) => !open);
            }
        };

        document.addEventListener('keydown', down);
        return () => document.removeEventListener('keydown', down);
    }, []);

    return (
        <Command.Dialog open={open} onOpenChange={setOpen} label={t('common:global_command_menu')}>
            <Command.Input />
            <Command.List>
                <Command.Empty>{t('common:no_results_found')}</Command.Empty>

                <Command.Group heading={t('common:pages')}>
                    <Command.Item onSelect={() => cmdkNavigate('')}>
                        <House />
                        {t('common:home')}
                    </Command.Item>
                    <Can action={'file.*'} matchAny>
                        <Command.Item onSelect={() => cmdkNavigate('/files')}>
                            <FolderOpen />
                            {t('common:files')}
                        </Command.Item>
                    </Can>
                    <Can action={'database.*'} matchAny>
                        <Command.Item onSelect={() => cmdkNavigate('/databases')}>
                            <Database />
                            {t('common:databases')}
                        </Command.Item>
                    </Can>
                    <Can action={'backup.*'} matchAny>
                        <Command.Item onSelect={() => cmdkNavigate('/backups')}>
                            <CloudUpload />
                            {t('common:backups')}
                        </Command.Item>
                    </Can>
                    <Can action={'allocation.*'} matchAny>
                        <Command.Item onSelect={() => cmdkNavigate('/network')}>
                            <GitBranch />
                            {t('common:networking')}
                        </Command.Item>
                    </Can>
                    <Can action={'user.*'} matchAny>
                        <Command.Item onSelect={() => cmdkNavigate('/users')}>
                            <Users />
                            {t('common:users')}
                        </Command.Item>
                    </Can>
                    <Can action={['startup.*']} matchAny>
                        <Command.Item onSelect={() => cmdkNavigate('/startup')}>
                            <Terminal />
                            {t('common:startup')}
                        </Command.Item>
                    </Can>
                    <Can action={['schedule.*']} matchAny>
                        <Command.Item onSelect={() => cmdkNavigate('/schedules')}>
                            <History />
                            {t('common:schedules')}
                        </Command.Item>
                    </Can>
                    <Can action={['settings.*', 'file.sftp']} matchAny>
                        <Command.Item onSelect={() => cmdkNavigate('/settings')}>
                            <Settings />
                            {t('common:settings')}
                        </Command.Item>
                    </Can>
                    <Can action={['activity.*']} matchAny>
                        <Command.Item onSelect={() => cmdkNavigate('/activity')}>
                            <PencilLine />
                            {t('common:activity')}
                        </Command.Item>
                    </Can>
                    <Can action={['modrinth.*']} matchAny>
                        <Command.Item onSelect={() => cmdkNavigate('/mods')}>
                            {t('common:download')}
                        </Command.Item>
                    </Can>
                    <Can action={['software.*']} matchAny>
                        <Command.Item onSelect={() => cmdkNavigate('/shell')}>
                            <Box />
                            {t('common:software')}
                        </Command.Item>
                    </Can>
                </Command.Group>
                <Command.Group heading={t('common:server')}>
                    <Can action={'control.start'}>
                        <Command.Item disabled={status !== 'offline'} onSelect={() => cmdkPowerAction('start')}>
                            <Power />
                            {t('common:start_server')}
                        </Command.Item>
                    </Can>
                    <Can action={'control.restart'}>
                        <Command.Item disabled={!status} onSelect={() => cmdkPowerAction('restart')}>
                            <Power />
                            {t('common:restart_server')}
                        </Command.Item>
                    </Can>
                    <Can action={'control.restart'}>
                        <Command.Item disabled={status === 'offline'} onSelect={() => cmdkPowerAction('stop')}>
                            <Power />
                            {t('common:stop_server')}
                        </Command.Item>
                    </Can>
                </Command.Group>
            </Command.List>
        </Command.Dialog>
    );
};

export default CommandMenu;
