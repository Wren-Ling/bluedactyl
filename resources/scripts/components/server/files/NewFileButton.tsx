import { useTranslation } from 'react-i18next';
import { NavLink } from 'react-router-dom';

import { Button } from '@/components/ui/button';

const NewFileButton = ({ id }: { id: string }) => {
    const { t } = useTranslation();

    return (
        <NavLink to={`/server/${id}/files/new${window.location.hash}`}>
            <Button variant='secondary'>
                {t('files:new_file')}
            </Button>
        </NavLink>
    );
};

export default NewFileButton;
