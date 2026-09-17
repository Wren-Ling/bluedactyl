import { RotateCcw } from 'lucide-react';
import { Actions, useStoreActions } from 'easy-peasy';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';
import Spinner from '@/components/elements/Spinner';

import { httpErrorToHuman } from '@/api/http';
import { ServerDatabase } from '@/api/server/databases/getServerDatabases';
import rotateDatabasePassword from '@/api/server/databases/rotateDatabasePassword';

import { ApplicationStore } from '@/state';
import { ServerContext } from '@/state/server';

const RotatePasswordButton = ({
    databaseId,
    onUpdate,
}: {
    databaseId: string;
    onUpdate: (database: ServerDatabase) => void;
}) => {
    const [loading, setLoading] = useState(false);
    const { t } = useTranslation('databases');
    const { addFlash, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);
    const server = ServerContext.useStoreState((state) => state.server.data!);

    if (!databaseId) {
        return null;
    }

    const rotate = () => {
        setLoading(true);
        clearFlashes();

        rotateDatabasePassword(server.uuid, databaseId)
            .then((database) => onUpdate(database))
            .catch((error) => {
                console.error(error);
                addFlash({
                    type: 'error',
                    title: t('error'),
                    message: httpErrorToHuman(error),
                    key: 'database-connection-modal',
                });
            })
            .then(() => {
                setTimeout(() => {
                    setLoading(false);
                }, 500);
            });
    };

    return (
        <Button onClick={rotate} className='flex-none'>
            <div className='flex justify-center items-center'>
                {!loading && <RotateCcw size={22} />}
                {loading && <Spinner size={'small'} />}
            </div>
        </Button>
    );
};

export default RotatePasswordButton;
