import { Button } from '@/components/ui/button';
import Spinner from '@/components/elements/Spinner';

import { Dialog, RenderDialogProps } from './';

type ConfirmationProps = Omit<RenderDialogProps, 'description' | 'children'> & {
    children: React.ReactNode;
    confirm?: string | undefined;
    loading?: boolean;
    onConfirmed: (e: React.MouseEvent<HTMLButtonElement, MouseEvent>) => void;
};

const ConfirmationDialog = ({ confirm = 'Okay', children, onConfirmed, loading, ...props }: ConfirmationProps) => {
    return (
        <Dialog {...props} description={typeof children === 'string' ? children : undefined}>
            {typeof children !== 'string' && children}
            <Dialog.Footer>
                <Button variant='outline' onClick={props.onClose}>
                    Cancel
                </Button>
                <Button variant='destructive' onClick={onConfirmed} disabled={loading}>
                    <div className='flex items-center gap-2'>
                        {loading && <Spinner size='small' />}
                        <span>{confirm}</span>
                    </div>
                </Button>
            </Dialog.Footer>
        </Dialog>
    );
};

export default ConfirmationDialog;
