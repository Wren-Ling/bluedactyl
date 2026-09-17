import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import Spinner from '@/components/elements/Spinner';

import { Dialog, RenderDialogProps } from './';

type ConfirmationProps = Omit<RenderDialogProps, 'description' | 'children'> & {
    children: React.ReactNode;
    confirm?: string | undefined;
    loading?: boolean;
    onConfirmed: (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => void;
};

const ConfirmationDialog = ({ confirm, children, onConfirmed, loading, ...props }: ConfirmationProps) => {
    const { t } = useTranslation();

    return (
        <Dialog {...props} description={typeof children === 'string' ? children : undefined}>
            {typeof children !== 'string' && children}
            <Dialog.Footer>
                <Button variant='outline' onClick={props.onClose}>
                    {t('common:cancel')}
                </Button>
                <Button variant='destructive' onClick={onConfirmed} disabled={loading}>
                    <div className='flex items-center gap-2'>
                        {loading && <Spinner size='small' />}
                        <span>{confirm || t('common:okay')}</span>
                    </div>
                </Button>
            </Dialog.Footer>
        </Dialog>
    );
};

export default ConfirmationDialog;
