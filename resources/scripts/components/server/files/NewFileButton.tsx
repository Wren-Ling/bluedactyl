import { useTranslation } from 'react-i18next';
import { NavLink } from 'react-router-dom';

import { buttonVariants } from '@/components/ui/button';

const NewFileButton = ({ id }: { id: string }) => {
    const { t } = useTranslation();

    return (
        <NavLink
            to={`/server/${id}/files/new${window.location.hash}`}
            className={buttonVariants({ variant: 'secondary', size: 'sm' })}
        >
            {t('files:new_file')}
        </NavLink>
    );
};

export default NewFileButton;
