import { Pencil } from 'lucide-react';
import { useStoreState } from 'easy-peasy';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';

import { Button } from '@/components/ui/button';
import Can from '@/components/elements/Can';
import { PageListItem } from '@/components/elements/pages/PageList';
import RemoveSubuserButton from '@/components/server/users/RemoveSubuserButton';

import { ServerContext } from '@/state/server';
import { Subuser } from '@/state/server/subusers';

interface Props {
    subuser: Subuser;
}

const UserRow = ({ subuser }: Props) => {
    const { t } = useTranslation('users');
    const uuid = useStoreState((state) => state.user!.data!.uuid);
    const navigate = useNavigate();
    const serverId = ServerContext.useStoreState((state) => state.server.data!.id);

    const handleEditClick = () => {
        navigate(`/server/${serverId}/users/${subuser.uuid}/edit`);
    };

    return (
        <PageListItem>
            <div className={`w-10 h-10 rounded-full bg-white border-2 border-border overflow-hidden hidden md:block`}>
                <img className={`w-full h-full`} src={`${subuser.image}?s=400`} />
            </div>
            <div className={`sm:ml-4 flex-1 overflow-hidden flex flex-col`}>
                <p className={`truncate text-lg`}>{subuser.email}</p>
                <p className={`mt-1 md:mt-0 text-xs text-muted-foreground truncate sm:text-left text-center`}>
                    {subuser.twoFactorEnabled ? t('mfa_enabled') : t('mfa_disabled')}
                </p>
            </div>

            <div className='flex flex-col items-center md:gap-12 gap-4 sm:flex-row'>
                <div>
                    <p className={`font-medium text-center`}>
                        {subuser.permissions.filter((permission) => permission !== 'websocket.connect').length}
                    </p>
                    <p className={`text-xs text-foreground0 uppercase`}>{t('permissions')}</p>
                </div>
                {subuser.uuid !== uuid && (
                    <>
                        <div className='flex align-middle items-center justify-center gap-2'>
                            <Can action={'user.update'}>
                                <Button
                                    variant='secondary'
                                    size='sm'
                                    className='flex items-center gap-2'
                                    onClick={handleEditClick}
                                    aria-label={t('edit_subuser_aria')}
                                >
                                    <Pencil size={22} />
                                    {t('edit')}
                                </Button>
                            </Can>
                            <Can action={'user.delete'}>
                                <RemoveSubuserButton subuser={subuser} />
                            </Can>
                        </div>
                    </>
                )}
            </div>
        </PageListItem>
    );
};

export default UserRow;
