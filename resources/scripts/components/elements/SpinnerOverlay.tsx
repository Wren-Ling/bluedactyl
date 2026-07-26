import { cn } from '@/lib/utils';

import Spinner, { SpinnerSize } from '@/components/elements/Spinner';

interface Props {
    visible: boolean;
    fixed?: boolean;
    size?: SpinnerSize;
    backgroundOpacity?: number;
    children?: React.ReactNode;
}

const SpinnerOverlay: React.FC<Props> = ({ visible, fixed, size, backgroundOpacity = 50 }) => {
    if (!visible) return null;

    return (
        <div
            className={cn(
                'inset-0 z-50 flex items-center justify-center',
                fixed ? 'fixed' : 'absolute',
            )}
            style={{ backgroundColor: `rgba(0, 0, 0, ${backgroundOpacity / 100})` }}
        >
            <Spinner size={size} />
        </div>
    );
};

export default SpinnerOverlay;
