import { ArrowLeft, Users } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate, useParams } from 'react-router-dom';

import { Button } from '@/components/ui/button';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import UserFormComponent from '@/components/server/users/UserFormComponent';

import { ServerContext } from '@/state/server';
import { Subuser } from '@/state/server/subusers';

const EditUserContainer = () => {
    const { t } = useTranslation('users');
    const navigate = useNavigate();
    const { id } = useParams<{ id: string }>();
    const [isSubmitting, setIsSubmitting] = useState(false);

    const serverId = ServerContext.useStoreState((state) => state.server.data!.id);
    const subusers = ServerContext.useStoreState((state) => state.subusers.data);

    const subuser = subusers.find((s: Subuser) => s.uuid === id);

    useEffect(() => {
        if (!subuser && subusers.length > 0) {
            navigate(`/server/${serverId}/users`);
        }
    }, [subuser, subusers, navigate, serverId]);

    const handleSuccess = () => {
        navigate(`/server/${serverId}/users`);
    };

    const handleCancel = () => {
        navigate(`/server/${serverId}/users`);
    };

    if (!subuser && subusers.length === 0) {
        return (
            <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
                <MainPageHeader title={t('edit_title_short')}>
                    <Button
                        variant='outline'
                        onClick={() => navigate(`/server/${serverId}/users`)}
                        className='flex items-center gap-2'
                    >
                        <ArrowLeft className='size-4' />
                        {t('back_to_users')}
                    </Button>
                </MainPageHeader>
                <div className='flex items-center justify-center py-12'>
                    <div className='size-8 animate-spin rounded-full border-b-2 border-primary' />
                </div>
            </div>
        );
    }

    if (!subuser) {
        return (
            <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
                <MainPageHeader title={t('edit_title_short')}>
                    <Button
                        variant='outline'
                        onClick={() => navigate(`/server/${serverId}/users`)}
                        className='flex items-center gap-2'
                    >
                        <ArrowLeft className='size-4' />
                        {t('back_to_users')}
                    </Button>
                </MainPageHeader>
                <div className='flex flex-col items-center justify-center px-4 py-12'>
                    <div className='text-center'>
                        <div className='mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-muted'>
                            <Users className='size-8 text-muted-foreground' />
                        </div>
                        <h3 className='mb-2 text-lg font-medium text-foreground'>{t('user_not_found')}</h3>
                        <p className='max-w-sm text-sm text-muted-foreground'>
                            {t('user_not_found_description')}
                        </p>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <MainPageHeader title={t('edit_title', { email: subuser.email })}>
                <Button
                    variant='outline'
                    onClick={() => navigate(`/server/${serverId}/users`)}
                    className='flex items-center gap-2'
                    disabled={isSubmitting}
                >
                    <ArrowLeft className='size-4' />
                    {t('back_to_users')}
                </Button>
            </MainPageHeader>

            <UserFormComponent
                subuser={subuser}
                onSuccess={handleSuccess}
                onCancel={handleCancel}
                flashKey='user:edit'
                isSubmitting={isSubmitting}
                setIsSubmitting={setIsSubmitting}
            />
        </div>
    );
};

export default EditUserContainer;
