import { Dialog as HDialog } from '@headlessui/react';
import { X } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useMemo, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';
import Spinner from '@/components/elements/Spinner';
import { DialogContext, IconPosition } from '@/components/elements/dialog';

export interface RequiredModalProps {
    visible: boolean;
    onDismissed: () => void;
    appear?: boolean;
    top?: boolean;
    children?: React.ReactNode;
}

export interface ModalProps extends RequiredModalProps {
    title?: string;
    closeButton?: boolean;
    dismissable?: boolean;
    closeOnEscape?: boolean;
    closeOnBackground?: boolean;
    showSpinnerOverlay?: boolean;
}

const variantAnimations = {
    open: {
        scale: 1,
        opacity: 1,
        transition: {
            type: 'spring',
            damping: 20,
            stiffness: 300,
            duration: 0.15,
        },
    },
    closed: {
        scale: 0.75,
        opacity: 0,
        transition: {
            type: 'easeIn',
            duration: 0.15,
        },
    },
    bounce: {
        scale: 0.95,
        opacity: 1,
        transition: { type: 'linear', duration: 0.075 },
    },
};

const Modal: React.FC<ModalProps> = ({
    title,
    visible,
    dismissable = true,
    closeButton,
    showSpinnerOverlay,
    onDismissed,
    children,
}) => {
    const isDismissable = useMemo(() => {
        return dismissable && !showSpinnerOverlay;
    }, [dismissable, showSpinnerOverlay]);

    const { t } = useTranslation();
    const container = useRef<HTMLDivElement>(null);
    const [icon, setIcon] = useState<React.ReactNode>();
    const [_, setFooter] = useState<React.ReactNode>();
    const [iconPosition, setIconPosition] = useState<IconPosition>('title');
    const [down, setDown] = useState(false);

    const onContainerClick = (down: boolean, e: React.MouseEvent<HTMLDivElement>): void => {
        if (e.target instanceof HTMLElement && container.current?.isSameNode(e.target)) {
            setDown(down);
        }
    };

    const onDialogClose = (): void => {
        if (isDismissable) {
            return onDismissed();
        }
    };

    return (
        <>
            {showSpinnerOverlay && (
                <div className='fixed inset-0 w-full h-full rounded-sm flex items-center justify-center bg-background/80 z-9999'>
                    <Spinner />
                </div>
            )}
            <AnimatePresence>
                {visible && (
                    <DialogContext.Provider value={{ setIcon, setFooter, setIconPosition }}>
                        <HDialog
                            static
                            as={motion.div}
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            transition={{ duration: 0.15 }}
                            open={visible}
                            onClose={onDialogClose}
                        >
                            <div className='fixed inset-0 z-9997 bg-black/50' />
                            <div className='fixed inset-0 overflow-y-auto z-9998'>
                                <div
                                    ref={container}
                                    className='flex min-h-full items-center justify-center p-4 text-center'
                                    onMouseDown={onContainerClick.bind(this, true)}
                                    onMouseUp={onContainerClick.bind(this, false)}
                                >
                                    <HDialog.Panel
                                        as={motion.div}
                                        initial={'closed'}
                                        animate={down ? 'bounce' : 'open'}
                                        exit={'closed'}
                                        variants={variantAnimations}
                                        className='relative mx-auto w-full max-w-xl rounded-xl border border-border text-left shadow-lg bg-background'
                                    >
                                        <div className='flex justify-between items-center m-6'>
                                            {title && <h2 className='text-2xl text-foreground'>{title}</h2>}
                                            {dismissable && (
                                                <button
                                                    onClick={onDismissed}
                                                    className='opacity-45 hover:opacity-100 p-6 -m-6 cursor-pointer'
                                                >
                                                    <X className='size-5' />
                                                </button>
                                            )}
                                        </div>
                                        <div className='flex px-6 overflow-y-auto'>
                                            {iconPosition === 'container' && icon}
                                            <div className='flex-1 max-h-[70vh] min-w-0'>
                                                <div className='flex items-center'>
                                                    {iconPosition !== 'container' && icon}
                                                    {children}
                                                </div>
                                                {closeButton && (
                                                    <div className='my-6 sm:flex items-center justify-end'>
                                                        <Button onClick={onDismissed} className='w-full'>
                                                            {t('common:close')}
                                                        </Button>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    </HDialog.Panel>
                                </div>
                            </div>
                        </HDialog>
                    </DialogContext.Provider>
                )}
            </AnimatePresence>
        </>
    );
};

export default Modal;
