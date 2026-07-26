import { ArrowLeft } from 'lucide-react';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

import { Button } from '@/components/ui/button';
import { MainPageHeader } from '@/components/elements/MainPageHeader';
import UserFormComponent from '@/components/server/users/UserFormComponent';

import { ServerContext } from '@/state/server';

const CreateUserContainer = () => {
    const navigate = useNavigate();
    const [isSubmitting, setIsSubmitting] = useState(false);

    const serverId = ServerContext.useStoreState((state) => state.server.data!.id);

    const handleSuccess = () => {
        navigate(`/server/${serverId}/users`);
    };

    const handleCancel = () => {
        navigate(`/server/${serverId}/users`);
    };

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <MainPageHeader title={'Create New User'}>
                <Button
                    variant='outline'
                    onClick={() => navigate(`/server/${serverId}/users`)}
                    className='flex items-center gap-2'
                    disabled={isSubmitting}
                >
                    <ArrowLeft className='size-4' />
                    Back to Users
                </Button>
            </MainPageHeader>

            <UserFormComponent
                onSuccess={handleSuccess}
                onCancel={handleCancel}
                flashKey='user:create'
                isSubmitting={isSubmitting}
                setIsSubmitting={setIsSubmitting}
            />
        </div>
    );
};

export default CreateUserContainer;
