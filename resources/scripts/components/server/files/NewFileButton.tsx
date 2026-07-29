import { useTranslation } from 'react-i18next';

import { buttonVariants } from '@/components/ui/button';

const NewFileButton = ({ id }: { id: string }) => {
    const { t } = useTranslation();

    return (
        <a
            href={`/server/${id}/files/new${window.location.hash}`}
            className={buttonVariants({ variant: 'secondary' })}
        >
            {t('files:new_file')}
        </a>
    );
};

export default NewFileButton;
