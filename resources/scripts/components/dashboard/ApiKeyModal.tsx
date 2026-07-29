import { useContext } from 'react';
import { useTranslation } from 'react-i18next';

import FlashMessageRender from '@/components/FlashMessageRender';
import CopyOnClick from '@/components/elements/CopyOnClick';
import { Button } from '@/components/ui/button';
import ModalContext from '@/context/ModalContext';

import asModal from '@/hoc/asModal';

interface Props {
    apiKey: string;
}

const ApiKeyModal = ({ apiKey }: Props) => {
    const { t } = useTranslation();
    const { dismiss } = useContext(ModalContext);

    return (
        <div className='mx-auto max-w-lg space-y-6 rounded-lg p-6 shadow-lg'>
            <FlashMessageRender byKey='account' />

            <p className='mt-2 text-sm text-muted-foreground'>
                {t('account:api_key_modal_description')}
            </p>

            <div className='relative mt-6'>
                <pre className='overflow-x-auto rounded-lg bg-muted p-4 font-mono text-foreground'>
                    <CopyOnClick text={apiKey}>
                        <code className='break-words text-sm'>{apiKey}</code>
                    </CopyOnClick>
                </pre>
            </div>

            <div className='flex justify-end space-x-4'>
                <Button type='button' onClick={() => dismiss()} variant='destructive'>
                    {t('account:close')}
                </Button>
            </div>
        </div>
    );
};

ApiKeyModal.displayName = 'ApiKeyModal';

export default asModal<any>({
    title: 'Your API Key',
    closeOnEscape: true,
    closeOnBackground: true,
})(ApiKeyModal);
