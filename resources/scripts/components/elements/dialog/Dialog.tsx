import { Dialog as BaseDialog } from '@base-ui/react/dialog';
import { X } from 'lucide-react';
import { AnimatePresence, motion } from 'motion/react';
import { useRef, useState } from 'react';

import { DialogContext, IconPosition, RenderDialogProps } from './';

const variants = {
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

const Dialog = ({
    open,
    title,
    description,
    onClose,
    hideCloseIcon,
    preventExternalClose,
    children,
}: RenderDialogProps) => {
    const container = useRef<HTMLDivElement>(null);
    const [icon, setIcon] = useState<React.ReactNode>();
    const [footer, setFooter] = useState<React.ReactNode>();
    const [iconPosition, setIconPosition] = useState<IconPosition>('title');
    const [down, setDown] = useState(false);

    const onContainerClick = (down: boolean, e: React.MouseEvent<HTMLDivElement>): void => {
        if (e.target instanceof HTMLElement && container.current?.isSameNode(e.target)) {
            setDown(down);
        }
    };

    const onDialogClose = (): void => {
        if (!preventExternalClose) {
            return onClose();
        }
    };

    return (
        <BaseDialog.Root open={open} onOpenChange={(isOpen) => !isOpen && onDialogClose()}>
            <AnimatePresence>
                {open && (
                    <DialogContext.Provider value={{ setIcon, setFooter, setIconPosition }}>
                        <BaseDialog.Portal>
                            <BaseDialog.Backdrop
                                as={motion.div}
                                initial={{ opacity: 0 }}
                                animate={{ opacity: 1 }}
                                exit={{ opacity: 0 }}
                                transition={{ duration: 0.15 }}
                                className='fixed inset-0 z-9997 bg-black/60 backdrop-blur-xs'
                            />
                            <div className='fixed inset-0 overflow-y-auto z-9998'>
                                <div
                                    ref={container}
                                    className='flex min-h-full items-center justify-center p-4 text-center'
                                    onMouseDown={onContainerClick.bind(this, true)}
                                    onMouseUp={onContainerClick.bind(this, false)}
                                >
                                    <BaseDialog.Popup
                                        as={motion.div}
                                        initial={'closed'}
                                        animate={down ? 'bounce' : 'open'}
                                        exit={'closed'}
                                        variants={variants}
                                        className='relative mx-auto w-full max-w-xl rounded-xl border border-white/10 bg-background text-left shadow-2xl'
                                    >
                                        <div className='flex p-6 pb-0 overflow-y-auto'>
                                            {iconPosition === 'container' && icon}
                                            <div className='flex-1 max-h-[70vh] min-w-0'>
                                                <div className='flex items-center'>
                                                    {iconPosition !== 'container' && icon}
                                                    <div>
                                                        {title && (
                                                            <h2 className='mb-2 pr-4 text-2xl font-extrabold tracking-tight'>
                                                                {title}
                                                            </h2>
                                                        )}
                                                        {description && <p>{description}</p>}
                                                    </div>
                                                </div>
                                                {children}
                                                <div className='invisible h-6' />
                                            </div>
                                        </div>
                                        {footer}
                                        {!hideCloseIcon && (
                                            <div className='absolute right-0 top-0 m-4 p-2 opacity-45 hover:opacity-100'>
                                                <button onClick={onClose} className='cursor-pointer'>
                                                    <X className='size-5' />
                                                </button>
                                            </div>
                                        )}
                                    </BaseDialog.Popup>
                                </div>
                            </div>
                        </BaseDialog.Portal>
                    </DialogContext.Provider>
                )}
            </AnimatePresence>
        </BaseDialog.Root>
    );
};

export default Dialog;
