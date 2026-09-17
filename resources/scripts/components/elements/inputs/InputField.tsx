import { cn } from '@/lib/utils';
import { forwardRef } from 'react';

enum Variant {
    Normal,
    Snug,
    Loose,
}

interface InputFieldProps extends React.ComponentProps<'input'> {
    variant?: Variant;
}

const Component = forwardRef<HTMLInputElement, InputFieldProps>(({ className, variant, ...props }, ref) => (
    <input
        ref={ref}
        className={cn('w-full rounded-lg bg-white/5 px-4 py-2 text-foreground outline-none ring-1 ring-border focus:ring-1 focus:ring-ring', {
            'px-6 py-3': variant === Variant.Loose,
        }, className)}
        {...props}
    />
));

const InputField = Object.assign(Component, { Variants: Variant });

Component.displayName = 'InputField';

export default InputField;
