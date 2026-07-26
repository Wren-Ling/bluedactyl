import { Plus, Users } from 'lucide-react';
import { Actions, useStoreActions, useStoreState } from 'easy-peasy';
import { For } from 'million/react';
import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import Can from '@/components/elements/Can';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import { PageListContainer } from '@/components/elements/pages/PageList';
import UserRow from '@/components/server/users/UserRow';

import { httpErrorToHuman } from '@/api/http';
import getServerSubusers from '@/api/server/users/getServerSubusers';

import { ApplicationStore } from '@/state';
import { ServerContext } from '@/state/server';

const UsersContainer = () => {
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const serverId = ServerContext.useStoreState((state) => state.server.data!.id);
    const subusers = ServerContext.useStoreState((state) => state.subusers.data);
    const setSubusers = ServerContext.useStoreActions((actions) => actions.subusers.setSubusers);

    const permissions = useStoreState((state: ApplicationStore) => state.permissions.data);
    const getPermissions = useStoreActions((actions: Actions<ApplicationStore>) => actions.permissions.getPermissions);
    const { addError, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    useEffect(() => {
        clearFlashes('users');
        getServerSubusers(uuid)
            .then((subusers) => {
                setSubusers(subusers);
                setLoading(false);
            })
            .catch((error) => {
                console.error(error);
                addError({ key: 'users', message: httpErrorToHuman(error) });
            });
    }, []);

    useEffect(() => {
        getPermissions().catch((error) => {
            addError({ key: 'users', message: httpErrorToHuman(error) });
            console.error(error);
        });
    }, []);

    const pageLayout = (content: React.ReactNode) => (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <FlashMessageRender byKey={'users'} />
            {content}
        </div>
    );

    if (!subusers.length && (loading || !Object.keys(permissions).length)) {
        return pageLayout(
            <>
                <MainPageHeader
                    direction='column'
                    title={'Users'}
                    titleChildren={
                        <div className='flex flex-col items-center justify-end gap-4 sm:flex-row'>
                            <p className='text-center text-sm text-muted-foreground sm:text-right'>0 users</p>
                            <Can action={'user.create'}>
                                <Button
                                    variant='default'
                                    onClick={() => navigate(`/server/${serverId}/users/new`)}
                                    className='flex items-center gap-2'
                                >
                                    <Plus className='size-4' />
                                    New User
                                </Button>
                            </Can>
                        </div>
                    }
                >
                    <p className='text-sm leading-relaxed text-muted-foreground'>
                        Manage user access to your server. Grant specific permissions to other users to help you manage
                        and maintain your server.
                    </p>
                </MainPageHeader>
                <div className='flex items-center justify-center py-12'>
                    <div className='size-8 animate-spin rounded-full border-b-2 border-primary' />
                </div>
            </>
        );
    }

    return pageLayout(
        <>
            <MainPageHeader
                direction='column'
                title={'Users'}
                titleChildren={
                    <div className='flex flex-col items-center justify-end gap-4 sm:flex-row'>
                        <p className='text-center text-sm text-muted-foreground sm:text-right'>{subusers.length} users</p>
                        <Can action={'user.create'}>
                            <Button
                                variant='default'
                                onClick={() => navigate(`/server/${serverId}/users/new`)}
                                className='flex items-center gap-2'
                            >
                                <Plus className='size-4' />
                                New User
                            </Button>
                        </Can>
                    </div>
                }
            >
                <p className='text-sm leading-relaxed text-muted-foreground'>
                    Manage user access to your server. Grant specific permissions to other users to help you manage and
                    maintain your server.
                </p>
            </MainPageHeader>
            {!subusers.length ? (
                <div className='flex min-h-[60vh] flex-col items-center justify-center px-4 py-12'>
                    <div className='text-center'>
                        <div className='mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-muted'>
                            <Users className='size-8 text-muted-foreground' />
                        </div>
                        <h3 className='mb-2 text-lg font-medium text-foreground'>No users found</h3>
                        <p className='max-w-sm text-sm text-muted-foreground'>
                            Your server does not have any additional users. Add others to help you manage your server.
                        </p>
                    </div>
                </div>
            ) : (
                <PageListContainer data-pyro-users-container-users>
                    <For each={subusers} memo>
                        {(subuser) => <UserRow key={subuser.uuid} subuser={subuser} />}
                    </For>
                </PageListContainer>
            )}
        </>
    );
};

export default UsersContainer;
