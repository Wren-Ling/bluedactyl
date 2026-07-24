import { cn } from '@/lib/utils';

import Spinner from '@/components/elements/Spinner';

import FadeTransition from './transitions/FadeTransition';

const InputSpinner = ({ visible, children }: { visible: boolean; children: React.ReactNode }) => (
    <div className={cn('relative', visible && '[&_select]:appearance-none')}>
        <FadeTransition
            className='relative'
            show={visible}
            duration='duration-150'
            appear
            unmount
        >
            <div className={`absolute right-0 h-full flex items-center justify-end pr-3`}>
                <Spinner size='small' />
            </div>
        </FadeTransition>
        {children}
    </div>
);

export default InputSpinner;
