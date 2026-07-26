import { cn } from '@/lib/utils';

type Props = Omit<React.ComponentProps<'input'>, 'type'> & {
    label?: string;
    inputField?: boolean;
};

const CheckBox = ({ className, label, inputField, ...props }: Props) => (
    <div className={cn('flex items-center', className)}>
        <input
            type='checkbox'
            className='size-4 accent-foreground'
            {...props}
        />
        {label && <label className='ml-2 text-sm text-foreground'>{label}</label>}
        {inputField && <input type='text' className='ml-2 border-foreground' />}
    </div>
);

export default CheckBox;
