import Code from './elements/Code';

export type FlashMessageType = 'success' | 'info' | 'warning' | 'error';

interface Props {
    title?: string;
    children: string;
    type?: FlashMessageType;
}

const MessageBox = ({ title, children, type }: Props) => (
    <div className='flex flex-col gap-2 bg-black border-[2px] border-foreground/20 p-4 rounded-2xl mb-4' role={'alert'}>
        {title && <h2 className='font-bold text-xl'>{title}</h2>}
        <Code>{children}</Code>
    </div>
);
MessageBox.displayName = 'MessageBox';

export default MessageBox;
